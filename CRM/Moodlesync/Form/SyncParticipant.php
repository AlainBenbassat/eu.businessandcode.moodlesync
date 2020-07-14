<?php

use CRM_Moodlesync_ExtensionUtil as E;

class CRM_Moodlesync_Form_SyncParticipant extends CRM_Event_Form_Task {
  private $queue;
  private $queueName = 'moodlesyncparticipant';

  public function __construct() {
    // create the queue
    $this->queue = CRM_Queue_Service::singleton()->create([
      'type' => 'Sql',
      'name' => $this->queueName,
      'reset' => TRUE, // flush queue upon creation
    ]);

    parent::__construct();
  }

  public function preProcess() {
    parent::preProcess();
  }

  public function buildQuickForm() {
    $this->addDefaultButtons(E::ts('Synchronize participants with Moodle'), 'done');
  }

  public function postProcess() {
    // store all the selected participant id's in the queue
    foreach ($this->_participantIds as $participantId) {
      $task = new CRM_Queue_Task(['CRM_Moodlesync_Form_SyncParticipant', 'syncParticipantTask'], [$participantId]);
      $this->queue->createItem($task);
    }

    // run the queue
    $runner = new CRM_Queue_Runner([
      'title' => E::ts('Synchronize participants with Moodle'),
      'queue' => $this->queue,
      'errorMode'=> CRM_Queue_Runner::ERROR_CONTINUE,
    ]);
    $runner->runAllViaWeb();
  }

  public static function syncParticipantTask(CRM_Queue_TaskContext $ctx, $id) {
    $syncHelper = new CRM_Moodlesync_Helper();
    $participant = civicrm_api3('Participant', 'getsingle', ['id' => $id]);
    $syncHelper->syncParticipant($participant);
    return TRUE;
  }
}
