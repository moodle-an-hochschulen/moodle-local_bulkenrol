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
require_once($CFG->dirroot . '/local/bulkenrol/db/upgrade.php');
require_once($CFG->libdir . '/upgradelib.php');

/**
 * Local plugin "bulkenrol" - Unit tests for the functions in locallib.php and db/upgrade.php
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

    /**
     * Creates a course which does not have any instance of the given enrolment method.
     *
     * @param \enrol_plugin $plugin
     * @return \stdClass
     */
    private function create_course_without_instance(\enrol_plugin $plugin): \stdClass {
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        foreach ($DB->get_records('enrol', ['courseid' => $course->id, 'enrol' => $plugin->get_name()]) as $instance) {
            $plugin->delete_instance($instance);
        }

        return $course;
    }

    /**
     * Test that a manual enrolment instance is added with the defaults of the enrolment method and is enabled.
     *
     * @covers ::local_bulkenrol_add_enrol_instance
     */
    public function test_add_enrol_instance_manual(): void {
        global $DB;

        $this->resetAfterTest();

        $studentroleid = $DB->get_field('role', 'id', ['shortname' => 'student'], MUST_EXIST);
        set_config('status', ENROL_INSTANCE_DISABLED, 'enrol_manual');
        set_config('roleid', $studentroleid, 'enrol_manual');
        set_config('enrolperiod', 10 * DAYSECS, 'enrol_manual');
        set_config('expirynotify', 2, 'enrol_manual');
        set_config('expirythreshold', 2 * DAYSECS, 'enrol_manual');

        $plugin = enrol_get_plugin('manual');
        $course = $this->create_course_without_instance($plugin);

        $sink = $this->redirectEvents();
        $instance = local_bulkenrol_add_enrol_instance($plugin, $course);
        $events = $sink->get_events();
        $sink->close();

        // The instance was created with the final values at once.
        $created = array_filter($events, fn($e) => $e instanceof \core\event\enrol_instance_created);
        $this->assertCount(1, $created);
        $snapshot = reset($created)->get_record_snapshot('enrol', $instance->id);
        $this->assertEquals($studentroleid, $snapshot->roleid);
        $this->assertEquals(2 * DAYSECS, $snapshot->expirythreshold);

        // The instance has the defaults of the enrolment method, but is enabled.
        $record = $DB->get_record('enrol', ['id' => $instance->id], '*', MUST_EXIST);
        $this->assertEquals('manual', $record->enrol);
        $this->assertEquals($course->id, $record->courseid);
        $this->assertEquals(ENROL_INSTANCE_ENABLED, $record->status);
        $this->assertEquals(ENROL_INSTANCE_ENABLED, $instance->status);
        $this->assertEquals($studentroleid, $record->roleid);
        $this->assertEquals(10 * DAYSECS, $record->enrolperiod);
        $this->assertEquals(1, $record->notifyall);
        $this->assertEquals(2 * DAYSECS, $record->expirythreshold);
    }

    /**
     * Test that a self enrolment instance is added with the defaults of the enrolment method and is enabled.
     *
     * @covers ::local_bulkenrol_add_enrol_instance
     */
    public function test_add_enrol_instance_self(): void {
        global $DB;

        $this->resetAfterTest();

        set_config('status', ENROL_INSTANCE_DISABLED, 'enrol_self');
        set_config('requirepassword', 1, 'enrol_self');
        set_config('expirynotify', 2, 'enrol_self');

        $plugin = enrol_get_plugin('self');
        $course = $this->create_course_without_instance($plugin);

        $instance = local_bulkenrol_add_enrol_instance($plugin, $course);

        $record = $DB->get_record('enrol', ['id' => $instance->id], '*', MUST_EXIST);
        $this->assertEquals('self', $record->enrol);
        $this->assertEquals(ENROL_INSTANCE_ENABLED, $record->status);
        $this->assertNotEmpty($record->password);
        // The setting value 2 is not stored as is, but is mapped to expirynotify = 1 by the enrolment method.
        // (The notifyall field is not checked here as Moodle core does not set it reliably for default instances).
        $this->assertEquals(1, $record->expirynotify);
    }

    /**
     * Test that an enrolment instance is added for an enrolment method which does not support default instances.
     *
     * @covers ::local_bulkenrol_add_enrol_instance
     */
    public function test_add_enrol_instance_fallback(): void {
        global $DB;

        $this->resetAfterTest();

        $teacherroleid = $DB->get_field('role', 'id', ['shortname' => 'teacher'], MUST_EXIST);
        set_config('roleid', $teacherroleid, 'enrol_cohort');

        // The cohort enrolment method is used here as a stand-in for a third-party enrolment method.
        $plugin = enrol_get_plugin('cohort');
        $course = $this->create_course_without_instance($plugin);
        $this->assertNull($plugin->add_default_instance($course));

        $instance = local_bulkenrol_add_enrol_instance($plugin, $course);

        $record = $DB->get_record('enrol', ['id' => $instance->id], '*', MUST_EXIST);
        $this->assertEquals('cohort', $record->enrol);
        $this->assertEquals(ENROL_INSTANCE_ENABLED, $record->status);
        // The enrolment method does not provide any instance defaults, so the database defaults are kept
        // and the configuration of the enrolment method is not evaluated by the plugin itself.
        $this->assertEquals(0, $record->roleid);
        $this->assertEquals(0, $record->expirynotify);
        $this->assertEquals(0, $record->expirythreshold);
    }

    /**
     * Test that the instance defaults of an enrolment method which does not support default instances are used.
     *
     * @covers ::local_bulkenrol_add_enrol_instance
     */
    public function test_add_enrol_instance_fallback_instance_defaults(): void {
        global $DB;

        $this->resetAfterTest();

        $teacherroleid = $DB->get_field('role', 'id', ['shortname' => 'teacher'], MUST_EXIST);

        // Build a stand-in for a third-party enrolment method which does not support default instances,
        // but which provides instance defaults.
        $plugin = new class ($teacherroleid) extends \enrol_plugin {
            /**
             * Constructor.
             *
             * @param int $roleid The role which is provided as instance default.
             */
            public function __construct(
                /** @var int The role which is provided as instance default. */
                private int $roleid
            ) {
            }

            /**
             * Returns the name of the enrolment method.
             *
             * @return string
             */
            public function get_name() {
                return 'bulkenroltest';
            }

            /**
             * Returns the defaults for new instances.
             *
             * @return array
             */
            public function get_instance_defaults() {
                return [
                    'status' => ENROL_INSTANCE_DISABLED,
                    'roleid' => $this->roleid,
                    'enrolperiod' => 5 * DAYSECS,
                    'customint1' => 42,
                ];
            }
        };

        $course = $this->getDataGenerator()->create_course();
        $this->assertNull($plugin->add_default_instance($course));

        $sink = $this->redirectEvents();
        $instance = local_bulkenrol_add_enrol_instance($plugin, $course);
        $events = $sink->get_events();
        $sink->close();

        // The instance was created with the instance defaults at once.
        $created = array_filter($events, fn($e) => $e instanceof \core\event\enrol_instance_created);
        $this->assertCount(1, $created);
        $snapshot = reset($created)->get_record_snapshot('enrol', $instance->id);
        $this->assertEquals($teacherroleid, $snapshot->roleid);

        // The instance has the instance defaults of the enrolment method, but is enabled.
        $record = $DB->get_record('enrol', ['id' => $instance->id], '*', MUST_EXIST);
        $this->assertEquals('bulkenroltest', $record->enrol);
        $this->assertEquals($course->id, $record->courseid);
        $this->assertEquals(ENROL_INSTANCE_ENABLED, $record->status);
        $this->assertEquals(ENROL_INSTANCE_ENABLED, $instance->status);
        $this->assertEquals($teacherroleid, $record->roleid);
        $this->assertEquals(5 * DAYSECS, $record->enrolperiod);
        $this->assertEquals(42, $record->customint1);
    }

    /**
     * Test that the bulk enrolment reports an error instead of failing fatally if the enrolment method is not available.
     *
     * @covers ::local_bulkenrol_users
     */
    public function test_users_enrolplugin_not_available(): void {
        global $SESSION;

        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();

        // Store the data of a bulk enrolment which should enrol the user in the session.
        $userdata = new \stdClass();
        $userdata->action = 'user_enroled_yes';
        $userdata->user = $user;
        $localbulkenroldata = new \stdClass();
        $localbulkenroldata->moodleusers_for_email = [$user->email => $userdata];
        $localbulkenroldata->course_groups = [];
        $localbulkenrolkey = $course->id . '_' . time();
        $SESSION->local_bulkenrol[$localbulkenrolkey] = $localbulkenroldata;

        // The enrolment plugin setting is not set (which may be the case after the plugin upgrade).
        unset_config('enrolplugin', 'local_bulkenrol');
        $retval = local_bulkenrol_users($localbulkenrolkey);
        $this->assertEquals('error', $retval->status);
        $this->assertEquals(get_string('error_enrol_users', 'local_bulkenrol'), $retval->text);
        $this->assertFalse(is_enrolled(\context_course::instance($course->id), $user->id));

        // The enrolment plugin setting holds an enrolment method which is not installed (anymore).
        set_config('enrolplugin', 'doesnotexist', 'local_bulkenrol');
        $retval = local_bulkenrol_users($localbulkenrolkey);
        $this->assertEquals('error', $retval->status);
        $this->assertEquals(get_string('error_enrol_users', 'local_bulkenrol'), $retval->text);
        $this->assertFalse(is_enrolled(\context_course::instance($course->id), $user->id));
    }

    /**
     * Test that the configured enrolment method is only returned if it is usable.
     *
     * @covers ::local_bulkenrol_get_enrolplugin
     */
    public function test_get_enrolplugin(): void {
        $this->resetAfterTest();

        // The enrolment plugin setting holds an installed enrolment method.
        set_config('enrolplugin', 'manual', 'local_bulkenrol');
        $this->assertInstanceOf(\enrol_manual_plugin::class, local_bulkenrol_get_enrolplugin());
        set_config('enrolplugin', 'self', 'local_bulkenrol');
        $this->assertInstanceOf(\enrol_self_plugin::class, local_bulkenrol_get_enrolplugin());

        // The enrolment plugin setting holds an enrolment method which is not installed (anymore).
        set_config('enrolplugin', 'doesnotexist', 'local_bulkenrol');
        $this->assertNull(local_bulkenrol_get_enrolplugin());

        // The enrolment plugin setting is empty or not set (which may be the case after the plugin upgrade).
        set_config('enrolplugin', '', 'local_bulkenrol');
        $this->assertNull(local_bulkenrol_get_enrolplugin());
        unset_config('enrolplugin', 'local_bulkenrol');
        $this->assertNull(local_bulkenrol_get_enrolplugin());
    }

    /**
     * Test which enrolment methods are offered in the enrolment plugin setting.
     *
     * @covers ::local_bulkenrol_is_allowed_enrolplugin
     * @covers ::local_bulkenrol_get_enrolplugin_options
     * @covers ::local_bulkenrol_get_enrolplugin_default
     */
    public function test_enrolplugin_options(): void {
        $this->resetAfterTest();

        // From the Moodle core enrolment methods, only manual and self are allowed.
        $this->assertTrue(local_bulkenrol_is_allowed_enrolplugin('manual'));
        $this->assertTrue(local_bulkenrol_is_allowed_enrolplugin('self'));
        $this->assertFalse(local_bulkenrol_is_allowed_enrolplugin('cohort'));
        $this->assertFalse(local_bulkenrol_is_allowed_enrolplugin('guest'));
        $this->assertFalse(local_bulkenrol_is_allowed_enrolplugin('meta'));
        // Third-party enrolment methods are allowed.
        $this->assertTrue(local_bulkenrol_is_allowed_enrolplugin('somethirdpartyplugin'));

        // Only enabled and allowed enrolment methods are offered, in the order in which they are enabled.
        set_config('enrol_plugins_enabled', 'guest,self,cohort,manual,meta');
        $this->assertEquals(['self', 'manual'], array_keys(local_bulkenrol_get_enrolplugin_options()));
        $this->assertEquals('self', local_bulkenrol_get_enrolplugin_default());

        set_config('enrol_plugins_enabled', 'manual,guest,cohort');
        $this->assertEquals(['manual'], array_keys(local_bulkenrol_get_enrolplugin_options()));
        $this->assertEquals('manual', local_bulkenrol_get_enrolplugin_default());

        // If no allowed enrolment method is enabled, nothing is offered.
        set_config('enrol_plugins_enabled', 'guest,cohort');
        $this->assertEquals([], local_bulkenrol_get_enrolplugin_options());
        $this->assertNull(local_bulkenrol_get_enrolplugin_default());
    }

    /**
     * Runs the upgrade step which resets the enrolment plugin setting and returns its output.
     *
     * @return string
     */
    private function run_enrolplugin_upgrade_step(): string {
        // Pretend that the plugin is still installed in the version before the upgrade step.
        set_config('version', 2026042002, 'local_bulkenrol');

        ob_start();
        try {
            $this->assertTrue(xmldb_local_bulkenrol_upgrade(2026042002));
        } finally {
            $output = ob_get_clean();
        }

        return $output;
    }

    /**
     * Test that the upgrade resets the enrolment plugin setting if it holds a disallowed enrolment method.
     *
     * @covers ::xmldb_local_bulkenrol_upgrade
     */
    public function test_upgrade_reset_enrolplugin(): void {
        $this->resetAfterTest();

        set_config('enrol_plugins_enabled', 'manual,guest,self,cohort');

        // Allowed enrolment methods are kept without any notification.
        foreach (['manual', 'self', 'somethirdpartyplugin'] as $allowed) {
            set_config('enrolplugin', $allowed, 'local_bulkenrol');
            $this->assertEquals('', $this->run_enrolplugin_upgrade_step());
            $this->assertEquals($allowed, get_config('local_bulkenrol', 'enrolplugin'));
        }

        // A missing setting is left alone.
        unset_config('enrolplugin', 'local_bulkenrol');
        $this->assertEquals('', $this->run_enrolplugin_upgrade_step());
        $this->assertFalse(get_config('local_bulkenrol', 'enrolplugin'));

        // Disallowed enrolment methods are reset to the default with a notification.
        foreach (['cohort', 'meta'] as $disallowed) {
            set_config('enrolplugin', $disallowed, 'local_bulkenrol');
            $output = $this->run_enrolplugin_upgrade_step();
            $this->assertStringContainsString(get_string('pluginname', 'enrol_' . $disallowed), $output);
            $this->assertStringContainsString(
                get_string('upgrade_notice_enrolplugin_reset', 'local_bulkenrol', get_string('pluginname', 'enrol_manual')),
                $output
            );
            $this->assertEquals('manual', get_config('local_bulkenrol', 'enrolplugin'));
        }

        // If no allowed enrolment method is enabled, the setting is removed with a notification.
        set_config('enrol_plugins_enabled', 'guest,cohort');
        set_config('enrolplugin', 'cohort', 'local_bulkenrol');
        $output = $this->run_enrolplugin_upgrade_step();
        $this->assertStringContainsString(get_string('pluginname', 'enrol_cohort'), $output);
        $this->assertStringContainsString(get_string('upgrade_notice_enrolplugin_unset', 'local_bulkenrol'), $output);
        $this->assertFalse(get_config('local_bulkenrol', 'enrolplugin'));

        // The upgrade step has set the plugin version.
        $this->assertEquals(2026042003, get_config('local_bulkenrol', 'version'));
    }
}
