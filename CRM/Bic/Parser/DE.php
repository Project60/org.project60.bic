<?php
/*-------------------------------------------------------+
| Project 60 - Little BIC extension                      |
| Copyright (C) 2019                                     |
| Author: D. Sieber (detlev.sieber -at- civiservice.de)  |
| based on code from B. Endres                           |
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

// Include Composer's autoloader file.
require_once __DIR__ . '/../../../vendor/autoload.php';

require_once 'CRM/Bic/Parser/Parser.php';

/**
 * Abstract class defining the basis for national bank info parsers
 */
class CRM_Bic_Parser_DE extends CRM_Bic_Parser_Parser {

  static $page_url = 'https://www.bundesbank.de/resource/blob/926192/07561adcd024c5752c46afa317d554af/mL/blz-aktuell-csv-data.csv';

  static $country_code = 'DE';

  public function update() {
    // First, download the file
    $file_name = sys_get_temp_dir() . '/DE-banks.csv';
    $downloaded_file = $this->downloadFile(CRM_Bic_Parser_DE::$page_url);

    if (empty($downloaded_file)) {
      return $this->createParserOutdatedError(ts("Couldn't download data file"));
    }

    // store file
    file_put_contents($file_name, $downloaded_file);
    unset($downloaded_file);

    // Open and read CSV file
    if (($handle = fopen($file_name, 'r')) === FALSE) {
      return $this->createParserOutdatedError(ts("Couldn't open data file"));
    }

    // Skip header row
    fgetcsv($handle, 1000, ';');

    $banks = [];
    while (($data = fgetcsv($handle, 1000, ';')) !== FALSE) {
      // skip entries with no bic
      if (empty($data[7])) {
        continue;
      }
      if ($data[1] != 1) {
        continue;
      }

      // Process row
      $bank = [
        'value' => $data[0],
        'name' => mb_convert_encoding($data[7], 'UTF-8', 'ISO-8859-1'),
        'label' => mb_convert_encoding($data[5], 'UTF-8', 'ISO-8859-1'),
        'description' => mb_convert_encoding($data[3], 'UTF-8', 'ISO-8859-1') . ' '
        . mb_convert_encoding($data[4], 'UTF-8', 'ISO-8859-1'),
      ];

      $banks[] = $bank;
    }

    fclose($handle);
    unlink($file_name);

    // Finally, update DB
    return $this->updateEntries(CRM_Bic_Parser_DE::$country_code, $banks);
  }

  /**
   *
   * Extracts the National Bank Identifier from an IBAN.
   *
   */
  public function extractNBIDfromIBAN($iban) {
    return [
      substr($iban, 4, 8),
    ];
  }

}
