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
 * Switches the view, day and page of a Timetable block without reloading the page.
 *
 * Clicking an event shows the calendar's event summary, from which it can be edited or deleted. After that, and after
 * new events, the block reloads its content.
 *
 * @module     block_timetable/main
 * @copyright  2021 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import CalendarCrud from 'core_calendar/crud';
import CalendarEvents from 'core_calendar/events';
import * as CalendarViewManager from 'core_calendar/view_manager';
import Fragment from 'core/fragment';
import Notification from 'core/notification';
import Pending from 'core/pending';
import Templates from 'core/templates';

/**
 * Replace the block content with another view, day or page.
 *
 * @param {HTMLElement} root The block's region
 * @param {String} view The view
 * @param {Number} time A time on the day shown in the This week view, 0 for today
 * @param {Number} page The page of events
 * @returns {Promise}
 */
const load = (root, view, time, page) => {
    const pending = new Pending('block_timetable/main:load');
    const params = {courseid: root.dataset.courseid, view, time, page};
    return Fragment.loadFragment('block_timetable', 'content', root.dataset.contextid, params)
        .then((html, js) => Templates.replaceNodeContents(root, html, js))
        .then(pending.resolve)
        .catch(Notification.exception);
};

/**
 * Set up a Timetable block.
 *
 * @param {Number} instanceid The block instance id
 */
export const init = (instanceid) => {
    const root = document.querySelector(`[data-region="block_timetable"][data-instanceid="${instanceid}"]`);
    if (!root || root.dataset.initialised) {
        return;
    }
    root.dataset.initialised = true;

    root.addEventListener('click', (e) => {
        const button = e.target.closest('[data-action="block_timetable-show"]');
        if (button) {
            e.preventDefault();
            load(root, button.dataset.view, button.dataset.time || 0, button.dataset.page || 1);
        }
    });

    CalendarViewManager.init($(root), 'upcoming');
    CalendarCrud.registerEditListeners($(root), CalendarCrud.registerEventFormModal($(root)));
    $('body').on([CalendarEvents.created, CalendarEvents.updated, CalendarEvents.deleted].join(' '), () => {
        const content = root.querySelector('[data-region="content"]');
        load(root, content.dataset.view, content.dataset.time, 1);
    });
};
