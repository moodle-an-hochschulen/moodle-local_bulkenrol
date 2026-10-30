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

namespace local_bulkenrol;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/local/bulkenrol/locallib.php');

/**
 * Local plugin "bulkenrol" - Unit tests for the session data handling in locallib.php
 *
 * @package   local_bulkenrol
 * @copyright 2026 Alexander Bias <bias@alexanderbias.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class locallib_test extends \advanced_testcase {
    /**
     * Stores dummy bulk enrolment data for the given key in the session.
     *
     * @param string $localbulkenrolkey
     */
    private function store_session_data(string $localbulkenrolkey): void {
        global $SESSION;

        $SESSION->local_bulkenrol[$localbulkenrolkey] = new \stdClass();
        $SESSION->local_bulkenrol_inputs[$localbulkenrolkey . '_data'] = 'user@example.com';
    }

    /**
     * Test that the session data of a single bulk enrolment is cleared.
     *
     * @covers ::local_bulkenrol_clear_session_data
     */
    public function test_clear_session_data(): void {
        global $SESSION;

        $this->resetAfterTest();

        $now = time();
        $this->store_session_data('2_' . $now);
        $this->store_session_data('3_' . $now);

        local_bulkenrol_clear_session_data('2_' . $now);

        $this->assertEquals(['3_' . $now], array_keys($SESSION->local_bulkenrol));
        $this->assertEquals(['3_' . $now . '_data'], array_keys($SESSION->local_bulkenrol_inputs));
    }

    /**
     * Test that the session data of the same course and outdated session data is pruned.
     *
     * @covers ::local_bulkenrol_prune_session_data
     */
    public function test_prune_session_data(): void {
        global $SESSION;

        $this->resetAfterTest();

        $now = time();
        $old = $now - LOCALBULKENROL_SESSIONDATA_MAXAGE - 1;

        // Data of the same course (e.g. left behind by the editlist path), recent and old.
        $this->store_session_data('2_' . $now);
        $this->store_session_data('2_' . $old);
        // Data of another course, recent and old.
        $this->store_session_data('3_' . $now);
        $this->store_session_data('4_' . $old);
        // Orphaned input data without checked data.
        $SESSION->local_bulkenrol_inputs['5_' . $old . '_data'] = 'user@example.com';

        local_bulkenrol_prune_session_data(2);

        $this->assertEquals(['3_' . $now], array_keys($SESSION->local_bulkenrol));
        $this->assertEquals(['3_' . $now . '_data'], array_keys($SESSION->local_bulkenrol_inputs));
    }

    /**
     * Test that pruning does not fail if there is no session data.
     *
     * @covers ::local_bulkenrol_prune_session_data
     */
    public function test_prune_session_data_empty_session(): void {
        global $SESSION;

        $this->resetAfterTest();

        unset($SESSION->local_bulkenrol, $SESSION->local_bulkenrol_inputs);

        local_bulkenrol_prune_session_data(2);

        $this->assertFalse(isset($SESSION->local_bulkenrol));
        $this->assertFalse(isset($SESSION->local_bulkenrol_inputs));
    }
}
