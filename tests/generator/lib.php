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
 * Data generator of the Timetable block.
 *
 * @package    block_timetable
 * @category   test
 * @copyright  2021 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Creates Timetable blocks, with their settings given as fields of the record.
 *
 * The settings are timetable (default view), checkboxtoday, checkboxthisweek, checkboxnextxday (views to switch
 * between), limit (events per page) and view (orientation).
 *
 * @package    block_timetable
 * @category   test
 * @copyright  2021 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_timetable_generator extends testing_block_generator {
    /** @var string[] The settings a record can set. */
    public const SETTINGS = ['timetable', 'checkboxtoday', 'checkboxthisweek', 'checkboxnextxday', 'limit', 'view'];

    /**
     * Turn the settings of the record into the block's configuration.
     *
     * @param stdClass $record
     * @param array $options
     */
    protected function preprocess_record(stdClass $record, array $options) {
        $config = [];
        foreach (self::SETTINGS as $setting) {
            if (isset($record->$setting)) {
                $config[$setting] = $record->$setting;
                unset($record->$setting);
            }
        }
        if ($config) {
            $record->configdata = base64_encode(serialize((object) $config));
        }
    }
}
