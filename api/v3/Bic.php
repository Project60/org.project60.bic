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
function civicrm_api3_bic_update($params) {
  if (empty($params['country'])) {
    return civicrm_api3_create_error("No country given");
  }

  $countries = array();
  if ($params['country']=='all') {
    $countries = CRM_Bic_Parser_Parser::getParserList();
  } else {
    $countries[] = $params['country'];
  }

  // now, loop through the given countries
  $result = array();
  $total_count =0;
  foreach ($countries as $country) {
    $parser = CRM_Bic_Parser_Parser::getParser($country);
    if (empty($parser)) {
      return civicrm_api3_create_error("Parser for '$country' not found!");
    }
    
    // and execute update for each
    // TODO: process errors
    $result[$country] = $parser->update();
    $total_count += $result[$country]['count'];
  }

  // TODO: remove for release...
  error_log(print_r($result,1));

  $null = NULL;
  return civicrm_api3_create_success($result, $params, $null, $null, $null, array('total_count' => $total_count));
}


/**
 * API call get stats about the stored banks
 *
 * @return a array of item_count per country
 */
function civicrm_api3_bic_stats($params) {
  try {
    $option_group = civicrm_api3('OptionGroup', 'getsingle', array('name' => 'bank_list'));
  } catch (Exception $e) {
    return civicrm_api3_create_error("OptionGroup not found. Reinstall extension!");
  }

  $option_group_id = (int) $option_group['id'];
  if (empty($option_group_id)) {
    return civicrm_api3_create_error("OptionGroup not found. Reinstall extension!");
  }

  $query = "
  SELECT
   LEFT(value, 2) AS country_code,
   COUNT(value)   AS count
  FROM
   civicrm_option_value
  WHERE 
   option_group_id = $option_group_id
  GROUP BY country_code;
  ";
  $result = array();
  $query_result = CRM_Core_DAO::executeQuery($query);
  while ($query_result->fetch()) {
    $result[$query_result->country_code] = (int) $query_result->count;
  }

  return civicrm_api3_create_success($result, $params);
}
