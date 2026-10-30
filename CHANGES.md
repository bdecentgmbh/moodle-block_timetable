# Changes

## 1.1 (2026102900)

- Moodle 4.5 to 5.3; Moodle 4.5 is now the minimum.
- Security: switching views, days or pages loaded the events of any course given in the request, and in a
  course every user saw the events of all its groups. The block now loads them through Moodle's fragment API,
  checks the block, `moodle/block:view` and the user's access to the course with an active enrolment, and shows
  group events to the members of the group only, as the calendar does.
- Fixed: in "This week", selecting a day showed today's events in languages other than English (#14, #6), and
  today was not highlighted in such languages and in right-to-left languages (#4, #5).
- Fixed: days followed the server's timezone instead of the user's, so events could show on the wrong day (#3).
- Fixed: the week starts on the user's "First day of week", or the site's "Start of week" (#1).
- Fixed: names with an ampersand showed as `&amp;` (#7, #10; thanks to guillogo for #8).
- Fixed: editing an event from its details failed with "invalidparameter: categoryid" (#9). After an event is
  added, edited or deleted, the block reloads its events.
- "Previous events" and "Next events" for paging, up to 200 events (#2).
- Views and days are buttons that work with the keyboard and screen readers, and on Bootstrap 5.
- Settings: proper labels and help (thanks to jrangelardila for #12), the views to switch between as
  checkboxes, events per page from 1 to 50. Blocks whose settings were never saved offer all views.
- Events that have ended are dimmed; events that are still running are not.
- With the Timetable admin tool (tool_timetable), the settings offer its visual timeline and completion visuals.
- Documentation for teachers, students, administrators and developers.
- PHPUnit tests, Behat features and CI for Moodle 4.5 to 5.3, and a release workflow for the Moodle
  Marketplace.

## 1.0 (2025032100)

- Moodle 4.5 compatibility (tag v1.0-r2).

## 1.0 (2021102800)

- First release, for Moodle 3.9 to 3.11.
