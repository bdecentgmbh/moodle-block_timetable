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
 * Privacy Subsystem implementation for block_timetable.
 *
 * @package    block_timetable
 * @copyright  2021 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_timetable\privacy;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy Subsystem implementing null_provider.
 *
 * @package    block_timetable
 * @copyright  2021 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements \core_privacy\local\metadata\null_provider, \core_privacy\local\request\user_preference_provider  {

    /**
     * Get the language string identifier with the component's language
     * file to explain why this plugin stores no data.
     *
     * @return  string
     */
    public static function get_reason() : string {
        return 'privacy:metadata';
    }
     /**
     * Returns meta-data information about the timetable block.
     *
     * @param  \core_privacy\local\metadata\collection $collection A collection of meta-data.
     * @return \core_privacy\local\metadata\collection Return the collection of meta-data.
     */
    public static function get_metadata(collection $collection) : collection {
        $collection->add_user_preference('block_timetable_user_sort_preference', 'privacy:metadata:timetablesortpreference');
        $collection->add_user_preference('block_timetable_user_filter_preference', 'privacy:metadata:timetablefilterpreference');
        $collection->add_user_preference('block_timetable_user_limit_preference', 'privacy:metadata:timetablelimitpreference');
        return $collection;
    }

    /**
     * Export all user preferences for the myoverview block
     *
     * @param int $userid The userid of the user whose data is to be exported.
     */
    public static function export_user_preferences(int $userid) {
        $preference = get_user_preferences('block_timetable_user_sort_preference', null, $userid);
        if (isset($preference)) {
            \core_privacy\local\request\writer::export_user_preference('block_timetable', 'block_timetable_user_sort_preference',
                    get_string($preference, 'block_timetable'),
                    get_string('privacy:metadata:timetablesortpreference', 'block_timetable')
            );
        }

        $preference = get_user_preferences('block_timetable_user_filter_preference', null, $userid);
        if (isset($preference)) {
            \core_privacy\local\request\writer::export_user_preference('block_timetable', 'block_timetable_user_filter_preference',
                    get_string($preference, 'block_timetable'),
                    get_string('privacy:metadata:timetablefilterpreference', 'block_timetable')
            );
        }

        $preference = get_user_preferences('block_timetable_user_limit_preference', null, $userid);
        if (isset($preference)) {
            \core_privacy\local\request\writer::export_user_preference('block_timetable', 'block_timetable_user_limit_preference',
                    $preference,
                    get_string('privacy:metadata:timetablelimitpreference', 'block_timetable')
            );
        }
    }
}
