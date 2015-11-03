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
class CRM_Bic_Parser_AT extends CRM_Bic_Parser_Parser {

  static $page_url = 'http://www.oenb.at/idakilz/kiverzeichnis?action=downloadAllData';

  public function update() {
    // first, download the page
    $data = $this->downloadFile(CRM_Bic_Parser_AT::$page_url);
    if (empty($data)) {
      return $this->createParserOutdatedError(ts("Couldn't download basic page"));
    }

    // store data in temp file
    $tmp_file = tempnam('/tmp', 'org.project60.bic_parserAT_');
    file_put_contents($tmp_file, $data);

    // unzip data
    $zip = new ZipArchive();
    if ($zip->open($tmp_file) !== TRUE) {
      return $this->createParserOutdatedError(ts("Couldn't unzip file"));
    }

    if ($zip->numFiles <= 0) {
      return $this->createParserOutdatedError(ts("Empty zipfile retrieved."));
    }
  
    $file_info = $zip->statIndex(0);
    $csv_data  = $zip->getStream($file_info['name']);
    $header    = NULL;
    $banks     = array();

    // iterate CSV records
    while (($line = fgetcsv($csv_data, 0, ';')) !== FALSE) {
      if ($header === NULL) {
        // FIND HEADER
        if ($line[2] == 'Bankleitzahl') {
          $header = $line;
        } else {
          // pre-header gibberish
          continue;
        }
      } else {
        // PROCESS LINE
        $blz     = $line[2];
        $bic     = $line[19]; // "SWIFT-Code"
        $name    = mb_convert_encoding($line[6], 'UTF-8', 'CP1252');
        $address = $line[7].', '.$line[8].' '.$line[9];
        $address = mb_convert_encoding($address, 'UTF-8', 'CP1252');
          
        // we will only process banks with a BIC/SWIFT Code
        if (empty($blz) || empty($bic)) continue;

        $banks[$blz] = array(
          'value'       => $blz,
          'name'        => $bic,
          'label'       => $name,
          'description' => $address,
          );
      }
    }
    
    // do some cleanup
    fclose($csv_data);
    unlink($tmp_file);

    // finally, update DB
    return $this->updateEntries('AT', $banks);
  }

  /*
   * Extracts the National Bank Identifier from an Austrian IBAN.
   */
  public function extractNBIDfromIBAN($iban) {
    return array(
      substr($iban, 4, 5),
    );
  }  
}
