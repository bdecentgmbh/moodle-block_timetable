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

use plugin_renderer_base;
use renderable;
use stdClass;
/**
 * Timetable
 *
 * Timetable renderer.
 *
 * @package    block_timetable
 * @copyright  2021 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {
    /**
     * @var int instanceid.
     */
    public $instanceid;

    /**
     * @var int courseid.
     */
    public $courseid;

    /**
     * @var int time.
     */
    public $time;

    /**
     * @var string ulayout.
     */
    public $ulayout;

    /**
     * Return the main content for the block timetable.
     *
     * @param main $main The main renderable
     * @return string HTML string
     */
    public function render_main(main $main) {
        return $this->render_from_template('block_timetable/main', $main->export_for_template($this));
    }
}
