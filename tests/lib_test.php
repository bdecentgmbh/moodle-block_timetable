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

namespace block_timetable;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/blocks/timetable/lib.php');

/**
 * Tests of the fragment that loads another view, day or page of a Timetable block.
 *
 * @package    block_timetable
 * @category   test
 * @copyright  2021 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     ::block_timetable_output_fragment_content
 */
final class lib_test extends \advanced_testcase {
    /** @var \stdClass A course with an event today. */
    protected $course;

    /** @var \stdClass A student of the course. */
    protected $student;

    /** @var \stdClass A Timetable block in the course. */
    protected $block;

    /**
     * A course with an event today, a student and a block.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $this->course = $generator->create_course();
        $this->student = $generator->create_and_enrol($this->course, 'student');
        $this->setAdminUser();
        $generator->create_event(['name' => 'Lecture', 'eventtype' => 'course', 'courseid' => $this->course->id,
            'timestart' => usergetmidnight(time()) + 12 * HOURSECS]);
        $this->block = $generator->create_block('timetable', [
            'parentcontextid' => \context_course::instance($this->course->id)->id,
            'pagetypepattern' => 'course-view-*',
        ]);
    }

    /**
     * Load the fragment.
     *
     * @param \context $context
     * @param int $courseid
     * @param string $view
     * @return string
     */
    protected function fragment(\context $context, int $courseid, string $view = 'nextxday'): string {
        return block_timetable_output_fragment_content([
            'context' => $context,
            'courseid' => $courseid,
            'view' => $view,
            'time' => 0,
            'page' => 1,
        ]);
    }

    /**
     * The fragment renders the requested view of the block.
     */
    public function test_content(): void {
        $this->setUser($this->student);
        $html = $this->fragment(\context_block::instance($this->block->id), $this->course->id);
        $this->assertStringContainsString('data-view="nextxday"', $html);
        $this->assertStringContainsString('Lecture', $html);

        $html = $this->fragment(\context_block::instance($this->block->id), $this->course->id, 'thisweek');
        $this->assertStringContainsString('timetable-days', $html);
    }

    /**
     * The events of a course the user cannot access are not shown.
     */
    public function test_course_not_accessible(): void {
        $other = $this->getDataGenerator()->create_course();
        $this->getDataGenerator()->create_event(['name' => 'Secret', 'eventtype' => 'course', 'courseid' => $other->id,
            'timestart' => usergetmidnight(time()) + 12 * HOURSECS]);
        $this->setUser($this->student);
        $this->expectException(\require_login_exception::class);
        $this->fragment(\context_block::instance($this->block->id), $other->id);
    }

    /**
     * Enrolments that are not active.
     *
     * @return array
     */
    public static function inactive_enrolment_provider(): array {
        return [
            'suspended' => [['status' => ENROL_USER_SUSPENDED]],
            'ended' => [['timeend' => time() - DAYSECS]],
            'not started' => [['timestart' => time() + DAYSECS]],
        ];
    }

    /**
     * Like a course page, the fragment needs an active enrolment.
     *
     * @dataProvider inactive_enrolment_provider
     * @param array $enrolment Status, start and end of the enrolment
     */
    public function test_enrolment_not_active(array $enrolment): void {
        $other = $this->getDataGenerator()->create_course();
        $this->getDataGenerator()->enrol_user(
            $this->student->id,
            $other->id,
            'student',
            'manual',
            $enrolment['timestart'] ?? 0,
            $enrolment['timeend'] ?? 0,
            $enrolment['status'] ?? ENROL_USER_ACTIVE
        );
        $this->setUser($this->student);
        $this->expectException(\require_login_exception::class);
        $this->fragment(\context_block::instance($this->block->id), $other->id);
    }

    /**
     * Only the context of a Timetable block is accepted.
     */
    public function test_other_context(): void {
        $this->setUser($this->student);
        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('invalidcontext', 'error'));
        $this->fragment(\context_course::instance($this->course->id), $this->course->id);
    }

    /**
     * Other blocks are not rendered as Timetable blocks.
     */
    public function test_other_block(): void {
        $block = $this->getDataGenerator()->create_block('online_users', [
            'parentcontextid' => \context_course::instance($this->course->id)->id,
        ]);
        $this->setUser($this->student);
        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('invalidcontext', 'error'));
        $this->fragment(\context_block::instance($block->id), $this->course->id);
    }

    /**
     * Users who cannot see the block cannot load it.
     */
    public function test_no_view_capability(): void {
        $context = \context_block::instance($this->block->id);
        $roleid = $this->getDataGenerator()->create_role();
        $this->getDataGenerator()->role_assign($roleid, $this->student->id, $context->id);
        assign_capability('moodle/block:view', CAP_PROHIBIT, $roleid, $context->id);
        $this->setUser($this->student);
        $this->expectException(\required_capability_exception::class);
        $this->fragment($context, $this->course->id);
    }
}
