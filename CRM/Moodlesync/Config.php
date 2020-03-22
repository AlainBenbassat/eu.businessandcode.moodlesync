<?php

use CRM_Moodlesync_ExtensionUtil as E;

class CRM_Moodlesync_Config {
  // property for singleton pattern (caching the config)
  static private $_singleton = NULL;

  private $customGroupIdMoodleEvent = 0;
  private $customGroupIdMoodleContact = 0;

  public function __construct() {
  }

  public static function &singleton() {
    if (self::$_singleton === NULL) {
      self::$_singleton = new CRM_Moodlesync_Config();
    }
    return self::$_singleton;
  }

  public function getMoodleURL() {
    return Civi::settings()->get('moodlesync_url');
  }

  public function setMoodleURL($value) {
    Civi::settings()->set('moodlesync_url', $value);
  }

  public function getMoodleToken() {
    return Civi::settings()->get('moodlesync_token');
  }

  public function setMoodleToken($value) {
    Civi::settings()->set('moodlesync_token', $value);
  }

  public function getMoodleRoleFromCiviRole($civiParticipantRoleId) {
    return Civi::settings()->get('moodlesync_' . $civiParticipantRoleId);
  }

  public function setMoodleRoleFromCiviRole($name, $value) {
    Civi::settings()->set("moodlesync_$name", $value);
  }

  public function createCustomFields() {
    // is called by the setup screen
    // we just call the custom field getters
    $this->getCustomFieldIdEventSyncWithMoodle();
    $this->getCustomFieldEventMoodleId();
    $this->getCustomFieldEventViewInMoodle();
    $this->getCustomFieldIdContactSyncWithMoodle();
    $this->getCustomFieldContactMoodleId();
    $this->getCustomFieldContactViewInMoodle();
  }

  public function getCustomFieldIdEventSyncWithMoodle() {
    $customFieldName = 'moodlesync_sync_with_moodle';
    $customGroupId = $this->getCustomGroupIdEvent();
    try {
      // get the field
      $customField = civicrm_api3('CustomField', 'getsingle', [
        'name' => $customFieldName,
        'column_name' => $customFieldName,
        'custom_group_id' => $customGroupId,
      ]);
    }
    catch (CiviCRM_API3_Exception $ex) {
      try {
        // field does not exist, create it
        $customField = civicrm_api3('CustomField', 'create', [
          'custom_group_id' => $customGroupId,
          'name' => $customFieldName,
          'column_name' => $customFieldName,
          'label' => E::ts('Synchronize event with Moodle?'),
          'data_type' => 'Boolean',
          'html_type' => 'Radio',
          'is_active' => 1,
          'is_searchable' => 1,
          'is_view' => 0,
          'weight' => 1,
        ]);
      }
      catch (CiviCRM_API3_Exception $ex) {
        CRM_Core_Error::createError(E::ts('Error in ') . __CLASS__ . '::' . __METHOD__ . ' - ' . E::ts('Could not find or create custom field'));
      }
    }

    return $customField['id'];
  }

  public function getCustomFieldEventMoodleId() {
    $customFieldName = 'moodlesync_course_id';
    $customGroupId = $this->getCustomGroupIdEvent();
    try {
      // get the field
      $customField = civicrm_api3('CustomField', 'getsingle', [
        'name' => $customFieldName,
        'column_name' => $customFieldName,
        'custom_group_id' => $customGroupId,
      ]);
    }
    catch (CiviCRM_API3_Exception $ex) {
      try {
        // field does not exist, create it
       $customField = civicrm_api3('CustomField', 'create', [
         'custom_group_id' => $customGroupId,
         'name' => $customFieldName,
         'column_name' => $customFieldName,
         'label' => E::ts('Course ID'),
         'data_type' => 'String',
         'html_type' => 'Text',
         'is_active' => 1,
         'is_searchable' => 1,
         'is_view' => 1,
         'weight' => 2,
        ]);
      }
      catch (CiviCRM_API3_Exception $ex) {
        CRM_Core_Error::createError(E::ts('Error in ') . __CLASS__ . '::' . __METHOD__ . ' - ' . E::ts('Could not find or create custom field'));
      }
    }

    return $customField['id'];
  }

  public function getCustomFieldEventViewInMoodle() {
    $customFieldName = 'moodlesync_view_in_moodle';
    $customGroupId = $this->getCustomGroupIdEvent();
    try {
      // get the field
      $customField = civicrm_api3('CustomField', 'getsingle', [
        'name' => $customFieldName,
        'column_name' => $customFieldName,
        'custom_group_id' => $customGroupId,
      ]);
    }
    catch (CiviCRM_API3_Exception $ex) {
      try {
        // field does not exist, create it
        $customField = civicrm_api3('CustomField', 'create', [
          'custom_group_id' => $customGroupId,
          'name' => $customFieldName,
          'column_name' => $customFieldName,
          'label' => E::ts('View in Moodle'),
          'data_type' => 'Link',
          'html_type' => 'Link',
          'is_active' => 1,
          'is_searchable' => 0,
          'is_view' => 1,
          'weight' => 3,
        ]);
      }
      catch (CiviCRM_API3_Exception $ex) {
        CRM_Core_Error::createError(E::ts('Error in ') . __CLASS__ . '::' . __METHOD__ . ' - ' . E::ts('Could not find or create custom field'));
      }
    }

    return $customField['id'];
  }

