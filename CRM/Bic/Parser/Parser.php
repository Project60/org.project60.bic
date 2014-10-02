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
    // TODO: error handling
    $parser_class = 'CRM_Bic_Parser_' . $country_code;
    return new $parser_class();
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
    $countries[] = array();
    foreach ($iterator as $fileinfo) {
      $file_name = $fileinfo->getFilename();
      $file_name_parts = explode(".", $file_name);

      if ((end($file_name_parts) == "php") && (reset($file_name_parts) != "Parser")) {
        $countries[] = reset($file_name_parts);
      }
    }

    return $countries;
  }



  /**
   * Triggers the parser instance to prepare a full update
   *
   * @return array(   'count' => nr of banks found
   *                  'error' => in case of an error
   *                  
   */
  public abstract function update();


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
   * Will update all entries for a given  
   * 
   * @param  $coutry   ISO country code
   * @param  $entries  a set of array('value'=>national_code, 'label'=>bank_name, 'name'=>BIC, 'description'=>optional data);
   */
  protected function updateEntries($country, $entries) {
    try {
      $option_group = civicrm_api3('OptionGroup', 'getsingle', array('name' => 'bank_list'));
      $option_group_id = (int) $option_group['id'];
    } catch (Exception $e) {
      return $this->createError("OptionGroup not found. Reinstall extension!");
    }

    // init stats
    $stats = array(
      'count_processed'  => 0,
      'count_added'      => 0,
      'count_deleted'    => 0,
      'count_ignored'    => 0,
      'count_updated'    => 0);

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
    $current_data = array();
    $query = CRM_Core_DAO::executeQuery($current_data_query);
    while ($query->fetch()) {
      $current_data[$query->value] = array(
        'id'          => $query->id,
        'value'       => $query->value,
        'name'        => $query->name,
        'label'       => $query->label,
        'description' => $query->description
        );
    }
    unset($query);

    // iterate through the data sets
    foreach ($entries as $bank) {
      $trimmed_value = trim($bank['value']);
      $trimmed_name = trim($bank['name']);
      if (empty($trimmed_value) || empty($trimmed_name)) {
        $stats['count_ignored'] += 1;
        continue;
      } else {
        $stats['count_processed'] += 1;
      }

      // set country prefix
      $bank['value'] = $country . $bank['value'];

      // now compare with the given data
      if (isset($current_data[$bank['value']])) {
        $oldbank = $current_data[$bank['value']];
        // it already exists -> update?
        if (   $bank['value']       != $oldbank['value']
            || $bank['name']        != $oldbank['name']
            || $bank['label']       != $oldbank['label']
            || $bank['description'] != $oldbank['description']) {
          
          // this has changed... UPDATE
          $bank['id'] = $oldbank['id'];
          $bank['option_group_id'] = $option_group_id;
          civicrm_api3('OptionValue', 'create', $bank);
          $stats['count_updated'] += 1;
        }  
        unset($current_data[$bank['value']]);
      } else {
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
    $ch = curl_init();
    $timeout = 10;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $data = curl_exec($ch);
    curl_close($ch);

    if (!curl_errno($ch)) { 
       return $data;
    } else {
      return NULL;
    }
  }

  /**
   * generate a compliant error reply for the updateEntries method
   */
  protected function createError($message) {
    return array(
      'count' => 0,
      'error' => $message
      );
  }
}
