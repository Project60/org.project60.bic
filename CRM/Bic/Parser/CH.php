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

declare(strict_types = 1);

/**
 * Abstract class defining the basis for national bank info parsers
 */
class CRM_Bic_Parser_CH extends CRM_Bic_Parser_Parser {

  public static string $page_url = 'https://api.six-group.com/api/epcd/bankmaster/v3/bankmaster_V3.csv';

  public static string $country_code = 'CH';

  public function update() {
    // First, download the file
    $file_name = sys_get_temp_dir() . '/CH-banks.csv';
    $downloaded_file = $this->downloadFile(CRM_Bic_Parser_CH::$page_url);

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
      if (empty($data[14])) {
        continue;
      }

      // Process row
      $bank = [
        'value' => $data[0],
        'name' => $data[14],
        'label' => $data[8],
        'description' => $data[8],
      ];

      $banks[] = $bank;
    }

    fclose($handle);
    unlink($file_name);

    // Finally, update DB
    return $this->updateEntries(CRM_Bic_Parser_CH::$country_code, $banks);
  }

  /**
   * Extracts the National Bank Identifier from an IBAN.
   */
  public function extractNBIDfromIBAN($iban) {
    return [
      substr($iban, 4, 5),
    ];
  }

}
