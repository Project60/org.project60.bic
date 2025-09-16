<?php
/*-------------------------------------------------------+
| Project 60 - Little BIC extension                      |
| Copyright (C) 2014                                     |
| Author: B. Endres (endres -at- systopia.de)            |
| http://www.systopia.de/                                |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/

use CRM_Bic_ExtensionUtil as E;

/**
 * Abstract class defining the basis for national bank info parsers
 */
abstract class CRM_Bic_Parser_Parser {

  /**
   * static function to instatiate the country parser
   *
   * @return a parser object
   */
  public static function getParser($country_code) {
    // since class_exists() also causes fatal errors, we'll just try to find the .php file
    $parser_implementation = dirname(__FILE__) . DIRECTORY_SEPARATOR . $country_code . '.php';
    if (file_exists($parser_implementation)) {
      $parser_class = 'CRM_Bic_Parser_' . $country_code;
      return new $parser_class();
    }
    else {
      return NULL;
    }
  }

  /**
   * static function to get all known parsers
   *
   * @return an array of countries
   */
  public static function getParserList() {
    $dir = dirname(__FILE__);
    $iterator = new DirectoryIterator($dir);

    // Iterates through the CRM/Bic/Parser folder looking for country files
    foreach ($iterator as $fileinfo) {
      $file_name = $fileinfo->getFilename();
      $file_name_parts = explode('.', $file_name);

      if ((end($file_name_parts) == 'php') &&
          (strlen(reset($file_name_parts)) == 2) &&
          (reset($file_name_parts) != 'Parser')) {
        $countries[] = reset($file_name_parts);
      }
    }

    return $countries;
  }

  /**
   * Triggers the parser instance to prepare a full update
   *
   * @return array(   'count' => nr of banks found
   *   'error' => in case of an error
   *
   */
  abstract public function update();

  /**
   * Extracts the national bank ID from a given IBAN
   *
   * @return array list of NBIDs to look for
   */
  public function extractNBIDfromIBAN($iban) {
    /*
     * The Wikipedia page about IBAN has a list with details
     * about the structure of IBAN accounts for every
     * SEPA country. You may consider useful reading it
     * to find the information about how override this
     * when adding support for a new country.
     *
     * @link http://en.wikipedia.org/wiki/International_Bank_Account_Number
     */

    return FALSE;
  }

  /**
   * Will update all entries for a given country
   *
   * @param  string $country
   *    ISO country code
   * @param  array $entries
   *    a set of ['value'=>national_code, 'label'=>bank_name, 'name'=>BIC, 'description'=>optional data];
   */
  protected function updateEntries($country, $entries) {
    // if there are no entries given, something probably went wrong.
    // As a result, all existing entries would be deleted. To prevent this, we'll throw an exception
    if (empty($entries)) {
      $error_message = E::ts('No bank entries could be extracted from the source for country <code>%1</code>. It is possible that the source is outdated. If you can rule out network issues, please report this here: https://github.com/Project60/org.project60.bic/issues.', [1 => $country]);
      CRM_Core_Session::setStatus($error_message, E::ts('Problem with %1 Data Source', [1 => $country]), 'error');
      throw new CRM_Core_Exception($error_message);
    }

    try {
      $option_group = civicrm_api3('OptionGroup', 'getsingle', ['name' => 'bank_list']);
      $option_group_id = (int) $option_group['id'];
    }
    catch (Exception $e) {
      return $this->createError('OptionGroup not found. Reinstall extension!');
    }

    // init stats
    $stats = [
      'count_processed'  => 0,
      'count_added'      => 0,
      'count_deleted'    => 0,
      'count_ignored'    => 0,
      'count_updated'    => 0,
    ];

    // get all data sets
    $current_data_query = "
    SELECT
      id, value, name, label, description
    FROM
      civicrm_option_value
    WHERE
      value LIKE '$country%'
    AND
      option_group_id = $option_group_id;";
    $current_data = [];
    $query = CRM_Core_DAO::executeQuery($current_data_query);
    while ($query->fetch()) {
      $current_data[$query->value] = [
        'id'          => $query->id,
        'value'       => $query->value,
        'name'        => $query->name,
        'label'       => $query->label,
        'description' => $query->description,
      ];
    }
    unset($query);

    // iterate through the data sets
    foreach ($entries as $bank) {
      $trimmed_value = trim($bank['value']);
      $trimmed_name = trim($bank['name']);
      if (empty($trimmed_value) || empty($trimmed_name)) {
        $stats['count_ignored'] += 1;
        continue;
      }
      else {
        $stats['count_processed'] += 1;
      }

      // set country prefix
      $bank['value'] = $country . $bank['value'];

      // now compare with the given data
      if (isset($current_data[$bank['value']])) {
        $oldbank = $current_data[$bank['value']];
        // it already exists -> update?
        if ($bank['value'] != $oldbank['value']
            || $bank['name'] != $oldbank['name']
            || $bank['label'] != $oldbank['label']
            || $bank['description'] != $oldbank['description']) {

          // this has changed... UPDATE
          $bank['id'] = $oldbank['id'];
          $bank['option_group_id'] = $option_group_id;
          civicrm_api3('OptionValue', 'create', $bank);
          $stats['count_updated'] += 1;
        }
        unset($current_data[$bank['value']]);
      }
      else {
        // this is new: add new option value
        $bank['option_group_id'] = $option_group_id;
        civicrm_api3('OptionValue', 'create', $bank);
        $stats['count_added'] += 1;
      }
    }

    // finally, delete the remaining (obsolete) banks
    foreach ($current_data as $value => $bank) {
      civicrm_api3('OptionValue', 'delete', $bank);
      $stats['count_deleted'] += 1;
    }

    return $stats;
  }

  /**
   * Download a file and return as a string
   *
   * @return file content or NULL on error
   */
  protected function downloadFile($url) {
    if (substr($url, 0, 1) == '/') {
      // local files we just read
      return file_get_contents($url);
    }

    // on other sources we use CURL
    $ch = curl_init();
    $timeout = 10;
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36');
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

    $data = curl_exec($ch);

    $curl_errno = curl_errno($ch);
    curl_close($ch);

    if (!$curl_errno) {
      return $data;
    }
    else {
      return NULL;
    }
  }

  /**
   * generate a compliant error reply for the updateEntries method
   */
  protected function createError($message) {
    return [
      'count' => 0,
      'error' => $message,
    ];
  }

  /**
   * generate a compliant error reply for the updateEntries method
   */
  protected function createParserOutdatedError($error_message) {
    $message = ts('<p>An error occurred while updating the bank information:<pre>%1</pre></p>', [1 => $error_message]);
    $message .= ts('<p>Please make sure your server is connected to the internet and try again.</p>');
    $message .= '<br/>';
    $message .= ts("<p>If the problem persists, the source file (provided by the bank) might have been changed. In this case the 'Little BIC Extension' needs to be updated. Please check <a href=\"https://github.com/Project60/org.project60.bic/releases\">here</a> for a newer release.</p>");
    $message .= '<br/>';
    $message .= ts("<p>If you are already running the newest version, feel free to contact us via <a href=\"mailto:endres@systopia.de\">email</a> or on <a href=\"https://github.com/Project60/org.project60.bic/issues/new\">GitHub</a>. The sooner we know about this problem, the sooner it'll be fixed.</p>");
    return $this->createError($message);
  }

}
