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
 * Form for editing HTML block instances.
 *
 * @package    block_timetable
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_timetable extends block_base {
    public function init() {
        $this->title = get_string('pluginname', 'block_timetable');
    }

    public function has_config() {
        return true;
    }

    public function applicable_formats() {
        return array('all' => true);
    }
    public function get_content() {
        global $CFG, $OUTPUT, $COURSE;

        require_once($CFG->dirroot . '/calendar/lib.php');

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text   = '';
        $this->content->footer = '';
        if (empty($this->instance)) {
            return $this->content;
        } else {
            $ulayout = optional_param('ulayout', $this->config->timetable, PARAM_RAW);
            if ($ulayout == "nextxday") {
                  $maxevents = get_user_preferences('calendar_maxevents', CALENDAR_DEFAULT_UPCOMING_LOOKAHEAD);
                  $lookahead = get_user_preferences('calendar_lookahead', CALENDAR_DEFAULT_UPCOMING_LOOKAHEAD);
            } else {
                if (empty($this->config->limit)) {
                      $this->config->limit = 80;
                }
                $limitnum = $this->config->limit;
                $lookahead = true;
            }
            if ($ulayout == "thisweek") {
                $calendartype = \core_calendar\type_factory::get_calendar_instance();
                $calendarweek = $calendartype->get_weekdays();
            }
            $page = optional_param('block_timetable_page', 1, PARAM_RAW);
            $limitfrom = $page > 1 ? ($page * $limitnum) - $limitnum : 0;
            $lastdate = 0;
            $lastid = 0;
            $courseid = $COURSE->id;
            if (empty($this->config->view)) {
                $this->config->view = 'vertical';
            }
            $blockview = $this->config->view;
            $renderable = new \block_timetable\output\main(
                $lookahead,
                $courseid,
                $lastid,
                $lastdate,
                $limitfrom,
                $limitnum,
                $page,
                $blockview
            );
            $checkboxtoday = $this->config->checkboxtoday;
            $checkboxthisweek = $this->config->checkboxthisweek;
            $checkboxnextxday = $this->config->checkboxnextxday;
            $renderer = $this->page->get_renderer('block_timetable');
            $this->content->text = "";
            if ( $checkboxtoday || $checkboxthisweek || $checkboxnextxday ) {
                $texttimetable = get_string('today', 'block_timetable');
                if ( $ulayout == "nextxday" ) {
                    $texttimetable = get_string('nextxday', 'block_timetable');
                } else if ( $ulayout == "thisweek" ) {
                    $texttimetable = get_string('thisweek', 'block_timetable');
                }
                $this->content->text .= '<div class="col-sm d-flex justify-content-end">
                <div data-region="view-selector" class="btn-group">';
                $this->content->text .= '<button type="button" class="btn btn-outline-secondary dropdown-toggle icon-no-margin"';
                $this->content->text .= ' data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" ';
                $this->content->text .= ' aria-label="Sort timeline items" aria-controls="menusortby"> ';
                $this->content->text .= $texttimetable;
                $this->content->text .= '</button><div id="menusortby" role="menu" class="dropdown-menu dropdown-menu-right';
                $this->content->text .= 'list-group hidden" data-show-active-item="" data-skip-active-class="true">';
                if ($checkboxtoday) {
                    $url = new moodle_url($this->page->url , ['ulayout' => 'today' ]);
                    $this->content->text .= ' <a class="dropdown-item" href="'. $url.'" >
                           '.get_string('today', 'block_timetable').'
                        </a>';
                }
                if ($checkboxthisweek) {
                    $url = new moodle_url($this->page->url, ['ulayout' => 'thisweek' ]);
                    $this->content->text .= ' <a class="dropdown-item" href="'. $url.'" >
                            '.get_string('thisweek', 'block_timetable').'
                        </a>';
                }
                if ($checkboxnextxday) {
                    $url = new moodle_url($this->page->url, ['ulayout' => 'nextxday' ]);
                    $this->content->text .= ' <a class="dropdown-item" href="'. $url.'" >
                        '.get_string('nextxday', 'block_timetable').'
                        </a>';
                }
                $this->content->text .= '</div></div></div>';
            }
            if ($ulayout == "thisweek") {
                $this->content->text .= "<div class='timetable_calendar'>";
                $l = 0;
                $time = optional_param('time', strtotime('today midnight'), PARAM_INT);
                $weeknumber = date( 'N' );
                $weekday = date('l' , $time);
                foreach ($calendarweek as $cal) {
                    $class = "";
                    if ($l < $weeknumber) {
                        $class = " inactive";
                    }
                    if (  $weekday == $cal['fullname'] ) {
                        $class = " active";
                    }
                    $url = new moodle_url($this->page->url, ['time' => strtotime( $cal['fullname'].' this week midnight')]);
                    $this->content->text .= "<div class='timetable_day".$class."'><a href='".$url."'>".$cal['shortname'];
                    $this->content->text .= "</a></div>";
                    $l++;
                }
                $this->content->text .= "</div>";
            }
            $this->content->text .= $renderer->render($renderable);
            $renderable = new \block_timetable\output\footer($courseid);
            $this->content->footer .= $renderer->render($renderable);
        }

        return $this->content;
    }
    /**
     * Return the plugin config settings for external functions.
     *
     * @return stdClass the configs for both the block instance and plugin
     * @since Moodle 3.8
     */
    public function get_config_for_external() {
        // Return all settings for all users since it is safe (no private keys, etc..).
        $configs = !empty($this->config) ? $this->config : new stdClass();

        return (object) [
            'instance' => $configs,
            'plugin' => new stdClass(),
        ];
    }

}
