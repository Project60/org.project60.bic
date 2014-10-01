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

/**
 * Abstract class defining the basis for national bank info parsers
 */
abstract class CRM_Bic_Parser_Parser {

  /**
   * static function to instatiate the country parser
   * 
   * @return a parser object
   */
  public static function getParser($country_code) {
    // TODO: error handling
    $parser_class = 'CRM_Bic_Parser_' . $country_code;
    return new $parser_class();
  }

  /**
   * Triggers the parser instance to prepare a full update
   *
   * @return array(   'count' => nr of banks found
   *                  'error' => in case of an error
   *                  
   */
  public abstract function update();


  /**
   * Will update all entries for a given  
   * 
   * @param  $coutry   ISO country code
   * @param  $entries  a set of array('value'=>national_code, 'title'=>bank_name, 'name'=>BIC);
   */
  protected function updateEntries($country, $entries) {
    // TODO:
  }

  /**
   * Download a file and return as a string
   * 
   * @return file content or NULL on error
   */
  protected function downloadFile($url) {
    // TODO:
  }
}
