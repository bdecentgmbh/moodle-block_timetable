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
 * Settings form of the Timetable block.
 *
 * @package    block_timetable
 * @copyright  2021 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_timetable\output\main;

/**
 * Settings form of the Timetable block.
 *
 * @package    block_timetable
 * @copyright  2021 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_timetable_edit_form extends block_edit_form {
    /**
     * The block's settings.
     *
     * @param MoodleQuickForm $mform
     */
    protected function specific_definition($mform) {
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $views = [];
        foreach (main::VIEWS as $view) {
            $views[$view] = get_string($view, 'block_timetable');
        }
        $mform->addElement('select', 'config_timetable', get_string('defaultview', 'block_timetable'), $views);
        $mform->setDefault('config_timetable', 'today');

        $checkboxes = [];
        foreach ($views as $view => $name) {
            $checkboxes[] = $mform->createElement('advcheckbox', 'config_checkbox' . $view, '', $name);
            $mform->setDefault('config_checkbox' . $view, 1);
        }
        $mform->addGroup($checkboxes, 'availableviews', get_string('availableviews', 'block_timetable'), ' ', false);
        $mform->addHelpButton('availableviews', 'availableviews', 'block_timetable');

        $mform->addElement('text', 'config_limit', get_string('limit', 'block_timetable'), ['size' => 3]);
        $mform->setType('config_limit', PARAM_INT);
        $mform->setDefault('config_limit', main::DEFAULT_LIMIT);
        $mform->addHelpButton('config_limit', 'limit', 'block_timetable');

        $mform->addElement('select', 'config_view', get_string('orientation', 'block_timetable'), [
            'vertical' => get_string('vertical', 'block_timetable'),
            'horizontal' => get_string('horizontal', 'block_timetable'),
        ]);

        if (class_exists(\tool_timetable\blocktimetable::class)) {
            (new \tool_timetable\blocktimetable())->blocktimetable_form_fields($mform);
        }
    }

    /**
     * Keep the number of events per page within the limits.
     *
     * @param array $data
     * @param array $files
     * @return array Errors
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if (isset($data['config_limit']) && ($data['config_limit'] < 1 || $data['config_limit'] > main::MAX_LIMIT)) {
            $errors['config_limit'] = get_string('limitrange', 'block_timetable', main::MAX_LIMIT);
        }
        return $errors;
    }
}
