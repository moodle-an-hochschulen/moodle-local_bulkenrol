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

$string['bulkenrol:enrolusers'] = 'Use user bulk enrolment';
$string['bulkenrol_form_intro'] = 'Here, you can bulk enrol users to your course. A user to be enrolled is identified by their {$a}.';
$string['choose_field'] = 'Choose field to match list to';
$string['enrol_users_successful'] = 'User bulk enrolment successful';
$string['enrol_users'] = 'Enrol users';
$string['enrolinfo_headline'] = 'Enrolment details';
$string['enrolplugin'] = 'Enrolment plugin';
$string['enrolplugin_desc'] = 'The enrolment method to be used to bulk enrol the users. If the configured enrolment method is not active / added in the course when the users are bulk-enrolled, it is automatically added / activated.';
$string['error_enrol_users'] = 'There was a problem when enrolling the users to the course.';
$string['error_enrol_user'] = 'There was a problem when enrolling the user <em>{$a->data}</em> to the course.';
$string['error_exception_info'] = 'Exception information';
$string['error_getting_user_for_data'] = 'There was a problem when getting the user record for <em>{$a}</em> from the database.';
$string['error_group_add_members'] = 'There was a problem when adding the users to the course group(s).';
$string['error_group_add_member'] = 'There was a problem when adding the user <em>{$a->data}</em> to the course group <em>{$a->group}</em>.';
$string['error_more_than_one_record_for_data'] = 'More than one existing Moodle user account with {$a->field} <em>{$a->identifier}</em> found.<br />This line will be ignored, none of the existing Moodle users will be enrolled.';
$string['error_no_valid_data_in_list'] = 'No valid entrys were found in the given list.<br />Please <a href=\'{$a->url}\'>go back and check your input</a>.';
$string['groupinfos_headline'] = 'Groups included in the list';
$string['group_name_headline'] = 'Group name';
$string['group_status_create'] = 'Group will be created';
$string['group_status_exists'] = 'Group already exists';
$string['group_status_headline'] = 'Group status';
$string['hints'] = 'Hints';
$string['error_no_data'] = 'No data found (<em>{$a}</em>). This line will be ignored.';
$string['error_no_record_found_for_data'] = 'No existing Moodle user account <em>{$a}</em> was found.<br />This line will be ignored, there won\'t be a Moodle user account created on-the-fly.';
$string['error_no_options_available'] = 'Your administrator has disabled all options. Please contact your administrator';
$string['error_list_empty'] = 'List is empty. Please add at least one fieldvalue';
$string['error_check_is_already_member'] = 'Error checking if the user (<em>{$a->data}</em>) is already a member of group (<em>{$a->groupname}</em>). {$a->error}';
$string['fieldoptions'] = 'Fieldoptions';
$string['fieldoptions_desc'] = 'Fields, that teachers can use as identifier to enrol students by.';
$string['pluginname'] = 'User bulk enrolment';
$string['or'] = 'or';
$string['privacy:metadata'] = 'The user bulk enrolment plugin acts as a tool to enrol users into courses, but does not store any personal data.';
$string['role'] = 'Role';
$string['role_assigned'] = 'Assigned role';
$string['role_description'] = 'The role to be used to bulk enrol the users.';
$string['row'] = 'Row';
$string['userlist'] = 'List of users identified by your chosen field';
$string['userlist_singleoption'] = 'List of users identified by their {$a}';
$string['userlist_email'] = 'data input';
$string['userlist_username'] = 'data input';
$string['userlist_idnumber'] = 'data input';
$string['userlist_email_help'] = 'To enrol an existing Moodle user into this course, choose a field to identify the user by and add the identifier to the list. <br /><br />Example for field "email" :<br />alice@example.com<br />bob@example.com<br /><br />Optionally, you are able to create groups and add the enrolled users to the groups. All you have to do is to add a heading line with a hash sign and the group\'s name, separating the list of users.<br /><br />Example:<br /># Group 1<br />alice@example.com<br />bob@example.com<br /># Group 2<br />carol@example.com<br />dave@example.com';
$string['userlist_username_help'] = 'To enrol an existing Moodle user into this course, choose a field to identify the user by and add the identifier to the list. <br /><br />Example for field "username" :<br />alice<br />bob<br /><br />Optionally, you are able to create groups and add the enrolled users to the groups. All you have to do is to add a heading line with a hash sign and the group\'s name, separating the list of users.<br /><br />Example:<br /># Group 1<br />alice<br />bob<br /># Group 2<br />carol<br />dave';
$string['userlist_idnumber_help'] = 'To enrol an existing Moodle user into this course, choose a field to identify the user by and add the identifier to the list. <br /><br />Example for field "idnumber" :<br />1001<br />1002<br /><br />Optionally, you are able to create groups and add the enrolled users to the groups. All you have to do is to add a heading line with a hash sign and the group\'s name, separating the list of users.<br /><br />Example:<br /># Group 1<br />1001<br />1002<br /># Group 2<br />1003<br />1004';
$string['users_to_enrol_in_course'] = 'Users to be enrolled into the course';
$string['user_enroled'] = 'User enrolment';
$string['user_enroled_yes'] = 'User will be enrolled';
$string['user_enroled_already'] = 'User is already enrolled';
$string['user_groups'] = 'Group membership';
$string['user_groups_yes'] = 'User will be added to group';
$string['user_groups_already'] = 'User is already group member';
$string['parameter_empty'] = 'Parameter empty';
$string['type_enrol'] = 'Enrolment method';
$string['identifying_data'] = 'Data';

