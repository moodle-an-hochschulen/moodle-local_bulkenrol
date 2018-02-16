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
        $enrol_options = array();
        foreach (enrol_get_plugins(true) as $name => $plugin) {
            $enrol_options[$name] = get_string('pluginname', 'enrol_'.$name);
        }
        
        $settings->add(
                new admin_setting_configselect(
                        'local_bulkenrol/bulkenrol_enrolment',
                        get_string('bulkenrol_enrolment', 'local_bulkenrol'),
                        get_string('bulkenrol_enrolment_description', 'local_bulkenrol'),
                        '',
                        $enrol_options)
        );
        unset($enrol_options);
    }
    
    $ADMIN->add('localplugins', $settings);

}