  public function getCustomFieldIdContactSyncWithMoodle() {
    $customFieldName = 'moodlesync_sync_with_moodle';
    $customGroupId = $this->getCustomGroupIdContact();
    try {
      // get the field
      $customField = civicrm_api3('CustomField', 'getsingle', [
        'name' => $customFieldName,
        'column_name' => $customFieldName,
        'custom_group_id' => $customGroupId,
      ]);
    }
    catch (CiviCRM_API3_Exception $ex) {
      try {
        // field does not exist, create it
        $customField = civicrm_api3('CustomField', 'create', [
          'custom_group_id' => $customGroupId,
          'name' => $customFieldName,
          'column_name' => $customFieldName,
          'label' => E::ts('Synchronize contact with Moodle?'),
          'data_type' => 'Boolean',
          'html_type' => 'Radio',
          'is_active' => 1,
          'is_searchable' => 1,
          'is_view' => 0,
          'weight' => 1,
        ]);
      }
      catch (CiviCRM_API3_Exception $ex) {
        CRM_Core_Error::createError(E::ts('Error in ') . __CLASS__ . '::' . __METHOD__ . ' - ' . E::ts('Could not find or create custom field'));
      }
    }

    return $customField['id'];
  }

  public function getCustomFieldContactMoodleId() {
    $customFieldName = 'moodlesync_user_id';
    $customGroupId = $this->getCustomGroupIdContact();
    try {
      // get the field
      $customField = civicrm_api3('CustomField', 'getsingle', [
        'name' => $customFieldName,
        'column_name' => $customFieldName,
        'custom_group_id' => $customGroupId,
      ]);
    }
    catch (CiviCRM_API3_Exception $ex) {
      try {
        // field does not exist, create it
        $customField = civicrm_api3('CustomField', 'create', [
          'custom_group_id' => $customGroupId,
          'name' => $customFieldName,
          'column_name' => $customFieldName,
          'label' => E::ts('User ID'),
          'data_type' => 'String',
          'html_type' => 'Text',
          'is_active' => 1,
          'is_searchable' => 1,
          'is_view' => 1,
          'weight' => 2,
        ]);
      }
      catch (CiviCRM_API3_Exception $ex) {
        CRM_Core_Error::createError(E::ts('Error in ') . __CLASS__ . '::' . __METHOD__ . ' - ' . E::ts('Could not find or create custom field'));
      }
    }

    return $customField['id'];
  }

  public function getCustomFieldContactViewInMoodle() {
    $customFieldName = 'moodlesync_view_in_moodle';
    $customGroupId = $this->getCustomGroupIdContact();
    try {
      // get the field
      $customField = civicrm_api3('CustomField', 'getsingle', [
        'name' => $customFieldName,
        'column_name' => $customFieldName,
        'custom_group_id' => $customGroupId,
      ]);
    }
    catch (CiviCRM_API3_Exception $ex) {
      try {
        // field does not exist, create it
        $customField = civicrm_api3('CustomField', 'create', [
          'custom_group_id' => $customGroupId,
          'name' => $customFieldName,
          'column_name' => $customFieldName,
          'label' => E::ts('View in Moodle'),
          'data_type' => 'Link',
          'html_type' => 'Link',
          'is_active' => 1,
          'is_searchable' => 0,
          'is_view' => 1,
          'weight' => 3,
        ]);
      }
      catch (CiviCRM_API3_Exception $ex) {
        CRM_Core_Error::createError(E::ts('Error in ') . __CLASS__ . '::' . __METHOD__ . ' - ' . E::ts('Could not find or create custom field'));
      }
    }

    return $customField['id'];
  }

  private function getCustomGroupIdEvent() {
    if ($this->customGroupIdMoodleEvent == 0) {
      $customGroupName = 'MoodleSync_Event';

      try {
        $customGroup = civicrm_api3('CustomGroup', 'getsingle', [
          'extends' => 'Event',
          'name' => $customGroupName,
        ]);
      }
      catch (CiviCRM_API3_Exception $ex) {
        try {
          $customGroup = civicrm_api3('CustomGroup', 'create', [
            'name' => $customGroupName,
            'title' => 'MoodleSync',
            'extends' => 'Event',
            'table_name' => 'civicrm_value_moodlesync_event',
            'is_reserved' => 0,
            'collapse_adv_display' => 0,
            'collapse_display' => 0,
          ]);
        }
        catch (CiviCRM_API3_Exception $ex) {
          CRM_Core_Error::createError(E::ts('Error in ') . __CLASS__ . '::' . __METHOD__ . ' - ' . E::ts('Could not find or create custom group'));
        }
      }

      $this->customGroupIdMoodleEvent = $customGroup['id'];
    }

    return $this->customGroupIdMoodleEvent;
  }

  private function getCustomGroupIdContact() {
    if ($this->customGroupIdMoodleContact == 0) {
      $customGroupName = 'MoodleSync_Contact';

      try {
        $customGroup = civicrm_api3('CustomGroup', 'getsingle', [
          'extends' => 'Individual',
          'name' => $customGroupName,
        ]);
      }
      catch (CiviCRM_API3_Exception $ex) {
        try {
          $customGroup = civicrm_api3('CustomGroup', 'create', [
            'name' => $customGroupName,
            'title' => 'MoodleSync',
            'extends' => 'Contact',
            'style' => 'Tab',
            'table_name' => 'civicrm_value_moodlesync_Contact',
            'is_reserved' => 0,
            'collapse_adv_display' => 0,
            'collapse_display' => 0,
          ]);
        }
        catch (CiviCRM_API3_Exception $ex) {
          CRM_Core_Error::createError(E::ts('Error in ') . __CLASS__ . '::' . __METHOD__ . ' - ' . E::ts('Could not find or create custom group'));
        }
      }

      $this->customGroupIdMoodleContact = $customGroup['id'];
    }

    return $this->customGroupIdMoodleContact;
  }

}
