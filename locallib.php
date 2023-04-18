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
 * @package    local_coursefisher
 * @copyright  2014 and above Roberto Pinna, Diego Fantoma, Angelo Calò
 * @copyright  2016 and above Francesco Carbone
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot .'/course/lib.php');

/**
 * Create course parents categories, if not exits, and return last parent id
 *
 * @param array $categories The course parents categories
 *
 * @return integer or null
 */
function local_coursefisher_create_categories($categories) {
    global $DB, $CFG;
    $parentid = 0;
    $result = null;

    if (!empty($categories)) {
        foreach ($categories as $category) {
            if (!empty($category->description)) {
                $newcategory = new stdClass();
                $newcategory->parent = $parentid;
                $newcategory->name = trim($category->description);

                $searchquery = array('name' => $newcategory->name, 'parent' => $newcategory->parent);
                if (!empty($category->code)) {
                    $newcategory->idnumber = $category->code;
                    $searchquery = array('idnumber' => $newcategory->idnumber);
                }

                if (! $oldcategory = $DB->get_record('course_categories', $searchquery)) {
                    if (class_exists('core_course_category')) {
                        $result = \core_course_category::create($newcategory);
                    } else {
                        require_once($CFG->libdir. '/coursecatlib.php');
                        $result = coursecat::create($newcategory);
                    }
                } else {
                    $result = $oldcategory;
                }
                $parentid = $result->id;
            }
        }
        return $result->id;
    }

    return null;
}

/**
 * Create a course, if not exits, and assign an editing teacher
 *
 * @param object  $coursedata   The new course data
 * @param integer $teacherid    The teacher id
 * @param array   $categories   The categories from top category for this course
 * @param object  $linkedcourse The main course
 *
 * @return object or null
 */
