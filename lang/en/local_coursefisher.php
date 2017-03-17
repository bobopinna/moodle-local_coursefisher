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

// Default strings.
$string['pluginname'] = 'Course Fisher';
$string['subplugintype_coursefisherbackend'] = 'Backend';
$string['subplugintype_coursefisherbackend_plural'] = 'Backends';

// Accessibility strings.
$string['coursefisher:addallcourses'] = 'Add all courses got from Course Fisher';
$string['coursefisher:addcourses'] = 'Add own courses got from Course Fisher';

// Settings strings.
$string['generalsettings'] = 'General Settings';
$string['title'] = 'Title';
$string['configtitle'] = 'The name of administration block section where users find Course Fisher utilities';
$string['coursehelplink'] = 'Help Page URL';
$string['configcoursehelplink'] = 'If defined a link will be shown in Course Fisher section of Administration block';
$string['actions'] = 'Actions';
$string['configactions'] = 'Enable what the teacher can choose to do after course creation';
$string['autocreation'] = 'Automatic course creation';
$string['configautocreation'] = 'If the choosen backend provide it, this enable cron managed automatic courses creation.';

$string['configurationbackend'] = 'Backend Configuration';
$string['backend'] = 'Backend';
$string['configbackend'] = 'Backend Type';
$string['locator'] = 'Locator (URL)';
$string['configlocator'] = 'Something like file://path or mysql://username:password@host:port/database/table use multiple rows if want to use multiple sources in a first match order';
$string['parameters'] = 'Parameters';
$string['configparameters'] = 'A query or filter get. You can use [%field%] to replace a moodle user field, es. [%uidnumber%]';
$string['separator'] = 'Separator';
$string['configseparator'] = 'Fields separator, used only where necessary (es. csv)';
$string['firstrow'] = 'Skip first row';
$string['configfirstrow'] = 'Useful in file backends, es. list of CSV fields';
$string['fieldlist'] = 'Fields list';
$string['configfieldlist'] = 'The list of returned field names, one for row in a first match order';
$string['configcoursefields'] = '<strong>Note:</strong><br />Fields in "Fields list" can be combined to create string using [%fieldname%] notation.<br />Numeric fields can use [%fieldname+number%] and [%fieldname-number%] notation to modify value. String fields can be trunched at nth character using [%fieldname#n%].<br />Example: "Courses [%department#10%] - [%year%]/[%year+1%]"';
$string['fieldlevel'] = 'Categories level list';
$string['configfieldlevel'] = 'One level per row. The root category level in the first row. You can use idnumber=>categoryname to assign an idnumber to category';
$string['courseidnumber'] = 'Course Id number';
$string['coursefullname'] = 'Course fullname';
$string['courseshortname'] = 'Course shortname';
$string['fieldtest'] = 'Test values';
$string['configfieldtest'] = 'Backend configuration test values, one for row. Syntax [field]:value';
$string['configurationtest'] = 'Test Backend Configuration';

$string['coursesgroup'] = 'Courses group creation';
$string['grouprule'] = 'Group rule';
$string['configgrouprule'] = 'This define a comparison rule to match father with child courses. Example: [%father_code%]=[%year%]-[%degree_cod%]-[%course_cod%]';
$string['forceonlygroups'] = 'Force only group creation';
$string['configforceonlygroups'] = 'If enabled teachers could create only single courses and defined groups of courses. If a course is member of a group could not be created as standalone course.';
$string['linktype'] = 'Child courses link';
$string['configlinktype'] = 'Child courses link must be done using the selected method';
$string['meta'] = 'Connected with Meta Link enrolment in father course';
$string['guest'] = 'Connected with Guest enrolment in childer courses';
$string['noguestormeta'] = 'You must enable Guest or Meta Link enrolment plugins to use Course groups creation';
$string['linkedcoursecategory'] = 'Child courses category';
$string['configlinkedcoursecategory'] = 'Set this to create all child courses in a course category. Example: "Child Courses [%department#10%] - [%year%]/[%year+1%]"';

$string['coursessettings'] = 'Courses custom settings';
$string['coursesummary'] = 'Course summary';
$string['configcoursesummary'] = 'This text will set as course summary. Fields get from backend could be used to customize it.';
$string['sectionzero'] = 'Course general section title';
$string['configsectionzero'] = 'If defined, new course general section titles will be set. Fields get from backend could be used to customize it.';
$string['educationalofferlink'] = 'Course informations link';
$string['configeducationalofferlink'] = 'An URL resource will be added to the general section in each created course. It could be useful to link to a public course presentation page.';
$string['coursetemplate'] = 'Template course shortname';
$string['configcoursetemplate'] = 'The shortname of a course that will be used as a template.';

$string['notifysettings'] = 'Notification Settings';
$string['notifycoursecreation'] = 'Notify course creation to';
$string['confignotifycoursecreation'] = 'Send course creation notification messages to these selected users.';
$string['emailcondition'] = 'Notify if';
$string['configemailcondition'] = 'A notification will be sent, on course creation, every time this rule will be verified.';

$string['filter'] = 'Visibility';
$string['coursefisherwill'] = 'Course Fisher will be';
$string['ifuserprofilefield'] = 'If user profile field';

// Plugin interface strings.
$string['addmoodlecourse'] = 'Add/manage courses';
$string['addcourse'] = 'Add course';
$string['notenabled'] = 'Sorry, you are not enabled to add courses';
$string['nocourseavailable'] = 'Sorry, no available courses';
$string['coursegroup'] = 'Course group';
$string['addcoursegroup'] = 'Add course group';
$string['addsinglecourse'] = 'Add single course';
$string['entercourse'] = 'Enter into course';
$string['enroltocourse'] = 'Enrol as teacher into course';
$string['availablecourses'] = 'Addable courses';
$string['existentcourses'] = 'Existent courses';
$string['backendfailure'] = 'Can not connect to course backend';
$string['configerrors'] = 'Found some configuration errors';
$string['edit'] = 'Edit course settings';
$string['view'] = 'View course';
$string['import'] = 'Import data from an other course';
$string['coursenotfound'] = 'Course not found';
$string['shown'] = 'Shown';
$string['hidden'] = 'Hidden';
$string['nouserfilterset'] = 'No user filter set';
$string['courselink'] = 'Linked course';
$string['courselinkmessage'] = 'This course is linked to {$a}. Please click the link below';
$string['choosewhatadd'] = 'Choose what would you add:';
$string['choosenextaction'] = 'What would you do after course creation:';
$string['execute'] = 'Execute';

$string['chooseexistsaction'] = 'Some courses in course group already exists. What you would to do with those courses?';
$string['join'] = 'Join them to course group';
$string['separated'] = 'Keep them separated from course group';
$string['educationaloffer'] = 'Educational Offer Page';
$string['educationaloffermessage'] = 'Here you find all information about this course educational offer';
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
$string['coursenotifyhtml'] = 'Dear Admin,<br />
You need to check a Course Fisher new course<br />
<b>{$a->coursefullname}</b><br /><br />
Course URL: <a href="{$a->courseurl}">{$a->courseurl}</a>';
$string['coursenotifyhtmlcomplete'] = 'Dear Admin,<br />
You need to check a Course Fisher new course<br />
<b>{$a->coursefullname}</b><br /><br />
Course URL: <a href="{$a->courseurl}">{$a->courseurl}</a><br />
Educational Offer Page URL: <a href="{$a->educationalofferurl}">{$a->educationalofferurl}</a>';
$string['existentcourse'] = 'This course was already created';
