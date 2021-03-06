<?php


class CRM_Moodlesync_Helper {
  private $config;

  public function __construct() {
    $this->config = CRM_Moodlesync_Config::singleton();
  }

  public function syncContact($contact, $force = FALSE) {
    $userId = 0;

    // make sure it's an individual
    if ($contact['contact_type'] != 'Individual') {
      return -1;
    }

    // get the custom field that stores if the contact should be sync'd
    $syncFieldId = $this->config->getCustomFieldIdContactSyncWithMoodle();
    $syncThisContact = civicrm_api3('CustomValue', 'get', [
      'sequential' => 1,
      'entity_id' => $contact['id'],
      'entity_table' => 'civicrm_contact',
      'return' => "custom_$syncFieldId",
    ]);

    // don't do anything if the contact is set explicitly to "do not sync"
    if ($syncThisContact['count'] > 0 && !empty($syncThisContact['values'][0]['latest']) && $syncThisContact['values'][0]['latest'] == 0) {
      return 0;
    }

    // sync if it's explicitly set to "sync", or it's not set but forced (the latter is the case when a participant is synced)
    if ($force == TRUE || ($syncThisContact['count'] > 0 && $syncThisContact['values'][0]['latest'] == 1)) {
      // get the custom field that stores the user id
      $userIdField = $this->config->getCustomFieldIdContactMoodleId();
      $userId = civicrm_api3('CustomValue', 'get', [
        'sequential' => 1,
        'entity_id' => $contact['id'],
        'entity_table' => 'civicrm_contact',
        'return' => "custom_$userIdField",
      ]);

      // see if we already have a user id
      if ($userId['count'] > 0 && $userId['values'][0]['latest']) {
        // yes, no sync needed
        $userId = $userId['values'][0]['latest'];
      }
      else {
        // create the user in Moodle
        $moodleApi = new CRM_Moodlesync_API($this->config);
        $userId = $moodleApi->createUser($contact['id'], $contact['first_name'], $contact['last_name'], $contact['email']);

        // store the Moodle user ID
        civicrm_api3('CustomValue', 'create', [
          'entity_id' => $contact['id'],
          'entity_table' => 'civicrm_contact',
          "custom_$userIdField" => $userId,
        ]);

        // store the url to the user
        $url = $this->config->getUserURL($userId);
        civicrm_api3('CustomValue', 'create', [
          'entity_id' => $contact['id'],
          'entity_table' => 'civicrm_contact',
          "custom_" . $this->config->getCustomFieldIdContactViewInMoodle() => "<a href=\"$url\">$url</a>",
        ]);

        // set sync field to "yes"
        civicrm_api3('CustomValue', 'create', [
          'entity_id' => $contact['id'],
          'entity_table' => 'civicrm_contact',
          "custom_$syncFieldId" => 1,
        ]);
      }
    }

    return $userId;
  }

  public function syncEvent($event) {
    $courseId = 0;

    // get the custom field that stores if the event should be sync'd
    $syncFieldId = $this->config->getCustomFieldIdEventSyncWithMoodle();
    $syncThisEvent = civicrm_api3('CustomValue', 'get', [
      'sequential' => 1,
      'entity_id' => $event['id'],
      'entity_table' => 'civicrm_event',
      'return' => "custom_$syncFieldId",
    ]);

    // see if it's "yes"
    if ($syncThisEvent['count'] > 0 && $syncThisEvent['values'][0]['latest'] == 1) {
      // get the custom field that stores the course id
      $courseIdField = $this->config->getCustomFieldIdEventMoodleId();
      $courseId = civicrm_api3('CustomValue', 'get', [
        'sequential' => 1,
        'entity_id' => $event['id'],
        'entity_table' => 'civicrm_event',
        'return' => "custom_$courseIdField",
      ]);

      // see if we already have a course id
      if ($courseId['count'] > 0 && $courseId['values'][0]['latest']) {
        // yes, no sync needed
        $courseId = $courseId['values'][0]['latest'];
      }
      else {
        // get the category
        $categoryField = $this->config->getCustomFieldIdEventCategories();
        $category = civicrm_api3('CustomValue', 'get', [
          'sequential' => 1,
          'entity_id' => $event['id'],
          'entity_table' => 'civicrm_event',
          'return' => "custom_$categoryField",
        ]);
        $categoryId = ($category['count'] > 0 && $category['values'][0]['latest']) ? $category['values'][0]['latest'] : 1;

        // get the course summary
        $summaryField = $this->config->getCustomFieldIdEventCourseSummary();
        $summary = civicrm_api3('CustomValue', 'get', [
          'sequential' => 1,
          'entity_id' => $event['id'],
          'entity_table' => 'civicrm_event',
          'return' => "custom_$summaryField",
        ]);

        // create the course in Moodle
        $moodleApi = new CRM_Moodlesync_API($this->config);
        $courseId = $moodleApi->createCourse($event['id'], $event['title'], $event['start_date'], $event['end_date'], $categoryId, $summary['values'][0]['latest']);

        // store the Moodle course ID
        civicrm_api3('CustomValue', 'create', [
          'entity_id' => $event['id'],
          'entity_table' => 'civicrm_event',
          "custom_$courseIdField" => $courseId,
        ]);

        // store the url to the course
        $url = $this->config->getCourseURL($courseId);
        civicrm_api3('CustomValue', 'create', [
          'entity_id' => $event['id'],
          'entity_table' => 'civicrm_event',
          "custom_" . $this->config->getCustomFieldIdEventViewInMoodle() => "<a href=\"$url\">$url</a>",
        ]);
      }
    }

    return $courseId;
  }

