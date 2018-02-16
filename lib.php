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
 * Local plugin "bulkenrol" - Library
 *
 * @package   local_bulkenrol
 * @copyright 2017 Soon Systems GmbH on behalf of Alexander Bias, Ulm University <alexander.bias@uni-ulm.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function local_bulkenrol_extend_navigation_course($navigation, $course, $context) {
    global $DB, $SITE, $USER;
    
    if(!has_capability('local/bulkenrol:enrolusers', $context)){
        return;
    }
    
    
    if (!$usersnode = $navigation->get("users")) {
        return;
    }
    if (!$coursecontext = $context->get_course_context(false)) {
        return;
    }
    
    $nodeproperties = array(
                    'text'          => get_string('pluginname','local_bulkenrol'),
                    'shorttext'     => get_string('pluginname','local_bulkenrol'),
                    // icon         - The icon to display for the node
                    // type         - The type of the node
                    // key          - The key to use to identify the node
                    // parent       - A reference to the nodes parent
                    'action'        => new moodle_url('/local/bulkenrol/index.php', array('id' => $coursecontext->instanceid))
    );
    
    $integrationnode = new navigation_node($nodeproperties);
    
    $usersnode->add_node($integrationnode);

}
