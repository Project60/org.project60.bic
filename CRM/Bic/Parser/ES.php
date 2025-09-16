<?php
/*-------------------------------------------------------+
| Project 60 - Little BIC extension                      |
| Copyright (C) 2021                                     |
| Author: iXiam Global Solutions (info -at- ixiam.com)   |
| http://www.ixiam.com/                                  |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/

declare(strict_types = 1);

/**
 * Implements CRM_Bic_Parser_Parser for the Spanish case.
 */
class CRM_Bic_Parser_ES extends CRM_Bic_Parser_Parser {

  public static $page_url = 'https://raw.githubusercontent.com/ixiam/sepa_bic/main/ES/bic.csv';

  public function update() {
    // first, download the page
    $raw = $this->downloadFile(CRM_Bic_Parser_ES::$page_url);
    $data = preg_split('/\r\n|\r|\n/', $raw);
    if (empty($data)) {
      return $this->createParserOutdatedError(ts("Couldn't download basic page"));
    }
    $header    = NULL;
    $banks     = [];

    // iterate CSV records
    foreach ($data as $line) {
      if ($header === NULL) {
        // first line is header
        $header = $line;
      }
      else {
        // PROCESS LINE
        $fields = $this->csvLineToArray($line);
        $name = $fields[0];
        $code = $fields[1];
        $bic  = $fields[2];

        // we will only process banks with a BIC/SWIFT Code
        if (empty($code) || empty($bic)) {
          continue;
        }

        $banks[$code] = [
          'value'       => $code,
          'name'        => $bic,
          'label'       => $name,
          'description' => $name,
        ];
      }
    }

    // finally, update DB
    return $this->updateEntries('ES', $banks);
  }

  /**
   * Extracts the National Bank Identifier from an Spanish IBAN.
   */
  public function extractNBIDfromIBAN($iban) {
    return [
      substr($iban, 4, 4),
      substr($iban, 4, 8),
    ];
  }

  /**
   * Returns csv line as array
   */
  private function csvLineToArray($str) {
    $expr = '/,(?=(?:[^"]*"[^"]*")*(?![^"]*"))/';
    $results = preg_split($expr, trim($str));
    return preg_replace('/^"(.*)"$/', '$1', $results);
  }

}
