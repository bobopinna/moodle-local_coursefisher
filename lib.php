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

function local_coursefisher_extend_settings_navigation(settings_navigation $nav, context $context) {
    global $PAGE;

    if (local_coursefisher_enabled_user($context)) {
        $url = new moodle_url('/local/coursefisher/addcourse.php', array());
        $coursefishertitle = get_config('local_coursefisher', 'title');
        if (empty($coursefishertitle)) {
            $coursefishertitle = get_string('pluginname', 'local_coursefisher');
        }
        $coursefisherlinks = $nav->add($coursefishertitle, null, navigation_node::TYPE_CONTAINER);
        $addcourses = $coursefisherlinks->add(get_string('addmoodlecourse', 'local_coursefisher'), $url);
        if (isset($PAGE->flatnav)) {
            $addcourses->showinflatnavigation = true;
            $PAGE->flatnav->add($addcourses);
        }
        $helplink = get_config('local_coursefisher', 'course_helplink');
        if (!empty($helplink)) {
            $url = new moodle_url($helplink, array());
            $coursefisherlinks->add(get_string('help'), $url);
        }
    }
}

function local_coursefisher_cron() {
    global $CFG, $DB;

    require_once('backend/lib.php');

    $backendname = get_config('local_coursefisher', 'backend');
    if (file_exists($CFG->dirroot.'/local/coursefisher/backend/'.$backend.'/lib.php')) {
        require_once($CFG->dirroot.'/local/coursefisher/backend/'.$backend.'/lib.php');
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
                            $coursecode = local_coursefisher_format_fields($coursecodepattern, $teachercourse);
                            $course = $DB->get_record('course', array('idnumber' => $coursecode));
                        } else {
                            $courseshortname = local_coursefisher_format_fields($courseshortnamepattern, $teachercourse);
                            $course = $DB->get_record('course', array('shortname' => $courseshortname));
                        }
                        if (! $course) {
                            $courseshortname = local_coursefisher_format_fields($courseshortnamepattern, $teachercourse);
                            $categories = array_filter(explode("\n",
                                    local_coursefisher_format_fields($fieldlevelpattern, $teachercourse)));
                            $categoriesdescriptions = local_coursefisher_get_fields_description($categories);
                            $coursepath = implode(' / ', $categoriesdescriptions);
                            $coursefullname = local_coursefisher_format_fields($coursefullnamepattern, $teachercourse);

                            $coursecode = local_coursefisher_format_fields($coursecodepattern, $teachercourse);
                            if (! $newcourse = local_coursefisher_create_course($coursefullname, $courseshortname,
                                    $coursecode, 0, local_coursefisher_get_fields_items($categories))) {
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

