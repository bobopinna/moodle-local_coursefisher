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
 * @copyright 2014 and above Roberto Pinna, Diego Fantoma, Angelo Calò
 * @copyright 2016 and above Francesco Carbone
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot .'/course/lib.php');
require_once($CFG->libdir .'/coursecatlib.php');

function local_coursefisher_create_categories($categories) {
    global $DB;
    $parentid = 0;
    $result = null;

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
                $result = coursecat::create($newcategory);
            } else {
                $result = $oldcategory;
            }
            $parentid = $result->id;
        }
    }

    return $result->id;
}

/**
 * Create a course, if not exits, and assign an editing teacher
 *
 * @param string course_fullname  The course fullname
 * @param string course_shortname The course shortname
 * @param string teacher_id       The teacher id code
 * @param array  categories       The categories from top category for this course
 *
 * @return object or null
 *
 */
function local_coursefisher_create_course($coursedata, $teacherid = 0, $categories = array(), $linkedcourse = null, $existent) {
    global $DB, $CFG;

    $newcourse = new stdClass();

    $newcourse->id = '0';

    $courseconfig = get_config('moodlecourse');

    // Apply course default settings.
    $newcourse->format             = $courseconfig->format;
    $newcourse->newsitems          = $courseconfig->newsitems;
    $newcourse->showgrades         = $courseconfig->showgrades;
    $newcourse->showreports        = $courseconfig->showreports;
    $newcourse->maxbytes           = $courseconfig->maxbytes;
    $newcourse->groupmode          = $courseconfig->groupmode;
    $newcourse->groupmodeforce     = $courseconfig->groupmodeforce;
    $newcourse->visible            = $courseconfig->visible;
    $newcourse->visibleold         = $newcourse->visible;
    $newcourse->lang               = $courseconfig->lang;

    $newcourse->startdate = time();

    $newcourse->fullname = $coursedata->fullname;
    $newcourse->shortname = $coursedata->shortname;
    $newcourse->idnumber = $coursedata->idnumber;
    if (isset($coursedata->summary) && !empty($coursedata->summary)) {
        $newcourse->summary = $coursedata->summary;
    }

    if ($linkedcourse !== null) {
        if (in_array('courselink', get_sorted_course_formats(true))) {
            $newcourse->format = 'courselink';
            $newcourse->linkedcourse = $linkedcourse->shortname;
        } else {
            $newcourse->format = 'singleactivity';
            $newcourse->activitytype = 'url';
        }
    }

    $course = null;
    if (!empty($coursedata->idnumber)) {
        $oldcourse = $DB->get_record('course', array('idnumber' => $coursedata->idnumber));
    } else {
        $oldcourse = $DB->get_record('course', array('shortname' => $coursedata->shortname));
    }
    if (!$oldcourse) {
        $newcourse->category = local_coursefisher_create_categories($categories);
        if (!$course = create_course($newcourse)) {
            print_error("Error inserting a new course in the database!");
        }
        if ($coursedata->notifycreation) {

            $notifyinfo = new stdClass();
            $notifyinfo->coursefullname = $coursedata->fullname;
            $notifyinfo->courseurl = new moodle_url('/course/view.php', array('id' => $course->id));

            $notifysubject = get_string('coursenotifysubject', 'local_coursefisher');
            $notifytext = get_string('coursenotifytext', 'local_coursefisher', $notifyinfo);
            $notifyhtml = get_string('coursenotifyhtml', 'local_coursefisher', $notifyinfo);
            if (isset($coursedata->educationofferurl) && !empty($coursedata->educationofferurl)) {
                $notifyinfo->educationalofferurl = $coursedata->educationofferurl;
                $notifytext = get_string('coursenotifycomplete', 'local_coursefisher', $notifyinfo);
                $notifyhtml = get_string('coursenotifyhtmlcomplete', 'local_coursefisher', $notifyinfo);
            }

            $notifycoursecreation = get_config('local_coursefisher', 'notifycoursecreation');
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
            $linktype = get_config('local_coursefisher', 'linktype');
            if (!empty($linktype) && ($linktype == 'guest')) {
                if (enrol_is_enabled('guest')) {
                    $guest = enrol_get_plugin('guest');
                    $hasguest = false;
                    if ($instances = enrol_get_instances($course->id, false)) {
                        foreach ($instances as $instance) {
                            if ($instance->enrol === 'guest') {
                                $guest->update_status($instance, ENROL_INSTANCE_ENABLED);
                                $hasguest = true;
                            }
                            if ($instance->enrol !== 'guest') {
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
                if (!$templateid) {
                    print_error("Error importing course template content!");
                } else {
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
    } else {
        $course = $oldcourse;
    }

    $editingteacherroleid = $DB->get_field('role', 'id', array('shortname' => 'editingteacher'));

    if (!empty($teacherid) && ($teacheruser = $DB->get_record('user', array('id' => $teacherid)))) {
        // Set student role at course context.
        $coursecontext = context_course::instance($course->id);

        $enrolled = false;
        // We use only manual enrol plugin here, if it is disabled no enrol is done.
        if (enrol_is_enabled('manual')) {
            $manual = enrol_get_plugin('manual');
            if ($instances = enrol_get_instances($course->id, false)) {
                foreach ($instances as $instance) {
                    if ($instance->enrol === 'manual') {
                        $manual->enrol_user($instance, $teacheruser->id, $editingteacherroleid, time(), 0);
                        $enrolled = true;
                        break;
                    }
                }
            }
        }
    }

    return $course;
}

function local_coursefisher_format_fields($formatstring, $data) {

    $callback = function($matches) use ($data) {
         return local_coursefisher_get_field($matches, $data);
    };

    $formattedstring = preg_replace_callback('/\[\%(\w+)(([#+-])(\d+))?\%\]/', $callback, $formatstring);

    return $formattedstring;
}

function local_coursefisher_get_field($matches, $data) {
    $replace = null;

    if (isset($matches[1])) {
        if (isset($data->$matches[1]) && !empty($data->$matches[1])) {
            if (isset($matches[2])) {
                switch($matches[3]) {
                    case '#':
                        $replace = substr($data->$matches[1], 0, $matches[4]);
                    break;
                    case '+':
                        $replace = $data->$matches[1] + $matches[4];
                    break;
                    case '-':
                        $replace = $data->$matches[1] - $matches[4];
                    break;
                }
            } else {
                $replace = $data->$matches[1];
            }
        }
    }
    return $replace;
}

function local_coursefisher_get_fields_items($field, $items = array('code' => 2, 'description' => 3)) {
    $result = array();
    if (!is_array($field)) {
        $fields = array($field);
    } else {
        $fields = $field;
    }

    foreach ($fields as $element) {
        preg_match('/^((.+)\=\>)?(.+)?$/', $element, $matches);
        $item = new stdClass();
        foreach ($items as $itemname => $itemid) {
            if (!empty($matches) && !empty($matches[$itemid])) {
                $item->$itemname = $matches[$itemid];
            }
        }
        if (count((array)$item)) {
            if (count($items) == 1) {
                reset($items);
                $result[] = $item->{key($items)};
            } else {
                $result[] = $item;
            }
        }
    }

    if (!is_array($field)) {
        if (!empty($result)) {
            return $result[0];
        } else {
            return null;
        }
    } else {
        return $result;
    }
}

function local_coursefisher_get_fields_description($field) {
    return local_coursefisher_get_fields_items($field, array('description' => 3));
}

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

function local_coursefisher_add_metacourses($course, $metacourseids = array()) {
    global $CFG;

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

function local_coursefisher_get_coursehashes($courses) {
    global $CFG;

    // Generate courses hash.
    $hashedcourses = array();
    if (!empty($courses)) {
        $courseshortnamepattern = get_config('local_coursefisher', 'course_shortname');
        $coursecodepattern = get_config('local_coursefisher', 'course_code');
        $fieldlevelpattern = get_config('local_coursefisher', 'fieldlevel');
        foreach ($courses as $i => $course) {
            $courseidnumber = '';
            $courseshortname = local_coursefisher_format_fields($courseshortnamepattern, $course);
            if (!empty($coursecodepattern)) {
                $courseidnumber = local_coursefisher_format_fields($coursecodepattern, $course);
            }
            $coursecode = !empty($courseidnumber) ? $courseidnumber : $courseshortname;

            $fieldlist = local_coursefisher_format_fields($fieldlevelpattern, $course);
            $categories = local_coursefisher_get_fields_description(array_filter(explode("\n", $fieldlist)));
            $coursepath = implode(' / ', $categories);
            $coursehash = md5($coursepath.' / '.$coursecode);
            $hashedcourses[$coursehash] = $course;
        }
    }
    return $hashedcourses;
}

function local_coursefisher_get_groupcourses($courses, $selectedcoursehash, $coursedata) {
    global $CFG, $DB;

    $groupcourses = array();

    $selectedcourse = $courses[$selectedcoursehash];

    $coursedata->exists = false;

    $firstcoursematch = null;
    $othercoursesmatch = null;

    $coursegrouppattern = get_config('local_coursefisher', 'course_group');
    if (!empty($coursegrouppattern)) {
        $courseshortnamepattern = get_config('local_coursefisher', 'course_shortname');
        $coursefullnamepattern = get_config('local_coursefisher', 'course_fullname');
        $coursecodepattern = get_config('local_coursefisher', 'course_code');
        $fieldlevelpattern = get_config('local_coursefisher', 'fieldlevel');
        $firstcoursematch = substr($coursegrouppattern, strpos($coursegrouppattern, "=") + 1);
        $othercoursesmatch = substr($coursegrouppattern, 0, strpos($coursegrouppattern, "="));

        /* Search for course group leader and members */
        $firstcourseid = local_coursefisher_format_fields($firstcoursematch, $selectedcourse);
        $othercourseid = local_coursefisher_format_fields($othercoursesmatch, $selectedcourse);

        if (!empty($othercourseid)) {
            /* Search for firstcourse */
            foreach ($courses as $coursehash => $course) {
                if ($othercourseid == local_coursefisher_format_fields($firstcoursematch, $course)) {
                    /* Found firstcourse match */
                    $firstcoursedata = new stdClass();
                    $firstcoursedata->idnumber = '';
                    $firstcoursedata->shortname = local_coursefisher_format_fields($courseshortnamepattern, $course);
                    if (!empty($coursecodepattern)) {
                        $firstcoursedata->idnumber = local_coursefisher_format_fields($coursecodepattern, $course);
                    }
                    $firstcoursedata->code = $firstcoursedata->shortname;
                    if (!empty($firstcoursedata->idnumber)) {
                        $firstcoursedata->code = $firstcoursedata->idnumber;
                    }

                    $categories = array_filter(explode("\n", local_coursefisher_format_fields($fieldlevelpattern, $course)));
                    $categoriesdescriptions = local_coursefisher_get_fields_description($categories);
                    $firstcoursedata->path = implode(' / ', $categoriesdescriptions);
                    $firstcoursedata->fullname = local_coursefisher_format_fields($coursefullnamepattern, $course);
                    $firstcoursedata->hash = $coursehash;
                    $firstcoursedata->exists = false;
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
                    $firstcourseid = local_coursefisher_format_fields($firstcoursematch, $course);
                }
            }
        } else {
            $groupcourses[$selectedcoursehash] = $coursedata;
        }
        if ((count($groupcourses) == 1) && !empty($firstcourseid)) {
            /* Search for othercourses */
            foreach ($courses as $coursehash => $course) {
                if ($firstcourseid == local_coursefisher_format_fields($othercoursesmatch, $course)) {
                    /* Found firstcourse match */
                    $othercoursedata = new stdClass();
                    $othercoursedata->idnumber = '';
                    $othercoursedata->shortname = local_coursefisher_format_fields($courseshortnamepattern, $course);
                    if (!empty($coursecodepattern)) {
                        $othercoursedata->idnumber = local_coursefisher_format_fields($coursecodepattern, $course);
                    }

                    $othercoursedata->code = $othercoursedata->shortname;
                    if (!empty($othercoursedata->idnumber)) {
                        $othercoursedata->code = $othercoursedata->idnumber;
                    }

                    $categories = array_filter(explode("\n", local_coursefisher_format_fields($fieldlevelpattern, $course)));
                    $categoriesdescriptions = local_coursefisher_get_fields_description($categories);
                    $othercoursedata->path = implode(' / ', $categoriesdescriptions);
                    $othercoursedata->fullname = local_coursefisher_format_fields($coursefullnamepattern, $course);
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

function local_coursefisher_enabled_user($context) {
    global $USER, $DB;

    $enabled = false;
    $userfield = get_config('local_coursefisher', 'userfield');
    $filterconfigured = (isset($userfield) && !empty($userfield));
    if ($filterconfigured && !has_capability('local/coursefisher:addallcourses', $context)) {

        $matchvalue = get_config('local_coursefisher', 'matchvalue');
        if (isset($matchvalue) && !empty($matchvalue)) {

            $userfieldvalue = '';
            $customfields = $DB->get_records('user_info_field');
            if (!empty($customfields)) {
                foreach ($customfields as $customfield) {
                    if ($customfield->shortname == $userfield) {
                        if (isset($USER->profile[$customfield->shortname]) && !empty($USER->profile[$customfield->shortname])) {
                            $userfieldvalue = $USER->profile[$customfield->shortname];
                        }
                    }
                }
            }
            if (empty($userfieldvalue)) {
                if (isset($USER->{$userfield})) {
                    $userfieldvalue = $USER->{$userfield};
                }
            }

            switch (get_config('local_coursefisher', 'operator')) {
                case 'contains':
                    if (mb_strpos($userfieldvalue, $matchvalue) !== false) {
                        $enabled = true;
                    }
                break;
                case 'doesnotcontains':
                    if (mb_strpos($userfieldvalue, $matchvalue) === false) {
                        $enabled = true;
                    }
                break;
                case 'isequalto':
                    if ($matchvalue == $userfieldvalue) {
                        $enabled = true;
                    }
                break;
                case 'isnotequalto':
                    if ($matchvalue != $userfieldvalue) {
                        $enabled = true;
                    }
                break;
                case 'startswith':
                    if (mb_ereg_match('^'.$matchvalue, $userfieldvalue) !== false) {
                        $enabled = true;
                    }
                break;
                case 'endswith':
                    if (mb_ereg($matchvalue.'$', $userfield) !== false) {
                        $enabled = true;
                    }
                break;
            }
            $display = get_config('local_coursefisher', 'display');
            if (!empty($display) && ($display == 'hidden')) {
                $enabled = !$enabled;
            }
        }
    } else {
        $enabled = true;
    }
    return $enabled;
}

