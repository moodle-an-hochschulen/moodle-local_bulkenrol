@local @local_bulkenrol @local_bulkenrol_users
Feature: Using the local_bulkenrol plugin for user enrolments
  In order to bulk enrol users into the course
  As user with the appropriate rights
  I need to be able to use the plugin local_bulkenrol

  Background:
    Given the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1        | topics |
    And the following "users" exist:
      | username  | firstname | lastname | email                | idnumber |
      | teacher1  | Teacher   | 1        | teacher1@example.com | 1 |
      | student1  | Student   | 1        | student1@example.com | 2 |
      | student2  | Student   | 2        | student2@example.com | 3 |
      | student3  | Student   | 3        | student3@example.com | 4 |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And the following config values are set as admin:
      | config      | value  | plugin          |
      | enrolplugin | manual | local_bulkenrol |
      | fieldoptions | u_email,u_idnumber,u_username | local_bulkenrol |
    Given I log in as "admin"
    And I navigate to "Plugins > Enrolments > User bulk enrolment" in site administration
    And I set the following fields to these values:
      | Role | Student |
    And I press "Save changes"
    And I set the following system permissions of "Teacher" role:
      | capability                 | permission |
      | local/bulkenrol:enrolusers | Allow      |
    And I log out

  Scenario: Bulk enrol students into the course who are not enrolled yet with authentication method self
    Given the following config values are set as admin:
      | config      | value | plugin          |
      | enrolplugin | self  | local_bulkenrol |
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Users > User bulk enrolment" in current page administration
    And I set the field "List of users identified by your chosen field" to multiline:
      """
      student1@example.com
      student2@example.com
      student3@example.com
      """
    And I click on "Enrol users" "button"
    Then the following should exist in the "localbulkenrol_enrolusers" table:
      | Email address        | First name | Surname | User enrolment        |
      | student1@example.com | Student    | 1       | User will be enrolled |
      | student2@example.com | Student    | 2       | User will be enrolled |
      | student3@example.com | Student    | 3       | User will be enrolled |
    And the following should exist in the "localbulkenrol_enrolinfo" table:
      | Enrolment method  | Assigned role |
      | Self enrolment    | Student       |
    And I click on "Enrol users" "button"
    Then the following should exist in the "participants" table:
      | Email address        | First name | Surname | Roles   |
      | student1@example.com | Student    | 1       | Student |
      | student2@example.com | Student    | 2       | Student |
      | student3@example.com | Student    | 3       | Student |
    When I click on "[data-enrolinstancename='Self enrolment (Student)'] a[data-action=showdetails]" "css_element" in the "Student 1" "table_row"
    Then I should see "Self enrolment (Student)"

  Scenario: Bulk enrol students into the course who are not enrolled yet with authentication method manual
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Users > User bulk enrolment" in current page administration
    And I set the field "List of users identified by your chosen field" to multiline:
      """
      student1@example.com
      student2@example.com
      student3@example.com
      """
    And I click on "Enrol users" "button"
    Then the following should exist in the "localbulkenrol_enrolusers" table:
      | Email address        | First name | Surname | User enrolment        |
      | student1@example.com | Student    | 1       | User will be enrolled |
      | student2@example.com | Student    | 2       | User will be enrolled |
      | student3@example.com | Student    | 3       | User will be enrolled |
    And the following should exist in the "localbulkenrol_enrolinfo" table:
      | Enrolment method  | Assigned role |
      | Manual enrolments | Student       |
    And I click on "Enrol users" "button"
    Then the following should exist in the "participants" table:
      | Email address        | First name | Surname | Roles   |
      | student1@example.com | Student    | 1       | Student |
      | student2@example.com | Student    | 2       | Student |
      | student3@example.com | Student    | 3       | Student |
    When I click on "[data-enrolinstancename='Manual enrolments'] a[data-action=showdetails]" "css_element" in the "Student 1" "table_row"
    Then I should see "Manual enrolments"

  Scenario: Bulk enrol users into the course who are not enrolled yet with role teacher
    Given I log in as "admin"
    And I navigate to "Plugins > Enrolments > User bulk enrolment" in site administration
    And I set the following fields to these values:
      | Role | Teacher |
    And I press "Save changes"
    And I log out
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Users > User bulk enrolment" in current page administration
    And I set the field "List of users identified by your chosen field" to multiline:
      """
      student1@example.com
      student2@example.com
      student3@example.com
      """
    And I click on "Enrol users" "button"
    Then the following should exist in the "localbulkenrol_enrolusers" table:
      | Email address        | First name | Surname | User enrolment        |
      | student1@example.com | Student    | 1       | User will be enrolled |
      | student2@example.com | Student    | 2       | User will be enrolled |
      | student3@example.com | Student    | 3       | User will be enrolled |
    And the following should exist in the "localbulkenrol_enrolinfo" table:
      | Enrolment method  | Assigned role |
      | Manual enrolments | Teacher       |
    And I click on "Enrol users" "button"
    Then the following should exist in the "participants" table:
      | Email address        | First name | Surname | Roles   |
      | student1@example.com | Student    | 1       | Teacher |
      | student2@example.com | Student    | 2       | Teacher |
      | student3@example.com | Student    | 3       | Teacher |
    When I click on "[data-enrolinstancename='Manual enrolments'] a[data-action=showdetails]" "css_element" in the "Student 1" "table_row"
    Then I should see "Manual enrolments"

  Scenario: Bulk enrol users into the course by their ID
    Given I log in as "admin"
    And I navigate to "Plugins > Enrolments > User bulk enrolment" in site administration
    And I set the following fields to these values:
      | Fieldoptions | idnumber,email,username |
    And I press "Save changes"
    And I log out
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Users > User bulk enrolment" in current page administration
    Then the "dbfield" select box should contain "email"
    And the "dbfield" select box should contain "idnumber"
    And the "dbfield" select box should contain "username"
    And I set the field "List of users identified by your chosen field" to multiline:
      """
      2
      3
      4
      """
    And I click on "Enrol users" "button"
    Then the following should exist in the "localbulkenrol_enrolusers" table:
      | idnumber        | First name | Surname | User enrolment        |
      | 1 | Student    | 1       | User will be enrolled |
      | 2 | Student    | 2       | User will be enrolled |
      | 3 | Student    | 3       | User will be enrolled |
    And the following should exist in the "localbulkenrol_enrolinfo" table:
      | Enrolment method  | Assigned role |
      | Manual enrolments | Student       |
    And I click on "Enrol users" "button"
    Then the following should exist in the "participants" table:
      | Email address        | First name | Surname | Roles   |
      | student1@example.com | Student    | 1       | Student |
      | student2@example.com | Student    | 2       | Student |
      | student3@example.com | Student    | 3       | Student |
    When I click on "[data-enrolinstancename='Manual enrolments'] a[data-action=showdetails]" "css_element" in the "Student 1" "table_row"
    Then I should see "Manual enrolments"

  Scenario: Bulk enrol students when there is only a single datafield option. It should automatically change the helptext.
    Given I log in as "admin"
    And I navigate to "Plugins > Enrolments > User bulk enrolment" in site administration
    And I set the following fields to these values:
      | Fieldoptions | email |
    And I press "Save changes"
    And I log out
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Users > User bulk enrolment" in current page administration
    And I set the field "List of users identified by their email" to multiline:
      """
      student1@example.com
      student2@example.com
      student3@example.com
      """
    And I click on "Enrol users" "button"
    Then the following should exist in the "localbulkenrol_enrolusers" table:
      | idnumber        | First name | Surname | User enrolment        |
      | 1 | Student    | 1       | User will be enrolled |
      | 2 | Student    | 2       | User will be enrolled |
      | 3 | Student    | 3       | User will be enrolled |
    And the following should exist in the "localbulkenrol_enrolinfo" table:
      | Enrolment method  | Assigned role |
      | Manual enrolments | Student       |
    And I click on "Enrol users" "button"
    Then the following should exist in the "participants" table:
      | Email address        | First name | Surname | Roles   |
      | student1@example.com | Student    | 1       | Student |
      | student2@example.com | Student    | 2       | Student |
      | student3@example.com | Student    | 3       | Student |
    When I click on "[data-enrolinstancename='Manual enrolments'] a[data-action=showdetails]" "css_element" in the "Student 1" "table_row"
    Then I should see "Manual enrolments"

  Scenario: Bulk enrol students into the course with students already enrolled
    Given the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | C1     | student |
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Users > User bulk enrolment" in current page administration
    And I set the field "List of users identified by your chosen field" to multiline:
      """
      student1@example.com
      student2@example.com
      student3@example.com
      """
    And I click on "Enrol users" "button"
    Then the following should exist in the "localbulkenrol_enrolusers" table:
      | Email address        | First name | Surname | User enrolment           |
      | student1@example.com | Student    | 1       | User is already enrolled |
      | student2@example.com | Student    | 2       | User will be enrolled    |
      | student3@example.com | Student    | 3       | User will be enrolled    |
    And I click on "Enrol users" "button"
    Then the following should exist in the "participants" table:
      | Email address        | First name | Surname | Roles   |
      | student1@example.com | Student    | 1       | Student |
      | student2@example.com | Student    | 2       | Student |
      | student3@example.com | Student    | 3       | Student |

  Scenario: Respect existing self enrolments during bulk enrol
    Given I log in as "admin"
    And I am on "Course 1" course homepage
    And I add "Self enrolment" enrolment method with:
      | Custom instance name | Self enrolment |
    And I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I press "Enrol me"
    And I should see "Topic 1"
    And I should not see "Enrol me in this course"
    And I log out
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Users > User bulk enrolment" in current page administration
    And I set the field "List of users identified by your chosen field" to multiline:
      """
      student1@example.com
      """
    And I click on "Enrol users" "button"
    And the following should exist in the "localbulkenrol_enrolusers" table:
      | Email address        | First name | Surname | User enrolment           |
      | student1@example.com | Student    | 1       | User is already enrolled |
    And I click on "Enrol users" "button"
    And the following should exist in the "participants" table:
      | Email address        | First name | Surname | Roles   |
      | student1@example.com | Student    | 1       | Student |
    Then "[data-enrolinstancename='Manual enrolments'] a[data-action=showdetails]" "css_element" should not exist in the "Student 1" "table_row"
    And "[data-enrolinstancename='Self enrolment'] a[data-action=showdetails]" "css_element" should exist in the "Student 1" "table_row"

  Scenario: Try to bulk enrol a student into the course that is not existent in the system.
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Users > User bulk enrolment" in current page administration
    And I set the field "List of users identified by your chosen field" to multiline:
      """
      student4@example.com
      """
    And I click on "Enrol users" "button"
    Then I should see "No existing Moodle user account student4@example.com was found."

  Scenario: Try to bulk enrol a list of invalid users.
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Users > User bulk enrolment" in current page administration
    And I set the field "List of users identified by your chosen field" to multiline:
      """
      foo
      bar
      """
    And I click on "Enrol users" "button"
    Then I should see " No valid entrys were found in the given list."
    And I should see "Please go back and check your input"
    And "Enrol users" "button" should not exist