function local_coursefisher_create_course($coursedata, $teacherid = 0, $categories = array(), $linkedcourse = null) {
    global $DB, $CFG;

    $newcourse = new stdClass();

    $newcourse->id = '0';

    $fisherconfig = get_config('local_coursefisher');

    $courseconfig = get_config('moodlecourse');
    // Apply course default settings.
    foreach ($courseconfig as $configname => $configvalue) {
        $newcourse->$configname = $configvalue;
    }

    $newcourse->startdate = time();
    if (isset($courseconfig->courseduration) && !empty($courseconfig->courseduration)) {
        $newcourse->enddate = $newcourse->startdate + $courseconfig->courseduration;
    }

    $newcourse->fullname = $coursedata->fullname;
    $newcourse->shortname = $coursedata->shortname;
    $newcourse->idnumber = $coursedata->idnumber;
    if (isset($coursedata->summary) && !empty($coursedata->summary)) {
        $newcourse->summary = $coursedata->summary;
        $newcourse->summaryformat = FORMAT_MOODLE;
    } else {
        $newcourse->summary = '';
        $newcourse->summaryformat = FORMAT_MOODLE;
    }

    if ($linkedcourse !== null) {
        $newcourse->format = 'singleactivity';
        $newcourse->activitytype = 'url';
    }

    $course = null;
    if (!empty($coursedata->idnumber)) {
        $oldcourse = $DB->get_record('course', array('idnumber' => $coursedata->idnumber));
    } else {
        $oldcourse = $DB->get_record('course', array('shortname' => $coursedata->shortname));
    }
    if (!$oldcourse) {
        $newcourse->category = local_coursefisher_create_categories($categories);
        if ($course = create_course($newcourse)) {
            if ($coursedata->notifycreation) {

                $notifyinfo = new stdClass();
                $notifyinfo->coursefullname = $coursedata->fullname;
                $notifyinfo->courseurl = (string) new moodle_url('/course/view.php', array('id' => $course->id));

                $notifysubject = get_string('coursenotifysubject', 'local_coursefisher');
                $notifytext = get_string('coursenotifytext', 'local_coursefisher', $notifyinfo);
                $notifyhtml = get_string('coursenotifyhtml', 'local_coursefisher', $notifyinfo);
                if (isset($coursedata->educationofferurl) && !empty($coursedata->educationofferurl)) {
                    $notifyinfo->educationalofferurl = $coursedata->educationofferurl;
                    $notifytext = get_string('coursenotifycomplete', 'local_coursefisher', $notifyinfo);
                    $notifyhtml = get_string('coursenotifyhtmlcomplete', 'local_coursefisher', $notifyinfo);
                }

                $notifycoursecreation = $fisherconfig->notifycoursecreation;
                if (empty($notifycoursecreation)) {
                    $notifycoursecreation = '$@NONE@$';
                }
                $recip = get_users_from_config($notifycoursecreation, 'local/coursefisher:addallcourses');
                foreach ($recip as $user) {
                    if (! email_to_user($user, \core_user::get_support_user(), $notifysubject, $notifytext, $notifyhtml)) {
                        mtrace('Error: Could not send out mail to user '.$user->id.' ('.$user->email.')');
                    }
                }

            }
            if (($linkedcourse !== null)) {
                if (!empty($fisherconfig->linktype) && ($fisherconfig->linktype == 'guest')) {
                    if (enrol_is_enabled('guest')) {
                        $guest = enrol_get_plugin('guest');
                        $hasguest = false;
                        if ($instances = enrol_get_instances($course->id, false)) {
                            foreach ($instances as $instance) {
                                if ($instance->enrol === 'guest') {
                                    $guest->update_status($instance, ENROL_INSTANCE_ENABLED);
                                    $hasguest = true;
                                } else {
                                    $guest->update_status($instance, ENROL_INSTANCE_DISABLED);
                                }
                            }
                        }
                        if (!$hasguest) {
                            $guest->add_instance($course);
                        }
                    }
                }

                if (($course->format == 'singleactivity')) {
                    $query = array('courseid' => $course->id, 'format' => 'singleactivity',
                                   'sectionid' => 0, 'name' => 'activitytype');
                    if ($DB->get_field('course_format_options', 'value', $query) != 'url') {
                        $DB->set_field('course_format_options', 'value', 'url', $query);
                    }
                    local_coursefisher_add_linkedcourse_url($course, $linkedcourse);
                }

            } else {
                // Set default name for section 0.
                if (isset($coursedata->sectionzero) && !empty($coursedata->sectionzero)) {
                    $query = array('section' => 0, 'course' => $course->id);
                    $DB->set_field('course_sections', 'name', $coursedata->sectionzero, $query);
                }

                // Add Educational offer external link.
                if (isset($coursedata->educationalofferurl) && !empty($coursedata->educationalofferurl)) {
                    require_once($CFG->dirroot.'/course/modlib.php');
                    $url = new stdClass();
                    $url->module = $DB->get_field('modules', 'id', array('name' => 'url', 'visible' => 1));
                    $url->name = get_string('educationaloffer', 'local_coursefisher');
                    $url->intro = get_string('educationaloffermessage', 'local_coursefisher');
                    $url->externalurl = $coursedata->educationalofferurl;
                    // Open the url in a new tab.
                    $url->display = 3;
                    $url->cmidnumber = null;
                    $url->visible = 1;
                    $url->instance = 0;
                    $url->section = 0;
                    $url->modulename = 'url';
                    add_moduleinfo($url, $course);
                }

                // Import course activities and resources from template.
                if (isset($coursedata->templateshortname) && !empty($coursedata->templateshortname)) {
                    require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
                    require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
                    $templateid = $DB->get_field('course', 'id', array('shortname' => $coursedata->templateshortname));
                    if (!empty($templateid)) {
                        $primaryadmin = get_admin();

                        $bc = new backup_controller(backup::TYPE_1COURSE, $templateid, backup::FORMAT_MOODLE,
                                                    backup::INTERACTIVE_NO, backup::MODE_IMPORT, $primaryadmin->id);
                        $bc->execute_plan();

                        $rc = new restore_controller($bc->get_backupid(), $course->id, backup::INTERACTIVE_NO,
                                                     backup::MODE_IMPORT, $primaryadmin->id, backup::TARGET_EXISTING_ADDING);
                        $rc->execute_precheck();
                        $rc->execute_plan();
                    }
                }
            }
            if (local_coursefisher_validate_sortcoursesby($fisherconfig->sortcoursesby)) {
                $adhocsorter = new \local_coursefisher\task\sort_courses();
                $data = new \stdClass();
                $data->categoryid = $course->category;
                $data->sortcoursesby = $fisherconfig->sortcoursesby;
                $adhocsorter->set_custom_data($data);
                \core\task\manager::queue_adhoc_task($adhocsorter);
            }
        }
    } else {
        $course = $oldcourse;
    }

    $editingteacherroleid = $DB->get_field('role', 'id', array('shortname' => 'editingteacher'));

    if (!empty($teacherid) && ($teacheruser = $DB->get_record('user', array('id' => $teacherid)))) {
        // Set student role at course context.
        $coursecontext = context_course::instance($course->id);

        // We use only manual enrol plugin here, if it is disabled no enrol is done.
        if (enrol_is_enabled('manual')) {
            $manual = enrol_get_plugin('manual');
            if ($instances = enrol_get_instances($course->id, false)) {
                foreach ($instances as $instance) {
                    if ($instance->enrol === 'manual' && $instance->status != ENROL_INSTANCE_DISABLED) {
                        $manual->enrol_user($instance, $teacheruser->id, $editingteacherroleid, time(), 0);
                        break;
                    }
                }
            }
        }
    }

    return $course;
}

