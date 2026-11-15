moodle-local_bulkenrol
======================

[![Moodle Plugin CI](https://github.com/moodle-an-hochschulen/moodle-local_bulkenrol/actions/workflows/moodle-plugin-ci.yml/badge.svg?branch=MOODLE_405_STABLE)](https://github.com/moodle-an-hochschulen/moodle-local_bulkenrol/actions?query=workflow%3A%22Moodle+Plugin+CI%22+branch%3AMOODLE_405_STABLE)

Moodle plugin which provides the possibility to bulk enrol a list of users who are identified by their e-mail adresses into a course.


Requirements
------------

This plugin requires Moodle 4.5+


Motivation for this plugin
--------------------------

In some organizations or some teaching scenarios, manually enrolling students into a course may be the preferred way. However, enrolling one user at a time into a course can be daunting for teachers. On the other hand, teachers may not be allowed to use (or be able to understand) the Moodle core upload CSV functionality.

To ease the life of teachers, there is the need for a bulk enrolment tool. There are already plugins out there which provide this functionality, so this is just another one. The goal of this bulk enrolment implementation is not to fulfil everybody's needs, but to do one thing and to do this well.

So, the key features of this plugin are:
1. to let teachers submit a line-separated list of email addresses to enrol them into or remove them from a course,
2. to let teachers submit this list to a textarea within their course instead of requiring them to create and upload a CSV file first,
3. to let teachers change the group assignments within the course.


Installation
------------

Install the plugin like any other plugin to folder
/local/bulkenrol

See http://docs.moodle.org/en/Installing_plugins for details on installing Moodle plugins


Usage & Settings
----------------

After installing the plugin, it does not do anything to Moodle yet.

To configure the plugin and its behaviour, please visit:
Site administration -> Plugins -> Enrolments -> User bulk enrolment

There, you find two settings:

### 1. Enrolment plugin
The enrolment method to be used to bulk enrol the users. If the configured enrolment method is not active / added in the course when the users are bulk-enrolled, it is automatically added / activated.

Please note: If the enrolment method is added to a course automatically, it is created with the default settings which are configured in the settings of the enrolment method itself, just like Moodle does it when a new course is created. However, the added enrolment method is always enabled, even if the enrolment method is configured to be disabled by default. This is especially important for the enrolment method "Self enrolment": If its default settings allow new self enrolments and do not require an enrolment key, every logged-in user will be able to enrol themselves into a course as soon as the enrolment method was added to the course by a bulk enrolment. Please stick to the "Manual enrolments" method unless you really need another enrolment method. Also check the method's default settings before you use it for bulk enrolments.

Please note as well: From the enrolment methods which are shipped with Moodle core, only "Manual enrolments" and "Self enrolment" are offered here as the other ones are not suitable for bulk-enrolling users at all. Enrolment methods which are provided by third-party plugins are offered here as well, but they are not tested together with this plugin. Before you use a third-party enrolment method for bulk enrolments on a production site, please test thoroughly if the enrolment method is added to the courses with the expected settings and if the users are enrolled properly.

### 2. Role
The role to be used to bulk enrol the users.

### 3. Navigation node placement
The location where the navigation node for this functionality will be added within a course.


Capabilities
------------
This plugin also introduces a new capability:

### local/bulkenrol:enrolusers

By default, Moodle users are _not_ allowed to use the functionality provided by this plugin. As administrator, you can selectively grant users the ability to use this functionality by adding the local/bulkenrol:enrolusers capability to an appropriate Moodle role.

Please note that this single capability covers everything the plugin does and that the plugin does not check any Moodle core capabilities in addition. See the section "Scope of the local/bulkenrol:enrolusers capability" below for details.


Scheduled Tasks
---------------

This plugin does not add any additional scheduled tasks.


How this plugin works / Pitfalls
--------------------------------

Teachers (rather users who have been granted the capability which is described in the "Capabilities" section above) will find an additional "User bulk enrolment" menu item within the jump menu on the course's participants page.

### User enrolments

To enrol existing Moodle users into the course, the teacher will then have to add a list of e-mail adresses to the form on this page, one user / e-mail adress per line.

Example:
```
alice@example.com
bob@example.com
```

### Group assignments

Optionally, the teacher will be able to create groups and add the enrolled users to the groups. All he has to do is to add a heading line with a hash sign and the group's name, separating the list of users.

Example:
```
# Group 1
alice@example.com
bob@example.com
# Group 2
carol@example.com
dave@example.com
```

### User unenrolments

The opposite can be done as well, removing students from a group or entirely remove them from the course. In this case, the line with the email address must be prefixed with an exclamation mark. When there is a group name in a previous line, the students are removed from that group only. When there is not group in any previous line, the enrolment is reverted and the student removed from the course.

There is a possibility to have several email addresses at one line.

Example:
```
!alice@example.com
!bob@example.com
# Group 2
!carol@example.com
# Group 3
carol@example.com
```

This would remove Alice and Bob from the course entirely. Carol will remain in the course but will be removed from group 2 and added to group 3.

### Enrolment methods which do not allow unenrolments

Please note that a user is only unenrolled from the enrolment methods which allow to unenrol users manually, just like on the course's participants page. Enrolment methods which synchronise their enrolments from another source, for example "Cohort sync" or "Course meta link", do not allow this as long as the enrolment is active. If a user is enrolled with such an enrolment method, this enrolment is left untouched and the user will remain enrolled in the course. The teacher is informed about this with a hint on the confirmation page before the bulk enrolment is executed.

### Multiple users per line

While building the user list by placing a single user per line is the recommended default, the plugin is also able to recognize multiple users per line if they are separated with commas or spaces.

Example:
```
alice@example.com bob@example.com
!carol@example.com, dave@example.com
```

This would enrol Alice and Bob into the course as well as unenrol Carol and Dave from the course.

As this syntax is likely more complicated than the normal one, it is just documented here and not on the enrolment page itself.

### Possible disclosure of user accounts by e-mail address

To be able to enrol users, the plugin has to look up each submitted e-mail address among all user accounts of the whole Moodle site. On the confirmation page, it then reports if no account or more than one account was found for an e-mail address and it shows the first name and last name of the user account which was found. This is needed so that the teacher can check if the right users are going to be enrolled before confirming the enrolment.

As a consequence, every user who has been granted the local/bulkenrol:enrolusers capability can find out if a Moodle user account exists for any given e-mail address and which name belongs to this account, even if this user is not related to the teacher's courses at all.

With Moodle's default settings, this does not disclose more than Moodle core already does: Editing teachers are allowed to enrol users manually and Moodle core's manual enrolment dialog searches all users of the site, including their e-mail addresses, and shows their names. However, if you have hardened your Moodle site in this regard, for example by removing the e-mail address from the "Show user identity" (showuseridentity) setting or by removing the moodle/site:viewuseridentity capability from teachers, this plugin will still resolve e-mail addresses to user names. Please keep this in mind when you grant the local/bulkenrol:enrolusers capability to other roles than the manager role. To remind you of this, the local/bulkenrol:enrolusers capability is flagged with the "personal information" risk (RISK_PERSONAL) in Moodle's role definitions.

### Scope of the local/bulkenrol:enrolusers capability

The local/bulkenrol:enrolusers capability is the only capability which is checked by this plugin. This is done on purpose so that you can allow users to use the bulk enrolment without having to grant them the general enrolment and group management capabilities of Moodle core. However, this also means that a user who has been granted this capability in a course is able to do all of the following things in this course, even if they do not hold the Moodle core capabilities which normally control these actions:

* Enrol any user of the Moodle site into the course with the role which is configured in the plugin settings (normally controlled by enrol/xxx:enrol).
* Enable the enrolment instance of the enrolment method which is configured in the plugin settings, or add it to the course if it does not exist yet (normally controlled by enrol/xxx:config).
* Create course groups, add users to course groups and remove users from course groups (normally controlled by moodle/course:managegroups).
* Unenrol users from the course (normally controlled by enrol/xxx:unenrol). The users are unenrolled from all enrolment instances of the course which allow to unenrol users manually, not only from the enrolment method which is configured in the plugin settings. Enrolment instances which do not allow to unenrol users manually (for example active "Cohort sync" enrolments) are left untouched.

When a user is unenrolled from a course, Moodle removes their role assignments, their group memberships and their grades in this course. Please keep this in mind when you grant the local/bulkenrol:enrolusers capability to other roles than the manager role. To remind you of this, the local/bulkenrol:enrolusers capability is flagged with the "data loss" risk (RISK_DATALOSS) in Moodle's role definitions.


Limitations
-----------

This plugin currently only accepts a list of e-mail adresses to be enrolled into a course. It does especially not accept lists of user names, matriculation IDs or something else.

Additionally, this plugin only enrols users who already exist in Moodle. It won't create Moodle user accounts on-the-fly.


Theme support
-------------

This plugin is developed and tested on Moodle Core's Boost theme.
It should also work with Boost child themes, including Moodle Core's Classic theme. However, we can't support any other theme than Boost.


Plugin repositories
-------------------

This plugin is published and regularly updated in the Moodle plugins repository:
http://moodle.org/plugins/view/local_bulkenrol

The latest development version can be found on Github:
https://github.com/moodle-an-hochschulen/moodle-local_bulkenrol


Bug and problem reports / Support requests
------------------------------------------

This plugin is carefully developed and thoroughly tested, but bugs and problems can always appear.

Please report bugs and problems on Github:
https://github.com/moodle-an-hochschulen/moodle-local_bulkenrol/issues

We will do our best to solve your problems, but please note that due to limited resources we can't always provide per-case support.


Feature proposals
-----------------

Due to limited resources, the functionality of this plugin is primarily implemented for our own local needs and published as-is to the community. We are aware that members of the community will have other needs and would love to see them solved by this plugin.

Please issue feature proposals on Github:
https://github.com/moodle-an-hochschulen/moodle-local_bulkenrol/issues

Please create pull requests on Github:
https://github.com/moodle-an-hochschulen/moodle-local_bulkenrol/pulls

We are always interested to read about your feature proposals or even get a pull request from you, but please accept that we can handle your issues only as feature _proposals_ and not as feature _requests_.


Moodle release support
----------------------

Due to limited resources, this plugin is only maintained for the most recent major release of Moodle as well as the most recent LTS release of Moodle. Bugfixes are backported to the LTS release. However, new features and improvements are not necessarily backported to the LTS release.

Apart from these maintained releases, previous versions of this plugin which work in legacy major releases of Moodle are still available as-is without any further updates in the Moodle Plugins repository.

There may be several weeks after a new major release of Moodle has been published until we can do a compatibility check and fix problems if necessary. If you encounter problems with a new major release of Moodle - or can confirm that this plugin still works with a new major release - please let us know on Github.

If you are running a legacy version of Moodle, but want or need to run the latest version of this plugin, you can get the latest version of the plugin, remove the line starting with $plugin->requires from version.php and use this latest plugin version then on your legacy Moodle. However, please note that you will run this setup completely at your own risk. We can't support this approach in any way and there is an undeniable risk for erratic behavior.


Translating this plugin
-----------------------

This Moodle plugin is shipped with an english language pack only. All translations into other languages must be managed through AMOS (https://lang.moodle.org) by what they will become part of Moodle's official language pack.

As the plugin creator, we manage the translation into german for our own local needs on AMOS. Please contribute your translation into all other languages in AMOS where they will be reviewed by the official language pack maintainers for Moodle.


Right-to-left support
---------------------

This plugin has not been tested with Moodle's support for right-to-left (RTL) languages.
If you want to use this plugin with a RTL language and it doesn't work as-is, you are free to send us a pull request on Github with modifications.


Maintainers
-----------

The plugin is maintained by\
Moodle an Hochschulen e.V.


Copyright
---------

The copyright of this plugin is held by\
Moodle an Hochschulen e.V.

Individual copyrights of individual developers are tracked in PHPDoc comments and Git commits.


Initial copyright
-----------------

This plugin was initially built by\
Soon Systems GmbH\
www.soon-systems.de

on behalf of\
Ulm University\
Communication and Information Centre (kiz)

and maintained and published by\
Ulm University\
Communication and Information Centre (kiz)\
Alexander Bias

It was contributed to the Moodle an Hochschulen e.V. plugin catalogue in 2022.
