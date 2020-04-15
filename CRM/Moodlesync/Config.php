<?php

use CRM_Moodlesync_ExtensionUtil as E;

class CRM_Moodlesync_Config {
  // property for singleton pattern (caching the config)
  static private $_singleton = NULL;

  private $customGroupIdMoodleEvent = 0;
  private $customGroupIdMoodleContact = 0;
  private $customGroupIdMoodleParticipant = 0;

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
    $url = CRM_Utils_File::addTrailingSlash($value, '/');
    Civi::settings()->set('moodlesync_url', $url);
  }

  public function getCourseURL($courseId) {
    return $this->getMoodleURL() . "course/view.php?id=$courseId";
  }

  public function getUserURL($userId) {
    return $this->getMoodleURL() . "user/profile.php?id=$userId";
  }

  public function getEnrolmentURL($enrolmentId) {
    // unfortunately, the api does not return an enrolment id!
    //      enrol/editenrolment.php?ue=$enrolmentId
    // so pass the course id, and the function will return a link to the participant list

    return $this->getMoodleURL() . "user/index.php?id=$enrolmentId";
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
    $this->getCustomFieldIdEventCategories();
    $this->getCustomFieldIdEventMoodleId();
    $this->getCustomFieldIdEventViewInMoodle();

    $this->getCustomFieldIdContactSyncWithMoodle();
    $this->getCustomFieldIdContactMoodleId();
    $this->getCustomFieldIdContactViewInMoodle();

    $this->getCustomFieldIdParticipantSyncWithMoodle();
    $this->getCustomFieldIdParticipantMoodleId();
    $this->getCustomFieldIdParticipantViewInMoodle();
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

  public function getCustomFieldIdEventMoodleId() {
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
         'weight' => 3,
        ]);
      }
      catch (CiviCRM_API3_Exception $ex) {
        CRM_Core_Error::createError(E::ts('Error in ') . __CLASS__ . '::' . __METHOD__ . ' - ' . E::ts('Could not find or create custom field'));
      }
    }

    return $customField['id'];
  }

  public function getCustomFieldIdEventViewInMoodle() {
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
          'data_type' => 'Memo',
          'html_type' => 'RichTextEditor',
          'note_columns' => '60',
          'note_rows' => '4',
          'is_active' => 1,
          'is_searchable' => 0,
          'is_view' => 1,
          'weight' => 4,
        ]);
      }
      catch (CiviCRM_API3_Exception $ex) {
        CRM_Core_Error::createError(E::ts('Error in ') . __CLASS__ . '::' . __METHOD__ . ' - ' . E::ts('Could not find or create custom field'));
      }
    }

    return $customField['id'];
  }

  public function getCourseCategoriesOptionGroupId() {
    $optionGroupName = 'moodlesync_course_categories';

    try {
      // get the option group
      $optionGroup = civicrm_api3('OptionGroup', 'getsingle', [
        'name' => $optionGroupName,
      ]);
    }
    catch (CiviCRM_API3_Exception $ex) {
      $optionGroup = civicrm_api3('OptionGroup', 'create', [
        'name' => $optionGroupName,
        'title' => ts('Moodle Course Categories'),
        'data_type' => 'Integer',
        'is_reserved' => '0',
        'is_active' => '1',
        'is_locked' => '0',
      ]);
      $optionValue = civicrm_api3('OptionValue', 'create', [
        'option_group_id' => $optionGroup['id'],
        'label' => ts('Miscellaneous'),
        'value' => '1',
        'name' => 'Miscellaneous',
        'is_default' => '0',
        'weight' => '1',
        'is_optgroup' => '0',
        'is_reserved' => '0',
        'is_active' => '1',
      ]);
    }

    return $optionGroup['id'];
  }

  public function getCustomFieldIdEventCategories() {
    $customFieldName = 'moodlesync_course_categories';
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
          'label' => E::ts('Course Category'),
          'data_type' => 'Int',
          'html_type' => 'Select',
          'is_active' => 1,
          'is_searchable' => 1,
          'is_search_range' => 0,
          'text_length' => 255,
          'is_view' => 0,
          'option_group_id' => $this->getCourseCategoriesOptionGroupId(),
          'weight' => 2,
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

  public function getCustomFieldIdContactMoodleId() {
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

  public function getCustomFieldIdContactViewInMoodle() {
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
          'data_type' => 'Memo',
          'html_type' => 'RichTextEditor',
          'note_columns' => '60',
          'note_rows' => '4',
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

  public function getCustomFieldIdParticipantSyncWithMoodle() {
    $customFieldName = 'moodlesync_sync_with_moodle';
    $customGroupId = $this->getCustomGroupIdParticipant();
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
          'label' => E::ts('Synchronize participant with Moodle?'),
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

  public function getCustomFieldIdParticipantMoodleId() {
    $customFieldName = 'moodlesync_enrolment_id';
    $customGroupId = $this->getCustomGroupIdParticipant();
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
          'label' => E::ts('Enrolment ID'),
          'data_type' => 'String',
          'html_type' => 'Text',
          'is_active' => 1,
          'is_searchable' => 1,
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

  public function getCustomFieldIdParticipantViewInMoodle() {
    $customFieldName = 'moodlesync_view_in_moodle';
    $customGroupId = $this->getCustomGroupIdParticipant();
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
          'data_type' => 'Memo',
          'html_type' => 'RichTextEditor',
          'note_columns' => '60',
          'note_rows' => '4',
          'is_active' => 1,
          'is_searchable' => 0,
          'is_view' => 1,
          'weight' => 4,
        ]);
      }
      catch (CiviCRM_API3_Exception $ex) {
        CRM_Core_Error::createError(E::ts('Error in ') . __CLASS__ . '::' . __METHOD__ . ' - ' . E::ts('Could not find or create custom field'));
      }
    }

    return $customField['id'];
  }

  public function setCourseCategories($categories) {
    $optionGroupId = $this->getCourseCategoriesOptionGroupId();

    // delete existing items and add new ones
    $sql = "delete from civicrm_option_value where option_group_id = $optionGroupId";
    CRM_Core_DAO::executeQuery($sql);
    foreach ($categories as $category) {
      civicrm_api3('OptionValue', 'create', [
        'option_group_id' => $optionGroupId,
        'label' => $category->name,
        'value' => $category->id,
        'name' => $category->name,
        'is_default' => '0',
        'weight' => $category->id,
        'is_optgroup' => '0',
        'is_reserved' => '0',
        'is_active' => '1',
      ]);
    }
  }

  public function getCustomGroupIdEvent() {
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

  public function getCustomGroupIdParticipant() {
    if ($this->customGroupIdMoodleParticipant == 0) {
      $customGroupName = 'MoodleSync_Participant';

      try {
        $customGroup = civicrm_api3('CustomGroup', 'getsingle', [
          'extends' => 'Participant',
          'name' => $customGroupName,
        ]);
      }
      catch (CiviCRM_API3_Exception $ex) {
        try {
          $customGroup = civicrm_api3('CustomGroup', 'create', [
            'name' => $customGroupName,
            'title' => 'MoodleSync',
            'extends' => 'Participant',
            'table_name' => 'civicrm_value_moodlesync_participant',
            'is_reserved' => 0,
            'collapse_adv_display' => 0,
            'collapse_display' => 0,
          ]);
        }
        catch (CiviCRM_API3_Exception $ex) {
          CRM_Core_Error::createError(E::ts('Error in ') . __CLASS__ . '::' . __METHOD__ . ' - ' . E::ts('Could not find or create custom group'));
        }
      }

      $this->customGroupIdMoodleParticipant = $customGroup['id'];
    }

    return $this->customGroupIdMoodleParticipant;
  }

  public function getCustomGroupIdContact() {
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
            'extends' => 'Individual',
            'style' => 'Tab',
            'table_name' => 'civicrm_value_moodlesync_contact',
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
