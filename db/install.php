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
 * @package local
 * @subpackage coursefisher
 * @author 2017 and above Roberto Pinna
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_local_coursefisher_install() {
    global $CFG;

    // Get plugin settings from Course Fisher Block.
    if (isset($CFG->block_course_fisher_backend) && !empty($CFG->block_course_fisher_backend)) {
        if ($CFG->block_course_fisher_backend == 'db') {
            $CFG->block_course_fisher_backend = 'database';
        }
        set_config('backend', $CFG->block_course_fisher_backend, 'local_coursefisher');
    }
    if (isset($CFG->block_course_fisher_locator) && !empty($CFG->block_course_fisher_locator)) {
        set_config('locator', $CFG->block_course_fisher_locator, 'local_coursefisher');
    }
    if (isset($CFG->block_course_fisher_parameters) && !empty($CFG->block_course_fisher_parameters)) {
        set_config('parameters', $CFG->block_course_fisher_parameters, 'local_coursefisher');
    }
    if (isset($CFG->block_course_fisher_fieldtest) && !empty($CFG->block_course_fisher_fieldtest)) {
        set_config('fieldtest', $CFG->block_course_fisher_fieldtest, 'local_coursefisher');
    }
    if (isset($CFG->block_course_fisher_separator) && !empty($CFG->block_course_fisher_separator)) {
        set_config('separator', $CFG->block_course_fisher_separator, 'local_coursefisher');
    }
    if (isset($CFG->block_course_fisher_firstrow) && !empty($CFG->block_course_fisher_firstrow)) {
        set_config('firstrow', $CFG->block_course_fisher_firstrow, 'local_coursefisher');
    }
    if (isset($CFG->block_course_fisher_fieldlist) && !empty($CFG->block_course_fisher_fieldlist)) {
        set_config('fieldlist', $CFG->block_course_fisher_fieldlist, 'local_coursefisher');
    }
    if (isset($CFG->block_course_fisher_fieldlevel) && !empty($CFG->block_course_fisher_fieldlevel)) {
        set_config('fieldlevel', $CFG->block_course_fisher_fieldlevel, 'local_coursefisher');
    }
    if (isset($CFG->block_course_fisher_course_code) && !empty($CFG->block_course_fisher_course_code)) {
        set_config('course_code', $CFG->block_course_fisher_course_code, 'local_coursefisher');
    }
    if (isset($CFG->block_course_fisher_course_fullname) && !empty($CFG->block_course_fisher_course_fullname)) {
        set_config('course_fullname', $CFG->block_course_fisher_course_fullname, 'local_coursefisher');
    }
    if (isset($CFG->block_course_fisher_course_shortname) && !empty($CFG->block_course_fisher_course_shortname)) {
        set_config('course_shortname', $CFG->block_course_fisher_course_shortname, 'local_coursefisher');
    }
    if (isset($CFG->block_course_fisher_course_group) && !empty($CFG->block_course_fisher_course_group)) {
        set_config('course_group', $CFG->block_course_fisher_course_group, 'local_coursefisher');
    }
    if (isset($CFG->block_course_fisher_forcegrouponly) && !empty($CFG->block_course_fisher_forcegrouponly)) {
        set_config('forcegrouponly', $CFG->block_course_fisher_forcegrouponly, 'local_coursefisher');
    }
    if (isset($CFG->block_course_fisher_linktype) && !empty($CFG->block_course_fisher_linktype)) {
        set_config('linktype', $CFG->block_course_fisher_linktype, 'local_coursefisher');
    }
    if (isset($CFG->block_course_fisher_linked_course_category) && !empty($CFG->block_course_fisher_linked_course_category)) {
        set_config('linked_course_category', $CFG->block_course_fisher_linked_course_category, 'local_coursefisher');
    }
    if (isset($CFG->block_course_fisher_course_summary) && !empty($CFG->block_course_fisher_course_summary)) {
        set_config('course_summary', $CFG->block_course_fisher_course_summary, 'local_coursefisher');
    }
    if (isset($CFG->block_course_fisher_sectionzero_name) && !empty($CFG->block_course_fisher_sectionzero_name)) {
        set_config('sectionzero_name', $CFG->block_course_fisher_sectionzero_name, 'local_coursefisher');
    }
    if (isset($CFG->block_course_fisher_educationaloffer_link) && !empty($CFG->block_course_fisher_educationaloffer_link)) {
        set_config('educationaloffer_link', $CFG->block_course_fisher_educationaloffer_link, 'local_coursefisher');
    }
    if (isset($CFG->block_course_fisher_course_template) && !empty($CFG->block_course_fisher_course_template)) {
        set_config('course_template', $CFG->block_course_fisher_course_template, 'local_coursefisher');
    }
    if (isset($CFG->block_course_fisher_course_helplink) && !empty($CFG->block_course_fisher_course_helplink)) {
        set_config('course_helplink', $CFG->block_course_fisher_course_helplink, 'local_coursefisher');
    }
    if (isset($CFG->block_course_fisher_actions) && !empty($CFG->block_course_fisher_actions)) {
        set_config('actions', $CFG->block_course_fisher_actions, 'local_coursefisher');
    }
    if (isset($CFG->block_course_fisher_email_condition) && !empty($CFG->block_course_fisher_email_condition)) {
        set_config('email_condition', $CFG->block_course_fisher_email_condition, 'local_coursefisher');
    }
    if (isset($CFG->block_course_fisher_notifycoursecreation) && !empty($CFG->block_course_fisher_notifycoursecreation)) {
        set_config('notifycoursecreation', $CFG->block_course_fisher_notifycoursecreation, 'local_coursefisher');
    }
    if (isset($CFG->block_course_fisher_autocreation) && !empty($CFG->block_course_fisher_autocreation)) {
        set_config('autocreation', $CFG->block_course_fisher_autocreation, 'local_coursefisher');
    }

}
