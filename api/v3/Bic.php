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
 * API call to update the stored bank data
 *
 * @param 'country'   country code to update or 'all'
 */
function civicrm_api3_bic_bla($params) {
  error_log("HERE");
  if (empty($params['country'])) {
    return civicrm_api3_create_error("No country given");
  }

  $countries = array();
  if ($params['country']=='all') {
    // TODO: get the list from somewhere
    $countries[] = 'DE';
    $countries[] = 'ES';

  } else {
    $countries[] = $params['country'];
  }

  // now, loop through the given countries
  $result = array('values' => array(), 'total_count' => 0);
  foreach ($countries as $country) {
    $parser = CRM_Bic_Parser_Parser::getParser($country);
    if (empty($parser)) {
      return civicrm_api3_create_error("Parser for '$country' not found!");
    }
    
    // and execute update for each
    $country_result = $parser->update();
    $result['values'][$country] = $country_result;
    $result['total_count'] += $country_result['count'];
  }

  return civicrm_api3_create_success($result);
}
