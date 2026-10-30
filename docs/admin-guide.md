---
pagetypes:
  - admin-blocks
  - my-indexsys
---

# Timetable — Administrator guide

*Timetable* (`block_timetable`) shows the calendar events of today, this week or the upcoming days on the
Dashboard and in courses. How teachers set it up is described in the [Teacher guide](teacher-guide.md).

## Requirements

- Moodle 4.5 or later; supported on Moodle 4.5 up to 5.3.
- No other plugins. The block reads the events of Moodle's calendar.

## Installation

1. Install it from the Moodle Marketplace under *Site administration ▸ Plugins ▸ Install plugins*, or unpack
   the `timetable` folder of the ZIP into `blocks/` of your Moodle code (`public/blocks/` on Moodle 5.1 and
   later).
2. Run the upgrade under *Site administration ▸ Notifications*.

The block has no site settings.

## Adding it to every Dashboard

1. Go to *Site administration ▸ Appearance ▸ Default Dashboard page*, turn **Edit mode** on and add the
   *Timetable* block.
2. Users who have not opened their Dashboard yet get the block. To give it to everybody else, select
   **Reset Dashboard for all users**; this also removes the blocks users added themselves.

## Capabilities

| Capability | Default |
| --- | --- |
| `block/timetable:addinstance` — add the block to course and other pages | Editing teacher, Manager |
| `block/timetable:myaddinstance` — add the block to one's own Dashboard | Authenticated user |

The events follow the calendar's own rules: users see the events they can see in the calendar, group events
of their groups (all groups with `moodle/site:accessallgroups` and `moodle/calendar:manageentries`), and
no events of hidden activities.

## Calendar settings that matter

- *Site administration ▸ Appearance ▸ Calendar ▸ Start of week* (`calendar_startwday`) and *Upcoming events
  look-ahead* (`calendar_lookahead`) are the defaults for the users' own calendar preferences, which the
  *This week* and *Upcoming* views follow.
- *Admin sees all* (`calendar_adminseesall`) works as in the calendar.

## Privacy

The block stores no personal data. It shows calendar events, which belong to the calendar.

## Timetable admin tool

When the *Timetable* admin tool (`tool_timetable`) is installed, the block settings also offer its visual
timeline and completion visuals, and a setting to hide the events list. Blocks saved before the tool was
installed keep showing their events.

## Upgrading from 1.0

Version 1.1 requires Moodle 4.5 and keeps the settings of existing blocks. Changes you may notice:

- The *This week* view works in every language and timezone, and starts the week on the users' *First day of
  week*.
- Blocks whose settings were never saved offer all three views.
- *Events per page* takes 1 to 50; larger numbers are shown as 50.
- In a course, group events are only shown to the members of the group, as in the calendar.
- Course names with an ampersand (&) show correctly.
