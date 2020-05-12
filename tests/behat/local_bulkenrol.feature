@local @local_bulkenrol
Feature: Using the local_bulkenrol plugin
  In order to bulk enrol users into the course
  As user with the appropriate rights
  I need to be able to use the plugin local_bulkenrol

  Background:
    Given the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1        | topics |
    And the following "users" exist:
      | username  | firstname | lastname | email                |
      | teacher1  | Teacher   | 1        | teacher1@example.com |
      | student1  | Student   | 1        | student1@example.com |
      | student2  | Student   | 2        | student2@example.com |
      | student3  | Student   | 3        | student3@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And the following config values are set as admin:
      | config      | value  | plugin          |
      | enrolplugin | manual | local_bulkenrol |
    Given I log in as "admin"
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
    And I set the field "List of e-mail addresses" to multiline:
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
    And I set the field "List of e-mail addresses" to multiline:
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
    And I click on "Enrol users" "button"
    Then the following should exist in the "participants" table:
      | Email address        | First name | Surname | Roles   |
      | student1@example.com | Student    | 1       | Student |
      | student2@example.com | Student    | 2       | Student |
      | student3@example.com | Student    | 3       | Student |
    When I click on "[data-enrolinstancename='Manual enrolments'] a[data-action=showdetails]" "css_element" in the "Student 1" "table_row"
    Then I should see "Manual enrolments"

  Scenario: Bulk enrol students into the course and into groups
    Given the following "groups" exist:
      | name    | course | idnumber |
      | Group 1 | C1     | CG1      |
      | Group 2 | C1     | CG2      |
      | Group 3 | C1     | CG3      |
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Users > User bulk enrolment" in current page administration
    And I set the field "List of e-mail addresses" to multiline:
      """
      # Group 1
      student1@example.com
      # Group 2
      student2@example.com
      # Group 3
      student3@example.com
      """
    And I click on "Enrol users" "button"
    Then the following should exist in the "localbulkenrol_enrolusers" table:
      | Email address        | First name | Surname | User enrolment        | Group membership              |
      | student1@example.com | Student    | 1       | User will be enrolled | Group 1 (User added to group) |
      | student2@example.com | Student    | 2       | User will be enrolled | Group 2 (User added to group) |
      | student3@example.com | Student    | 3       | User will be enrolled | Group 3 (User added to group) |
    And I click on "Enrol users" "button"
    Then the following should exist in the "participants" table:
      | Email address        | First name | Surname | Roles   | Groups  |
      | student1@example.com | Student    | 1       | Student | Group 1 |
      | student2@example.com | Student    | 2       | Student | Group 2 |
      | student3@example.com | Student    | 3       | Student | Group 3 |

  Scenario: Bulk enrol students into the course with students already enrolled
    Given the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | C1     | student |
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Users > User bulk enrolment" in current page administration
    And I set the field "List of e-mail addresses" to multiline:
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

  Scenario: Bulk enrol students into the course that are not existent in the system.
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Users > User bulk enrolment" in current page administration
    And I set the field "List of e-mail addresses" to multiline:
      """
      student4@example.com
      """
    And I click on "Enrol users" "button"
    Then I should see "No existing Moodle user account with e-mail address student4@example.com."
    When I click on "Enrol users" "button"
    Then I should see "User bulk enrolment successful"

  Scenario: Bulk enrol students into the course with students already enrolled and who only have to be added to groups
    Given the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | C1     | student |
      | student2 | C1     | student |
    And the following "groups" exist:
      | name    | course | idnumber |
      | Group 1 | C1     | CG1      |
      | Group 2 | C1     | CG2      |
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Users > User bulk enrolment" in current page administration
    And I set the field "List of e-mail addresses" to multiline:
      """
      # Group 1
      student1@example.com
      # Group 2
      student2@example.com
      """
    And I click on "Enrol users" "button"
    Then the following should exist in the "localbulkenrol_enrolusers" table:
      | Email address        | First name | Surname | User enrolment           | Group membership              |
      | student1@example.com | Student    | 1       | User is already enrolled | Group 1 (User added to group) |
      | student2@example.com | Student    | 2       | User is already enrolled | Group 2 (User added to group) |
    And I click on "Enrol users" "button"
    Then the following should exist in the "participants" table:
      | Email address        | First name | Surname | Roles   | Groups  |
      | student1@example.com | Student    | 1       | Student | Group 1 |
      | student2@example.com | Student    | 2       | Student | Group 2 |

  Scenario: Bulk enrol students into the course with students already enrolled and who are also a member of the given groups
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | C1     | student |
      | student2 | C1     | student |
    And the following "groups" exist:
      | name    | course | idnumber |
      | Group 1 | C1     | CG1      |
      | Group 2 | C1     | CG2      |
    And the following "group members" exist:
      | group | user     |
      | CG1   | student1 |
      | CG2   | student2 |
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Users > User bulk enrolment" in current page administration
    And I set the field "List of e-mail addresses" to multiline:
      """
      # Group 1
      student1@example.com
      # Group 2
      student2@example.com
      """
    And I click on "Enrol users" "button"
    Then the following should exist in the "localbulkenrol_enrolusers" table:
      | Email address        | First name | Surname | User enrolment           | Group membership              |
      | student1@example.com | Student    | 1       | User is already enrolled | Group 1 (User already member) |
      | student2@example.com | Student    | 2       | User is already enrolled | Group 2 (User already member) |
    And I click on "Enrol users" "button"
    Then the following should exist in the "participants" table:
      | Email address        | First name | Surname | Roles   | Groups  |
      | student1@example.com | Student    | 1       | Student | Group 1 |
      | student2@example.com | Student    | 2       | Student | Group 2 |
