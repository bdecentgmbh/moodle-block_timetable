@block @block_timetable
Feature: Timetable block
  In order to plan my day and week
  As a user
  I see the calendar events of today, this week or the upcoming days in a Timetable block

  Background:
    Given the following config values are set as admin:
      | calendar_startwday | 1 |
    And the following "courses" exist:
      | fullname        | shortname |
      | Maths & Physics | C1        |
      | Chemistry       | C2        |
    And the following "users" exist:
      | username | firstname | lastname |
      | teacher1 | Teacher   | 1        |
      | student1 | Student   | 1        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the following "events" exist:
      | name              | eventtype | course | timestart                  |
      | Morning lecture   | course    | C1     | ##today 08:00##            |
      | Lab session       | course    | C1     | ##today 14:00##            |
      | Office hours      | course    | C1     | ##today 16:00##            |
      | Monday meeting    | course    | C1     | ##monday this week 10:00## |
      | Sunday reflection | course    | C1     | ##sunday this week 20:00## |
      | Midterm exam      | course    | C1     | ##monday next week 09:00## |
      | Chemistry lab     | course    | C2     | ##today 10:00##            |
    And the following "blocks" exist:
      | blockname | contextlevel | reference | pagetypepattern | defaultregion |
      | timetable | Course       | C1        | course-view-*   | side-pre      |

  @javascript
  Scenario: Students see today's events of the course and switch to a day of this week
    Given I am on the "C1" "Course" page logged in as "student1"
    Then I should see "Morning lecture" in the "Timetable" "block"
    And I should see "Maths & Physics" in the "Timetable" "block"
    And I should not see "Chemistry lab" in the "Timetable" "block"
    And I should not see "Midterm exam" in the "Timetable" "block"
    When I click on "This week" "button" in the "Timetable" "block"
    And I click on "Mon" "button" in the "Timetable" "block"
    Then I should see "Monday meeting" in the "Timetable" "block"
    And I should not see "Sunday reflection" in the "Timetable" "block"
    When I click on "Sun" "button" in the "Timetable" "block"
    Then I should see "Sunday reflection" in the "Timetable" "block"
    And I should not see "Monday meeting" in the "Timetable" "block"
    When I click on "Upcoming" "button" in the "Timetable" "block"
    Then I should see "Midterm exam" in the "Timetable" "block"
    And I should not see "Monday meeting" in the "Timetable" "block"

  @javascript
  Scenario: Teachers choose the views, the default view and the events per page
    Given I am on the "C1" "Course" page logged in as "teacher1"
    And I turn editing mode on
    And I configure the "Timetable" block
    And I set the following fields to these values:
      | Default view    | Today |
      | This week       | 0     |
      | Events per page | 2     |
    And I press "Save changes"
    And I turn editing mode off
    Then "Upcoming" "button" should exist in the "Timetable" "block"
    And "This week" "button" should not exist in the "Timetable" "block"
    And I should see "Morning lecture" in the "Timetable" "block"
    And I should see "Lab session" in the "Timetable" "block"
    And I should not see "Office hours" in the "Timetable" "block"
    When I click on "Next events" "button" in the "Timetable" "block"
    Then I should see "Office hours" in the "Timetable" "block"
    And I should not see "Morning lecture" in the "Timetable" "block"
    When I click on "Previous events" "button" in the "Timetable" "block"
    Then I should see "Morning lecture" in the "Timetable" "block"

  Scenario: Events per page must be between 1 and 50
    Given I am on the "C1" "Course" page logged in as "teacher1"
    And I turn editing mode on
    And I configure the "Timetable" block
    When I set the field "Events per page" to "51"
    And I press "Save changes"
    Then I should see "Enter a number from 1 to 50."

  @javascript
  Scenario: Clicking an event shows its details from the calendar
    Given I am on the "C1" "Course" page logged in as "student1"
    When I click on "Morning lecture" "link" in the "Timetable" "block"
    Then "Morning lecture" "dialogue" should be visible
    And I should see "Course event" in the "Morning lecture" "dialogue"

  @javascript
  Scenario: Teachers edit an event from its details and the block shows the change
    Given I am on the "C1" "Course" page logged in as "teacher1"
    When I click on "Morning lecture" "link" in the "Timetable" "block"
    And I click on "Edit" "button" in the "Morning lecture" "dialogue"
    And I set the field "Event title" to "Morning seminar"
    And I press "Save"
    Then I should see "Morning seminar" in the "Timetable" "block"
    And I should not see "Morning lecture" in the "Timetable" "block"

  @javascript
  Scenario: On the dashboard the block shows the events of all the user's courses
    Given the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | C2     | student |
    And I log in as "student1"
    And I turn editing mode on
    And I add the "Timetable" block
    Then I should see "Morning lecture" in the "Timetable" "block"
    And I should see "Chemistry lab" in the "Timetable" "block"
