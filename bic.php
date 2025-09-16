<?php

require_once 'bic.civix.php';

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Implements hook_civicrm_container()
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_container/
 */
function bic_civicrm_container(ContainerBuilder $container) {
  if (class_exists('\Civi\Bic\ContainerSpecs')) {
    $container->addCompilerPass(new \Civi\Bic\ContainerSpecs());
  }
}

/**
 * Implementation of hook_civicrm_config
 */
function bic_civicrm_config(&$config) {
  _bic_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_install
 */
function bic_civicrm_install() {
  return _bic_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_enable
 */
function bic_civicrm_enable() {
  return _bic_civix_civicrm_enable();
}

/**
 * Set permissions for runner/engine API call
 */
function bic_civicrm_alterAPIPermissions($entity, $action, &$params, &$permissions) {
  // TODO: adjust to correct permission
  $permissions['bic']['getfromiban'] = ['access CiviCRM'];
  $permissions['bic']['findbyiban']  = ['access AJAX API'];
  $permissions['bic']['get']         = ['access CiviCRM'];
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * Inject the 'civicrm/bicList' item unter the 'Search' top menu, unless it's already in there...
 *
 */
function bic_civicrm_navigationMenu(&$menu) {
  _bic_civix_insert_navigation_menu($menu, 'Search', [
    'label' => ts('Find Banks', ['domain' => 'org.project60.bic']),
    'name' => 'BankLists',
    'url' => 'civicrm/bicList',
    'permission' => 'access CiviContribute',
    'operator' => NULL,
    'separator' => 2,
    'active' => 1,
  ]);

  _bic_civix_navigationMenu($menu);
}
