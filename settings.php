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
 * Local plugin "bulkenrol" - Settings
 *
 * @package   local_bulkenrol
 * @copyright 2017 Soon Systems GmbH on behalf of Alexander Bias, Ulm University <alexander.bias@uni-ulm.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

// Require library.
require_once($CFG->dirroot . '/local/bulkenrol/lib.php');

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_bulkenrol', get_string('pluginname', 'local_bulkenrol', null, true));

    if ($ADMIN->fulltree) {
        // Require local library.
        require_once($CFG->dirroot . '/local/bulkenrol/locallib.php');

        // Create enrolment chooser widget.
        // From the Moodle core enrolment methods, only the ones which are suitable for bulk enrolments are offered.
        $settings->add(
            new admin_setting_configselect(
                'local_bulkenrol/enrolplugin',
                get_string('enrolplugin', 'local_bulkenrol'),
                get_string('enrolplugin_desc', 'local_bulkenrol') . '<br /><br />' .
                        get_string('enrolplugin_desc_defaults', 'local_bulkenrol') . '<br /><br />' .
                        get_string('enrolplugin_desc_restriction', 'local_bulkenrol'),
                local_bulkenrol_get_enrolplugin_default(),
                local_bulkenrol_get_enrolplugin_options()
            )
        );

        // Create role chooser widget.
        $roleoptions = [];
        // Get some basic data we are going to need.
        $roles = get_all_roles();
        $systemcontext = context_system::instance();
        $rolenames = role_fix_names($roles, $systemcontext, ROLENAME_ORIGINAL);
        if (!empty($rolenames)) {
            foreach ($rolenames as $key => $role) {
                if (!array_key_exists($role->id, $roleoptions)) {
                    $roleoptions[$role->id] = $role->localname;
                }
            }
        }
        // Get first default role for 'student' archetype.
        $studentarchetype = get_archetype_roles('student');
        if ($studentarchetype != false && count($studentarchetype) > 0) {
            $firststudentrole = array_shift($studentarchetype);
            $firststudentroleid = $firststudentrole->id;
        } else {
            $firststudentroleid = '';
        }
        $settings->add(
            new admin_setting_configselect(
                'local_bulkenrol/role',
                get_string('role', 'local_bulkenrol'),
                get_string('role_description', 'local_bulkenrol'),
                $firststudentroleid,
                $roleoptions
            )
        );
        unset($roleoptions);

        // Create navigation node placement widget.
        $navigationoptions = [LOCALBULKENROL_NAV_COURSE => get_string('nav_course', 'local_bulkenrol'),
                LOCALBULKENROL_NAV_PARTICIPANTS => get_string('nav_participants', 'local_bulkenrol'),
                LOCALBULKENROL_NAV_BOTH => get_string('nav_both', 'local_bulkenrol'), ];
        $settings->add(
            new admin_setting_configselect(
                'local_bulkenrol/navigation',
                get_string('navigation', 'local_bulkenrol'),
                get_string('navigation_desc', 'local_bulkenrol'),
                LOCALBULKENROL_NAV_PARTICIPANTS,
                $navigationoptions
            )
        );
        unset($navigationoptions);
    }

    $ADMIN->add('enrolments', $settings);
}
