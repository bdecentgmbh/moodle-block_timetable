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
use moodle_url;

/**
 * Timetable
 *
 * Pagination renderable.
 *
 * @package    block_timetable
 * @copyright  2021 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class pagination implements templatable, renderable {
    /**
     * @var bool whether or not there are previous pages.
     */
    public $prev;

    /**
     * @var bool whether or not there are following pages.
     */
    public $next;

    /**
     * Constructor.
     *
     * @param bool $prev
     * @param bool $next
     */
    public function __construct($prev, $next) {
        $this->prev = $prev;
        $this->next = $next;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        $pagination = $this->get_pagination($this->prev, $this->next, $output->instanceid, $output->time,
                                            $output->ulayout, $output->courseid);
        return $pagination;
    }

    /**
     * Function to create the pagination. This will only show up for non-js
     * enabled browsers.
     *
     * @param bool $prev whether or not there are previous pages
     * @param bool $next whether or not there are following pages
     * @param int $instanceid id of current instance
     * @param int $time time of current event range start
     * @param string $ulayout layout of current event
     * @param int $courseid course id
     * @return stdClass
     */
    public function get_pagination($prev = false, $next = false, $instanceid = null, $time = null,
        $ulayout = '', $courseid = null) {
        global $CFG;

        $pagination = new stdClass();

        if ($prev) {
            $pagination->prev = new stdClass();
            $varparams = ['block_timetable_page' => $prev , 'instanceid' => $instanceid , 'time' => $time ,
                          'ulayout' => $ulayout, 'courseid' => $courseid];
            $pagination->prev->prevurl = new moodle_url($CFG->wwwroot.'/blocks/timetable/ajax.php', $varparams);
            $pagination->prev->prevtext = get_string('previous', 'block_timetable');
        }
        if ($prev && $next) {
            $pagination->sep = '&nbsp;|&nbsp;';
        } else {
            $pagination->sep = '';
        }
        if ($next) {
            $pagination->next = new stdClass();
            $varparams = ['block_timetable_page' => $next , 'instanceid' => $instanceid , 'time' => $time ,
                          'ulayout' => $ulayout, 'courseid' => $courseid];
            $pagination->next->nexturl = new moodle_url($CFG->wwwroot.'/blocks/timetable/ajax.php', $varparams );
            $pagination->next->nexttext = get_string('next', 'block_timetable');
        }

        return $pagination;
    }
}
