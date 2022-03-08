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
 * Course fisher
 *
 * @package    local
 * @subpackage coursefisher
 * @copyright  2014 Roberto Pinna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/locallib.php');

/**
 * Extend Navigation block
 *
 * @param object $navigation global_navigation
 * @return void
 */
function local_coursefisher_extend_navigation(global_navigation $navigation) {
    global $PAGE;

    if (local_coursefisher_enabled_user($PAGE->context)) {
        local_coursefisher_links($PAGE->navigation, true);
    }
}

/**
 * Extend Settings block
 *
 * @param object $nav settings_navigation
 * @param object $context context
 * @return void
 */
function local_coursefisher_extend_settings_navigation(settings_navigation $nav, context $context) {
    global $PAGE, $DB, $CFG, $USER;

    if (local_coursefisher_enabled_user($context)) {
        $coursefishertitle = get_config('local_coursefisher', 'title');
        if (empty($coursefishertitle)) {
            $coursefishertitle = get_string('pluginname', 'local_coursefisher');
        }
        $coursefisherlinks = $nav->add($coursefishertitle, null, navigation_node::TYPE_CONTAINER);
        local_coursefisher_links($coursefisherlinks);

        $helplink = get_config('local_coursefisher', 'course_helplink');
        if (!empty($helplink)) {
            $url = new moodle_url($helplink, array());
            $coursefisherlinks->add(get_string('help'), $url);
        }
    }

    if ($context->contextlevel == CONTEXT_COURSE) {
        if ($instances = enrol_get_instances($context->instanceid, true)) {
            foreach ($instances as $instance) {
                if ($instance->enrol === 'meta') {
                    $metacoursecontext = context_course::instance($instance->customint1);
                    if (is_enrolled($metacoursecontext)) {
                        $metacourse = $DB->get_record('course', array('id' => $instance->customint1));
                        $activitytype = $DB->get_field('course_format_options', 'value',
                                                       array('courseid' => $instance->customint1, 'name' => 'activitytype'));
                        $courselinkformat = $metacourse->format == 'courselink';
                        $singleurlformat = ($metacourse->format == 'singleactivity') && ($activitytype == 'url');
                        if ($courselinkformat || $singleurlformat) {
                            if ($metacourseinstances = enrol_get_instances($metacoursecontext->instanceid, true)) {
                                foreach ($metacourseinstances as $metacourseinstance) {
                                    if ($metacourseinstance->enrol === 'self') {
                                        $query = array('enrolid' => $metacourseinstance->id, 'userid' => $USER->id);
                                        if ($userenrolment = $DB->get_record('user_enrolments', $query)) {
                                            $enrolledbefore = $userenrolment->timestart < time();
                                            $stillenrolled = ($userenrolment->timeend == 0) || ($userenrolment->timeend < time());
                                            if ($enrolledbefore && $stillenrolled) {
                                                $node = $nav->get('courseadmin');
                                                $unenrolink = new moodle_url('/enrol/self/unenrolself.php',
                                                                             array('enrolid' => $metacourseinstance->id));
                                                $unenrolstr = get_string('unenrolme', 'enrol', $metacourse->shortname);
                                                $icon = new pix_icon('i/user', '');
                                                $node->add($unenrolstr, $unenrolink, navigation_node::TYPE_USER, null, null, $icon);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

/**
 * Add coursefisher links
 *
 * @param object $nav navigation_node
 * @param boolean $flatnavenabled
 * @return void
 */
function local_coursefisher_links($nav, $flatnavenabled = false) {

    $url = new moodle_url('/local/coursefisher/addcourse.php', array());
    $coursefishertitle = get_config('local_coursefisher', 'title');
    if (empty($coursefishertitle)) {
        $coursefishertitle = get_string('pluginname', 'local_coursefisher');
    }
    $addcoursestr = get_string('addcourses', 'local_coursefisher');
    if (!empty($flatnavenabled)) {
        $addcourses = $nav->add($coursefishertitle.': '.$addcoursestr, $url);
        $addcourses->showinflatnavigation = true;
    } else {
        $addcourses = $nav->add($addcoursestr, $url);
    }
}

/**
 * Execute automatic operations
 *
 * @return boolean
 */
function local_coursefisher_cron() {
    global $CFG, $DB;

    require_once(__DIR__ . '/backend/lib.php');

    $backendname = get_config('local_coursefisher', 'backend');
    if (file_exists(__DIR__ . '/backend/'.$backend.'/lib.php')) {
        require_once(__DIR__ . '/backend/'.$backend.'/lib.php');
        $backendclassname = 'local_coursefisher_backend_'.$backend;
        if (class_exists($backendclassname)) {
            $backend = new $backendclassname();
            if (method_exists($backend, 'cron')) {
                mtrace('Processing backend '.$backend.' cron...');
                $backend->cron();
                mtrace('done.');
            }

            $autocreation = get_config('local_coursefisher', 'autocreation');
            if (!empty($autocreation)) {
                mtrace('Processing course autocreation...');
                $teachercourses = local_coursefisher_get_coursehashes($backend->get_data(true));

                if (!empty($teachercourses)) {
                    $coursecodepattern = get_config('local_coursefisher', 'course_code');
                    $courseshortnamepattern = get_config('local_coursefisher', 'course_shortname');
                    $coursefullnamepattern = get_config('local_coursefisher', 'course_fullname');
                    $fieldlevelpattern = get_config('local_coursefisher', 'fieldlevel');
                    foreach ($teachercourses as $coursehash => $teachercourse) {
                        $course = null;
                        $coursecode = '';
                        $courseshortname = '';
                        if (!empty($coursecodeipattern)) {
                            $coursecode = $backend->format_fields($coursecodepattern, $teachercourse);
                            $course = $DB->get_record('course', array('idnumber' => $coursecode));
                        } else {
                            $courseshortname = $backend->format_fields($courseshortnamepattern, $teachercourse);
                            $course = $DB->get_record('course', array('shortname' => $courseshortname));
                        }
                        if (! $course) {
                            $courseshortname = $backend->format_fields($courseshortnamepattern, $teachercourse);
                            $categories = array_filter(explode("\n", $backend->format_fields($fieldlevelpattern, $teachercourse)));
                            $categoriesdescriptions = $backend->get_fields_description($categories);
                            $coursepath = implode(' / ', $categoriesdescriptions);
                            $coursefullname = $backend->format_fields($coursefullnamepattern, $teachercourse);

                            $coursecode = $backend->format_fields($coursecodepattern, $teachercourse);
                            if (! $newcourse = local_coursefisher_create_course($coursefullname, $courseshortname,
                                    $coursecode, 0, $backend->get_fields_items($categories))) {
                                 notice(get_string('coursecreationerror', 'local_coursefisher'));
                            } else {
                                 mtrace('... added course'.$coursefullname.' - '.$courseshortname.' - '.$coursecode);
                            }
                        }
                    }
                }
            }
        }
    }

    return true;
}

