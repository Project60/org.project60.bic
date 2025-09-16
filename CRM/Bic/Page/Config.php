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

declare(strict_types = 1);

use CRM_Bic_ExtensionUtil as E;

class CRM_Bic_Page_Config extends CRM_Core_Page {

  public function run() {

    CRM_Utils_System::setTitle(E::ts('Available Banks'));

    $countries = CRM_Bic_Parser_Parser::getParserList();
    $stats = civicrm_api3('Bic', 'stats');
    $total_count = 0;
    foreach ($countries as $country) {
      if (isset($stats['values'][$country])) {
        $total_count += $stats['values'][$country];
      }
      else {
        $stats['values'][$country] = 0;
      }
    }

    // gather the names
    $country_names = [];

    $config = CRM_Core_Config::singleton();

    $id2code = CRM_Core_PseudoConstant::countryIsoCode();
    $default_country = $id2code[$config->defaultContactCountry] ?? '';
    $code2id = array_flip($id2code);
    $id2country = CRM_Core_PseudoConstant::country(FALSE, FALSE);
    foreach ($countries as $code) {
      $country_id = $code2id[$code];
      $country_name = $id2country[$country_id];
      $country_names[$code] = $country_name;
    }

    $this->assign('countries', $countries);
    $this->assign('country_names', $country_names);
    $this->assign('default_country', $default_country);
    $this->assign('stats', $stats['values']);
    $this->assign('total_count', $total_count);

    parent::run();
  }

}
