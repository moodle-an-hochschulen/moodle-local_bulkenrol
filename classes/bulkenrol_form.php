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

class bulkenrol_form extends moodleform {

    protected function definition() {
        global $CFG;

        require_once($CFG->dirroot.'/local/bulkenrol/lib.php');

        $mform = $this->_form;

        // Infotext.
        $msg = get_string('infotext', 'local_bulkenrol');
        $mform->addElement('html', '<div class="local_bulkenrol infotext">'.$msg.'</div>');

        // Textarea für Emails.
        $mform->addElement('textarea', 'usermails',
                get_string('usermails', 'local_bulkenrol'), 'wrap="virtual" rows="10" cols="80"');
        $mform->addRule('usermails', null, 'required');

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_RAW);
        $mform->setDefault('id', $this->_customdata['courseid']);

        $this->add_action_buttons(true, get_string('submit'));
    }

    public function validation($data, $files) {
        $retval = array();

        if (empty($data['usermails'])) {
            $retval['usermails'] = get_string('usermails_empty', 'local_bulkenrol');
        }

        return $retval;
    }
}
