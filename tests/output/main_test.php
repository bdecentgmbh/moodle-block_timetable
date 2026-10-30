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

namespace block_timetable\output;

/**
 * Tests of the Timetable block's content: views, time spans, events and pagination.
 *
 * @package    block_timetable
 * @category   test
 * @copyright  2021 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \block_timetable\output\main
 */
final class main_test extends \advanced_testcase {
    /**
     * Reset after each test.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * Create a Timetable block.
     *
     * @param \context $context The parent context
     * @param array $settings Block settings, see block_timetable_generator
     * @return \stdClass Block instance record
     */
    protected function create_block(\context $context, array $settings = []): \stdClass {
        return $this->getDataGenerator()->create_block('timetable', ['parentcontextid' => $context->id] + $settings);
    }

    /**
     * Export the content of a block for the current user.
     *
     * @param \stdClass $block Block instance record
     * @param int $courseid The course of the page
     * @param string $view
     * @param int $time
     * @param int $page
     * @return array
     */
    protected function export(\stdClass $block, int $courseid, string $view = '', int $time = 0, int $page = 1): array {
        global $PAGE;

        $main = new main(block_instance_by_id($block->id)->config, $block->id, $courseid, $view, $time, $page);
        return $main->export_for_template($PAGE->get_renderer('core'));
    }

    /**
     * Names of the exported events.
     *
     * @param array $data Exported content
     * @return string[]
     */
    protected function names(array $data): array {
        return array_map(fn($event) => $event->name, $data['events']);
    }

    /**
     * Noon of today in the user's timezone.
     *
     * @return int
     */
    protected function noon(): int {
        return usergetmidnight(time()) + 12 * HOURSECS;
    }

    /**
     * Create a site event; the data generator does not keep the site's course id.
     *
     * @param string $name
     * @param int $timestart
     */
    protected function create_site_event(string $name, int $timestart): void {
        global $CFG;
        require_once($CFG->dirroot . '/calendar/lib.php');

        \calendar_event::create((object) ['name' => $name, 'eventtype' => 'site', 'courseid' => SITEID,
            'timestart' => $timestart, 'timeduration' => 0], false);
    }

    /**
     * The views offered and shown, from the settings.
     */
    public function test_views(): void {
        $this->setAdminUser();
        $context = \context_system::instance();

        $main = new main(null, 1, SITEID);
        $this->assertSame(['today', 'thisweek', 'nextxday'], $main->get_views(), 'Unconfigured blocks offer all views.');
        $this->assertSame('today', $main->get_default_view());

        $config = (object) ['timetable' => 'thisweek', 'checkboxtoday' => 0, 'checkboxthisweek' => 0, 'checkboxnextxday' => 0];
        $this->assertSame(['thisweek'], (new main($config, 1, SITEID))->get_views(), 'The default view is always offered.');

        $config = (object) ['timetable' => 'nextxday', 'checkboxtoday' => 1, 'checkboxthisweek' => 0, 'checkboxnextxday' => 0];
        $this->assertSame(['today', 'nextxday'], (new main($config, 1, SITEID))->get_views());

        $config->timetable = 'unknown';
        $this->assertSame('today', (new main($config, 1, SITEID))->get_default_view());

        // A view that is not offered shows the default view.
        $block = $this->create_block($context, ['timetable' => 'nextxday', 'checkboxtoday' => 1]);
        $data = $this->export($block, SITEID, 'thisweek');
        $this->assertSame('nextxday', $data['view']);
        $this->assertTrue($data['showviews']);
        $this->assertSame([false, true], array_column($data['views'], 'active'));
        $this->assertFalse($data['showdays']);

        $block = $this->create_block($context, ['timetable' => 'thisweek']);
        $data = $this->export($block, SITEID, 'today');
        $this->assertSame('thisweek', $data['view']);
        $this->assertFalse($data['showviews']);
        $this->assertCount(7, $data['days']);
        $this->assertFalse($data['horizontal']);

        $block = $this->create_block($context, ['timetable' => 'today', 'view' => 'horizontal']);
        $this->assertTrue($this->export($block, SITEID)['horizontal']);
    }