  public function syncParticipant($participant) {
    $enrolmentId = 0;

    // skip sync if the participant status is "negative"
    $statusTypes = CRM_Event_PseudoConstant::participantStatus(NULL, "is_counted = 1");
    if (!array_key_exists($participant['participant_status_id'], $statusTypes)) {
      return -1;
    }

    // get the custom field that stores if the contact should be sync'd
    $syncFieldId = $this->config->getCustomFieldIdParticipantSyncWithMoodle();
    $syncThisParticipant = civicrm_api3('CustomValue', 'get', [
      'sequential' => 1,
      'entity_id' => $participant['id'],
      'entity_table' => 'civicrm_participant',
      'return' => "custom_$syncFieldId",
    ]);

    // skip if sync contact = "no"
    if ($syncThisParticipant['count'] == 1 && !empty($syncThisParticipant['values'][0]['latest']) && $syncThisParticipant['values'][0]['latest'] == 0) {
      // skip
      return -1;
    }
    else {
      // get the custom field that stores the enrolment id
      $enrolmentField = $this->config->getCustomFieldIdParticipantMoodleId();
      $enrolmentId = civicrm_api3('CustomValue', 'get', [
        'sequential' => 1,
        'entity_id' => $participant['id'],
        'entity_table' => 'civicrm_contact',
        'return' => "custom_$enrolmentField",
      ]);

      // see if we already have an enrolment id
      if ($enrolmentId['count'] > 0 && $enrolmentId['values'][0]['latest']) {
        // yes, no sync needed
        $enrolmentId = $enrolmentId['values'][0]['latest'];
      }
      else {
        // get the user id of this contact
        $contact = civicrm_api3('Contact', 'getsingle', ['id' => $participant['contact_id']]);
        $userId = $this->syncContact($contact, TRUE);

        // get the course id
        $event = civicrm_api3('Event', 'getsingle', ['id' => $participant['event_id']]);
        $courseId = $this->syncEvent($event);

        // make sure we have a user id and a course id
        if ($userId > 0 && $courseId > 0) {
          // get the moodle role
          $roleId = $this->config->getMoodleRoleFromCiviRole('map_role_id_' . $participant['participant_role_id']);

          // create the enrolment in Moodle
          $moodleApi = new CRM_Moodlesync_API($this->config);
          $moodleApi->createEnrolment($roleId, $userId, $courseId);

          // the Moodle api does not return an enrolment id
          // so we store the course id, with a link to the particpants
          $enrolmentId = $courseId;

          // set the url to the enrolment
          $url = $this->config->getEnrolmentURL($enrolmentId);
          $url = "<a href=\"$url\">$url</a>";

          // We can't simply update the custom fields using the API due to transaction/deadlock issues
          $sql = "
            update
              civicrm_value_moodlesync_participant
            set
              moodlesync_sync_with_moodle = 1,
              moodlesync_enrolment_id = %2,
              moodlesync_view_in_moodle = %3
            where
              entity_id = %1
          ";
          $sqlParams = [
            1 => [$participant['id'], 'Integer'],
            2 => [$enrolmentId, 'Integer'],
            3 => [$url, 'String'],
          ];
          CRM_Core_DAO::executeQuery($sql, $sqlParams);
        }
      }
    }

    return $enrolmentId;
  }

}
