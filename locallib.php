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

define('LOCALBULKENROL_HINT', 'hint');
define('LOCALBULKENROL_ENROLUSERS', 'enrolusers');

/**
 * Check list of submitted user mails and creates a data structure for displaying information on the confirm page and for performing the bulkenrol.
 *
 * @param string $emailstextfield Text field value to be checked for emails and course groups.
 * @param int $courseid ID of the course, used to determine the context for checking whether a user is already enroled. 
 * @return stdClass Object containing information to be displayed on confirm page and being used for bulkenrol.
 */
function local_bulkenrol_check_user_mails($emailstextfield) {

    $checkedemails = new stdClass();
    $checkedemails->emails_to_ignore = array();
    $checkedemails->error_messages = array();
    $checkedemails->moodleusers_for_email = array();
    $checkedemails->course_groups = array();

    $emaildelimiters = array(', ', ' ', ',');

    if (!empty($emailstextfield)) {
        $emailslines = local_bulkenrol_parse_emails($emailstextfield);

        $linecnt = 0;

        $currentgroup = '';

        // Process emails from textfield.
        foreach ($emailslines as $emailline) {
            $linecnt++;

            $emailline = trim($emailline);

            // Check for course group.
            $grouppos = strpos($emailline , '#');
            if ($grouppos !== false) {
                $groupname = substr($emailline, $grouppos + 1);
                $currentgroup = trim($groupname);
                $checkedemails->course_groups[$currentgroup] = array();
                continue;
            }

            // Check number of emails in current row/line.
            $emailsinlinecnt = substr_count($emailline , '@');

            // No email in row/line.
            if ($emailsinlinecnt == 0) {
                $a = new stdClass();
                $a->line = $linecnt;
                $a->content = $emailline;
                $error = get_string('error_no_email', 'local_bulkenrol', $a);
                $checkedemails->error_messages[$linecnt] = $error;

                // One email in row/line.
            } else if ($emailsinlinecnt == 1) {
                $email = $emailline;

                // Check for valid email.
                $emailisvalid = validate_email($email);

                // Email is not valid.
                if (!$emailisvalid) {
                    $a = new stdClass();
                    $a->row = $linecnt;
                    $a->email = $email;
                    $error = get_string('error_invalid_email', 'local_bulkenrol', $a);
                    $checkedemails->error_messages[$linecnt] = $error;
                    $checkedemails->emails_to_ignore[] = $email;

                    // Email is valid.
                } else {
                    // Check for moodle user with email.
                    list($error, $userrecord) = local_bulkenrol_get_user($email);

                    if (!empty($error)) {
                        $checkedemails->error_messages[$linecnt] = $error;
                        $checkedemails->emails_to_ignore[] = $email;
                    } else if (!empty($userrecord) && !empty($userrecord->id)) {
                        $checkedemails->moodleusers_for_email[$email] = $userrecord;

                        if (!empty($currentgroup) && array_key_exists($currentgroup, $checkedemails->course_groups)) {
                            $checkedemails->course_groups[$currentgroup][] = $userrecord;
                        }
                    }
                }
            }
            // More than one email in row/line.
            if ($emailsinlinecnt > 1) {
                $delimiter = '';

                // Check delimiters.
                foreach ($emaildelimiters as $emaildelimiter) {
                    $pos = strpos($emailline, $emaildelimiter);
                    if ($pos) {
                        $delimiter = $emaildelimiter;
                        break;
                    }
                }
                if (!empty($delimiter)) {
                    $emailsinline = explode($delimiter, $emailline);

                    // Iterate emails in row/line.
                    foreach ($emailsinline as $emailinline) {

                        $email = trim($emailinline);

                        // Check for valid email.
                        $emailisvalid = validate_email($email);

                        // Email is not valid.
                        if (!$emailisvalid) {
                            $a = new stdClass();
                            $a->row = $linecnt;
                            $a->email = $email;

                            $error = get_string('error_invalid_email', 'local_bulkenrol', $a);

                            $checkedemails->emails_to_ignore[] = $email;

                            if (array_key_exists($linecnt, $checkedemails->error_messages)) {
                                $errors = $checkedemails->error_messages[$linecnt];
                                $errors .= "<br>".$error;
                                $checkedemails->error_messages[$linecnt] = $errors;
                            } else {
                                  $checkedemails->error_messages[$linecnt] = $error;
                            }

                            // Email is valid.
                        } else {
                            // Get user(s) for email.
                            // Check for moodle user with email.
                            list($error, $userrecord) = local_bulkenrol_get_user($email);

                            if (!empty($error)) {
                                if (array_key_exists($linecnt, $checkedemails->error_messages)) {
                                    $errors = $checkedemails->error_messages[$linecnt];
                                    $errors .= "<br>".$error;
                                    $checkedemails->error_messages[$linecnt] = $errors;
                                } else {
                                    $checkedemails->error_messages[$linecnt] = $error;
                                }
                                $checkedemails->emails_to_ignore[] = $email;
                            } else if (!empty($userrecord) && !empty($userrecord->id)) {
                                $checkedemails->moodleusers_for_email[$email] = $userrecord;

                                if (!empty($currentgroup) && array_key_exists($currentgroup, $checkedemails->course_groups)) {
                                    $checkedemails->course_groups[$currentgroup][] = $userrecord;
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    return $checkedemails;
}

/**
 * Takes input from text area containing a list of e-mail adresses (optionally group names starting with '#').
 * Returns an array representation of the input.
 *
 * @param unknown $emails input value of the text area.
 * @return array of e-emails and optional group names
 */
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

/**
 * Takes an e-mail and returns a moodle user record and error string (if occured).
 *
 * @param string $email E-mail used to search for a user
 * @return multitype:NULL |multitype:string mixed
 */
function local_bulkenrol_get_user($email) {
    global $DB;

    $error = null;
    $userrecord = null;

    if (empty($email)) {
        return array($error, $userrecord);
    } else {
        // Get user records for email.
        try {
            $userrecords = $DB->get_records('user', array('email' => $email));
            $count = count($userrecords);
            if (!empty($count)) {
                // More than one user with email -> ignore email and don't enrol users later!
                if ($count > 1) {
                    $error = get_string('error_more_than_one_record_for_email', 'local_bulkenrol', $email);
                } else {
                    $userrecord = current($userrecords);
                }
            } else {
                $error = get_string('error_no_record_found_for_email', 'local_bulkenrol', $email);
            }
        } catch (Exception $e) {
            $error = get_string('error_getting_user_for_email', 'local_bulkenrol', $email).local_bulkenrol_get_exception_info($e);
        }

        return array($error, $userrecord);
    }
}

/**
 * Get an understandable reason from an exception which happened during bulkenrol.
 *
 * @param $e should be of instanceof Exception
 * @return string $e->getMessage()." -> ".$e->getTraceAsString()
 */
function local_bulkenrol_get_exception_info($e) {
    if (empty($e) || !($e instanceof Exception) ) {
        return '';
    }

    return " ".get_string('error_exception_info', 'local_bulkenrol').": ".$e->getMessage()." -> ".$e->getTraceAsString();
}

/**
 * Perform user enrolment into the course and optionally add users as member into course groups. Groups are created if necessary.
 *
 * @param unknown $localbulkenrolkey
 */
function local_bulkenrol_users($localbulkenrolkey) {
    global $CFG, $DB, $SESSION;

    $error = '';
    $exceptions_msg = array();

    if (!empty($localbulkenrolkey)) {
        if (!empty($localbulkenrolkey) && !empty($SESSION->local_bulkenrol) &&
                array_key_exists($localbulkenrolkey, $SESSION->local_bulkenrol)) {
            $localbulkenroldata = $SESSION->local_bulkenrol[$localbulkenrolkey];
            if (!empty($localbulkenroldata)) {
                $error = '';

                $courseid = 0;

                $tmpdata = explode('_', $localbulkenrolkey);
                if (!empty($tmpdata)) {
                    $courseid = $tmpdata[0];
                }

                $userstoenrol = $localbulkenroldata->moodleusers_for_email;

                if (!empty($courseid) && !empty($userstoenrol)) {
                    try {
                        $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

                        $enrolinstances = enrol_get_instances($course->id, false);

                        // Get enrolment for bulkenrol.
                        $bulkenrolplugin = get_config('local_bulkenrol', 'enrolplugin');
                        $plugin = enrol_get_plugin($bulkenrolplugin);

                        $enrolinstance = null;

                        foreach ($enrolinstances as $instance) {
                            // Check enrolment.
                            if ($bulkenrolplugin == $instance->enrol) {
                                if ($instance->status != ENROL_INSTANCE_ENABLED) {
                                    $plugin->update_status($instance, ENROL_INSTANCE_ENABLED);
                                }
                                $enrolinstance = $instance;
                                break;
                            }
                        }

                        if (empty($enrolinstance)) {
                            $fields = $plugin->get_instance_defaults();
                            $id = $plugin->add_instance($course, $fields);

                            $enrolinstance = $DB->get_record('enrol', array('id' => $id));
                            $enrolinstance->expirynotify    = $plugin->get_config('expirynotify');
                            $enrolinstance->expirythreshold = $plugin->get_config('expirythreshold');
                            $enrolinstance->roleid = $plugin->get_config('roleid');
                            $enrolinstance->timemodified = time();
                            $DB->update_record('enrol', $enrolinstance);
                        }

                        if (!empty($enrolinstance)) {
                            // Enrol users in course.
                            $roleid = $enrolinstance->roleid;
                            foreach ($userstoenrol as $user) {
                                try {
                                    $plugin->enrol_user($enrolinstance, $user->id, $roleid);
                                } catch (Exception $e) {
                                    $a = new stdClass();
                                    $a->email = $user->email;
                                    
                                    $msg = get_string('error_enrol_user', 'local_bulkenrol', $a).local_bulkenrol_get_exception_info($e);
                                    $exceptions_msg[] = $msg;
                                }
                            }
                        }
                    } catch (Exception $e) {
                        $msg = get_string('error_enrol_users', 'local_bulkenrol').local_bulkenrol_get_exception_info($e);
                        $exceptions_msg[] = $msg;
                    }

                    // Check for course groups to create.
                    $groups = $localbulkenroldata->course_groups;

                    if (!empty($groups)) {

                        try {
                            require_once($CFG->dirroot . '/group/lib.php');

                            $existingcoursegroups = groups_get_all_groups($courseid);

                            foreach ($groups as $name => $members) {
                                $groupname = trim($name);

                                // Check if group already exists.
                                $groupid = null;
                                foreach ($existingcoursegroups as $key => $existingcoursegroup) {
                                    if ($groupname == $existingcoursegroup->name) {
                                        $groupid = $existingcoursegroup->id;
                                        break;
                                    }
                                }
                                // Group not found in course -> create new course group.
                                if (empty($groupid)) {
                                    $groupdata = new stdClass();
                                    $groupdata->courseid = $courseid;
                                    $groupdata->name = $groupname;
                                    $groupid = groups_create_group($groupdata, false, false);
                                }
                                if (!empty($groupid) && !empty($members)) {
                                    foreach ($members as $key => $member) {
                                        try {
                                            $useradded = groups_add_member($groupid, $member->id);
                                        } catch (Exception $e) {
                                            $a = new stdClass();
                                            $a->email = $member->email;
                                            $a->group = $groupname;
                                            $msg = get_string('error_group_add_member', 'local_bulkenrol', $a).local_bulkenrol_get_exception_info($e);
                                            $exceptions_msg[] = $msg;
                                        }
                                    }
                                }
                            }
                        } catch (Exception $e) {
                            $msg = get_string('error_group_add_members', 'local_bulkenrol').local_bulkenrol_get_exception_info($e);
                            $exceptions_msg[] = $msg;
                        }
                    }
                } else {
                    $error = 'error_no_courseid_or_no_users_to_enrol';
                }
            }
        }
    }

    $retval = new stdClass();
    $retval->status = '';
    $retval->text = '';

    if (!empty($error) || !empty($exceptions_msg)) {
        $retval->status = 'error';

        if(!empty($error)){
            $error_msg = get_string($error, 'local_bulkenrol');
            $retval->text = $error_msg;
        }

        if(!empty($exceptions_msg)){
            if(!empty($error)){
                $retval->text .= '<br>';
            }
            $retval->text .= implode('<br>', $exceptions_msg);
        }
    } 
    else {
        $msg = get_string('enrol_users_successful', 'local_bulkenrol');
        $retval->text = $msg;
    }

    return $retval;
}

/**
 * According to the parameter, either a table with hints is displayed or a table with users to be written is displayed.
 * @param unknown $localbulkenroldata
 * @param unknown $key
 */
function local_bulkenrol_display_table($localbulkenroldata, $key) {
    global $OUTPUT;

    if (!empty($localbulkenroldata) && !empty($key)) {

        switch ($key) {
            case LOCALBULKENROL_HINT:

                $data = array();

                if (!empty($localbulkenroldata->error_messages)) {
                    foreach ($localbulkenroldata->error_messages as $line => $errormessages) {
                        $row = array();

                        $cell = new html_table_cell();
                        $cell->text = $line;
                        $row[] = $cell;

                        $cell = new html_table_cell();
                        $cell->text = $errormessages;
                        $row[] = $cell;

                        $data[] = $row;
                    }
                }

                $table = new html_table();
                $table->id = "localbulkenrol_hints";
                $table->attributes['class'] = 'generaltable';
                $table->summary = get_string('hints', 'local_bulkenrol');
                $table->size = array('10%', '90%');
                $table->head = array();
                $table->head[] = get_string('row', 'local_bulkenrol');
                $table->head[] = get_string('hints', 'local_bulkenrol');
                $table->data = $data;

                if(!empty($data)){
                    echo $OUTPUT->heading(get_string('hints', 'local_bulkenrol'), 3);
                    echo html_writer::tag('div', html_writer::table($table), array('class' => 'flexible-wrap'));
                }
                break;

            case LOCALBULKENROL_ENROLUSERS:
                $data = array();

                if (!empty($localbulkenroldata->moodleusers_for_email)) {
                    foreach ($localbulkenroldata->moodleusers_for_email as $email => $user) {
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
                $table->summary = get_string('users_to_enrol_in_course', 'local_bulkenrol');
                $table->size = array('33%', '33%', '33%');
                $table->head = array();
                $table->head[] = get_string('email');
                $table->head[] = get_string('firstname');
                $table->head[] = get_string('lastname');
                $table->data = $data;

                if(!empty($data)){
                    echo $OUTPUT->heading(get_string('users_to_enrol_in_course', 'local_bulkenrol'), 3);
                    echo html_writer::tag('div', html_writer::table($table), array('class' => 'flexible-wrap'));
                }
                break;

            default:
            break;
        }
    }
}
