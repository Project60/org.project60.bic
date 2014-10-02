<?php

require_once 'CRM/Core/Page.php';

class CRM_Bic_Page_BicList extends CRM_Core_Page {
  function run() {
    // Prepares variables for being sent to Smarty
    $countries = CRM_Bic_Parser_Parser::getParserList();
    
    // Get country names
    $country_names = array();
    $id2code = CRM_Core_PseudoConstant::countryIsoCode();
    $code2id = array_flip($id2code);
    $id2country = CRM_Core_PseudoConstant::country(false, false);
    foreach ($countries as $code) {
      $country_id = $code2id[$code];
      $country_name = $id2country[$country_id];
      $country_names[$code] = $country_name;
    }
    
    // Sends variables to Smarty
    $this->assign('countries', $countries);
    $this->assign('country_names', $country_names);
    
    parent::run();
  }
}
