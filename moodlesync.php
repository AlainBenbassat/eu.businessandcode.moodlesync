<?php

require_once 'moodlesync.civix.php';
use CRM_Moodlesync_ExtensionUtil as E;

function moodlesync_civicrm_custom($op, $groupID, $entityID, &$params) {
  if ($op == 'create' || $op == 'edit') {
    try {
      $conf = CRM_Moodlesync_Config::singleton();

      // see if it's the MoodleSync custom group for Contacts, Events, or Participant
      if ($groupID == $conf->getCustomGroupIdContact()) {
        $syncHelper = new CRM_Moodlesync_Helper();
        $contact = civicrm_api3('Contact', 'getsingle', ['id' => $entityID]);
        $syncHelper->syncContact($contact, FALSE);
      }
      elseif ($groupID == $conf->getCustomGroupIdEvent()) {
        $syncHelper = new CRM_Moodlesync_Helper();
        $event = civicrm_api3('Event', 'getsingle', ['id' => $entityID]);
        $syncHelper->syncEvent($event);
      }
      elseif ($groupID == $conf->getCustomGroupIdParticipant()) {
        $syncHelper = new CRM_Moodlesync_Helper();
        $participant = civicrm_api3('Participant', 'getsingle', ['id' => $entityID]);
        $syncHelper->syncParticipant($participant);
      }

    }
    catch (Exception $e) {
      CRM_Core_Session::setStatus($e->getMessage(), ts('Error'), 'error');
    }
  }
}

function moodlesync_civicrm_searchTasks( $objectName, &$tasks ){
  if($objectName == 'event'){
    $tasks[] = [
      'title' => E::ts('Synchronize participants with Moodle'),
      'class' => 'CRM_Moodlesync_Form_SyncParticipant'
    ];
  }
}

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function moodlesync_civicrm_config(&$config) {
  _moodlesync_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_xmlMenu
 */
function moodlesync_civicrm_xmlMenu(&$files) {
  _moodlesync_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function moodlesync_civicrm_install() {
  _moodlesync_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function moodlesync_civicrm_postInstall() {
  _moodlesync_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function moodlesync_civicrm_uninstall() {
  _moodlesync_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function moodlesync_civicrm_enable() {
  _moodlesync_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function moodlesync_civicrm_disable() {
  _moodlesync_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function moodlesync_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _moodlesync_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
 */
function moodlesync_civicrm_managed(&$entities) {
  _moodlesync_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_caseTypes
 */
function moodlesync_civicrm_caseTypes(&$caseTypes) {
  _moodlesync_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_angularModules
 */
function moodlesync_civicrm_angularModules(&$angularModules) {
  _moodlesync_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterSettingsFolders
 */
function moodlesync_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _moodlesync_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function moodlesync_civicrm_entityTypes(&$entityTypes) {
  _moodlesync_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_thems().
 */
function moodlesync_civicrm_themes(&$themes) {
  _moodlesync_civix_civicrm_themes($themes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 *
function moodlesync_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
function moodlesync_civicrm_navigationMenu(&$menu) {
  _moodlesync_civix_insert_navigation_menu($menu, 'Administer', array(
    'label' => E::ts('MoodleSync'),
    'name' => 'moodlesync_settings',
    'url' => 'civicrm/moodlesyncsettings',
    'permission' => 'administer CiviCRM',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _moodlesync_civix_navigationMenu($menu);
}