    /**
     * Visitors who are not logged in cannot switch views, days or pages, which needs a login.
     */
    public function test_not_logged_in(): void {
        $block = $this->create_block(\context_system::instance(), ['timetable' => 'thisweek', 'checkboxtoday' => 1]);
        $data = $this->export($block, SITEID);
        $this->assertFalse($data['interactive']);
        $this->assertFalse($data['showviews']);
        $this->assertFalse($data['showdays']);

        $this->setAdminUser();
        $data = $this->export($block, SITEID);
        $this->assertTrue($data['interactive']);
        $this->assertTrue($data['showviews']);
        $this->assertTrue($data['showdays']);
    }

    /**
     * Events per page.
     */
    public function test_limit(): void {
        $this->assertSame(main::DEFAULT_LIMIT, (new main(null, 1, SITEID))->get_limit());
        $this->assertSame(main::DEFAULT_LIMIT, (new main((object) ['limit' => ''], 1, SITEID))->get_limit());
        $this->assertSame(main::DEFAULT_LIMIT, (new main((object) ['limit' => 0], 1, SITEID))->get_limit());
        $this->assertSame(3, (new main((object) ['limit' => '3'], 1, SITEID))->get_limit());
        $this->assertSame(main::MAX_LIMIT, (new main((object) ['limit' => 500], 1, SITEID))->get_limit());
    }

    /**
     * What an event belongs to: names are formatted once, so an ampersand is not shown as &amp; (#7).
     *
     * This test creates the first group of the class: core caches group names statically in calendar_get_group_cached(),
     * and group ids start again after each reset.
     */
    public function test_source(): void {
        $generator = $this->getDataGenerator();
        $course = $generator->create_course(['fullname' => 'Maths & Physics']);
        $group = $generator->create_group(['courseid' => $course->id, 'name' => 'Group A & B']);
        $this->setAdminUser();
        $generator->enrol_user(get_admin()->id, $course->id, 'editingteacher');
        $noon = $this->noon();
        $generator->create_event(['name' => 'Lecture', 'eventtype' => 'course', 'courseid' => $course->id, 'timestart' => $noon]);
        $generator->create_event(['name' => 'Tutorial', 'eventtype' => 'group', 'courseid' => $course->id,
            'groupid' => $group->id, 'timestart' => $noon + 1]);
        $this->create_site_event('Open day', $noon + 2);

        $block = $this->create_block(\context_system::instance());
        $sources = array_column($this->export($block, SITEID)['events'], 'source', 'name');
        // Escaped once: decoding once gives the name. Moodle versions differ in the entity (&amp; or &#38;).
        $this->assertSame([
            'Lecture' => 'Maths & Physics',
            'Tutorial' => 'Group A & B',
            'Open day' => get_string('typesite', 'calendar'),
        ], array_map(fn($source) => html_entity_decode($source, ENT_QUOTES | ENT_HTML5), $sources));
        $this->assertStringNotContainsString('&', str_replace(['&amp;', '&#38;'], '', $sources['Lecture']));
    }

