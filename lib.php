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
 * Timetable  block instances.
 *
 * @package    block_timetable
 * @copyright  2021 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * Check if the timetable tool is installed.
 *
 * @return bool
 */
function timetable_tool_installed() {
    global $CFG;
    static $result;

    if ($result == null) {
        if (array_key_exists('timetable', core_component::get_plugin_list('tool'))) {
            require_once($CFG->dirroot.'/admin/tool/timetable/classes/blocktimetable.php');
            $result = true;
        } else {
            $result = false;
        }
    }

    return $result;
}
