<?php
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
 * Strings for component 'local_coursefisher', language 'en'
 *
 * @package   local_coursefisher
 * @copyright 2014 and above Roberto Pinna, Diego Fantoma and Angelo Calò
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

























$string['actions'] = 'Actions';
$string['addcourse'] = 'Add course';
$string['addcoursegroup'] = 'Add course group';
$string['addcourses'] = 'Add courses';
$string['addsinglecourse'] = 'Add single course';
$string['autocreation'] = 'Automatic course creation';
$string['availablecourses'] = 'Addable courses';
$string['backend'] = 'Backend';
$string['backendconfigerror'] = 'Backend configuration error';
$string['backendemptydata'] = 'Backend returns no data, please check configuration';
$string['backendfailure'] = 'Can not connect to courses backend';
$string['backendnotinstalled'] = 'The chosen backend is not installed';
$string['backendnotset'] = 'Backend not set, please choose one and save configuration before test';
$string['backendready'] = 'Backend ready';
$string['backendsettings'] = 'Backend Settings';
$string['backendtestpage'] = 'Backend Test page';
$string['chooseexistsaction'] = 'Some courses in course group already exists. What you would to do with those courses?';
$string['choosenextaction'] = 'What would you do after course creation:';
$string['choosewhatadd'] = 'Choose what would you add:';
$string['configactions'] = 'Enable what the teacher can choose to do after course creation';
$string['configautocreation'] = 'If the choosen backend provide it, this enable cron managed automatic courses creation.';
$string['configbackend'] = 'Backend Type';
$string['configcoursefields'] = '<strong>Note:</strong><br />Fields in "Fields list" can be combined to create string using [%fieldname%] notation.<br />Numeric fields can use [%fieldname+number%] and [%fieldname-number%] notation to modify value. String fields can be trunched at nth character using [%fieldname#n%].<br />Example: "Courses [%department#10%] - [%year%]/[%year+1%]"';
$string['configcoursehelplink'] = 'If defined a link or a button will be shown near Course Fisher tools';
$string['configcoursesummary'] = 'This text will set as course summary. Fields get from backend could be used to customize it.';
$string['configcoursetemplate'] = 'The shortname of a course that will be used as a template.';
$string['configeducationalofferlink'] = 'An URL resource will be added to the general section in each created course. It could be useful to link to a public course presentation page.';
$string['configemailcondition'] = 'A notification will be sent, on course creation, every time this rule will be verified.';
$string['configerrors'] = 'Found some configuration errors';
$string['configfieldlevel'] = 'One level per row. The root category level in the first row. You can use idnumber=>categoryname to assign an idnumber to category';
$string['configfieldlist'] = 'The list of returned field names, one for row in a first match order';
$string['configfieldtest'] = 'Backend configuration test values, one for row. Syntax [field]:value';
$string['configfirstrow'] = 'Useful in file backends, es. list of CSV fields';
$string['configforceonlygroups'] = 'If enabled teachers could create only single courses and defined groups of courses. If a course is member of a group could not be created as standalone course.';
$string['configgrouprule'] = 'This define a comparison rule to match father with child courses. Example: [%father_code%]=[%year%]-[%degree_cod%]-[%course_cod%]';
$string['configlinkedcoursecategory'] = 'Set this to create all child courses in a course category. Example: "Child Courses [%department#10%] - [%year%]/[%year+1%]"';
$string['configlinktype'] = 'Child courses link must be done using the selected method';
$string['configlocator'] = 'Something like file://path or mysql://username:password@host:port/database/table use multiple rows if want to use multiple sources in a first match order';
$string['confignotifycoursecreation'] = 'Send course creation notification messages to these selected users.';
$string['configparameters'] = 'A query or filter get. You can use [%field%] to replace a moodle user field, es. [%uidnumber%]';
$string['configsectionzero'] = 'If defined, new course general section titles will be set. Fields get from backend could be used to customize it.';
$string['configseparator'] = 'Fields separator, used only where necessary (es. csv)';
$string['configsortcoursesby'] = 'How courses should be sorted in categories';
$string['configtitle'] = 'The name of administration block sub tree where users find Course Fisher tools';
$string['configurationtest'] = 'Test Backend Configuration';
$string['coursecontentsettings'] = 'Course content Settings';
$string['coursefisher:addallcourses'] = 'Add all courses got from Course Fisher';
$string['coursefisher:addcourses'] = 'Add own courses got from Course Fisher';
$string['coursefisherwill'] = 'Course Fisher will be';
$string['coursefullname'] = 'Course fullname';
$string['coursegroup'] = 'Course group';
$string['coursehelplink'] = 'Help Page URL';
$string['courseidnumber'] = 'Course Id number';
$string['courselink'] = 'Linked course';
$string['courselinkmessage'] = 'This course is linked to {$a}. Please click the link below';
$string['coursenotfound'] = 'Course not found';
$string['coursenotifyhtml'] = 'Dear Admin,<br />
You need to check a Course Fisher new course<br />
<b>{$a->coursefullname}</b><br /><br />
Course URL: <a href="{$a->courseurl}">{$a->courseurl}</a>';
$string['coursenotifyhtmlcomplete'] = 'Dear Admin,<br />
You need to check a Course Fisher new course<br />
<b>{$a->coursefullname}</b><br /><br />
Course URL: <a href="{$a->courseurl}">{$a->courseurl}</a><br />
Educational Offer Page URL: <a href="{$a->educationalofferurl}">{$a->educationalofferurl}</a>';
$string['coursenotifysubject'] = 'Course Fisher - A new course requires your attention!';
$string['coursenotifytext'] = 'Dear Admin,
You need to check a Course Fisher new course
{$a->coursefullname}