/**
 * Check if given string is a valid courses sort order.
 *
 * @param string $sortorder The sortorder to check
 *
 * @return boolean The check result
 */
function local_coursefisher_validate_sortcoursesby($sortorder) {
    if (!empty($sortorder)) {
        $sortorders = array(
            'fullname', 'fullnamedesc',
            'shortname', 'shortnamedesc',
            'idnumber', 'idnumberdesc',
            'timecreated', 'timecreateddesc'
        );
        if (in_array($sortorder, $sortorders)) {
            return true;
        }
    }
    return false;
}

/**
 * Add an URL resource to child course section 0 that link to main course.
 *
 * @param object $course The child course
 * @param object $linkedcourse The main course
 *
 * @return void
 */
function local_coursefisher_add_linkedcourse_url($course, $linkedcourse) {
    global $CFG, $DB;

    require_once($CFG->dirroot.'/course/modlib.php');

    $cw = get_fast_modinfo($course->id)->get_section_info(0);

    $urlresource = new stdClass();

    $urlresource->cmidnumber = null;
    $urlresource->section = 0;

    $urlresource->course = $course->id;
    $urlresource->name = get_string('courselink', 'local_coursefisher');
    $urlresource->intro = get_string('courselinkmessage', 'local_coursefisher', $linkedcourse->fullname);

    $urlresource->display = 0;
    $displayoptions = array();
    $displayoptions['printintro'] = 1;
    $urlresource->displayoptions = serialize($displayoptions);
    $urlresource->parameters = '';

    $urlresource->externalurl = $CFG->wwwroot.'/course/view.php?id='.$linkedcourse->id;
    $urlresource->timemodified = time();

    $urlresource->visible = $cw->visible;
    $urlresource->instance = 0;

    $urlresource->module = $DB->get_field('modules', 'id', array('name' => 'url', 'visible' => 1));
    $urlresource->modulename = 'url';

    add_moduleinfo($urlresource, $course);
}

/**
 * Add metacourse enrolment instances to main course that link to children courses.
 *
 * @param object $course The main course
 * @param array  $metacourseids Children courses ids
 *
 * @return void
 */
function local_coursefisher_add_metacourses($course, $metacourseids = array()) {
    if (enrol_is_enabled('meta')) {
        $context = context_course::instance($course->id, MUST_EXIST);
        if (!empty($metacourseids) && has_capability('moodle/course:enrolconfig', $context)) {
            $enrol = enrol_get_plugin('meta');
            $context = context_course::instance($course->id, MUST_EXIST);
            if (has_capability('enrol/meta:config', $context)) {
                foreach ($metacourseids as $metacourseid) {
                    $eid = $enrol->add_instance($course, array('customint1' => $metacourseid));
                }
            }
        }
    }
}

