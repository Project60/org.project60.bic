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
class CRM_Bic_Parser_DE extends CRM_Bic_Parser_Parser {

  protected static $base_url = 'https://www.bundesbank.de/de/aufgaben/unbarer-zahlungsverkehr/serviceangebot/bankleitzahlen/download---bankleitzahlen-602592';
  protected static $page_url = 'https://www.bundesbank.de/resource/blob/602702/b6f18dadd412af2b8f47fac0cd8a8dd4/mL/blz-neu-txt-data.txt';

  public function update() {
    // first, download the page
    $banks = array();
    $data = $this->downloadFile(CRM_Bic_Parser_DE::$page_url);
    if (empty($data)) {
      return $this->createParserOutdatedError(ts("Couldn't download data set"));
    }

    $lines = preg_split('/\n/', $data);

    foreach ($lines as $line) {
      $bc_code = substr($line, 0, 8);
      $bic     = trim(substr($line, 139, 11));
      $name    = trim(substr($line, 107, 27));
      $address = substr($line, 67, 5) . ' ' . trim(substr($line, 72, 35));

      // we only want branches with BICs
      if (empty($bic)) continue;

      $name    = mb_convert_encoding($name,'UTF-8', 'ISO-8859-1');
      $address = mb_convert_encoding($address,'UTF-8', 'ISO-8859-1');

      $banks[$bc_code] = array(
          'value'       => $bc_code,
          'name'        => $bic,
          'label'       => $name,
          'description' => $address,
      );
    }

    // some cleanup
    unset($lines);

    // finally, update DB
    return $this->updateEntries('DE', $banks);
  }
  /*
   * Extracts the National Bank Identifier from an IBAN.
   */
  public function extractNBIDfromIBAN($iban) {
    return array(
      substr($iban, 4, 8)
    );
  }

}
