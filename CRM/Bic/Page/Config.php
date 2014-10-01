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

require_once 'CRM/Core/Page.php';

class CRM_Bic_Page_Config extends CRM_Core_Page {
  function run() {

    // TODO: get this from somewhere
    $countries = array('DE', 'ES', 'BE'); 

    $stats = civicrm_api3('Bic', 'stats');
    $total_count = 0;
    foreach ($countries as $country) {
      if (isset($stats['values'][$country])) {
        $total_count += $stats['values'][$country];
      } else {
        $stats['values'][$country] = 0;
      }
    }


    $this->assign('countries', $countries);
    $this->assign('stats', $stats['values']);
    $this->assign('total_count', $total_count);

    parent::run();
  }
}
