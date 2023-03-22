<?php
/*-------------------------------------------------------+
| Project 60 - Little BIC extension                      |
| Copyright (C) 2014                                     |
| Polish bank information
| Author: Michał Mach (michal -at- civicrm.org)          |
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
 * Implementation of abstract class defining the basis for national bank info parsers, Polish banks
 */
class CRM_Bic_Parser_PL extends CRM_Bic_Parser_Parser {

  // official source of Polish National Bank
  static $page_url = 'https://ewib.nbp.pl/plewibnra?dokNazwa=plewibnra.txt';

  public function update() {
    // first, download the page, it's a CSV file, so more convenient not to use built in method
    $lines = file(CRM_Bic_Parser_PL::$page_url);
    if (empty($lines)) {
      return $this->createParserOutdatedError(ts("Couldn't download CSV file"));
    }

    $data = array();
    $count = 0;

    foreach( $lines as $line ) {
      // convert to UTF8 - original encoding is IBM Latin 2 (CP852)
      $line = iconv('CP852', 'UTF-8', $line );
      // it's tab delimited file with a lot of extra whitespace - splitting, trimming
      $data[] = array_map('trim', str_getcsv($line, "\t"));
      $count++;
    }

    unset($lines);

    if (empty($count)) {
      return $this->createParserOutdatedError(ts("Couldn't find any bank information in the data source"));
    }

    // process lines
    for ($i=0; $i<$count; $i++) {
      $key = $data[$i][4];
      $banks[$key] = array(
        'value'       => $key,
        'name'        => $data[$i][20],
        'label'       => $data[$i][1] . ', ' . $data[$i][5],
        'description' => '',
        );
    }

    unset($data);

    // finally, update DB
    return $this->updateEntries('PL', $banks);
  }

  /*
   * Extracts the National Bank Identifier from an IBAN.
   * In Poland it's 8 digits after checksum
   */
  public function extractNBIDfromIBAN($iban) {
    return array(
      substr($iban, 4, 8)
    );
  }
}
