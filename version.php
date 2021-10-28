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
 * Version details
 *
 * @package    block_timetable
 * @copyright  2021 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2021102800;        // The current plugin version (Date: YYYYMMDDXX).
$plugin->requires  = 2020061500;        // Requires this Moodle version.
$plugin->component = 'block_timetable'; // Full name of the plugin (used for diagnostics).
$plugin->maturity = MATURITY_RC;   // This is considered as not ready for production sites.
$plugin->supported = [39, 311];      // This is version of moodle.
$plugin->release = '1.0';           // This is our first revision for Moodle 3.9.x branch.
