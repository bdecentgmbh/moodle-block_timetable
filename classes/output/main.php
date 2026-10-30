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

use core_calendar\external\calendar_upcoming_exporter;
use core_calendar\external\events_related_objects_cache;
use core_calendar\type_factory;
use renderable;
use renderer_base;
use stdClass;
use templatable;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/calendar/lib.php');

/**
 * The content of a Timetable block: view selector, days of the week, events and pagination.
 *
 * @package    block_timetable
 * @copyright  2021 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class main implements renderable, templatable {
    /** @var string[] The views in the order they are offered. 'nextxday' is the Upcoming view. */
    public const VIEWS = ['today', 'thisweek', 'nextxday'];

    /** @var int Events per page when the block has no setting. */
    public const DEFAULT_LIMIT = 5;

    /** @var int Most events per page. */
    public const MAX_LIMIT = 50;

    /** @var int Most events to page through. */
    public const MAX_EVENTS = 200;

    /** @var stdClass The block configuration. */
    protected stdClass $config;

    /** @var int The block instance id. */
    protected int $instanceid;

    /** @var int The course whose events are shown, SITEID for all the user's courses. */
    protected int $courseid;

    /** @var string The view shown. */
    protected string $view;

    /** @var int Midnight of the day shown in the This week view. */
    protected int $day;

    /** @var int The page of events shown, starting at 1. */
    protected int $page;

    /**
     * Constructor.
     *
     * @param stdClass|null $config The block configuration
     * @param int $instanceid The block instance id
     * @param int $courseid The course whose events are shown, SITEID for all the user's courses
     * @param string $view The view to show; the block's default view if it does not offer it
     * @param int $time A time on the day to show in the This week view; today if 0
     * @param int $page The page of events to show
     */
    public function __construct(
        ?stdClass $config,
        int $instanceid,
        int $courseid,
        string $view = '',
        int $time = 0,
        int $page = 1
    ) {
        $this->config = $config ?? new stdClass();
        $this->instanceid = $instanceid;
        $this->courseid = $courseid;
        $this->view = in_array($view, $this->get_views(), true) ? $view : $this->get_default_view();
        $this->day = usergetmidnight($time ?: time());
        $this->page = max(1, min($page, (int) ceil(self::MAX_EVENTS / $this->get_limit())));
    }

    /**
     * The view shown first.
     *
     * @return string
     */
    public function get_default_view(): string {
        $view = $this->config->timetable ?? '';
        return in_array($view, self::VIEWS, true) ? $view : 'today';
    }

    /**
     * The views users can switch between: the default view and the views selected in the settings.
     *
     * A block whose settings were never saved offers all views.
     *
     * @return string[]
     */
    public function get_views(): array {
        $default = $this->get_default_view();
        $configured = isset($this->config->timetable);
        return array_values(array_filter(self::VIEWS, function (string $view) use ($default, $configured): bool {
            return $view === $default || !$configured || !empty($this->config->{'checkbox' . $view});
        }));
    }

    /**
     * Events per page.
     *
     * @return int
     */
    public function get_limit(): int {
        $limit = (int) ($this->config->limit ?? 0);
        return $limit < 1 ? self::DEFAULT_LIMIT : min($limit, self::MAX_LIMIT);
    }

    /**
     * The time span of the view shown.
     *
     * @return int[] First and last second
     */
    public function get_timespan(): array {
        global $CFG;

        $start = $this->view === 'thisweek' ? $this->day : usergetmidnight(time());
        $end = $this->get_date($start);
        if ($this->view === 'nextxday') {
            // The look-ahead of the user's calendar preferences, as in the calendar's upcoming view.
            $lookahead = $CFG->calendar_lookahead ?? CALENDAR_DEFAULT_UPCOMING_LOOKAHEAD;
            $end->modify('+' . (int) get_user_preferences('calendar_lookahead', $lookahead) . ' days');
        } else {
            $end->modify('+1 day');
        }
        return [$start, $end->getTimestamp() - 1];
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output): array {
        global $PAGE;

        [$from, $to] = $this->get_timespan();
        $limitfrom = ($this->page - 1) * $this->get_limit();
        $limitto = min($limitfrom + $this->get_limit(), self::MAX_EVENTS);
        $calendar = \calendar_information::create($from, $this->courseid);
        $events = $this->get_events($calendar, $from, $to, $limitto + 1);
        $more = count($events) > $limitto && $limitto < self::MAX_EVENTS;
        $events = array_slice($events, $limitfrom, $limitto - $limitfrom);

        $related = [
            'events' => $events,
            'cache' => new events_related_objects_cache($events),
            'type' => type_factory::get_calendar_instance(),
        ];
        // The calendar's exporter needs the calendar's renderer.
        $exported = (new calendar_upcoming_exporter($calendar, $related))->export($PAGE->get_renderer('core_calendar'));
        foreach ($exported->events as $event) {
            $event->source = $this->get_source($event);
            $event->ended = $event->timestart + $event->timeduration < time();
        }

        // Switching views, days and pages needs a login, as the fragment API does.
        $interactive = isloggedin();
        $views = [];
        foreach ($this->get_views() as $view) {
            $views[] = ['view' => $view, 'name' => get_string($view, 'block_timetable'), 'active' => $view === $this->view];
        }

        return [
            'instanceid' => $this->instanceid,
            'contextid' => \context_block::instance($this->instanceid)->id,
            'courseid' => $this->courseid,
            'defaulteventcontext' => $exported->defaulteventcontext ?? 0,
            'view' => $this->view,
            'time' => $this->day,
            'horizontal' => ($this->config->view ?? '') === 'horizontal',
            'interactive' => $interactive,
            'showviews' => $interactive && count($views) > 1,
            'views' => $views,
            'showdays' => $interactive && $this->view === 'thisweek',
            'days' => $this->view === 'thisweek' ? $this->get_days() : [],
            'events' => $exported->events,
            'showpagination' => $interactive && ($this->page > 1 || $more),
            'previous' => $this->page > 1 ? $this->page - 1 : 0,
            'next' => $more ? $this->page + 1 : 0,
        ];
    }

    /**
     * The calendar events the user can see in the time span.
     *
     * The filters are the calendar's for the course, so a user sees the same events as in the calendar. In a course,
     * the block leaves out site and category events.
     *
     * @param \calendar_information $calendar The calendar of the course
     * @param int $from First second
     * @param int $to Last second
     * @param int $limitnum Most events to return
     * @return \core_calendar\local\event\entities\event_interface[]
     */
    protected function get_events(\calendar_information $calendar, int $from, int $to, int $limitnum): array {
        [$users, $groups, $courses, $categories] = array_map(function ($filter) {
            if ($filter === true) {
                return null;
            }
            if ($filter === false) {
                return [];
            }
            return is_array($filter) ? $filter : [$filter];
        }, [$calendar->users, $calendar->groups, $calendar->courses, $calendar->categories]);

        if ($this->courseid != SITEID) {
            $courses = array_values(array_intersect($courses, [$this->courseid]));
            $categories = [];
        }

        return \core_calendar\local\api::get_events(
            $from,
            $to,
            null,
            null,
            null,
            null,
            $limitnum,
            null,
            $users,
            $groups,
            $courses,
            $categories,
            true,
            true,
            function ($event): bool {
                if ($proxy = $event->get_course_module()) {
                    return $proxy->get_proxied_instance()->uservisible;
                }
                if ($proxy = $event->get_category()) {
                    return $proxy->get_proxied_instance()->is_uservisible();
                }
                return true;
            }
        );
    }

    /**
     * What an event belongs to, shown above its name: course, category or group name, or the event type.
     *
     * The names are formatted by the calendar's exporters already.
     *
     * @param stdClass $event An event exported by the calendar
     * @return string
     */
    protected function get_source(stdClass $event): string {
        switch ($event->normalisedeventtype) {
            case 'course':
                return $event->course->fullname ?? '';
            case 'category':
                return $event->category->name ?? '';
            case 'group':
                return $event->groupname ?? '';
            default:
                return $event->normalisedeventtypetext;
        }
    }

    /**
     * The days of this week, from the first day of the week in the user's calendar preferences.
     *
     * @return array
     */
    protected function get_days(): array {
        $type = type_factory::get_calendar_instance();
        $weekdays = $type->get_weekdays();
        $today = usergetmidnight(time());
        $offset = ($type->timestamp_to_date_array($today)['wday'] - calendar_get_starting_weekday() + 7) % 7;
        $date = $this->get_date($today)->modify('-' . $offset . ' days');

        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $time = $date->getTimestamp();
            $days[] = [
                'name' => $weekdays[$type->timestamp_to_date_array($time)['wday']]['shortname'],
                'fullname' => userdate($time, get_string('strftimedaydate', 'langconfig')),
                'time' => $time,
                'today' => $time === $today,
                'past' => $time < $today,
                'active' => $time === $this->day,
            ];
            $date->modify('+1 day');
        }
        return $days;
    }

    /**
     * A date in the user's timezone, so that adding days keeps midnight across daylight saving changes.
     *
     * @param int $time
     * @return \DateTime
     */
    protected function get_date(int $time): \DateTime {
        return (new \DateTime('@' . $time))->setTimezone(\core_date::get_user_timezone_object());
    }
}
