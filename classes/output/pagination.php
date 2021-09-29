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
 * Timetable
 *
 * Pagination renderable.
 *
 * @package    block_timetable
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 *
 */
namespace block_timetable\output;

use renderer_base;
use renderable;
use templatable;
use stdClass;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

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
        $pagination = $this->get_pagination($this->prev, $this->next);

        return $pagination;
    }

    /**
     * Function to create the pagination. This will only show up for non-js
     * enabled browsers.
     *
     * @param bool $prev whether or not there are previous pages
     * @param bool $next whether or not there are following pages
     * @return stdClass
     */
    public function get_pagination($prev = false, $next = false) {
        global $PAGE;

        $pagination = new stdClass();

        if ($prev) {
            $pagination->prev = new stdClass();
            $pagination->prev->prevurl = new moodle_url($PAGE->url, ['block_timetable_page' => $prev]);
            $pagination->prev->prevtext = get_string('previous', 'block_timetable');
        }
        if ($prev && $next) {
            $pagination->sep = '&nbsp;|&nbsp;';
        } else {
            $pagination->sep = '&nbsp;|&nbsp;';
        }
        if ($next) {
            $pagination->next = new stdClass();
            $pagination->next->nexturl = new moodle_url($PAGE->url, ['block_timetable_page' => $next]);
            $pagination->next->nexttext = get_string('next', 'block_timetable');
        }

        return $pagination;
    }
}
