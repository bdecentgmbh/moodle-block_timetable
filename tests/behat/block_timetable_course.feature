@block @block_timetable @javascript
Feature: Timetable block used in a course
  In order to be kept informed
  As a user
  I see a feed of relevant events in my course

  Background:
    Given the following "courses" exist:
        | fullname | shortname | category | groupmode |
        | Course 1 | C1 | 0 | 1 |
        | Course 2 | C2 | 0 | 1 |
    And the following "users" exist:
        | username | firstname | lastname | email |
        | teacher1 | Teacher | 1 | teacher1@example.com |
        | student1 | Student | 1 | student1@example.com |
    And the following "course enrolments" exist:
        | user | course | role |
        | teacher1 | C1 | editingteacher |
        | student1 | C1 | student |
        | teacher1 | C2 | editingteacher |
        | student1 | C2 | student |
    And the following "activities" exist:
      | activity   | name                     | intro                         | course | section | assignsubmission_onlinetext_enabled | assignsubmission_file_enabled |
      | assign     | Test assignment name 1   | Submit your online text       | C1     | 1       |  1                                  | 0                             |
      | assign     | Test assignment name 2   | Submit your online text       | C2     | 1       |  1                                  | 0                             |
    And I log in as "admin"
    And I create a calendar event with form data:
        | id_eventtype | Site |
        | id_name | Course Event |
    And I log out
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add the "Timetable" block
    And I am on "Course 2" course homepage
    And I add the "Timetable" block
    And I log out

  Scenario: Timetable shows current course events
    When I log in as "student1"
    And I am on "Course 1" course homepage
    # Confirm the submission event is visible.
    And I should not see "Course Event" in the "Timetable" "block"
    And I am on "Course 2" course homepage
    # Confirm the submission event is visible.
    And I should not see "Course Event" in the "Timetable" "block"
    And I log out
