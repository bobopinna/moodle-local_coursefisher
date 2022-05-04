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
 * Settings for the Course Fisher.
 *
 * @package   local_coursefisher
 * @copyright 2014 and above Roberto Pinna, Diego Fantoma, Angelo Calò
 * @copyright 2016 and above Francesco Carbone
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {

    $ADMIN->add('localplugins', new admin_category('coursefisher', new lang_string('pluginname', 'local_coursefisher')));

    $page = new admin_settingpage('local_coursefisher_general', new lang_string('generalsettings', 'local_coursefisher'));

    $page->add(new admin_setting_configtext('local_coursefisher/title',
                new lang_string('title', 'local_coursefisher'),
                new lang_string('configtitle', 'local_coursefisher'),
                new lang_string('pluginname', 'local_coursefisher')));

    $page->add(new admin_setting_configtext('local_coursefisher/course_helplink',
                new lang_string('coursehelplink', 'local_coursefisher'),
                new lang_string('configcoursehelplink', 'local_coursefisher'),
                ''));

    $choices = array();
    $choices['view'] = new lang_string('view', 'local_coursefisher');
    $choices['edit'] = new lang_string('edit', 'local_coursefisher');
    $choices['import'] = new lang_string('import', 'local_coursefisher');
    $defaultchoices = array('view', 'edit', 'import');
    $page->add(new admin_setting_configmultiselect('local_coursefisher/actions',
                new lang_string('actions', 'local_coursefisher'),
                new lang_string('configactions', 'local_coursefisher'),
                 $defaultchoices, $choices));

    $page->add(new admin_setting_configcheckbox('local_coursefisher/autocreation',
                new lang_string('autocreation', 'local_coursefisher'),
                new lang_string('configautocreation', 'local_coursefisher'),
                0));

    $ADMIN->add('coursefisher', $page);

    $page = new admin_settingpage('local_coursefisher_backend', new lang_string('backendsettings', 'local_coursefisher'));

    $choices = array();
    foreach (core_plugin_manager::instance()->get_plugins_of_type('coursefisherbackend') as $backend) {
        $choices[$backend->name] = $backend->displayname;
    }
    $page->add(new admin_setting_configselect('local_coursefisher/backend',
                new lang_string('backend', 'local_coursefisher'),
                new lang_string('configbackend', 'local_coursefisher'),
                '', $choices));

    $page->add(new admin_setting_configtextarea('local_coursefisher/locator',
                new lang_string('locator', 'local_coursefisher'),
                new lang_string('configlocator', 'local_coursefisher'),
                ''));

    $page->add(new admin_setting_configtextarea('local_coursefisher/parameters',
                new lang_string('parameters', 'local_coursefisher'),
                new lang_string('configparameters', 'local_coursefisher'),
                ''));

    $page->add(new admin_setting_configtext('local_coursefisher/separator',
                new lang_string('separator', 'local_coursefisher'),
                new lang_string('configseparator', 'local_coursefisher'),
                ''));

    $page->add(new admin_setting_configcheckbox('local_coursefisher/firstrow',
                new lang_string('firstrow', 'local_coursefisher'),
                new lang_string('configfirstrow', 'local_coursefisher'),
                0));

    $page->add(new admin_setting_configtextarea('local_coursefisher/fieldlist',
                new lang_string('fieldlist', 'local_coursefisher'),
                new lang_string('configfieldlist', 'local_coursefisher'),
                ''));

    $page->add(new admin_setting_heading('local_coursefisher/configurablefields', '',
                new lang_string('configcoursefields', 'local_coursefisher')));

    $backendtestlink = html_writer::tag('a', new lang_string('configurationtest', 'local_coursefisher'),
                array('href' => new moodle_url('/local/coursefisher/backend/test.php')));

    $page->add(new admin_setting_heading('local_coursefisher/backendtestlink', '', $backendtestlink));

    $ADMIN->add('coursefisher', $page);

    $page = new admin_settingpage('local_coursefisher_course', new lang_string('coursesettings', 'local_coursefisher'));

    $page->add(new admin_setting_configtextarea('local_coursefisher/fieldlevel',
                new lang_string('fieldlevel', 'local_coursefisher'),
                new lang_string('configfieldlevel', 'local_coursefisher'),
                ''));

    $page->add(new admin_setting_configtext('local_coursefisher/course_code',
                new lang_string('courseidnumber', 'local_coursefisher'),
                '',
                ''));

    $page->add(new admin_setting_configtext('local_coursefisher/course_fullname',
                new lang_string('coursefullname', 'local_coursefisher'),
                '',
                ''));

    $page->add(new admin_setting_configtext('local_coursefisher/course_shortname',
                new lang_string('courseshortname', 'local_coursefisher'),
                '',
                ''));

    $page->add(new admin_setting_configtextarea('local_coursefisher/fieldtest',
                new lang_string('fieldtest', 'local_coursefisher'),
                new lang_string('configfieldtest', 'local_coursefisher'),
                ''));

    $choices = array(
        'none' => get_string('dontsortcourses'),
        'fullname' => get_string('sortbyx', 'moodle', get_string('fullnamecourse')),
        'fullnamedesc' => get_string('sortbyxreverse', 'moodle', get_string('fullnamecourse')),
        'shortname' => get_string('sortbyx', 'moodle', get_string('shortnamecourse')),
        'shortnamedesc' => get_string('sortbyxreverse', 'moodle', get_string('shortnamecourse')),
        'idnumber' => get_string('sortbyx', 'moodle', get_string('idnumbercourse')),
        'idnumberdesc' => get_string('sortbyxreverse', 'moodle', get_string('idnumbercourse')),
        'timecreated' => get_string('sortbyx', 'moodle', get_string('timecreatedcourse')),
        'timecreateddesc' => get_string('sortbyxreverse', 'moodle', get_string('timecreatedcourse'))
    );
    $page->add(new admin_setting_configselect('local_coursefisher/sortcoursesby',
                new lang_string('sortcoursesby', 'local_coursefisher'),
                new lang_string('configsortcoursesby', 'local_coursefisher'),
                'none', $choices));

    $ADMIN->add('coursefisher', $page);

    $page = new admin_settingpage('local_coursefisher_coursesgroup', new lang_string('coursesgroupsettings', 'local_coursefisher'));

    $choices = array();
    $default = '';
    if (enrol_is_enabled('guest')) {
        $choices['guest'] = new lang_string('guest', 'local_coursefisher');
        $default = 'guest';
    }
    if (enrol_is_enabled('meta')) {
        $choices['meta'] = new lang_string('meta', 'local_coursefisher');
        $default = 'meta';
    }
    if (!empty($default)) {
        $page->add(new admin_setting_configtext('local_coursefisher/course_group',
                    new lang_string('grouprule', 'local_coursefisher'),
                    new lang_string('configgrouprule', 'local_coursefisher'),
                    ''));

        $page->add(new admin_setting_configcheckbox('local_coursefisher/forceonlygroups',
                    new lang_string('forceonlygroups', 'local_coursefisher'),
                    new lang_string('configforceonlygroups', 'local_coursefisher'),
                    0));

        $page->add(new admin_setting_configselect('local_coursefisher/linktype',
                    new lang_string('linktype', 'local_coursefisher'),
                    new lang_string('configlinktype', 'local_coursefisher'),
                    $default, $choices));

        $page->add(new admin_setting_configtext('local_coursefisher/linked_course_category',
                    new lang_string('linkedcoursecategory', 'local_coursefisher'),
                    new lang_string('configlinkedcoursecategory', 'local_coursefisher'),
                    ''));

    } else {
        $page->add(new admin_setting_heading('local_coursefisher/noguestormeta', '',
                    new lang_string('noguestormeta', 'local_coursefisher')));
    }

    $ADMIN->add('coursefisher', $page);

    $page = new admin_settingpage('local_coursefisher_coursecontent',
                new lang_string('coursecontentsettings', 'local_coursefisher'));

    $page->add(new admin_setting_configtextarea('local_coursefisher/course_summary',
                new lang_string('coursesummary', 'local_coursefisher'),
                new lang_string('configcoursesummary', 'local_coursefisher'),
                ''));

    $page->add(new admin_setting_configtext('local_coursefisher/sectionzero_name',
                new lang_string('sectionzero', 'local_coursefisher'),
                new lang_string('configsectionzero', 'local_coursefisher'),
                ''));

    $page->add(new admin_setting_configtext('local_coursefisher/educationaloffer_link',
                new lang_string('educationalofferlink', 'local_coursefisher'),
                new lang_string('configeducationalofferlink', 'local_coursefisher'),
                ''));

    $page->add(new admin_setting_configtext('local_coursefisher/course_template',
                new lang_string('coursetemplate', 'local_coursefisher'),
                new lang_string('configcoursetemplate', 'local_coursefisher'),
                ''));

    $ADMIN->add('coursefisher', $page);

    $page = new admin_settingpage('local_coursefisher_notifications', new lang_string('notifysettings', 'local_coursefisher'));

    $page->add(new admin_setting_users_with_capability('local_coursefisher/notifycoursecreation',
                new lang_string('notifycoursecreation', 'local_coursefisher'),
                new lang_string('confignotifycoursecreation', 'local_coursefisher'),
                array(), 'local/coursefisher:addallcourses'));

    $page->add(new admin_setting_configtextarea('local_coursefisher/email_condition',
                new lang_string('emailcondition', 'local_coursefisher'),
                new lang_string('configemailcondition', 'local_coursefisher'),
                ''));

    $ADMIN->add('coursefisher', $page);

    $page = new admin_settingpage('local_coursefisher_userfilter', new lang_string('filter', 'local_coursefisher'));

    $choices = array();
    $choices['shown'] = new lang_string('shown', 'local_coursefisher');
    $choices['hidden'] = new lang_string('hidden', 'local_coursefisher');
    $page->add(new admin_setting_configselect('local_coursefisher/display',
                new lang_string('coursefisherwill', 'local_coursefisher'),
                '',
                'shown', $choices));

    $fieldnames = array('lastname', 'firstname', 'username', 'email', 'city', 'idnumber', 'institution', 'department', 'address');
    $fields = array('' => new lang_string('choose'));
    foreach ($fieldnames as $fieldname) {
        $fields[$fieldname] = new lang_string($fieldname);
    }

    $customfields = $DB->get_records('user_info_field');
    if (!empty($customfields)) {
        foreach ($customfields as $customfield) {
            if (in_array($customfield->datatype, array('text', 'menu', 'checkbox'))) {
                $fields[$customfield->shortname] = $customfield->name;
            }
        }
    }

    $page->add(new admin_setting_configselect('local_coursefisher/userfield',
                new lang_string('ifuserprofilefield', 'local_coursefisher'),
                '',
                '', $fields));

    $operators = array('contains' => new lang_string('contains', 'filters'),
                       'doesnotcontain' => new lang_string('doesnotcontain', 'filters'),
                       'isequalto' => new lang_string('isequalto', 'filters'),
                       'isnotequalto' => new lang_string('isnotequalto', 'filters'),
                       'startswith' => new lang_string('startswith', 'filters'),
                       'endswith' => new lang_string('endswith', 'filters'));

    $page->add(new admin_setting_configselect('local_coursefisher/operator',
                '',
                '',
                'contains', $operators));

    $page->add(new admin_setting_configtext('local_coursefisher/matchvalue',
                '',
                '',
                ''));

    $ADMIN->add('coursefisher', $page);
}

