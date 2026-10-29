---
exclude: true
---

# Timetable — Developer guide

For developers who create Timetable blocks in code, theme them or extend them.

## How it works

- `block_timetable::get_content()` renders `\block_timetable\output\main` with the `block_timetable/main`
  template and the footer (`\block_timetable\output\footer`, `block_timetable/footer`).
- `main` reads the events like the calendar does: `calendar_information::create()` gives the user's filters for
  the course of the page, and `\core_calendar\local\api::get_events()` returns the events of the view's time
  span. In a course (not the site), only that course's events are kept: no site and category events. The
  calendar's `calendar_upcoming_exporter` formats them.
- Switching view, day or page calls the fragment `block_timetable_output_fragment_content()` from the AMD
  module `block_timetable/main` with `courseid`, `view`, `time` and `page`. The fragment API validates the block
  context; the callback checks that it is a Timetable block, `moodle/block:view` in the block context and
  `can_access_course()` for the course. It renders `block_timetable/content`.

## Settings

The block instance's `configdata` holds:

| Key | Value |
| --- | --- |
| `timetable` | Default view: `today`, `thisweek` or `nextxday` (Upcoming) |
| `checkboxtoday`, `checkboxthisweek`, `checkboxnextxday` | `1` when the view is offered. If `timetable` is not set, all views are offered. |
| `limit` | Events per page, 1 to 50 (`main::MAX_LIMIT`); empty or 0 is 5 |
| `view` | Orientation: `vertical` or `horizontal` |

In tests, the data generator takes these keys as fields of the record:

```php
$this->getDataGenerator()->create_block('timetable', [
    'parentcontextid' => context_course::instance($course->id)->id,
    'pagetypepattern' => 'course-view-*',
    'timetable' => 'thisweek',
    'checkboxtoday' => 1,
    'limit' => 10,
]);
```

In Behat, `the following "blocks" exist` creates blocks with the default settings.

## Theming

Each event has the class `calendar_event_<type>` (`course`, `group`, `user`, `site`, `category`, `other`),
and `ended` once it has ended; the events container is `.timetable.vertical` or `.timetable.horizontal`. The
templates `block_timetable/content` and `block_timetable/event` can be overridden by themes.

## Timetable admin tool

When `\tool_timetable\blocktimetable` exists, the block calls it instead of rendering its own content:

- `blocktimetable_form_fields(MoodleQuickForm $mform)` adds the tool's settings to the block settings form.
- `timetable_content($config, $pagecontext, $instanceid, $courseid, $renderer, $renderable)` returns
  `['text' => ..., 'footer' => ...]`. `$renderable` is the block's `main`; the tool renders it and
  `new \block_timetable\output\footer($courseid)` when `$config->showevents` is set. The block sets
  `showevents` to 1 when the setting was never saved.
