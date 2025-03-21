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

use renderer_base;
use renderable;
use templatable;
use stdClass;

/**
 * Timetable
 *
 * Footer renderable.
 *
 * @package    block_timetable
 * @copyright  2021 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class footer implements templatable, renderable {
    /**
     * @var int The course id.
     */
    public $courseid;

    /**
     * Constructor.
     *
     * @param int $courseid
     */
    public function __construct($courseid) {
        $this->courseid = $courseid;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output) {
        global $PAGE;

        $footer = new stdClass();
        $footer->manageentries = false;
        $context = \context_course::instance($this->courseid);
        $footer->calendarurl = new \moodle_url('/calendar/view.php', ['view' => 'upcoming', 'course' => $this->courseid]);

        if (has_any_capability(['moodle/calendar:manageentries', 'moodle/calendar:manageownentries'], $context)) {
            $footer->addeventurl = new \moodle_url('/calendar/event.php', ['action' => 'new', 'course' => $this->courseid]);
            $footer->manageentries = true;
        }

        return ['footer' => $footer];
    }
}