    /**
     * In a course, users see the events of the course and of their groups, as in the course calendar, but no site and
     * category events.
     */
    public function test_course_events(): void {
        $generator = $this->getDataGenerator();
        $category = $generator->create_category();
        $course = $generator->create_course(['category' => $category->id, 'groupmode' => SEPARATEGROUPS]);
        $othercourse = $generator->create_course(['category' => $category->id]);
        $student = $generator->create_and_enrol($course, 'student');
        $teacher = $generator->create_and_enrol($course, 'editingteacher');
        $generator->enrol_user($student->id, $othercourse->id, 'student');
        $group1 = $generator->create_group(['courseid' => $course->id]);
        $group2 = $generator->create_group(['courseid' => $course->id]);
        $generator->create_group_member(['groupid' => $group1->id, 'userid' => $student->id]);

        $this->setAdminUser();
        $noon = $this->noon();
        $events = [
            ['name' => 'Course event', 'eventtype' => 'course', 'courseid' => $course->id],
            ['name' => 'Group 1 event', 'eventtype' => 'group', 'courseid' => $course->id, 'groupid' => $group1->id],
            ['name' => 'Group 2 event', 'eventtype' => 'group', 'courseid' => $course->id, 'groupid' => $group2->id],
            ['name' => 'Other course event', 'eventtype' => 'course', 'courseid' => $othercourse->id],
            ['name' => 'Category event', 'eventtype' => 'category', 'categoryid' => $category->id],
            ['name' => 'Own event', 'eventtype' => 'user', 'userid' => $student->id],
            ['name' => 'Teacher event', 'eventtype' => 'user', 'userid' => $teacher->id],
        ];
        foreach ($events as $event) {
            $generator->create_event($event + ['timestart' => $noon]);
        }
        $this->create_site_event('Site event', $noon);
        $generator->create_module('assign', ['course' => $course->id, 'name' => 'Essay', 'duedate' => $noon]);
        $generator->create_module('assign', ['course' => $course->id, 'name' => 'Hidden', 'duedate' => $noon, 'visible' => 0]);

        $block = $this->create_block(\context_course::instance($course->id));

        $this->setUser($student);
        $names = $this->names($this->export($block, $course->id));
        sort($names);
        $this->assertSame(['Course event', 'Essay is due', 'Group 1 event', 'Own event'], $names);

        $this->setUser($teacher);
        $names = $this->names($this->export($block, $course->id));
        sort($names);
        // Events of hidden activities are left out for teachers too, as in the calendar.
        $this->assertSame(['Course event', 'Essay is due', 'Group 1 event', 'Group 2 event', 'Teacher event'], $names);

        // On the dashboard, the events of all the user's courses, the site and their categories.
        $this->setUser($student);
        $dashboard = $this->create_block(\context_user::instance($student->id), ['limit' => 10]);
        $names = $this->names($this->export($dashboard, SITEID));
        sort($names);
        $this->assertSame(['Category event', 'Course event', 'Essay is due', 'Group 1 event', 'Other course event', 'Own event',
            'Site event'], $names);
    }

    /**
     * Paging through the events.
     */
    public function test_pagination(): void {
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $this->setAdminUser();
        $noon = $this->noon();
        for ($i = 1; $i <= 5; $i++) {
            $generator->create_event(['name' => "Event $i", 'eventtype' => 'course', 'courseid' => $course->id,
                'timestart' => $noon + $i * MINSECS]);
        }
        $block = $this->create_block(\context_course::instance($course->id), ['limit' => 2]);

        $data = $this->export($block, $course->id, 'today', 0, 1);
        $this->assertSame(['Event 1', 'Event 2'], $this->names($data));
        $this->assertTrue($data['showpagination']);
        $this->assertSame(0, $data['previous']);
        $this->assertSame(2, $data['next']);

        $data = $this->export($block, $course->id, 'today', 0, 2);
        $this->assertSame(['Event 3', 'Event 4'], $this->names($data));
        $this->assertSame([1, 3], [$data['previous'], $data['next']]);

        $data = $this->export($block, $course->id, 'today', 0, 3);
        $this->assertSame(['Event 5'], $this->names($data));
        $this->assertSame([2, 0], [$data['previous'], $data['next']]);

        $block = $this->create_block(\context_course::instance($course->id), ['limit' => 5]);
        $this->assertFalse($this->export($block, $course->id)['showpagination']);

        // Paging stops after main::MAX_EVENTS events: with 50 per page, page 4 is the last.
        for ($i = 6; $i <= main::MAX_EVENTS + 1; $i++) {
            $generator->create_event(['name' => "Event $i", 'eventtype' => 'course', 'courseid' => $course->id,
                'timestart' => $noon + $i * MINSECS]);
        }
        $block = $this->create_block(\context_course::instance($course->id), ['limit' => 3]);
        $data = $this->export($block, $course->id, 'today', 0, 67);
        $this->assertSame(['Event 199', 'Event 200'], $this->names($data));
        $this->assertSame([66, 0], [$data['previous'], $data['next']]);
        $block = $this->create_block(\context_course::instance($course->id), ['limit' => 50]);
        $data = $this->export($block, $course->id, 'today', 0, 1000);
        $this->assertCount(50, $this->names($data));
        $this->assertSame('Event 200', $this->names($data)[49]);
        $this->assertSame([3, 0], [$data['previous'], $data['next']]);
    }

