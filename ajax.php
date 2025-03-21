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
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);
require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/../../calendar/lib.php');
use block_timetable\output\eventlist;
require_login();
global $DB , $CFG, $COURSE;
require_once( $CFG->libdir.'/blocklib.php' );
$PAGE->set_context(context_system::instance());
$page = optional_param('block_timetable_page', 1, PARAM_INT);
$time = optional_param('time', strtotime('today midnight'), PARAM_INT);
$courseid = optional_param('courseid', $COURSE->id, PARAM_INT);
$instanceid = required_param('instanceid', PARAM_INT);
$ulayout = required_param('ulayout', PARAM_RAW);
$context = context_block::instance($instanceid);
$instance = $DB->get_record('block_instances', ['id' => $instanceid]);

if (!empty($instanceid)) {
    if (!empty($instance->configdata)) {
        $config = unserialize(base64_decode($instance->configdata));
    } else {
        $config = new \stdClass();
        $config->view = 'vertical';
    }
}

if ( $ulayout == "nextxday" ) {
    if (isloggedin()) {
        $maxevents = get_user_preferences('calendar_maxevents', 10);
        $lookahead = get_user_preferences('calendar_lookahead', 6);
    } else {
        $maxevents = 10;
        $lookahead = 6;
    }
} else {
        $lookahead = true;
}

$list = '';
$end = false;
$limitnum = $config->limit;
$limitfrom = $page > 1 ? ($page * $limitnum) - $limitnum : 0;
$lastdate = 0;
$lastid = 0;
$renderer = $PAGE->get_renderer('block_timetable');

$blockview = $config->view;
$events = new eventlist(
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
    $ulayout
);
$templatecontext = $events->export_for_template($renderer);
$events = $templatecontext['events'];
if ($events) {
        $list .= $renderer->render_from_template('block_timetable/events',  $templatecontext);
}

echo json_encode(['output' => $list]);
