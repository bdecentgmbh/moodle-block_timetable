moodle-block_timetable
========================

Moodle block plugin which displays the calendar events of today, this week or the upcoming days in a beautiful way – on the Dashboard and in courses.


Requirements
------------

This plugin requires Moodle 4.5 or later and is supported on Moodle 4.5 up to 5.3.


Motivation for this plugin
--------------------------

Moodle's unified calendar is a brilliant foundation for everything related to events and provides deep integration for a lot of activities as well. This block therefore provides an additional interface for the existing calendar entries.

It aims to be an additional alternative to block_upcomingevents and block_calendar. The best location to put the block is the Dashboard: there it provides an overview of the learner's day, week and upcoming events across all courses. In a course, it shows the events of that course.


Installation
------------

Install the plugin like any other plugin to folder
/blocks/timetable (on Moodle 5.1 and later: /public/blocks/timetable)

See http://docs.moodle.org/en/Installing_plugins for details on installing Moodle plugins


Usage & Settings
----------------

After installing the plugin, it is ready to use without the need for any configuration.

Teachers can add the block to their courses, users to their Dashboard, and admins to the default Dashboard for everybody. The block shows three views, which users switch between with the buttons at the top of the block:

* Today: the events of today.
* This week: a button for each day of the week; selecting a day shows its events. The week starts on the first day of the week of the user's calendar preferences.
* Upcoming: the events from today on, for as many days as the user's "Upcoming events look-ahead" calendar preference.

The block shows the same events as the calendar: activity dates such as assignment due dates, course events, events of the user's groups and the user's own events, and on the Dashboard also site and category events. In a course, it shows the events of that course and the user's own events, but no site and category events. Selecting an event opens its details as in the calendar, where it can be edited or deleted by users who may do so.

The block has the following settings:

1. Default view – Today, This week or Upcoming
2. Views to switch between – any of the three views; the default view is always offered
3. Events per page – 1 to 50, default 5; users page through more events
4. Orientation – vertical (a list) or horizontal (cards side by side)

Several Timetable blocks with different settings can be added to one page.

If you want to learn more about using blocks in Moodle, please see https://docs.moodle.org/en/Blocks.


Documentation
-------------

* [Teacher guide](docs/teacher-guide.md): adding the block, its settings and what learners see.
* [Student guide](docs/student-guide.md): the views, and why an event may be missing.
* [Administrator guide](docs/admin-guide.md): installation, the default Dashboard, capabilities, calendar settings and upgrading from 1.0.
* [Developer guide](docs/developer-guide.md): how the block reads the events, its settings in code, theming and the Timetable admin tool integration.

The guides follow the conventions of the Documentation Center (tool_mdocs), which shows each of them on the pages where its readers use the block.


Theme support
-------------

This plugin is developed and tested on Moodle Core's Boost theme.
It should also work with Boost child themes, including Moodle Core's Classic theme. However, we can't support any other theme than Boost.


Plugin repositories
-------------------

This plugin is published and regularly updated in the Moodle plugins repository:
https://moodle.org/plugins/block_timetable

The latest development version can be found on Github:
https://github.com/bdecentgmbh/moodle-block_timetable


Bug and problem reports / Support requests
------------------------------------------

This plugin is carefully developed and thoroughly tested, but bugs and problems can always appear.

Please report bugs and problems on Github:
https://github.com/bdecentgmbh/moodle-block_timetable/issues

We will do our best to solve your problems, but please note that due to limited resources we can't always provide per-case support.


Feature proposals
-----------------

Please issue feature proposals on Github:
https://github.com/bdecentgmbh/moodle-block_timetable/issues

Please create pull requests on Github:
https://github.com/bdecentgmbh/moodle-block_timetable/pulls

We are always interested to read about your feature proposals or even get a pull request from you, but please accept that we can handle your issues only as feature _proposals_ and not as feature _requests_.


Moodle release support
----------------------

This plugin is maintained for the two most recent major releases of Moodle as well as the most recent LTS release of Moodle. Changes between releases are listed in [CHANGES.md](CHANGES.md).

If you are running a legacy version of Moodle, but want or need to run the latest version of this plugin, you can get the latest version of the plugin, remove the line starting with $plugin->requires from version.php and use this latest plugin version then on your legacy Moodle. However, please note that you will run this setup completely at your own risk. We can't support this approach in any way and there is an undeniable risk for erratic behavior.


Releasing this plugin
---------------------

Releases are published from GitHub Actions (.github/workflows/moodle-release.yml) with Moodle HQ's release workflow:

1. Changes are merged into dev, then dev into main with a merge commit. version.php and the CHANGES.md section "## <release> (<version>)" must be updated.
2. Push a tag "v" followed by $plugin->release, optionally with "-r" and a number, on the merge commit, for example v1.1-r1. The workflow checks the tag, waits for Moodle Plugin CI on that commit, publishes the version with the CHANGES.md section as release notes to the Moodle Marketplace and creates the GitHub Release with the ZIP.
3. Actions › Release to Moodle Marketplace › Run workflow repeats a release or, with "Only check", runs the checks without publishing.

The repository secret MOODLE_MARKETPLACE_TOKEN holds an API token created by a maintainer of the plugin at https://marketplace.moodle.com/account/security.


Translating this plugin
-----------------------

This Moodle plugin is shipped with an english language pack only. All translations into other languages must be managed through AMOS (https://lang.moodle.org) by what they will become part of Moodle's official language pack.


Copyright
---------

bdecent gmbh
bdecent.de
