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
 * block_timetable  block instances.
 *
 * @package    block_timetable
 * @copyright  2021 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_timetable extends block_base {
    /**
     * Return the initiation content for the block timetable.
     *
     * @return string HTML string
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_timetable');
    }
    /**
     * Return the configuration for the block timetable.
     *
     * @return boolean
     */
    public function has_config() {
        return true;
    }
     /**
      * Return the configuration for the block timetable.
      *
      * @return boolean
      */
    public function instance_allow_multiple() {
        return true;
    }
     /**
      * Return the applicable format.
      *
      * @return array
      */
    public function applicable_formats() {
        return ['all' => true];
    }
     /**
      * Return the applicable format.
      *
      * @return object content
      */
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
            if (!isset($this->config)) {
                $this->config = new stdClass();
            }
            if (empty($this->config->view)) {
                $this->config->view = 'today';
            }
            $instanceid = optional_param('instanceid', 0, PARAM_INT);
            if ($instanceid == $this->instance->id) {
                $ulayout = optional_param('ulayout', @$this->config->timetable, PARAM_RAW);
                $page = optional_param('block_timetable_page', 1, PARAM_RAW);
                $time = optional_param('time', strtotime('today midnight'), PARAM_INT);
            } else {
                $ulayout = @$this->config->timetable;
                $page = 1;
                $time = strtotime('today midnight');
            }
            if ($ulayout == "nextxday") {
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
            if (empty(@$this->config->limit)) {
                $this->config->limit = 5;
            } else if ($this->config->limit == 0) {
                $this->config->limit = 80;
            }
            $limitnum = $this->config->limit;
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
                $blockview,
                $time,
                $this->instance->id,
                $ulayout
            );
            $checkboxtoday = @$this->config->checkboxtoday;
            $checkboxthisweek = @$this->config->checkboxthisweek;
            $checkboxnextxday = @$this->config->checkboxnextxday;
            $renderer = $this->page->get_renderer('block_timetable');
            $this->content->text = "";
            $qblock = false;
            if ( $checkboxtoday || $checkboxthisweek || $checkboxnextxday ) {
                $texttimetable = get_string('today', 'block_timetable');
                if ( $ulayout == "nextxday" ) {
                    $texttimetable = get_string('nextxday', 'block_timetable');
                } else if ( $ulayout == "thisweek" ) {
                    $texttimetable = get_string('thisweek', 'block_timetable');
                }
                if ( $checkboxtoday == true&&$checkboxthisweek == false&&$checkboxnextxday == false ) {
                    $qblock = true;
                } else if ( $checkboxtoday == false&&$checkboxthisweek == true&&$checkboxnextxday == false ) {
                    $qblock = true;
                } else if ($checkboxtoday == false&&$checkboxthisweek == false&&$checkboxnextxday == true) {
                    $qblock = true;
                } else {
                    $this->content->text .= '<div class="col-sm d-flex justify-content-end">';
                    $this->content->text .= '<div data-region="view-selector" class="btn-group">';
                    $this->content->text .= '<button type="button" class="btn btn-outline-secondary dropdown-toggle ';
                    $this->content->text .= 'icon-no-margin" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" ';
                    $this->content->text .= ' aria-label="Sort timeline items" aria-controls="menusortby"> ';
                    $this->content->text .= $texttimetable;
                    $this->content->text .= '</button><div id="menusortby" role="menu" class="dropdown-menu dropdown-menu-right';
                    $this->content->text .= 'list-group hidden" data-show-active-item="" data-skip-active-class="true">';
                    if ( $checkboxtoday ) {
                        $varparams = ['ulayout' => 'today' , 'instanceid' => $this->context->instanceid , 'courseid' => $courseid];
                        $url = new moodle_url($CFG->wwwroot.'/blocks/timetable/ajax.php' , $varparams);
                        $this->content->text .= ' <a class="dropdown-item timeblock'.$this->context->instanceid.'" ';
                        $this->content->text .= ' "href="#" data-mode="today" data-url="'.$url.'">';
                        $this->content->text .= get_string('today', 'block_timetable').'</a>';
                    }
                    if ( $checkboxthisweek ) {
                        $varparams = ['ulayout' => 'thisweek' , 'instanceid' => $this->context->instanceid ,
                                      'courseid' => $courseid];
                        $url = new moodle_url($CFG->wwwroot.'/blocks/timetable/ajax.php', $varparams);
                        $this->content->text .= ' <a class="dropdown-item timeblock'.$this->context->instanceid.'"
                        href="#" data-mode="thisweek" data-url="'.$url.'">
                            '.get_string('thisweek', 'block_timetable').'
                        </a>';
                    }
                    if ( $checkboxnextxday ) {
                        $varparams = ['ulayout' => 'nextxday' , 'instanceid' => $this->context->instanceid,
                                      'courseid' => $courseid];
                        $url = new moodle_url($CFG->wwwroot.'/blocks/timetable/ajax.php', $varparams);
                        $this->content->text .= ' <a class="dropdown-item timeblock'.$this->context->instanceid.'"
                        href="#" data-mode="nextxday" data-url="'.$url.'">
                        '.get_string('nextxday', 'block_timetable').'
                        </a>';
                    }
                    $this->content->text .= '</div></div></div>';
                }
            }
            if ($ulayout == "thisweek" || $checkboxthisweek) {
                $calendartype = \core_calendar\type_factory::get_calendar_instance();
                $calendarweek = $calendartype->get_weekdays();
                if (isloggedin()) {
                    $startwday = (int)get_user_preferences('calendar_startwday', 1);
                } else {
                    $startwday = 1;
                }
                if ( $ulayout == "thisweek" ) {
                    $style = "style = 'display:block;'";
                } else {
                    $style = "style = 'display:none;'";
                }
                $preference = $startwday;
                $now = strtotime(("today midnight"));
                $this->content->text .= "<div class='timetable_calendar' id='cal".$this->context->instanceid."'  ".$style.">";
                $l = $startwday - 1;
                $todayweek = date( 'N');
                $todayweekname = date( 'l');
                $weekday = date('l' , $time);
                $midnight = "this";
                $c = 0;
                $m = 0;
                $n = 0;
                foreach ($calendarweek as $cal) {
                    if ($startwday == 7) {
                        $startwday = 0;
                    }
                    $cal = $calendarweek[$startwday];
                    $class = "";
                    $l++;
                    if ( $m == 0 ) {
                        $class = " inactive";
                        $week = "last";
                    }
                    if ( $todayweekname == $cal['fullname']) {
                        $m = 1;
                        if (  $weekday == $cal['fullname'] ) {
                            $class = " now active";
                        } else {
                            $class = " now";
                        }
                        $week = " this";
                    }
                    $caltime = strtotime( $cal['fullname'].' '.$week.' week  midnight');
                    $clayout = "thisweek";
                    $varparams = ['time' => $caltime , 'instanceid' => $this->context->instanceid ,
                                  'ulayout' => $clayout , 'courseid' => $courseid ];
                    $url = new moodle_url($CFG->wwwroot.'/blocks/timetable/ajax.php', $varparams);
                    $this->content->text .= "<div class='dateblock_day".$this->context->instanceid." timetable_day".$class."'>
                    <a class='dateblock".$this->context->instanceid."' href='#'
                    data-mode='".$clayout."'  data-url='".$url."' >".$cal['shortname'];
                    $this->content->text .= "</a></div>";
                    $startwday++;
                    $n++;
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
