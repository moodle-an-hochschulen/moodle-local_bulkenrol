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
 * Local plugin "bulkenrol" - Local Library
 *
 * @package   local_bulkenrol
 * @copyright 2017 Soon Systems GmbH on behalf of Alexander Bias, Ulm University <alexander.bias@uni-ulm.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


define("LOCALBULKENROL_HINT", 'hint');
define("LOCALBULKENROL_ENROLUSERS", 'enrolusers');

/**
 * @param unknown $emails textfield value to be checked for emails
 * @return stdClass Object containing information to be displayed on confirm page and being used for bulkenrol 
 */
function local_bulkenrol_check_user_mails($emails_textfield) {

    $checked_emails = new stdClass();
    $checked_emails->emails_to_ignore = array();
    $checked_emails->error_messages = array();          // [line]->message
    $checked_emails->moodleusers_for_email = array();   // [email]->user
    $checked_emails->course_groups = array();   // course groups
    
    
    $email_delimiters = array(', ', ' ', ',');
    
    if(!empty($emails_textfield)){
        $emails_lines = local_bulkenrol_parse_emails($emails_textfield);
        
        $line_cnt = 0;
        
        $current_group = '';
        
        // process emails from textfield
        foreach ($emails_lines as $email_line) {
            $line_cnt++;
            
            $email_line = trim($email_line);
            
            // check for course group
            $group_pos = strpos($email_line , '#');
            if($group_pos !== false){
                $groupname = substr($email_line, $group_pos+1);
                $current_group = trim($groupname);
                $checked_emails->course_groups[$current_group] = array();
                continue;
            }
            
            
            // check number of emails in current row/line
            $emails_in_line_cnt = substr_count($email_line , '@');
            
            // no email in row/line
            if($emails_in_line_cnt == 0){
                $a = new stdClass();
                $a->line = $line_cnt;
                $a->content = $email_line;
                $error = get_string('no_email', 'local_bulkenrol', $a);
                $checked_emails->error_messages[$line_cnt] = $error;
            }
            // one email in row/line
            else if($emails_in_line_cnt == 1){
                $email = $email_line;
                
                // check for valid email
                $email_is_valid = validate_email($email);
                
                // email is not valid
                if(!$email_is_valid){
                    $a = new stdClass();
                    $a->row = $line_cnt;
                    $a->email = $email;
                    $error = get_string('invalid_email', 'local_bulkenrol', $a);
                    $checked_emails->error_messages[$line_cnt] = $error;
                    $checked_emails->emails_to_ignore[] = $email;
                }
                // email is valid
                else{
                    // check for moodle user with email
                    list($error, $user_record) = local_bulkenrol_get_user($email);
                    
                    if(!empty($error)){
                        $checked_emails->error_messages[$line_cnt] = $error;
                        $checked_emails->emails_to_ignore[] = $email;
                    }
                    else if(!empty($user_record) && !empty($user_record->id)){
                        $checked_emails->moodleusers_for_email[$email] = $user_record;
                        
                        if(!empty($current_group) && array_key_exists($current_group, $checked_emails->course_groups)){
                            $checked_emails->course_groups[$current_group][] = $user_record;
                        }
                    }
                }
            }
            // more than one email in row/line
            if($emails_in_line_cnt > 1){
                $delimiter = '';
                
                // check delimiters
                foreach ($email_delimiters as $email_delimiter) {
                    $pos = strpos($email_line, $email_delimiter);
                    if($pos){
                        $delimiter = $email_delimiter;
                        break;
                    }
                }
                if(!empty($delimiter)){
                    $emails_in_line = explode($delimiter, $email_line);
                    
                    // iterate emails in row/line
                    foreach ($emails_in_line as $email_in_line){
                        
                        $email = trim($email_in_line);
                        
                        // check for valid email
                        $email_is_valid = validate_email($email);
                        
                        // email is not valid
                          if (!$email_is_valid) {
                              $a = new stdClass();
                              $a->row = $line_cnt;
                              $a->email = $email;
                              
                              $error = get_string('invalid_email', 'local_bulkenrol', $a);
                              
                              $checked_emails->emails_to_ignore[] = $email;
                              
                              if(array_key_exists($line_cnt, $checked_emails->error_messages)){
                                  $errors = $checked_emails->error_messages[$line_cnt];
                                  $errors .= "<br>".$error;
                                  $checked_emails->error_messages[$line_cnt] = $errors;
                              }else{
                                  $checked_emails->error_messages[$line_cnt] = $error;
                              }
                        }
                        // email is valid
                        else {
                            // get user(s) for email
                            // check for moodle user with email
                            list($error, $user_record) = local_bulkenrol_get_user($email);
                            
                            if(!empty($error)){
                                if(array_key_exists($line_cnt, $checked_emails->error_messages)){
                                    $errors = $checked_emails->error_messages[$line_cnt];
                                    $errors .= "<br>".$error;
                                    $checked_emails->error_messages[$line_cnt] = $errors;
                                }else{
                                    $checked_emails->error_messages[$line_cnt] = $error;
                                }
                                $checked_emails->emails_to_ignore[] = $email;
                            }
                            else if(!empty($user_record) && !empty($user_record->id)){
                                $checked_emails->moodleusers_for_email[$email] = $user_record;
                                
                                if(!empty($current_group) && array_key_exists($current_group, $checked_emails->course_groups)){
                                    $checked_emails->course_groups[$current_group][] = $user_record;
                                }
                            }
                        }
                    }
                    // END: iterate emails in row/line
                    
                }
            }
        }
        // END: iteriere über Emails (aus Textfeld)
    }

    return $checked_emails;
}

