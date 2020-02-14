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

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_bulkenrol', get_string('pluginname', 'local_bulkenrol', null, true));

    if ($ADMIN->fulltree) {

        // Create enrolment chooser widget.
        $enroloptions = array();
        foreach (enrol_get_plugins(true) as $name => $plugin) {
            $enroloptions[$name] = get_string('pluginname', 'enrol_'.$name);
        }
        $settings->add(
                new admin_setting_configselect(
                        'local_bulkenrol/enrolplugin',
                        get_string('enrolplugin', 'local_bulkenrol'),
                        get_string('enrolplugin_desc', 'local_bulkenrol'),
                        '',
                        $enroloptions)
        );
        unset($enroloptions);
        
        // #13833 Moodle plugin local_bulkenrol: Konfiguration und Ausgabe der Rolle
        // Create role chooser widget.
        $roleoptions = array();
        // Get some basic data we are going to need.
        $roles = get_all_roles();
        $systemcontext = context_system::instance();
        $rolenames = role_fix_names($roles, $systemcontext, ROLENAME_ORIGINAL);
        if(!empty($rolenames)){
            foreach ($rolenames as $key => $role) {
                if(!array_key_exists($role->id, $roleoptions)){
                    $roleoptions[$role->id] = $role->localname;
                }
            }
        }
        $settings->add(
                new admin_setting_configselect(
                        'local_bulkenrol/role',
                        get_string('role', 'local_bulkenrol'),
                        get_string('role_description', 'local_bulkenrol'),
                        '',
                        $roleoptions
                        )
                );
        unset($roleoptions);
    }

    $ADMIN->add('enrolments', $settings);
}
