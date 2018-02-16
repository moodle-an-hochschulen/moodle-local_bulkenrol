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
 * Local plugin "bulkenrol" - Language pack
 *
 * @package   local_bulkenrol
 * @copyright 2017 Soon Systems GmbH on behalf of Alexander Bias, Ulm University <alexander.bias@uni-ulm.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['bulkenrol_form_intro'] = 'Here, you can bulk enrol users to your course. A user to be enrolled is identified by his e-mail adress stored in his Moodle account.';
$string['enrol_users_successful'] = 'Enrolment successful';
$string['enrol_users'] = 'Enrol users';
$string['enrolplugin'] = 'Enrolment plugin';
$string['enrolplugin_desc'] = 'The enrolment method to be used to bulk enrol the users. If the configured enrolment method is not active / added in the course when the users are bulk-enrolled, it is automatically added / activated.';
$string['error_enrol_users'] = 'There was a problem when enrolling the user to the course.';
$string['error_exception_info'] = 'Exception information';
$string['error_getting_user_for_email'] = 'There was a problem when getting the user record for e-mail adress <em>{$a}</em> from the database.';
$string['error_group_add_members'] = 'There was a problem when adding the user to the course group(s).';
$string['error_invalid_email'] = 'Invalid e-mail adress found in line {$a->row} (<em>{$a->email}</em>). This line will be ignored.';
$string['error_more_than_one_record_for_email'] = 'More than one existing Moodle user account with e-mail adress <em>{$a}</em>em> found.<br /> This line will be ignored, none of the existing Moodle users will be enrolled.';
$string['error_no_courseid_or_no_users_to_enrol'] = 'Keine KursId oder keine Benutzer zum Einschreiben in den Kurs.'; // TODO: Eliminate the need for this string.
$string['error_no_data'] = 'Keine Benutzer zum Einschreiben in den Kurs.'; // TODO: Eliminate the need for this string.
$string['error_no_email'] = 'No e-mail adress found in line {$a->line} (<em>{$a->content}</em>). This line will be ignored.';
$string['error_no_record_found_for_email'] = 'No existing Moodle user account with e-mail adress <em>{$a}</em>em>.<br />This line will be ignored, there won\'be a Moodle user account created on-the-fly.';
$string['error_usermails_empty'] = 'List of e-mail adresses is empty. Please add at least one e-mail adress.';
$string['pluginname'] = 'User bulk enrolment';
$string['hints'] = 'Hints';
$string['row'] = 'Row';
$string['usermails'] = 'List of e-mail adresses';
$string['usermails_help'] = 'To enrol an existing Moodle user into this course, add his e-mail adress to this form, one user / e-mail adress per line.<br /><br />Example:<br />foo@bar.com<br />bar@foo.com'; // TODO: Add instructions for groups.
$string['users_to_enrol_in_course'] = 'Users to be enrolled into the course';
