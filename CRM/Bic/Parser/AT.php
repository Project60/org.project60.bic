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

  static $page_url = 'http://www.conserio.at/bankleitzahl/';
  static $regex = '#<tr>[\n]<td>[\t](?P<NBIT>[0-9]{5})[\t]</td>[\n]<td>[\t](?P<BIC>[A-Z]{6,6}[A-Z2-9][A-NP-Z0-9]([A-Z0-9]{3,3}){0,1})[\t]</td>[\n]<td>[\t](?P<name>.*)[\t]</td>[\n]</tr>#';

  public function update() {
    // first, download the page
    $data = $this->downloadFile(CRM_Bic_Parser_AT::$page_url);
    if (empty($data)) {
      return $this->createError("Couldn't download page. Please contact us.");
    }

    // convert to UTF8
    $data = mb_convert_encoding($data, 'UTF-8');

    // parse the lines and build up data structure
    $matches = array();
    $count = preg_match_all(CRM_Bic_Parser_AT::$regex, $data, $matches);
    if (empty($count)) {
      return $this->createError("Couldn't find any bank information in the data source. Please try and update this org.project60.bic extension. If the problem persists, please contact us.");
    }

    // process matches
    for ($i=0; $i<$count; $i++) {
      $key = $matches[1][$i];
      $banks[$key] = array(
        'value'       => $key,
        'name'        => $matches[2][$i],
        'label'       => $matches[4][$i],
        'description' => '',
        );
    }
    unset($matches);
    
    // // finally, update DB
    return $this->updateEntries('AT', $banks);
  }
}
