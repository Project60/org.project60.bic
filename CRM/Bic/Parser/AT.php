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

require_once 'CRM/Bic/Parser/Parser.php';

/**
 * Abstract class defining the basis for national bank info parsers
 */
class CRM_Bic_Parser_AT extends CRM_Bic_Parser_Parser {

  static $page_url = 'https://www.oenb.at/docroot/downloads_observ/sepa-zv-vz_gesamt.csv';

  public function update() {
    // first, download the page
    $raw = $this->downloadFile(CRM_Bic_Parser_AT::$page_url);
    $data = preg_split('/\r\n|\r|\n/', $raw);
    if (empty($data)) {
      return $this->createParserOutdatedError(E::ts("Couldn't download source "));
    }

    $banks   = [];
    $headers = NULL;

    // iterate CSV records
    foreach ($data as $line) {
      // process line
      $line = iconv('ISO-8859-15', 'UTF-8', $line);
      $fields = str_getcsv($line, ';');

      // skip some stuff: preamble
      if (count($fields) < 20) {
        continue;
      }

      if ($headers === NULL) {
        // first 'real' line should be header
        $headers = $fields;

        if (!in_array('Identnummer', $headers)
           || !in_array('Bankleitzahl', $headers)
           || !in_array('SWIFT-Code', $headers)) {
          return $this->createParserOutdatedError(E::ts("Source file doesn't contain Identnummer/Bankleitzahl/SWIFT-Code"));
        }
      }
      else {
        // parse line
        $data_set = array_combine($headers, $fields);

        // we will only process banks with a BIC/SWIFT Code
        if (empty($data_set['Identnummer'])
            || empty($data_set['Bankleitzahl'])
            || empty($data_set['SWIFT-Code'])
        ) {
          continue;
        }

        // compile data set
        $banks[$data_set['Bankleitzahl']] = [
          'value'       => $data_set['Bankleitzahl'],
          'name'        => $data_set['SWIFT-Code'],
          'label'       => $data_set['Bankenname'] ?? 'Unknown',
          'description' => $this->getDescription($data_set),
        ];
      }
    }

    // finally, update DB
    return $this->updateEntries('AT', $banks);
  }

  /**
   * Extract the description part from the data set
   *
   * @param array $data_set
   *   the data set for this bank
   *
   * @return string
   *   the description
   */
  protected function getDescription($data_set) {
    $description = '';
    if (!empty($data_set['Straße'])) {
      $description .= $data_set['Straße'] . ',';
    }
    if (!empty($data_set['PLZ'])) {
      $description .= ' ' . $data_set['PLZ'];
    }
    if (!empty($data_set['Ort'])) {
      $description .= ' ' . $data_set['Ort'];
    }
    return $description;
  }

  /**
   *
   * Extracts the National Bank Identifier from an Austrian IBAN.
   *
   */
  public function extractNBIDfromIBAN($iban) {
    return [
      substr($iban, 4, 5),
    ];
  }

}
