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
 * bulkenrol plugin upgrade code
 *
 * @package     local_bulkenrol
 * @copyright   2022 Marco Ferrante, University of Genoa (I) <marco@csita.unige.it>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Function to upgrade bulkenrol.
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_local_bulkenrol_upgrade($oldversion) {
    global $CFG, $DB;

    if ($oldversion < 2022100600) {
        // Enable e-mail address as key field if unique
        if (empty($CFG->allowaccountssameemail)) {
            set_config('fieldoptions', ["u_email"], 'local_bulkenrol');
            upgrade_plugin_savepoint(true, 2022100600, 'local', 'bulkenrol');
        }
    }
    
    return true;
}