Course URL: {$a->courseurl}';
$string['coursenotifytextcomplete'] = 'Dear Admin,
You need to check a Course Fisher new course
{$a->coursefullname}

Course URL: {$a->courseurl}

Educational Offer Page URL: {$a->educationalofferurl}';
$string['coursesautocreationtask'] = 'Automatic courses creation task';
$string['coursesettings'] = 'Course Settings';
$string['coursesgroupsettings'] = 'Courses group Settings';
$string['courseshortname'] = 'Course shortname';
$string['coursesummary'] = 'Course summary';
$string['coursetemplate'] = 'Template course shortname';
$string['disabled'] = 'Disabled';
$string['edit'] = 'Edit course settings';
$string['educationaloffer'] = 'Educational Offer Page';
$string['educationalofferlink'] = 'Course informations link';
$string['educationaloffermessage'] = 'Here you find all information about this course educational offer';
$string['emailcondition'] = 'Notify if';
$string['enroltocourse'] = 'Enrol as teacher into course';
$string['entercourse'] = 'Enter into course';
$string['execute'] = 'Execute';
$string['existentcourse'] = 'This course was already created';
$string['existentcourses'] = 'Existent courses';
$string['fieldlevel'] = 'Categories level list';
$string['fieldlist'] = 'Fields list';
$string['fieldtest'] = 'Test values';
$string['filter'] = 'Visibility';
$string['firstrow'] = 'Skip first row';
$string['forceonlygroups'] = 'Force only group creation';
$string['generalsettings'] = 'General Settings';
$string['grouprule'] = 'Group rule';
$string['guest'] = 'Connected with Guest enrolment in childer courses';
$string['help'] = 'Help';
$string['hidden'] = 'Hidden';
$string['ifuserprofilefield'] = 'If user profile field';
$string['import'] = 'Import data from an other course';
$string['join'] = 'Join them to course group';
$string['linkedcoursecategory'] = 'Child courses category';
$string['linktype'] = 'Child courses link';
$string['locator'] = 'Locator (URL)';
$string['meta'] = 'Connected with Meta Link enrolment in father course';
$string['nocourseavailable'] = 'Sorry, no available courses';
$string['noguestormeta'] = 'You must enable Guest or Meta Link enrolment plugins to use Course groups creation';
$string['notenabled'] = 'Sorry, you are not enabled to add courses';
$string['notifycoursecreation'] = 'Notify course creation to';
$string['notifysettings'] = 'Notification Settings';
$string['nouserfilterset'] = 'No user filter set';
$string['parameters'] = 'Parameters';
$string['pluginname'] = 'Course Fisher';
$string['privacy:metadata'] = 'The Course Fisher plugin get data from backend, create courses and assign teachers role. It does not store any data itself.';
$string['sectionzero'] = 'Course general section title';
$string['separated'] = 'Keep them separated from course group';
$string['separator'] = 'Separator';
$string['shown'] = 'Shown';
$string['sortcoursesby'] = 'Sort courses by';
$string['subplugintype_coursefisherbackend'] = 'Backend';
$string['subplugintype_coursefisherbackend_plural'] = 'Backends';
$string['title'] = 'Title';
$string['view'] = 'View course';

