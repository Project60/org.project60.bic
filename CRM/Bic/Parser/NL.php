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

// Include Composer's autoloader file.
require_once __DIR__ . '/../../../vendor/autoload.php';

require_once 'CRM/Bic/Parser/Parser.php';

/**
 * Abstract class defining the basis for national bank info parsers
 */
class CRM_Bic_Parser_NL extends CRM_Bic_Parser_Parser {

  static $page_url = 'https://www.betaalvereniging.nl/wp-content/uploads/BIC-lijst-NL.xlsx';

  static $country_code = 'NL';

  public function update() {
    // First, download the file
    $file_name = sys_get_temp_dir() . '/nl-banks.xls';
    $downloaded_file = $this->downloadFile(CRM_Bic_Parser_NL::$page_url);
    if (empty($downloaded_file)) {
      return $this->createParserOutdatedError(ts("Couldn't download data file"));
    }

    // store file
    file_put_contents($file_name, $downloaded_file);
    unset($downloaded_file);

    // Automatically detect the correct reader to load for this file type
    $excel_reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file_name);

    // Set reader options
    $excel_reader->setReadDataOnly(TRUE);
    $excel_reader->setLoadSheetsOnly(['BIC-lijst']);

    // Read Excel file
    $excel_object = $excel_reader->load($file_name);
    $excel_rows = $excel_object->getActiveSheet()->toArray();

    // Process Excel data
    $skip_lines = 2;
    $banks[] = [];
    foreach ($excel_rows as $excel_row) {
      $skip_lines -= 1;
      if ($skip_lines >= 0) {
        continue;
      }

      // Process row
      $bank = [
        'value' => $excel_row[1],
        'name' => $excel_row[0],
        'label' => $excel_row[2],
        'description' => '',
      ];
      $banks[] = $bank;
    }

    // clean up before importing
    unset($excel_rows);
    unset($excel_object);
    unset($excel_reader);
    unlink($file_name);

    // Finally, update DB
    return $this->updateEntries(CRM_Bic_Parser_NL::$country_code, $banks);
  }

  /**
   *
   * Extracts the National Bank Identifier from an IBAN.
   *
   */
  public function extractNBIDfromIBAN($iban) {
    return [
      substr($iban, 4, 4),
    ];
  }

}
