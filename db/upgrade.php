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
 * Local plugin "bulkenrol" - Upgrade script
 *
 * @package   local_bulkenrol
 * @copyright 2026 Alexander Bias <bias@alexanderbias.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Function to upgrade local_bulkenrol.
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_local_bulkenrol_upgrade($oldversion) {
    global $CFG, $OUTPUT;

    if ($oldversion < 2026100503) {
        require_once($CFG->dirroot . '/local/bulkenrol/locallib.php');

        // From the Moodle core enrolment methods, only manual and self are allowed for bulk enrolments from now on.
        // Thus, the enrolment plugin setting is reset to its default if it holds another Moodle core enrolment method.

        // Get the configured enrolment method.
        $oldenrolplugin = get_config('local_bulkenrol', 'enrolplugin');

        // If there is an enrolment method configured and if it is not allowed anymore.
        if (!empty($oldenrolplugin) && !local_bulkenrol_is_allowed_enrolplugin($oldenrolplugin)) {
            // Compose the first part of the notice which is the same in both cases.
            $oldpluginname = get_string('pluginname', 'enrol_' . $oldenrolplugin);
            $message = get_string('upgrade_notice_enrolplugin_intro', 'local_bulkenrol', $oldpluginname) . ' ';

            // Reset the setting to its default.
            $newenrolplugin = local_bulkenrol_get_enrolplugin_default();
            if ($newenrolplugin !== null) {
                set_config('enrolplugin', $newenrolplugin, 'local_bulkenrol');
                $newpluginname = get_string('pluginname', 'enrol_' . $newenrolplugin);
                $message .= get_string('upgrade_notice_enrolplugin_reset', 'local_bulkenrol', $newpluginname);

                // Or remove the setting if there isn't any allowed enrolment method enabled.
            } else {
                unset_config('enrolplugin', 'local_bulkenrol');
                $message .= get_string('upgrade_notice_enrolplugin_unset', 'local_bulkenrol');
            }

            // Show a notification.
            $notification = new \core\output\notification($message, \core\output\notification::NOTIFY_WARNING);
            $notification->set_show_closebutton(false);
            echo $OUTPUT->render($notification);
        }

        // Bulkenrol savepoint reached.
        upgrade_plugin_savepoint(true, 2026100503, 'local', 'bulkenrol');
    }

    return true;
}
