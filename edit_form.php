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

defined('MOODLE_INTERNAL') || die();

class block_timetable_edit_form extends block_edit_form {

    protected function specific_definition($mform) {
        $mform->addElement(
            'header',
            'configheader',
            get_string(
                'blocksettings',
                'core_block'
            )
        );

        $options = array(
        'today' => get_string('today', 'block_timetable'),
        'thisweek' => get_string('thisweek', 'block_timetable'),
        'nextxday' => get_string('nextxday', 'block_timetable')
        );
        $mform->addElement('select', 'config_timetable', get_string('type', 'block_timetable'), $options);
        $mform->addElement('header', null, 'Available View');
        $mform->addElement('checkbox', 'config_checkboxtoday', get_string('today', 'block_timetable'));
        if (isset($this->block->config->checkboxtoday)) {
            $mform->setDefault('checkboxtoday', $this->block->config->checkboxtoday);
        } else {
            $mform->setDefault('checkboxtoday', '1');
        }
        $mform->addElement('checkbox', 'config_checkboxthisweek', get_string('thisweek', 'block_timetable'));
        if (isset($this->block->config->checkboxthisweek)) {
            $mform->setDefault('checkboxthisweek', $this->block->config->checkboxthisweek);
        } else {
            $mform->setDefault('checkboxthisweek', '1');
        }
        $mform->addElement('checkbox', 'config_checkboxnextxday', get_string('nextxday', 'block_timetable'));
        if (isset($this->block->config->checkboxnextxday)) {
            $mform->setDefault('checkboxnextxday', $this->block->config->checkboxnextxday);
        } else {
            $mform->setDefault('checkboxnextxday', '1');
        }
        $mform->addElement('text', 'config_limit', get_string('limit', 'block_timetable'), 'maxlength="100" size="10"');
        $mform->setType('config_limit', PARAM_INT);
        if (isset($this->block->config->limit)) {
            $mform->setDefault('config_limit', $this->block->config->limit);
        } else {
            $mform->setDefault('config_limit', 5);
        }
        $options = array(
        'vertical' => get_string('vertical', 'block_timetable'),
        'horizontal' => get_string('horizontal', 'block_timetable'),
        );

        $mform->addElement('select', 'config_view', get_string('view', 'block_timetable'), $options);
    }
}
