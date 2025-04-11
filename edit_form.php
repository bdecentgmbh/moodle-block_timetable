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
 * Form for editing Timeline block instances.
 *
 * @package    block_timetable
 * @copyright  2021 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_timetable_edit_form extends block_edit_form {
     /**
      * Function that create a specific defination of form
      * readable Object of mform
      *
      * @param object $mform
      * @return object $mform
      */
    protected function specific_definition($mform) {
        $mform->addElement(
            'header',
            'configheader',
            get_string(
                'blocksettings',
                'core_block'
            )
        );

        $options = [
        'today' => get_string('today', 'block_timetable'),
        'thisweek' => get_string('thisweek', 'block_timetable'),
        'nextxday' => get_string('nextxday', 'block_timetable'),
        ];
        $mform->addElement('select', 'config_timetable', get_string('type', 'block_timetable'), $options);
        $mform->addElement('header', '', 'Available View');
        $mform->addElement('selectyesno', 'config_checkboxtoday', get_string('today', 'block_timetable'));
        $mform->setDefault('config_checkboxtoday', !empty($config->checkboxtoday) ? 1 : 0);
        $mform->addElement('selectyesno', 'config_checkboxthisweek', get_string('thisweek', 'block_timetable'));
        $mform->setDefault('config_checkboxthisweek', !empty($config->checkboxthisweek) ? 1 : 0);
        $mform->addElement('selectyesno', 'config_checkboxnextxday', get_string('nextxday', 'block_timetable'));
        $mform->setDefault('config_checkboxtoday', !empty($config->checkboxtoday) ? 1 : 0);
        $mform->addElement('text', 'config_limit', get_string('limit', 'block_timetable'), 'maxlength="100" size="10"');
        $mform->setType('config_limit', PARAM_INT);
        if (isset($this->block->config->limit)) {
            $mform->setDefault('config_limit', $this->block->config->limit);
        } else {
            $mform->setDefault('config_limit', 5);
        }
        $options = [
        'vertical' => get_string('vertical', 'block_timetable'),
        'horizontal' => get_string('horizontal', 'block_timetable'),
        ];

        $mform->addElement('select', 'config_view', get_string('view', 'block_timetable'), $options);
    }
}
