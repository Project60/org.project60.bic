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

require_once 'CRM/Bic/Parser/Parser.php';

/**
 * Abstract class defining the basis for national bank info parsers
 */
class CRM_Bic_Parser_DE extends CRM_Bic_Parser_Parser {

  static $base_url = 'http://www.bundesbank.de';
  static $page_url = 'http://www.bundesbank.de/Redaktion/DE/Standardartikel/Aufgaben/Unbarer_Zahlungsverkehr/bankleitzahlen_download.html';
  static $link_regex = '#href="(?P<link>/Redaktion/DE/Downloads/Aufgaben/Unbarer_Zahlungsverkehr/Bankleitzahlen/.*.txt.*[?]__blob=publicationFile)"#';


  public function update() {
    // first, download the page
    $page = $this->downloadFile(CRM_Bic_Parser_DE::$page_url);
    if (empty($page)) {
      return $this->createParserOutdatedError(ts("Couldn't download basic page"));
    }

    // now, find the download link
    $match = array();
    if (!preg_match(CRM_Bic_Parser_DE::$link_regex, $page, $match)) {
      return $this->createParserOutdatedError(ts("The information source at www.bundesbank.de has changed"));
    }

    // finally, download the data file
    unset($page); // save some memory
    $data_url = CRM_Bic_Parser_DE::$base_url.$match['link'];
    $data = $this->downloadFile($data_url);

    // convert to UTF8
    $data = mb_convert_encoding($data, 'UTF-8', 'ISO-8859-1');

    if (empty($data)) {
      return $this->createParserOutdatedError(ts("Couldn't download basic page"));
    }

    // parse the lines and build up data structure
    $banks = array();
    $lines = explode(PHP_EOL, $data);
    unset($data); // save some memory
    foreach ($lines as $line) {
      $key = trim(substr($line, 0, 8));
      if (isset($banks[$key])) continue;
      $banks[$key] = array(
        'value'       => $key,
        'name'        => trim(mb_substr($line, 139, 11)),
        'label'       => rtrim(mb_substr($line, 9, 58)),
        'description' => mb_substr($line, 67, 5) . ' ' . rtrim(mb_substr($line, 72, 35))
        );
    }
    unset($lines);
    
    // finally, update DB
    return $this->updateEntries('DE', $banks);
  }

  /*
   * Extracts the National Bank Identifier from an IBAN.
   */
  public function extractNBIDfromIBAN($iban) {
    return array(
      substr($iban, 4, 8)
    );
  }

}