function local_bulkenrol_parse_emails($emails) {
    if (empty($emails)) {
        return array();
    } else {
        $rawlines = explode(PHP_EOL, $emails);
        $result = array();
        foreach ($rawlines as $rawline) {
            $result[] = trim($rawline);
        }
        return $result;
    }
}

function local_bulkenrol_get_user($email) {
    global $CFG, $DB;
    
    $error = null;
    $user_record = null;
    
    if (empty($email)) {
        return array($error, $user_record);
    } else {
        // get user records for email
        try {
            $user_records = $DB->get_records('user', array('email' => $email));
            $count = count($user_records);
            if(!empty($count)){
                // more than one user with email -> ignore email and don't enrol users later!
                if($count > 1){
                    $error = get_string('more_than_one_record_for_email', 'local_bulkenrol', $email);
                }
                else{
                    $user_record = current($user_records);
                }
            }
            else{
                $error = get_string('no_record_found_for_email', 'local_bulkenrol', $email);
            }
        } catch (Exception $e) {
            $error = get_string('error_getting_user_for_email', 'local_bulkenrol', $email).local_bulkenrol_get_exception_info($e);
        }
        
        return array($error, $user_record);
    }
}

/**
 * @param unknown $e should be of instanceof Exception
 * @return string $e->getMessage()." -> ".$e->getTraceAsString()
 */
function local_bulkenrol_get_exception_info($e){
    if(empty($e) || !($e instanceof Exception) ){
        return '';
    }
    return " ".get_string('exception_info', 'local_bulkenrol').": ".$e->getMessage()." -> ".$e->getTraceAsString();
}

function local_bulkenrol_users($local_bulkenrol_key){
    global $CFG, $DB, $SESSION;
    
    $error = 'no_data';
    
    if(!empty($local_bulkenrol_key)){
        if(!empty($local_bulkenrol_key) && !empty($SESSION->local_bulkenrol) && array_key_exists($local_bulkenrol_key, $SESSION->local_bulkenrol)){
            $local_bulkenrol_data = $SESSION->local_bulkenrol[$local_bulkenrol_key];
            if(!empty($local_bulkenrol_data)){
                $error = '';
                
                $courseid = 0;
                
                $tmp_data = explode('_', $local_bulkenrol_key);
                if(!empty($tmp_data)){
                    $courseid = $tmp_data[0];
                }
                
                $users_to_enrol = $local_bulkenrol_data->moodleusers_for_email;
                
                if(!empty($courseid) && !empty($users_to_enrol)){
                    $no_data = false;
                    
                    try {
                        $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
                        
                        $enrolinstances = enrol_get_instances($course->id, false);
                        
                        // get enrolment for bulkenrol
                        $bulkenrol_plugin = get_config('local_bulkenrol','bulkenrol_enrolment');
                        $plugin = enrol_get_plugin($bulkenrol_plugin);
                        
                        $enrolinstance = null;
                        
                        foreach ($enrolinstances as $instance) {
                            // check enrolment
                            if($bulkenrol_plugin == $instance->enrol){
                                if ($instance->status != ENROL_INSTANCE_ENABLED) {
                                    $plugin->update_status($instance, ENROL_INSTANCE_ENABLED);
                                }
                                $enrolinstance = $instance;
                                break;
                            }
                        }
                        
                        if(empty($enrolinstance)){
                            $fields = $plugin->get_instance_defaults();
                            $id = $plugin->add_instance($course, $fields);
                            
                            $enrolinstance = $DB->get_record('enrol', array('id' => $id));
                            $enrolinstance->expirynotify    = $plugin->get_config('expirynotify');
                            $enrolinstance->expirythreshold = $plugin->get_config('expirythreshold');
                            $enrolinstance->roleid = $plugin->get_config('roleid');
                            $enrolinstance->timemodified = time();
                            $DB->update_record('enrol', $enrolinstance);
                        }
                        
                        if(!empty($enrolinstance)){
                            // Enrol users in course
                            $roleid = $enrolinstance->roleid;
                            foreach ($users_to_enrol as $user) {
                                $plugin->enrol_user($enrolinstance, $user->id, $roleid);
                            }
                        }
                    } catch (Exception $e) {
                        $msg = get_string('error_enrol_users', 'local_bulkenrol').local_bulkenrol_get_exception_info($e);
                        echo html_writer::tag('div', $msg, array('class' => 'local_bulkenrol error'));
                    }
                    
                    // check for course groups to create
                    $groups = $local_bulkenrol_data->course_groups;
                    
                    if(!empty($groups)){
                        
                        try {
                            require_once($CFG->dirroot . '/group/lib.php');
                            
                            $existing_course_groups = groups_get_all_groups($courseid);
                            
                            foreach ($groups as $name => $members) {
                                $groupname = trim($name);
                                
                                // check if group already exists
                                $groupid = null;
                                foreach ($existing_course_groups as $key => $existing_course_group) {
                                    if($groupname == $existing_course_group->name){
                                        $groupid = $existing_course_group->id;
                                        break;
                                    }
                                }
                                // group not found in course -> create new course group
                                if(empty($groupid)){
                                    $groupdata = new stdClass();
                                    $groupdata->courseid = $courseid;
                                    $groupdata->name = $groupname;
                                    $groupid = groups_create_group($groupdata, false, false);
                                }
                                if(!empty($groupid) && !empty($members)){
                                    foreach ($members as $key => $member) {
                                        $user_added = groups_add_member($groupid, $member->id);
                                    }
                                }
                            }
                        } catch (Exception $e) {
                            $msg = get_string('error_group_add_members', 'local_bulkenrol').local_bulkenrol_get_exception_info($e);
                            echo html_writer::tag('div', $msg, array('class' => 'local_bulkenrol error'));
                        }
                    }
                }
                else{
                    $error = 'no_courseid_or_no_users_to_enrol';
                }
            }
        }
    }
    
    if(!empty($error)){
        $msg = get_string($error, 'local_bulkenrol');
        echo html_writer::tag('div', $msg, array('class' => 'local_bulkenrol error'));
    }
    else{
        $msg = get_string('enrol_users_successful', 'local_bulkenrol');
        echo html_writer::tag('div', $msg, array('class' => 'local_bulkenrol error'));
    }
}