/**
 * Get MD5 hashes of given courses.
 *
 * @param array $courses Courses that need hashes
 *
 * @return array Course hashes
 */
function local_coursefisher_get_coursehashes($courses) {
    $hashedcourses = array();
    if (!empty($courses)) {
        foreach ($courses as $i => $course) {
            $coursehash = md5(serialize($course));
            $hashedcourses[$coursehash] = $course;
        }
    }
    return $hashedcourses;
}

/**
 * Get course group members.
 *
 * @param array  $courses Courses that need to be check
 * @param string $selectedcoursehash Hash of selected course for creation hash
 * @param object $coursedata Data of selected course for creation
 *
 * @return array Course group members with the main course as first element
 */
function local_coursefisher_get_groupcourses($courses, $selectedcoursehash, $coursedata) {
    global $DB;

    $config = get_config('local_coursefisher');

    $groupcourses = array();

    $selectedcourse = $courses[$selectedcoursehash];

    $firstcoursematch = null;
    $othercoursesmatch = null;

    if (!empty($config->course_group)) {
        $equalpos = strpos($config->course_group, "=");
        $firstcoursematch = substr($config->course_group, $equalpos + 1);
        $othercoursesmatch = substr($config->course_group, 0, $equalpos);

        // Search for course group leader and members.
        $firstcourseid = \local_coursefisher\backend::format_fields($firstcoursematch, $selectedcourse);
        $othercourseid = \local_coursefisher\backend::format_fields($othercoursesmatch, $selectedcourse);

        if (!empty($othercourseid)) {
            // Search for firstcourse.
            foreach ($courses as $coursehash => $course) {
                if ($othercourseid == \local_coursefisher\backend::format_fields($firstcoursematch, $course)) {
                    // Found firstcourse match.
                    $firstcoursedata = new stdClass();
                    $firstcoursedata->idnumber = '';
                    $firstcoursedata->shortname = \local_coursefisher\backend::format_fields($config->course_shortname, $course);
                    if (!empty($config->course_code)) {
                        $firstcoursedata->idnumber = \local_coursefisher\backend::format_fields($config->course_code, $course);
                    }
                    $firstcoursedata->code = $firstcoursedata->shortname;
                    if (!empty($firstcoursedata->idnumber)) {
                        $firstcoursedata->code = $firstcoursedata->idnumber;
                    }

                    $fieldlevel = \local_coursefisher\backend::format_fields($config->fieldlevel, $course);
                    $categories = array_filter(explode("\n", $fieldlevel));
                    $categoriesdescriptions = \local_coursefisher\backend::get_fields_description($categories);
                    $firstcoursedata->path = implode(' / ', $categoriesdescriptions);
                    $firstcoursedata->fullname = \local_coursefisher\backend::format_fields($config->course_fullname, $course);
                    $firstcoursedata->hash = $coursehash;
                    $firstcoursedata->exists = false;
                    $firstcoursedata->first = true;
                    if (!empty($firstcoursedata->idnumber)) {
                        $oldcourse = $DB->get_record('course', array('idnumber' => $firstcoursedata->idnumber));
                    } else {
                        $oldcourse = $DB->get_record('course', array('shortname' => $firstcoursedata->shortname));
                    }
                    if ($oldcourse) {
                        $firstcoursedata->exists = true;
                        $firstcoursedata->id = $oldcourse->id;
                    }

                    $groupcourses[$coursehash] = $firstcoursedata;
                    $firstcourseid = \local_coursefisher\backend::format_fields($firstcoursematch, $course);
                }
            }
        } else {
            $coursedata->first = true;
            $groupcourses[$selectedcoursehash] = $coursedata;
        }
        if ((count($groupcourses) == 1) && !empty($firstcourseid)) {
            // Search for othercourses.
            foreach ($courses as $coursehash => $course) {
                if ($firstcourseid == \local_coursefisher\backend::format_fields($othercoursesmatch, $course)) {
                    // Found firstcourse match.
                    $othercoursedata = new stdClass();
                    $othercoursedata->idnumber = '';
                    $othercoursedata->shortname = \local_coursefisher\backend::format_fields($config->course_shortname, $course);
                    if (!empty($config->course_code)) {
                        $othercoursedata->idnumber = \local_coursefisher\backend::format_fields($config->course_code, $course);
                    }

                    $othercoursedata->code = $othercoursedata->shortname;
                    if (!empty($othercoursedata->idnumber)) {
                        $othercoursedata->code = $othercoursedata->idnumber;
                    }

                    $fieldlevel = \local_coursefisher\backend::format_fields($config->fieldlevel, $course);
                    $categories = array_filter(explode("\n", $fieldlevel));
                    $categoriesdescriptions = \local_coursefisher\backend::get_fields_description($categories);
                    $othercoursedata->path = implode(' / ', $categoriesdescriptions);
                    $othercoursedata->fullname = \local_coursefisher\backend::format_fields($config->course_fullname, $course);
                    $othercoursedata->hash = $coursehash;
                    $othercoursedata->exists = false;
                    if (!empty($othercoursedata->idnumber)) {
                        $oldcourse = $DB->get_record('course', array('idnumber' => $othercoursedata->idnumber));
                    } else {
                        $oldcourse = $DB->get_record('course', array('shortname' => $othercoursedata->shortname));
                    }
                    if ($oldcourse) {
                        $othercoursedata->exists = true;
                        $othercoursedata->id = $oldcourse->id;
                    }

                    $groupcourses[$coursehash] = $othercoursedata;
                }
            }
        } else {
            $groupcourses[$selectedcoursehash] = $coursedata;
        }
    } else {
        $groupcourses[$selectedcoursehash] = $coursedata;
    }
    return $groupcourses;
}

