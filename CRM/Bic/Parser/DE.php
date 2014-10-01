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

  static $base_url = 'http://www.bundesbank.de';
  static $page_url = 'http://www.bundesbank.de/Redaktion/DE/Standardartikel/Aufgaben/Unbarer_Zahlungsverkehr/bankleitzahlen_download.html';
  static $link_regex = '#href="(?P<link>/Redaktion/DE/Downloads/Aufgaben/Unbarer_Zahlungsverkehr/Bankleitzahlen/.*txt[?]__blob=publicationFile)"#';


  public function update() {
    // first, download the page
    $page = $this->downloadFile(CRM_Bic_Parser_DE::$page_url);
    if (empty($page)) {
      return $this->createError("Couldn't download basic page. You either have no internect connection, or the extension is outdated. In this case, please contact us.");
    }

    // now, find the download link
    $match = array();
    if (!preg_match(CRM_Bic_Parser_DE::$link_regex, $page, $match)) {
      return $this->createError("The information source at www.bundesbank.de has changed, and the extension is outdated. Please contact us.");
    }

    // finally, download the data file
    unset($page); // save some memory
    $data_url = CRM_Bic_Parser_DE::$base_url.$match['link'];
    $data = $this->downloadFile($data_url);

    // convert to UTF8
    $data = mb_convert_encoding($data, 'UTF-8', 'ISO-8859-1');

    if (empty($data)) {
      return $this->createError("Couldn't download basic page. Please contact us.");
    }

    // parse the lines and build up data structure
    $banks = array();
    $lines = explode(PHP_EOL, $data);
    unset($data); // save some memory
    foreach ($lines as $line) {
      $key = trim(substr($line, 0, 9));
      $banks[$key] = array(
        'value'       => $key,
        'name'        => trim(substr($line, 140, 11)),
        'label'       => trim(substr($line, 9, 58)),
        'description' => substr($line, 67, 5).' '.trim(substr($line, 72, 35))
        );
    }
    unset($lines);
    
    // finally, update DB
    return $this->updateEntries('DE', $banks);
  }
}
