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
require_once 'dependencies/PHPExcel.php';

/**
 * Abstract class defining the basis for national bank info parsers
 */
class CRM_Bic_Parser_ES extends CRM_Bic_Parser_Parser {

  static $page_url = 'http://www.bde.es/f/webbde/IFI/servicio/regis/ficheros/es/REGBANESP_CONESTAB_A.XLS';
  static $country_code = 'ES';

  public function update() {
    // First, download the file
    $file_name = sys_get_temp_dir() . '/es-banks.xls';
    $downloaded_file = $this->downloadFile(CRM_Bic_Parser_DE::$page_url);
    file_put_contents($file_name, $downloaded_file);

    // Automatically detect the correct reader to load for this file type
    $excel_reader = PHPExcel_IOFactory::createReaderForFile($file_name);

    // Set reader options
    $excel_reader->setReadDataOnly();
    $excel_reader->setLoadSheetsOnly(array("ENTIDADES"));

    // Read Excel file
    //$excel_object = $excel_reader->load($file_name);
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
        // Process every row
        $banks[] = array(
          'value' => $excel_row[$column_ids["BIC"]],
          'name' => $this->country_code . $excel_row[$column_ids["COD_BE"]],
          'label' => $excel_row[$column_ids["NOMBRE105"]],
          'description' => 'CIF: ' . $excel_row[$column_ids["CODIGOCIF"]],
        );
      }
    }

    // Finally, update DB
    return $this->updateEntries($this->country_code, $banks);
  }
}
