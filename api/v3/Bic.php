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
 * API call to look up BIC codes for a given IBAN
 * 
 * @param 'iban'  an IBAN number
 */
function civicrm_api3_bic_getfromiban($params) {
  if (empty($params['iban'])) {
    return civicrm_api3_create_error("You need to provied an 'iban'.");
  }

  $country = strtoupper(substr($params['iban'], 0, 2));
  $parser = CRM_Bic_Parser_Parser::getParser($country);
  if (empty($parser)) {
    return civicrm_api3_create_error("Parser for '$country' not found!");
  }

  $nbids = $parser->extractNBIDfromIBAN($params['iban']);
  if ($nbids==FALSE) {
    return civicrm_api3_create_error("IBAN parsing not supported for country '$country'!");
  }

  foreach ($nbids as $nbid) {
    try {
      return civicrm_api3('Bic', 'getsingle', array('country' => $country, 'nbid' => $nbid));
    } catch (Exception $e) {
      // not found? no problem, just keep looking
    }
  }

  return civicrm_api3_create_error("BIC for the given IBAN not found.");
}


/**
 * API call to look up BIC codes or national IDs
 * 
 * You can either provide the BIC in the 'bic' parameter,
 * or you would have to give the ISO country code in 'country'
 * along with the national bank ID in 'nbid'
 *
 */
function civicrm_api3_bic_get($params) {
  $query = array();
  if (!empty($params['bic'])) {
    $query['name'] = $params['bic'];
  } elseif (!empty($params['country']) && !empty($params['nbid'])) {
    $query['value'] = $params['country'] . $params['nbid'];
  } else {
    return civicrm_api3_create_error("You have to either provide 'bic' or 'country'+'nbid' parameters.");
  }

  try {
    $option_group = civicrm_api3('OptionGroup', 'getsingle', array('name' => 'bank_list'));
    $query['option_group_id'] = $option_group['id'];
  } catch (Exception $e) {
    return civicrm_api3_create_error("OptionGroup not found. Reinstall extension!");
  }

  try {
    $data = array();
    $option_values = civicrm_api3('OptionValue', 'get', $query);
    foreach ($option_values['values'] as $key => $value) {
      $data[$key] = array(
        'bic'         => $value['name'],
        'country'     => substr($value['value'], 0, 2),
        'nbid'        => substr($value['value'], 2),
        'description' => $value['description'],
        'title'       => $value['label']
        );
    }
  } catch (Exception $e) {
    return civicrm_api3_create_error("Entity does not exist.");
  }
  
  return civicrm_api3_create_success($data, $params);
}


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
