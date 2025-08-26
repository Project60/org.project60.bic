<?php
/*-------------------------------------------------------+
| Project 60 - Little BIC extension                      |
| Copyright (C) 2020                                     |
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

use CRM_Bic_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_Bic_Upgrader extends CRM_Extension_Upgrader_Base
{

    /**
     * Make sure the option group is there
     */
    public function enable()
    {
        // check if option group is there, and create if it isn't
        try {
            $option_group = civicrm_api3('OptionGroup', 'getsingle', array('name' => 'bank_list'));
        } catch (Exception $e) {
            // group's not there yet, create:
            try {
                $option_group = civicrm_api3('OptionGroup', 'create', array(
                    'name'        => 'bank_list',
                    'title'       => ts('List of banks'),
                    'is_reserved' => 0,
                    'is_active'   => 1,
                ));
            } catch (Exception $create_ex) {
                // TODO: more info?
                Civi::log()->warning("Couldn't create 'bank_list' OptionGroup.");
            }
        }
    }
}
