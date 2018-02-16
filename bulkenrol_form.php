<?php

defined('MOODLE_INTERNAL') || die();

require_once $CFG->libdir.'/formslib.php';

class local_bulkenrol_form extends moodleform {
    
    function definition () {
        global $CFG;
        
        require_once($CFG->dirroot.'/local/bulkenrol/lib.php');
        
        $mform = $this->_form;
        
        // Infotext
        $msg = get_string('infotext','local_bulkenrol');
        $mform->addElement('html', '<div class="local_bulkenrol infotext">'.$msg.'</div>');
        
        // Textarea für Emails
        $mform->addElement('textarea', 'usermails', get_string('usermails', 'local_bulkenrol'), 'wrap="virtual" rows="10" cols="80"');
        $mform->addRule('usermails', null, 'required');
        
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_RAW);
        $mform->setDefault('id', $this->_customdata['courseid']);
        
        $this->add_action_buttons(true, get_string('submit'));
    }
    
    function validation($data, $files) {
        $retval = array();
        
        if(empty($data['usermails'])){
            $retval['usermails'] = get_string('usermails_empty', 'local_bulkenrol');
        }
        
        return $retval;
    }
}
