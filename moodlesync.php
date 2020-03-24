<?php

require_once 'moodlesync.civix.php';
use CRM_Moodlesync_ExtensionUtil as E;

function moodlesync_civicrm_post($op, $objectName, $objectId, &$objectRef) {
  if (CRM_Core_Transaction::isActive()) {
    CRM_Core_Transaction::addCallback(CRM_Core_Transaction::PHASE_POST_COMMIT, 'moodlesync_civicrm_post_callback', [$op, $objectName, $objectId, $objectRef]);
  }
  else {
    moodlesync_civicrm_post_callback($op, $objectName, $objectId, $objectRef);
  }
}

function moodlesync_civicrm_post_callback($op, $objectName, $objectId, $objectRef) {
  if ($objectName == 'Event' && ($op == 'create' || $op == 'edit')) {
    try {
      // see if we have to sync this event
      $config = CRM_Moodlesync_Config::singleton();
      $syncFieldId = $config->getCustomFieldIdEventSyncWithMoodle();
      $syncThisEvent = civicrm_api3('CustomValue', 'get', [
        'sequential' => 1,
        'entity_id' => $objectRef->id,
        'entity_table' => 'civicrm_event',
        'return' => "custom_$syncFieldId",
      ]);
      if ($syncThisEvent['count'] > 0 && $syncThisEvent['values'][0]['latest'] == 1) {
        // store the url to the course
        $url = $config->getCourseURL(5);
        civicrm_api3('CustomValue', 'create', [
          'entity_id' => $objectRef->id,
          'entity_table' => 'civicrm_event',
          "custom_" . $config->getCustomFieldIdEventViewInMoodle() => "<a href=\"$url\" target=\"_blank\">$url</a>",
        ]);

        // see if we already have a course id
        $courseIdField = $config->getCustomFieldIdEventMoodleId();
        $courseId = civicrm_api3('CustomValue', 'get', [
          'sequential' => 1,
          'entity_id' => $objectRef->id,
          'entity_table' => 'civicrm_event',
          'return' => "custom_$courseIdField",
        ]);
        if ($courseId['count'] > 0 && $courseId['values'][0]['latest']) {
          // already sync'd
        }
        else {
          // get the category
          $categoryField = $config->getCustomFieldIdEventCategories();
          $category = civicrm_api3('CustomValue', 'get', [
            'sequential' => 1,
            'entity_id' => $objectRef->id,
            'entity_table' => 'civicrm_event',
            'return' => "custom_$categoryField",
          ]);
          $categoryId = ($courseId['count'] > 0 && $courseId['values'][0]['latest']) ? $category['values'][0]['latest'] : 1;

          // create the course in Moodle
          $moodleApi = new CRM_Moodlesync_API($config);
          $courseId = $moodleApi->createCourse($objectRef->id, $objectRef->title, $objectRef->start_date, $objectRef->end_date, $categoryId);

          // store the Moodle course ID
          civicrm_api3('CustomValue', 'create', [
            'entity_id' => $objectRef->id,
            'entity_table' => 'civicrm_event',
            "custom_$courseIdField" => $courseId,
          ]);

          // store the url to the course
          $url = $config->getCourseURL($courseId);
          civicrm_api3('CustomValue', 'create', [
            'entity_id' => $objectRef->id,
            'entity_table' => 'civicrm_event',
            "custom_" . $config->getCustomFieldIdEventViewInMoodle() => "<a href=\"$url\">$url</a>",
          ]);
        }
      }
    }
    catch (Exception $e) {
      CRM_Core_Session::setStatus($e->getMessage(), ts('Error'), 'error');
    }
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
