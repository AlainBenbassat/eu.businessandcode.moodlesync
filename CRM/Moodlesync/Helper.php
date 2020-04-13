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

    // see if sync should be forced (e.g. when contact is an event participant) or if it's "yes"
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
      'entity_id' => $event->id,
      'entity_table' => 'civicrm_event',
      'return' => "custom_$syncFieldId",
    ]);

    // see if it's "yes"
    if ($syncThisEvent['count'] > 0 && $syncThisEvent['values'][0]['latest'] == 1) {
      // get the custom field that stores the course id
      $courseIdField = $this->config->getCustomFieldIdEventMoodleId();
      $courseId = civicrm_api3('CustomValue', 'get', [
        'sequential' => 1,
        'entity_id' => $event->id,
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
          'entity_id' => $event->id,
          'entity_table' => 'civicrm_event',
          'return' => "custom_$categoryField",
        ]);
        $categoryId = ($courseId['count'] > 0 && $courseId['values'][0]['latest']) ? $category['values'][0]['latest'] : 1;

        // create the course in Moodle
        $moodleApi = new CRM_Moodlesync_API($this->config);
        $courseId = $moodleApi->createCourse($event->id, $event->title, $event->start_date, $event->end_date, $categoryId);

        // store the Moodle course ID
        civicrm_api3('CustomValue', 'create', [
          'entity_id' => $event->id,
          'entity_table' => 'civicrm_event',
          "custom_$courseIdField" => $courseId,
        ]);

        // store the url to the course
        $url = $this->config->getCourseURL($courseId);
        civicrm_api3('CustomValue', 'create', [
          'entity_id' => $event->id,
          'entity_table' => 'civicrm_event',
          "custom_" . $this->config->getCustomFieldIdEventViewInMoodle() => "<a href=\"$url\">$url</a>",
        ]);
      }
    }

    return $courseId;
  }

  public function syncParticipant($participant) {
    $enrolementId = 0;
    return $enrolementId;
  }
}
