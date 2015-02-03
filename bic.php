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

  return _bic_civix_civicrm_enable();
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

/**
 * Set permissions for runner/engine API call
 */
function bic_civicrm_alterAPIPermissions($entity, $action, &$params, &$permissions) {
  // TODO: adjust to correct permission
  $permissions['bic']['getfromiban'] = array('access CiviCRM');
  $permissions['bic']['findbyiban']  = array('access AJAX API');
  $permissions['bic']['get']         = array('access CiviCRM');
}

/**
 * Implementation of hook_civicrm_navigationMenu
 *
 * Inject the 'civicrm/bicList' item unter the 'Search' top menu, unless it's already in there...
 *
 * based on https://github.com/Project60/sepa_dd/commit/f342a223c41d7fc940294f24999fc5bdf637b98b
 */
function bic_civicrm_navigationMenu(&$params) {
  // see if it is already in the menu...
  $menu_item_search = array('url' => 'civicrm/bicList');
  $menu_items = array();
  CRM_Core_BAO_Navigation::retrieve($menu_item_search, $menu_items);

  if (empty($menu_items)) {
    // it's not already contained, so we want to add it to the menu
    
    // now, by default we want to add it to the Contributions menu -> find it
    $search_menu_id = 0;
    foreach ($params as $key => $value) {
      if ($value['attributes']['name'] == 'Search...') {
        $search_menu_id = $key;
        break;
      }
    }

    if (empty($search_menu_id)) {
      error_log("org.project60.bic: Connot find 'Contributions' menu item.");
    } else {
      // insert at the bottom
      $params[$search_menu_id]['child'][] = array(
          'attributes' => array (
          'label' => ts('Find Banks',array('domain' => 'org.project60.bic')),
          'name' => 'BankLists',
          'url' => 'civicrm/bicList',
          'permission' => 'access CiviContribute',
          'operator' => NULL,
          'separator' => 2,
          'parentID' => $search_menu_id,
          'navID' => bic_navhelper_create_unique_nav_id($params),
          'active' => 1
        ));
    }
  }
}

/**
 * Helper function for civicrm_navigationMenu
 * 
 * Will create a new, unique ID for the navigation menu
 */
function bic_navhelper_create_unique_nav_id($menu) {
  $max_stored_navId = CRM_Core_DAO::singleValueQuery("SELECT max(id) FROM civicrm_navigation");
  $max_current_navId = bic_navhelper_get_max_nav_id($menu);
  return max($max_stored_navId, $max_current_navId) + 1;  
}

/**
 * Helper function for civicrm_navigationMenu
 * 
 * Will find the (currently) highest nav_item ID
 */
function bic_navhelper_get_max_nav_id($menu) {
  $max_id = 1;
  foreach ($menu as $entry) {
    $max_id = max($max_id, $entry['attributes']['navID']);
    if (!empty($entry['child'])) {
      $max_id_children = bic_navhelper_get_max_nav_id($entry['child']);
      $max_id = max($max_id, $max_id_children);
    }
  }
  return $max_id;  
}
