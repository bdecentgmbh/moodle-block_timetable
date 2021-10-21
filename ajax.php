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
 * Footer renderable.
 *
 * @package    block_timetable
 * @copyright  2021 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 *
 */

define('AJAX_SCRIPT', true);
require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/../../calendar/lib.php');
use block_timetable\output\eventlist;
require_login();
global $DB;
$PAGE->set_context(context_system::instance());
$lookahead = 1;
$courseid = optional_param('courseid', 1, PARAM_INT);
$limitnum = optional_param('limitnum', 5, PARAM_INT);
$page = optional_param('block_timetable_page', 1, PARAM_INT);
$time = optional_param('time', strtotime('today midnight'), PARAM_INT);
$instanceid = required_param('instanceid', PARAM_INT);
$list = '';
$end = false;
$renderer = $PAGE->get_renderer('block_timetable');
$events = new eventlist(
    $lookahead,
    $courseid,
    0,
    0,
    0,
    $limitnum,
    $page,
    'vertical',
    $time
);
$templatecontext = $events->export_for_template($renderer);
$events = $templatecontext['events'];
if ($events) {
        $list .= $renderer->render_from_template('block_timetable/events',  $templatecontext);
}

echo json_encode(['output' => $list]);
