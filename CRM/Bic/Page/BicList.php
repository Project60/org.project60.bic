<?php

declare(strict_types = 1);

use CRM_Bic_ExtensionUtil as E;

class CRM_Bic_Page_BicList extends CRM_Core_Page {

  public function run() {
    // Prepares variables for being sent to Smarty

    CRM_Utils_System::setTitle(E::ts('Find Banks'));

    //Only show countries with attached information
    $countries = NULL;
    $stats = civicrm_api3('Bic', 'stats');
    foreach ($stats['values'] as $country => $count) {
      $countries[] = $country;
    }

    // Get country names
    $country_names = NULL;
    if ($countries) {

      $config = CRM_Core_Config::singleton();

      $country_names = [];
      $id2code = CRM_Core_PseudoConstant::countryIsoCode();
      $default_country = $id2code[$config->defaultContactCountry] ?? '';
      $code2id = array_flip($id2code);
      $id2country = CRM_Core_PseudoConstant::country(FALSE, FALSE);
      foreach ($countries as $code) {
        $country_id = $code2id[$code];
        $country_name = $id2country[$country_id];
        $country_names[$code] = $country_name;
      }
    }

    // Sends variables to Smarty
    $this->assign('countries', $countries);
    $this->assign('country_names', $country_names);
    $this->assign('default_country', $default_country);
    $this->assign('show_message', TRUE);

    parent::run();
  }

}
