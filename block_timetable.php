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
 * Timetable block.
 *
 * @package    block_timetable
 * @copyright  2021 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Shows the calendar events of today, this week or the upcoming days.
 *
 * @package    block_timetable
 * @copyright  2021 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_timetable extends block_base {
    /**
     * Set the block title.
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_timetable');
    }

    /**
     * Allow several blocks on a page.
     *
     * @return bool
     */
    public function instance_allow_multiple() {
        return true;
    }

    /**
     * Pages the block can be added to.
     *
     * @return array
     */
    public function applicable_formats() {
        return ['all' => true];
    }

    /**
     * The block content: the events of the course, or of all the user's courses outside courses.
     *
     * @return stdClass
     */
    public function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = (object) ['text' => '', 'footer' => ''];
        if (empty($this->instance)) {
            return $this->content;
        }

        $courseid = (int) $this->page->course->id;
        $renderer = $this->page->get_renderer('core');
        $main = new \block_timetable\output\main($this->config, $this->instance->id, $courseid);

        if (class_exists(\tool_timetable\blocktimetable::class)) {
            // The Timetable admin tool adds its visual timeline and completion visuals, and can hide the events.
            $config = $this->config ? clone $this->config : new stdClass();
            $config->showevents = $config->showevents ?? 1;
            $content = (new \tool_timetable\blocktimetable())->timetable_content(
                $config,
                $this->page->context,
                $this->instance->id,
                $courseid,
                $renderer,
                $main
            );
            $this->content->text = $content['text'];
            $this->content->footer = $content['footer'];
        } else {
            $this->content->text = $renderer->render($main);
            $this->content->footer = $renderer->render(new \block_timetable\output\footer($courseid));
        }

        return $this->content;
    }

    /**
     * Return the plugin config settings for external functions.
     *
     * @return stdClass the configs for both the block instance and plugin
     */
    public function get_config_for_external() {
        // The settings are views, a limit and the orientation, so all of them can be returned.
        return (object) [
            'instance' => $this->config ?? new stdClass(),
            'plugin' => new stdClass(),
        ];
    }
}
