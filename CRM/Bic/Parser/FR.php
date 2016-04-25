<?php
/*-------------------------------------------------------+
| Project 60 - Little BIC extension                      |
| Copyright (C) 2014                                     |
| French bank information                                |
| Author: scardinius (scardinius -at- chords.pl)         |
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
 * Implementation of abstract class defining the basis for national bank info parsers, French banks
 */
class CRM_Bic_Parser_FR extends CRM_Bic_Parser_Parser {

  // temporary source
  static $page_url = '../../../data/fr-bic-codes.csv';

  public function update() {
    // first, download the page, it's a CSV file, so more convenient not to use built in method
    $lines = file(dirname(__FILE__).'/'.self::$page_url);
    if (empty($lines)) {
      return $this->createParserOutdatedError(ts("Couldn't download CSV file"));
    }

    $data = array();
    $count = 0;
    foreach ($lines as $line) {
      $data[] = str_getcsv($line, ";", '"');
      $count++;
    }
    unset($lines);

    if (empty($count)) {
      return $this->createParserOutdatedError(ts("Couldn't find any bank information in the data source"));
    }

    // process lines instead of first (header)
    for ($i = 1; $i < $count; $i++) {
      $key = $data[$i][1];
      $banks[$key] = array(
        'value' => $key,
        'name' => $data[$i][0],
        'label' => $data[$i][0],
        'description' => '',
      );
    }
    unset($data);

    // finally, update DB
    return $this->updateEntries('FR', $banks);
  }

  /*
   * Extracts the National Bank Identifier from an IBAN.
   * In Poland it's 8 digits after checksum
   */
  public function extractNBIDfromIBAN($iban) {
    return array(
      substr($iban, 4, 8)
    );
  }
}
