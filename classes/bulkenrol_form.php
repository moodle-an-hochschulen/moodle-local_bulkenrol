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
 * Local plugin "bulkenrol" - Enrolment form
 *
 * @package   local_bulkenrol
 * @copyright 2017 Soon Systems GmbH on behalf of Alexander Bias, Ulm University <alexander.bias@uni-ulm.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_bulkenrol;

use moodleform;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->libdir.'/formslib.php');

/**
 * Class bulkenrol_form
 * @package local_bulkenrol
 * @copyright 2017 Soon Systems GmbH on behalf of Alexander Bias, Ulm University <alexander.bias@uni-ulm.de>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class bulkenrol_form extends moodleform {

    /**
     * Form definition. Abstract method - always override!
     */
    protected function definition() {
        global $CFG, $SESSION;

        require_once($CFG->dirroot.'/local/bulkenrol/lib.php');

        $mform = $this->_form;

        // Selector for database field to match list to.
        $availablefieldsstring = get_config('local_bulkenrol', 'fieldoptions');
        $availablefieldsarray = explode(',', $availablefieldsstring);
        if (count($availablefieldsarray) < 1 or $availablefieldsarray[0] == '') {
            print_error(get_string('error_no_options_available', 'local_bulkenrol'));
        }

        $selectoptions = [];
        foreach ($availablefieldsarray as $fieldoption) {
            $selectoptions[$fieldoption] = $this->get_fieldname($fieldoption);
        }

        // Format CSV, replace last , with 'or' and add spaces after remaining.
        $fieldnamestring = implode(', ', $selectoptions);
        $formattedfieldnamestring = $this->str_last_replace(', ', ' ' . get_string('or', 'local_bulkenrol') . ' ', $fieldnamestring);

        // Infotext.
        $msg = get_string('bulkenrol_form_intro', 'local_bulkenrol', $formattedfieldnamestring);
        $mform->addElement('html', '<div id="intro">'.$msg.'</div>');

        // Helptext.
        if ($availablefieldsarray[0] == 'u_username') {
            $helpstringidentifier = 'userlist_username';
        } else if ($availablefieldsarray[0] == 'u_idnumber') {
            $helpstringidentifier = 'userlist_idnumber';
        } else {
            $helpstringidentifier = 'userlist_email';
        }

        $singleoption = count($availablefieldsarray) == 1;
        if (!$singleoption) {
            $mform->addElement('select', 'dbfield', get_string('choose_field', 'local_bulkenrol'), $selectoptions);
            $listfieldtitle = get_string('userlist', 'local_bulkenrol');
        } else {
            $field = $availablefieldsarray[0];
            $mform->addElement('hidden', 'dbfield');
            $mform->setType('dbfield', PARAM_TEXT);
            $mform->setDefault('dbfield', $field);
            $listfieldtitle = get_string('userlist_singleoption', 'local_bulkenrol', $this->get_fieldname($field));
        }
        // Textarea for uservalues.
        $mform->addElement('textarea', 'uservalues',
                $listfieldtitle, 'wrap="virtual" rows="10" cols="80"');
        $mform->addRule('uservalues', null, 'required');
        $mform->addHelpButton('uservalues', $helpstringidentifier, 'local_bulkenrol');

        // Add form content if the user came back to check his input.
        $localbulkenroleditlist = optional_param('editlist', 0, PARAM_ALPHANUMEXT);
        if (!empty($localbulkenroleditlist)) {
            $localbulkenroldata = $localbulkenroleditlist.'_data';
            if (!empty($localbulkenroldata) && !empty($SESSION->local_bulkenrol_inputs) &&
                    array_key_exists($localbulkenroldata, $SESSION->local_bulkenrol_inputs)) {
                $formdatatmp = $SESSION->local_bulkenrol_inputs[$localbulkenroldata]['users'];
                $dbfield = $SESSION->local_bulkenrol_inputs[$localbulkenroldata]['dbfield'];
                $mform->setDefault('uservalues', $formdatatmp);
                $mform->setDefault('dbfield', $dbfield);
            }
        }

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_RAW);
        $mform->setDefault('id', $this->_customdata['courseid']);

        $this->add_action_buttons(true, get_string('enrol_users', 'local_bulkenrol'));
    }

    /**
     * Get each of the rules to validate its own fields
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @return array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK (true allowed for backwards compatibility too).
     */
    public function validation($data, $files) {
        $retval = array();

        if (empty($data['uservalues'])) {
            $retval['uservalues'] = get_string('error_list_empty', 'local_bulkenrol');
        }

        return $retval;
    }

    /**
     * Returns the name of a fieldoption without its table prefix
     * @param string $fieldoption fieldname with type prefix
     * @return string name of field without type prefix
     * @throws \UnexpectedValueException Field is not prefixed by c_ or u_
     * @throws \dml_exception Database connection error
     */
    private function get_fieldname($fieldoption) {
        global $DB;
        $fieldinfo = explode("_", $fieldoption, 2);
        switch ($fieldinfo[0]) {
            case "u":
                return $fieldinfo[1];
            case "c":
                return $DB->get_field('user_info_field', 'name', array("id" => intval($fieldinfo[1])));
            default:
                throw new \UnexpectedValueException("field is not from usertable (u_) or customfield (c_)");
        }
    }

    /**
     * Replaces the last occurence of the needle in a string.
     * @param string $search needle to search for
     * @param string $replace string replacement for needle
     * @param string $subject string subject string to search
     * @return string subject string with the last occurence of the needle replaced
     */
    private function str_last_replace($search, $replace, $subject) {
        $pos = strrpos($subject, $search);

        if ($pos !== false) {
            $subject = substr_replace($subject, $replace, $pos, strlen($search));
        }

        return $subject;
    }
}