/**
 * Test if current user could add new courses
 *
 * @param context $context Current page context
 *
 * @return boolean If current user could add new courses or not
 */
function local_coursefisher_enabled_user($context) {
    global $USER, $DB;

    $enabled = false;
    $config = get_config('local_coursefisher');
    $filterconfigured = (isset($config->userfield) && !empty($config->userfield));
    if ($filterconfigured && !has_capability('local/coursefisher:addallcourses', $context)) {

        if (isset($config->matchvalue) && !empty($config->matchvalue)) {

            $userfieldvalue = '';
            $customfields = $DB->get_records('user_info_field');
            if (!empty($customfields)) {
                foreach ($customfields as $customfield) {
                    if ($customfield->shortname == $config->userfield) {
                        if (isset($USER->profile[$customfield->shortname]) && !empty($USER->profile[$customfield->shortname])) {
                            $userfieldvalue = $USER->profile[$customfield->shortname];
                        }
                    }
                }
            }
            if (empty($userfieldvalue)) {
                if (isset($USER->{$config->userfield})) {
                    $userfieldvalue = $USER->{$config->userfield};
                }
            }

            switch ($config->operator) {
                case 'contains':
                    if (mb_strpos($userfieldvalue, $config->matchvalue) !== false) {
                        $enabled = true;
                    }
                break;
                case 'doesnotcontains':
                    if (mb_strpos($userfieldvalue, $config->matchvalue) === false) {
                        $enabled = true;
                    }
                break;
                case 'isequalto':
                    if ($config->matchvalue == $userfieldvalue) {
                        $enabled = true;
                    }
                break;
                case 'isnotequalto':
                    if ($config->matchvalue != $userfieldvalue) {
                        $enabled = true;
                    }
                break;
                case 'startswith':
                    if (preg_match('/^'.$config->matchvalue.'/', $userfieldvalue) === 1) {
                        $enabled = true;
                    }
                break;
                case 'endswith':
                    if (preg_match('/'.$config->matchvalue.'$/', $userfieldvalue) === 1) {
                        $enabled = true;
                    }
                break;
            }
            if (!empty($config->display) && ($config->display == 'hidden')) {
                $enabled = !$enabled;
            }
        }
    } else {
        $enabled = true;
    }
    return $enabled;
}
