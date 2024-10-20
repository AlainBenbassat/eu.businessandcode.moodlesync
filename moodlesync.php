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
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function moodlesync_civicrm_install() {
  _moodlesync_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function moodlesync_civicrm_enable() {
  _moodlesync_civix_civicrm_enable();
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 *

 // */

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
