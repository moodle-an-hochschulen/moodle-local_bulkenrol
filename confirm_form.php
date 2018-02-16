<?php

defined('MOODLE_INTERNAL') || die();

require_once $CFG->libdir.'/formslib.php';

class local_bulkenrol_confirm_form extends moodleform {
    
    function definition () {
        $local_bulkenrol_key = $this->_customdata['local_bulkenrol_key'];
        $courseid = $this->_customdata['courseid'];
        
        $mform = $this->_form;
        
        $mform->addElement('hidden', 'key');
        $mform->setType('key', PARAM_RAW);
        $mform->setDefault('key', $local_bulkenrol_key);
        
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', $courseid);
        
        $this->add_action_buttons(true, get_string('enrol_users', 'local_bulkenrol'));
    }
}
