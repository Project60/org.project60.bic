<?php
/*-------------------------------------------------------+
| Project 60 - Little BIC extension                      |
| Copyright (C) 2014                                     |
| Author: Carlos Capote                                  |
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
require_once 'dependencies/PHPExcel.php';

/**
 * Implements CRM_Bic_Parser_Parser for the Spanish case.
 */
class CRM_Bic_Parser_ES extends CRM_Bic_Parser_Parser {

  static $page_url = 'http://www.bde.es/f/webbde/IFI/servicio/regis/ficheros/es/REGBANESP_CONESTAB_A.XLS';
  static $country_code = 'ES';

  /**
   * Dowloads the Spanish list of banks (from bde.es) and
   * creates/updates/deletes the corresponding BIC records.
   */
  public function update() {
    // First, download the file
    $file_name = sys_get_temp_dir() . '/' . CRM_Bic_Parser_ES::$country_code . '-banks.xls';
    $downloaded_file = $this->downloadFile(CRM_Bic_Parser_ES::$page_url);
    if(!$downloaded_file) {
      return $this->createError("Couldn't download the Spanish list of banks. Please contact us.");
    }

    // Save the downloaded file
    file_put_contents($file_name, $downloaded_file);
    unset($downloaded_file);

    // Automatically detect the correct reader to load for this file type
    $excel_reader = PHPExcel_IOFactory::createReaderForFile($file_name);

    // Set reader options
    $excel_reader->setReadDataOnly();
    $excel_reader->setLoadSheetsOnly(array('ENTIDADES'));

    // Read Excel file
    $excel_object = $excel_reader->load($file_name);
    $excel_rows = $excel_object->getActiveSheet()->toArray();

    // Process Excel data
    $is_header = true;
    $banks[] = array();
    foreach($excel_rows as $excel_row) {
      if($is_header) {
        // Get column Ids from first row
        $column_ids = array();
        foreach($excel_row as $id => $column_name) {
          $column_ids[$column_name] = $id;
        }

        $is_header = false;
      } else {
        // If there's no bank code, I may assume we're at the end of the file
        if(!$excel_row[$column_ids['COD_BE']]) { break; }

        // Add the office code, if it exists
        if($excel_row[$column_ids['CODENTSUC']] == '0000') {
          $value = $excel_row[$column_ids['COD_BE']];
        } else {
          $value = $excel_row[$column_ids['COD_BE']] . $excel_row[$column_ids['CODENTSUC']];
        }

        // If there's a "closed date", we don't import this bank
        if(!$excel_row[$column_ids['FCHBAJA']]) {
          $banks[] = array(
            'value'       => $value,
            'name'        => $excel_row[$column_ids['BIC']],
            'label'       => $excel_row[$column_ids['ANAGRAMA']],
            'description' => '<b>CIF</b>: ' . $excel_row[$column_ids['CODIGOCIF']] . '<br>' .
                             '<b>Phone</b>: ' . $excel_row[$column_ids['TELEFONO']] . '<br>' .
                             '<b>Fax</b>: ' . $excel_row[$column_ids['NUMFAX']] . '<br>' .
                             '<b>Web</b>: <a href="' . strtolower($excel_row[$column_ids['DIRINTERNET']]) . '">' .
                               strtolower($excel_row[$column_ids['DIRINTERNET']]) . '</a><br>',
          );
        }
      }
    }

    // Remove temporary file
    unlink($file_name);

    // Free some memory
    unset($excel_rows);
    unset($excel_object);
    unset($excel_reader);

    // Finally, update DB
    return $this->updateEntries(CRM_Bic_Parser_ES::$country_code, $banks);
  }

  /*
   * Extracts the National Bank Identifier from an Spanish IBAN.
   */
  public function extractNBIDfromIBAN($iban) {
    return array(
      CRM_Bic_Parser_ES::$country_code . substr($iban, 4, 4),
      CRM_Bic_Parser_ES::$country_code . substr($iban, 4, 8)
    );
  }
}
