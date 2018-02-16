<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Seite Einschreiben von Usern anhand einer Emailliste
 *
 * @package    local
 * @subpackage bulkenrol
 * @copyright  2017 Soon-Systems GmbH {@link https://soon-systems.de}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/local/bulkenrol/locallib.php');
require_once($CFG->dirroot.'/local/bulkenrol/bulkenrol_form.php');
require_once($CFG->dirroot.'/local/bulkenrol/confirm_form.php');


###################################################################

$id     = optional_param('id', 0, PARAM_INT); // This are required.
$local_bulkenrol_key = optional_param('key', 0, PARAM_RAW);

$context = context_system::instance();

if(!empty($id)){
    $course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);
    $context = context_course::instance($course->id, MUST_EXIST);
    
    $PAGE->set_context($context);
    $PAGE->set_url('/local/bulkenrol/index.php', array('id' => $id));
    $PAGE->set_title("$course->shortname: ".get_string('pluginname', 'local_bulkenrol'));
    $PAGE->set_heading($course->fullname);
    
    
    require_login($course);
}

if (!has_capability('local/bulkenrol:enrolusers', $context)) {
    print_error('nopermissions', 'error', '', 'local/bulkenrol:enrolusers');
}

$PAGE->set_pagelayout('incourse');


if(empty($local_bulkenrol_key)){
    $form = new local_bulkenrol_form(null, array('courseid' => $id));
    if ($formdata = $form->get_data()) {
        $emails = $formdata->usermails;
        $courseid = $formdata->id;
        
        $checked_mails = local_bulkenrol_check_user_mails($emails);
        
        // Create local_bulkenrol array in Session
        if(!isset($SESSION->local_bulkenrol)){
            $SESSION->local_bulkenrol = array();
        }
        // Save data in Session
        $local_bulkenrol_key = $courseid.'_'.time();
        $SESSION->local_bulkenrol[$local_bulkenrol_key] = $checked_mails;
        
    } 
    else if($form->is_cancelled()){
        if(!empty($id)){
            redirect($CFG->wwwroot .'/course/view.php?id='.$id, '', 0);
        }
        else{
            redirect($CFG->wwwroot, '', 0);
        }
    }
    else {
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('pluginname', 'local_bulkenrol'));
        echo $form->display();
        echo $OUTPUT->footer();
    }
}

if ($local_bulkenrol_key) {
    $form2 = new local_bulkenrol_confirm_form(null, array('local_bulkenrol_key' => $local_bulkenrol_key, 'courseid' => $id));
    
    if ($formdata = $form2->get_data()) {
        global $SESSION;
    
        if(!empty($local_bulkenrol_key) && !empty($SESSION->local_bulkenrol) && array_key_exists($local_bulkenrol_key, $SESSION->local_bulkenrol)){
            set_time_limit(600);
    
//             echo $OUTPUT->header();
//             echo $OUTPUT->heading(get_string('pluginname', 'local_bulkenrol'));
            
            local_bulkenrol_users($local_bulkenrol_key);
    
            redirect($CFG->wwwroot .'/local/bulkenrol/index.php?id='.$id, '', 0);
//             echo $OUTPUT->footer();
        }
        else{
            redirect($CFG->wwwroot .'/local/bulkenrol/index.php?id='.$id, '', 0);
        }
    }
    else if($form2->is_cancelled()){
        redirect($CFG->wwwroot .'/local/bulkenrol/index.php?id='.$id, '', 0);
    }
    else {
        
        $PAGE->set_url('/local/bulkenrol/index.php', array('id' => $id));
        
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('pluginname', 'local_bulkenrol'));
        if(!empty($local_bulkenrol_key) && !empty($SESSION->local_bulkenrol) && array_key_exists($local_bulkenrol_key, $SESSION->local_bulkenrol)){
            $local_bulkenrol_data = $SESSION->local_bulkenrol[$local_bulkenrol_key];
    
            if(!empty($local_bulkenrol_data)){
                local_bulkenrol_display_table($local_bulkenrol_data, LOCALBULKENROL_HINT);
                local_bulkenrol_display_table($local_bulkenrol_data, LOCALBULKENROL_ENROLUSERS);
            }
        }
    
        echo $form2->display();
        echo $OUTPUT->footer();
    }
}
