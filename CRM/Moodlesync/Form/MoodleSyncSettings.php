<?php

use CRM_Moodlesync_ExtensionUtil as E;

class CRM_Moodlesync_Form_MoodleSyncSettings extends CRM_Core_Form {
  public function buildQuickForm() {
    $elements = [];
    $buttons = [];
    $defaults = [];

    // get the MoodleSync configuration
    $config = CRM_Moodlesync_Config::singleton();

    // set the title
    CRM_Utils_System::setTitle(E::ts('Moodle Synchronization Settings'));

    // add the Save button
    $buttons[] = [
      'type' => 'submit',
      'name' => E::ts('Save'),
      'isDefault' => TRUE,
    ];

    // add the fields
    $this->add('text', 'url','Moodle URL', NULL, TRUE);
    $elements[] = 'url';
    $defaults['url'] = $config->getMoodleURL();

    $this->add('text', 'token', 'Token', NULL, TRUE);
    $elements[] = 'token';
    $defaults['token'] = $config->getMoodleToken();

    $moodleRoles = $this->getMoodleRoles();
    $participantRoles = CRM_Event_PseudoConstant::participantRole();
    foreach ($participantRoles as $roleID => $roleName) {
      $label = "Map participant role '$roleName' to Moodle role";
      $name = "map_role_id_$roleID";
      $this->add('select', $name, $label, $moodleRoles, FALSE);
      $elements[] = $name;
      $v = $config->getMoodleRoleFromCiviRole($name);
      if ($v) {
        $defaults[$name] = $v;
      }
    }

    // add the Test button if we have a url and token
    if ($defaults['url'] && $defaults['token']) {
      $buttons[] = [
        'type' => 'refresh',
        'name' => E::ts('Test'),
        'icon' => 'fa-check-circle',
      ];
    }

    // add the fields and buttons to the template
    $this->setDefaults($defaults);
    $this->addButtons($buttons);
    $this->assign('elementNames', $elements);
    parent::buildQuickForm();
  }

  public function postProcess() {
    $config = CRM_Moodlesync_Config::singleton();
    $values = $this->exportValues();

    // store values
    $config->setMoodleURL($values['url']);
    $config->setMoodleToken($values['token']);
    foreach ($values as $k => $v) {
      if (strpos($k, 'map_role_id_') === 0) {
        $config->setMoodleRoleFromCiviRole($k, $v);
      }
    }

    // make sure the custom fields exist
    $config->createCustomFields();

    // test the settings?
    if (array_key_exists('_qf_MoodleSyncSettings_refresh', $values)) {
      try {
        $moodleApi = new CRM_Moodlesync_API($config);
        $moodleApi->testConnection();
        CRM_Core_Session::setStatus(E::ts('OK, successful connection with Moodle!'), E::ts('Success'), 'success');
      }
      catch (Exception $e) {
        CRM_Core_Session::setStatus($e->getMessage(), E::ts('Error'), 'error');
      }
    }

    parent::postProcess();
  }

  private function getRenderableElementNames() {
    $elementNames = array();
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

  private function getMoodleRoles() {
    $moodleRoles = [
      'student' => E::ts('Student'),
      'teacher' => E::ts('Teacher'),
      'manager' => E::ts('Manager'),
      'nonediting' => E::ts('Non-editing Teacher'),
      'nosync' => E::ts('DO NOT SYNCHRONIZE'),
    ];

    return $moodleRoles;
  }

}
