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
 * Main renderable.
 *
 * @package    block_timetable
 * @copyright  2021 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class main implements templatable, renderable {
    /**
     * @var int The number of days to look ahead.
     */
    public $lookahead;

    /**
     * @var int The course id.
     */
    public $courseid;

    /**
     * @var int The id of the last event.
     */
    public $lastid;

    /**
     * @var int The date of the last event.
     */
    public $lastdate;

    /**
     * @var int The event number to start from.
     */
    public $limitfrom;

    /**
     * @var int  The number of events to show.
     */
    public $limitnum;

    /**
     * @var int The current page if JS is disabled.
     */
    public $page;
    /**
     * @var int The current page if JS is disabled.
     */
    public $time;
    /**
     * @var int The instance id page.
     */
    public $instanceid;
    /**
     * @var string The blockview.
     */
    public $blockview;
    /**
     * @var string layout of current event
     */
    public $ulayout;

    /**
     * Constructor.
     *
     * @param int $lookahead
     * @param int $courseid
     * @param int $lastid
     * @param int $lastdate
     * @param int $limitfrom
     * @param int $limitnum
     * @param int $page
     * @param int $blockview
     * @param int $time
     * @param int $instanceid
     * @param string $ulayout layout of current event
     */
    public function __construct(
        $lookahead,
        $courseid,
        $lastid,
        $lastdate,
        $limitfrom,
        $limitnum,
        $page,
        $blockview,
        $time,
        $instanceid,
        $ulayout) {
        $this->lookahead = $lookahead;
        $this->courseid = $courseid;
        $this->lastid = $lastid;
        $this->lastdate = $lastdate;
        $this->limitfrom = $limitfrom;
        $this->limitnum = $limitnum;
        $this->page = $page;
        $this->blockview = $blockview;
        $this->time = $time;
        $this->instanceid = $instanceid;
        $this->ulayout = $ulayout;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output) {
        global $PAGE;

        $events = new eventlist(
            $this->lookahead,
            $this->courseid,
            $this->lastid,
            $this->lastdate,
            $this->limitfrom,
            $this->limitnum,
            $this->page,
            $this->blockview,
            $this->time ,
            $this->instanceid,
            $this->ulayout
        );
        $templatecontext = $events->export_for_template($output);
        return $templatecontext;
    }
}
