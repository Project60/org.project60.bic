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

  static $page_url = 'https://www.bundesbank.de/resource/blob/602630/1b5138f22cc648106b16a29ecc3117c1/mL/blz-aktuell-xls-data.xlsx';

  static $country_code = 'DE';

  public function update() {
    // First, download the file
    $file_name = sys_get_temp_dir() . '/DE-banks.xlsx';
    $downloaded_file = $this->downloadFile(CRM_Bic_Parser_DE::$page_url);

    if (empty($downloaded_file)) {
      return $this->createParserOutdatedError(ts("Couldn't download data file"));
    }

    // store file
    file_put_contents($file_name, $downloaded_file);
    unset($downloaded_file);

    // Automatically detect the correct reader to load for this file type
    $excel_reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file_name);

    // Set reader options
    $excel_reader->setReadDataOnly(true);
    $excel_reader->setLoadSheetsOnly(["Daten"]);

    // Read Excel file
    $excel_object = $excel_reader->load($file_name);
    $excel_rows = $excel_object->getActiveSheet()->toArray();

    // Process Excel data
    //$skip_lines = 1;
    $banks[] = array();
    foreach($excel_rows as $excel_row) {
      // skip entries with no bic
      if (empty($excel_row[7])) continue;
      if ($excel_row[1] != 1) continue;

      // Process row
      $bank = array(
        'value' => $excel_row[0],
        'name' => $excel_row[7],
        'label' => $excel_row[5],
        'description' => $excel_row[3] . ' ' . $excel_row[4]
      );
      $banks[] = $bank;
    }

    // clean up before importing
    unset($excel_rows);
    unset($excel_object);
    unset($excel_reader);
    unlink($file_name);

    // Finally, update DB
    return $this->updateEntries(CRM_Bic_Parser_DE::$country_code, $banks);
  }

  /*
   * Extracts the National Bank Identifier from an IBAN.
   */
  public function extractNBIDfromIBAN($iban) {
    return array(
      substr($iban, 4, 8),
    );
  }

}