function local_bulkenrol_display_table($local_bulkenrol_data, $key){
    global $OUTPUT;
    
    if(!empty($local_bulkenrol_data) && !empty($key)){
        
        switch ($key) {
            case LOCALBULKENROL_HINT:
                
                $data = array();
                
                if(!empty($local_bulkenrol_data->error_messages)){
                    foreach ($local_bulkenrol_data->error_messages as $line => $error_messages) {
                        $row = array();
                        
                        $cell = new html_table_cell();
                        $cell->text = $line;
                        $row[] = $cell;
                        
                        $cell = new html_table_cell();
                        $cell->text = $error_messages;
                        $row[] = $cell;
                        
                        $data[] = $row;
                    }
                }
                
                $table = new html_table();
                $table->id = "localbulkenrol_hints";
                $table->attributes['class'] = 'generaltable';
                $table->tablealign = 'center';
                $table->summary = get_string('hints_label', 'local_bulkenrol');
                $table->size = array('10%', '90%');
                $table->head = array();
                $table->head[] = get_string('row', 'local_bulkenrol');
                $table->head[] = get_string('hints_label', 'local_bulkenrol');
                $table->data = $data;
                
                echo $OUTPUT->heading(get_string('hints_label', 'local_bulkenrol'), 3);
                echo html_writer::tag('div', html_writer::table($table), array('class' => 'flexible-wrap'));
                
            break;
            
            case LOCALBULKENROL_ENROLUSERS:
                $data = array();
                
                if(!empty($local_bulkenrol_data->moodleusers_for_email)){
                    foreach ($local_bulkenrol_data->moodleusers_for_email as $email => $user) {
                        $row = array();
                        
                        $cell = new html_table_cell();
                        $cell->text = $user->email;
                        $row[] = $cell;
                        
                        $cell = new html_table_cell();
                        $cell->text = $user->firstname;
                        $row[] = $cell;
                        
                        $cell = new html_table_cell();
                        $cell->text = $user->lastname;
                        $row[] = $cell;
                
                        $data[] = $row;
                    }
                }
                
                $table = new html_table();
                $table->id = "localbulkenrol_enrolusers";
                $table->attributes['class'] = 'generaltable';
                $table->tablealign = 'center';
                $table->summary = get_string('users_to_enrol_in_course_label', 'local_bulkenrol');
                $table->size = array('33%', '33%', '33%');
                $table->head = array();
                $table->head[] = get_string('email');
                $table->head[] = get_string('firstname');
                $table->head[] = get_string('lastname');
                $table->data = $data;
                
                echo $OUTPUT->heading(get_string('users_to_enrol_in_course_label', 'local_bulkenrol'), 3);
                echo html_writer::tag('div', html_writer::table($table), array('class' => 'flexible-wrap'));
                break;
                
            default:
                ;
            break;
        }
    }
}