    /**
     * Days and time spans follow the user's timezone, first day of the week and look-ahead (#1, #3, #14).
     */
    public function test_user_calendar(): void {
        $this->setTimezone('Europe/London');
        $user = $this->getDataGenerator()->create_user(['timezone' => 'Pacific/Auckland']);
        $this->setUser($user);
        set_user_preference('calendar_startwday', 3);
        set_user_preference('calendar_lookahead', 7);
        $midnight = usergetmidnight(time());
        $this->assertSame('00:00', userdate($midnight, '%H:%M'));

        [$from, $to] = (new main(null, 1, SITEID, 'today'))->get_timespan();
        $this->assertSame($midnight, $from);
        $this->assertSame('00:00', userdate($to + 1, '%H:%M'));
        $this->assertSame(userdate($midnight, '%d'), userdate($to, '%d'));

        [$from, $to] = (new main(null, 1, SITEID, 'nextxday'))->get_timespan();
        $this->assertSame($midnight, $from);
        $this->assertSame('00:00', userdate($to + 1, '%H:%M'));
        $this->assertEqualsWithDelta(7 * DAYSECS, $to + 1 - $from, HOURSECS, 'Seven days, give or take a DST change.');

        $tomorrow = $midnight + DAYSECS + 2 * HOURSECS;
        [$from] = (new main(null, 1, SITEID, 'thisweek', $tomorrow))->get_timespan();
        $this->assertSame(usergetmidnight($tomorrow), $from);

        $block = $this->create_block(\context_user::instance($user->id));
        $days = $this->export($block, SITEID, 'thisweek')['days'];
        $this->assertCount(7, $days);
        $this->assertSame(get_string('wed', 'calendar'), $days[0]['name'], 'The week starts on Wednesday.');
        $this->assertSame([$midnight], array_column(array_filter($days, fn($day) => $day['today']), 'time'));
        $this->assertSame([$midnight], array_column(array_filter($days, fn($day) => $day['active']), 'time'));
        foreach ($days as $day) {
            $this->assertSame('00:00', userdate($day['time'], '%H:%M'));
        }

        // A late event is shown today in the user's timezone, not on the day of the server's timezone.
        $generator = $this->getDataGenerator();
        $generator->create_event(['name' => 'Late', 'eventtype' => 'user', 'userid' => $user->id,
            'timestart' => $midnight + 23 * HOURSECS + 30 * MINSECS]);
        $generator->create_event(['name' => 'Tomorrow', 'eventtype' => 'user', 'userid' => $user->id,
            'timestart' => $midnight + DAYSECS + 30 * MINSECS]);
        $this->assertSame(['Late'], $this->names($this->export($block, SITEID, 'today')));
        $this->assertSame(['Tomorrow'], $this->names($this->export($block, SITEID, 'thisweek', $tomorrow)));
    }

    /**
     * Events that have ended are marked.
     */
    public function test_ended(): void {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $start = time() - 2 * HOURSECS;
        $this->getDataGenerator()->create_event(['name' => 'Ended', 'eventtype' => 'user', 'userid' => $user->id,
            'timestart' => $start, 'timeduration' => HOURSECS]);
        $this->getDataGenerator()->create_event(['name' => 'Running', 'eventtype' => 'user', 'userid' => $user->id,
            'timestart' => $start, 'timeduration' => 3 * HOURSECS]);

        $block = $this->create_block(\context_user::instance($user->id));
        $ended = array_column($this->export($block, SITEID, 'thisweek', $start)['events'], 'ended', 'name');
        $this->assertSame(['Ended' => true, 'Running' => false], $ended);
    }
}
