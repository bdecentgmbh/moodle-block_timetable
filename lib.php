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
 * Callbacks of the Timetable block.
 *
 * @package    block_timetable
 * @copyright  2021 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * The block content for another view, day or page, loaded by block_timetable/main.
 *
 * The fragment API has validated the block context already. The events are always the current user's, filtered as in
 * the calendar; the course is checked like a course page would check it.
 *
 * @param array $args The block context, and courseid, view, time and page
 * @return string HTML
 */
function block_timetable_output_fragment_content(array $args): string {
    global $PAGE;

    $context = $args['context'];
    if ($context->contextlevel != CONTEXT_BLOCK) {
        throw new moodle_exception('invalidcontext');
    }
    $block = block_instance_by_id($context->instanceid);
    if ($block->instance->blockname !== 'timetable') {
        throw new moodle_exception('invalidcontext');
    }
    require_capability('moodle/block:view', $context);

    $courseid = clean_param($args['courseid'] ?? SITEID, PARAM_INT);
    if ($courseid != SITEID && !can_access_course(get_course($courseid))) {
        throw new require_login_exception('Course not accessible');
    }

    $main = new \block_timetable\output\main(
        $block->config,
        $block->instance->id,
        $courseid,
        clean_param($args['view'] ?? '', PARAM_ALPHA),
        clean_param($args['time'] ?? 0, PARAM_INT),
        clean_param($args['page'] ?? 1, PARAM_INT)
    );
    $output = $PAGE->get_renderer('core');
    return $output->render_from_template('block_timetable/content', $main->export_for_template($output));
}
