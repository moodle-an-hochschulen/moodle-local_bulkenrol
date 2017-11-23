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

$string['pluginname'] = 'Bulkenrol';

$string['bulkenrol_auth'] = 'Emails';

$string['bulkenrol_enrolment'] = 'Bulkenrol Plugin';
$string['bulkenrol_enrolment_description'] = 'Das für die Einschreibungen zu nutzende Einschreibeplugin. Falls das konfigurierte Einschreibeplugin nicht aktiv ist im Kurs wird es während des Imports aktiviert / hinzugefügt.';

$string['infotext'] = 'Hier kommt der Hinweistext mit Nutzungshinweisen zum Plugin';
$string['usermails'] = 'Emails';

$string['hints_label'] = 'Hinweise';
$string['row'] = 'Zeile';
$string['users_to_enrol_in_course_label'] = 'In den Kurs einzuschreibende Nutzer';
$string['enrol_users'] = 'Nutzer einschreiben';
$string['enrol_users_successful'] = 'Nutzer Einschreibung erfolgreich';

// Errors
$string['no_email'] = 'Keine Email in Zeile {$a->line} -> "{$a->content}"';
$string['invalid_email'] = 'Ungültige Email in Zeile {$a->row}: "{$a->email}" -> Email-Adresse wird ignoriert';
$string['more_than_one_record_for_email'] = 'Mehr als ein Moodle-User für Email "{$a}" -> Email-Adresse wird ignoriert und keiner der Nutzer mit dieser Email wird eingeschrieben.';
$string['no_record_found_for_email'] = 'Kein Moodle-User für Email "{$a}" -> Email-Adresse wird ignoriert und es wird kein neuer Nutzer angelegt.';
$string['error_getting_user_for_email'] = 'Fehler beim Abfragen der DB nach User zur Email "{$a}".';
$string['exception_info'] = 'Exception info';
$string['error_enrol_users'] = 'Fehler beim Einschreiben der User in den Kurs.';
$string['error_group_add_members'] = 'Fehler beim Einschreiben der User in Kursgruppen.';

$string['no_data'] = 'Keine Benutzer zum Einschreiben in den Kurs.';
$string['no_courseid_or_no_users_to_enrol'] = 'Keine KursId oder keine Benutzer zum Einschreiben in den Kurs.';

$string['usermails_empty'] = 'Keine Emails eingetragen';

