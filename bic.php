<?php

require_once 'bic.civix.php';

/**
 * Implementation of hook_civicrm_config
 */
function bic_civicrm_config(&$config) {
  _bic_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 */
function bic_civicrm_xmlMenu(&$files) {
  _bic_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 */
function bic_civicrm_install() {
  return _bic_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 */
function bic_civicrm_uninstall() {
  return _bic_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 */
function bic_civicrm_enable() {
  return _bic_civix_civicrm_enable();

  // check if option group is there, and create if it isn't
  try {
    $option_group = civicrm_api3('OptionGroup', 'getsingle', array('name' => 'bank_list'));
  } catch (Exception $e) {
    // group's not there yet, create:
    try {
      $option_group = civicrm_api3('OptionGroup', 'create', array(
          'name'         => 'bank_list',
          'title'        => ts('List of banks'),
          'is_reserved'  => 0,
          'is_active'    => 1,
          ));      
    } catch (Exception $create_ex) {
      // TODO: more info?
      error_log("Couldn't create 'bank_list' OptionGroup.");
    }
  }
}

/**
 * Implementation of hook_civicrm_disable
 */
function bic_civicrm_disable() {
  return _bic_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 */
function bic_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _bic_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function bic_civicrm_managed(&$entities) {
  return _bic_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 */
function bic_civicrm_caseTypes(&$caseTypes) {
  _bic_civix_civicrm_caseTypes($caseTypes);
}
