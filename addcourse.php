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

require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/locallib.php');
require_once(__DIR__ . '/backend/lib.php');
require_once(__DIR__ . '/preferences_form.php');

$courseid = optional_param('courseid', '', PARAM_ALPHANUM);
$action = optional_param('action', '', PARAM_ALPHANUM);
$existent = optional_param('existent', '', PARAM_ALPHANUM);


$actions = get_config('local_coursefisher', 'actions');
if (isset($actions) && !empty($actions) && !in_array($action, explode(',', $actions))) {
    $action = '';
} else if ((!isset($actions) || empty($actions)) && ($action != 'view')) {
    $action = '';
}

$urlquery = array();
if (!empty($courseid)) {
    $urlquery['courseid'] = $courseid;
}

$url = new moodle_url('/local/coursefisher/addcourse.php', $urlquery);

$PAGE->set_url($url);
require_login();

$systemcontext = context_system::instance();
require_capability('local/coursefisher:addcourses', $systemcontext);

if (! $user = $DB->get_record('user', array('id' => $USER->id)) ) {
    error("No such user");
}


$PAGE->set_context($systemcontext);
$PAGE->set_pagelayout('incourse');

$fullname = fullname($user, has_capability('moodle/site:viewfullnames', $systemcontext));

// Print the page header.
$straddcourse = get_string('addmoodlecourse', 'local_coursefisher');

$PAGE->set_title($straddcourse);
$PAGE->set_heading($straddcourse);

$backendname = get_config('local_coursefisher', 'backend');
if (file_exists(__DIR__ . '/backend/'.$backendname.'/lib.php')) {
    require_once(__DIR__ . '/backend/'.$backendname.'/lib.php');

    $backendclassname = 'local_coursefisher_backend_'.$backendname;

    if (class_exists($backendclassname)) {

        $backend = new $backendclassname();

        $cancreateallcourses = has_capability('local/coursefisher:addallcourses', $systemcontext);
        $teachercourses = local_coursefisher_get_coursehashes($backend->get_data($cancreateallcourses));

        if (!empty($teachercourses)) {
            $courseshortnamepattern = get_config('local_coursefisher', 'course_shortname');
            $coursefullnamepattern = get_config('local_coursefisher', 'course_fullname');
            $coursecodepattern = get_config('local_coursefisher', 'course_code');
            $fieldlevelpattern = get_config('local_coursefisher', 'fieldlevel');

            $categorieslist = coursecat::make_categories_list();
            if (empty($courseid)) {
                echo $OUTPUT->header();
                echo html_writer::start_tag('div', array('class' => 'teachercourses'));

                $availablecourses = array();
                $existentcourses = '';
                foreach ($teachercourses as $coursehash => $teachercourse) {

                    $course = null;
                    $courseidnumber = '';
                    $courseshortname = $backend->format_fields($courseshortnamepattern, $teachercourse);
                    if (!empty($coursecodepattern)) {
                        $courseidnumber = $backend->format_fields($coursecodepattern, $teachercourse);
                        $course = $DB->get_record('course', array('idnumber' => $courseidnumber));
                    } else {
                        $course = $DB->get_record('course', array('shortname' => $courseshortname));
                    }

                    $coursegroup = '';
                    $groupcourses = local_coursefisher_get_groupcourses($teachercourses, $coursehash, $teachercourse);
                    if (count($groupcourses) > 1) {
                        reset($groupcourses);
                        $coursegroup = key($groupcourses);
                    }

                    if (! $course) {
                        $coursecode = !empty($courseidnumber) ? $courseidnumber : $courseshortname;

                        $fieldlist = $backend->format_fields($fieldlevelpattern, $teachercourse);
                        $categories = $backend->get_fields_description(array_filter(explode("\n", $fieldlist)));
                        $coursepath = implode(' / ', $categories);
                        $coursefullname = $backend->format_fields($coursefullnamepattern, $teachercourse);

                        $addcourseurl = new moodle_url('/local/coursefisher/addcourse.php', array('courseid' => $coursehash));
                        $link = html_writer::tag('a', get_string('addcourse', 'local_coursefisher'),
                                                 array('href' => $addcourseurl, 'class' => 'addcourselink'));
                        $coursecategories = html_writer::tag('span', $coursepath, array('class' => 'addcoursecategory'));
                        $coursename = html_writer::tag('span', $coursefullname, array('class' => 'addcoursename'));
                        $courselinkandtext = $link.$coursename.$coursecategories;
                        if (has_capability('local/coursefisher:addallcourses', $systemcontext)) {
                            $coursecodes = html_writer::tag('span', $coursecode.$courseshortname,
                                                            array('class' => 'addcoursecode'));
                            $availablecourses[$coursegroup][$coursehash] = html_writer::tag('li', $courselinkandtext.$coursecodes,
                                                                                            array('class' => 'addcourseitem'));
                        } else {
                            $availablecourses[$coursegroup][$coursehash] = html_writer::tag('li', $courselinkandtext,
                                                                                            array('class' => 'addcourseitem'));
                        }
                    } else {
                        $coursecode = $course->shortname;
                        if (isset($course->idnumber) && !empty($course->idnumber)) {
                            $coursecode = $course->idnumber;
                        }

                        $link = '';

                        $isalreadyteacher = is_enrolled(context_course::instance($course->id), $user, 'moodle/course:update', true);
                        $canaddall = has_capability('local/coursefisher:addallcourses', $systemcontext);
                        if (!$isalreadyteacher && !$canaddall) {
                            $courseurl = new moodle_url('/local/coursefisher/addcourse.php',
                                                        array('courseid' => $coursehash, 'action' => 'view'));
                            $link = html_writer::tag('a', get_string('enroltocourse', 'local_coursefisher'),
                                                     array('href' => $courseurl, 'class' => 'enroltocourselink'));
                            $coursecategories = html_writer::tag('span', $categorieslist[$course->category],
                                                                 array('class' => 'enroltocoursecategory'));
                            $coursename = html_writer::tag('span', $course->fullname, array('class' => 'enroltocoursename'));
                            $courselinkandtext = $link.$coursename.$coursecategories;
                            $existentcourses[$coursegroup][$coursehash] = html_writer::tag('li', $courseilinkandtext,
                                                                                           array('class' => 'enroltocourseitem'));
                        }
                    }
                }
                if (!empty($availablecourses)) {
                    echo html_writer::tag('h1', get_string('availablecourses', 'local_coursefisher'), array());
                    foreach ($availablecourses as $coursegroup => $availablegroupelements) {
                        echo html_writer::start_tag('ul', array('class' => 'availablecourses'));
                        if (count($availablegroupelements) > 1) {
                            if (!empty($coursegroup)) {
                                echo html_writer::start_tag('li', array('class' => 'availablecourses coursegroup'));
                                echo html_writer::tag('span', get_string('coursegroup', 'local_coursefisher'),
                                                      array('class' => 'coursegrouptitle'));
                                echo html_writer::start_tag('ul', array('class' => 'availablecourses'));
                                echo implode("\n", $availablegroupelements);
                                echo html_writer::end_tag('ul');
                                echo html_writer::end_tag('li');
                            } else {
                                echo implode("\n", $availablegroupelements);
                            }
                        } else {
                            echo current($availablegroupelements);
                        }
                        echo html_writer::end_tag('ul');
                    }
                }
                if (!empty($existentcourses)) {
                    echo html_writer::tag('h1', get_string('existentcourses', 'local_coursefisher'), array());

                    foreach ($existentcourses as $coursegroup => $existentgroupelements) {
                        echo html_writer::start_tag('ul', array('class' => 'existentcourses'));
                        if (count($existentgroupelements) > 1) {
                            if (!empty($coursegroup)) {
                                echo html_writer::start_tag('li', array('class' => 'existentcourses coursegroup'));
                                echo html_writer::tag('span', get_string('coursegroup', 'local_coursefisher'),
                                                      array('class' => 'coursegrouptitle'));
                                echo html_writer::start_tag('ul', array('class' => 'existentcourses'));
                                echo implode("\n", $existentgroupelements);
                                echo html_writer::end_tag('ul');
                                echo html_writer::end_tag('li');
                            } else {
                                echo implode("\n", $existentgroupelements);
                            }
                        } else {
                            echo current($existentgroupelements);
                        }
                        echo html_writer::end_tag('ul');
                    }
                }
                if (empty($availablecourses) && empty($existentcourses)) {
                    notice(get_string('nocourseavailable', 'local_coursefisher'), new moodle_url('/index.php'));
                }

                echo html_writer::end_tag('div');
                echo $OUTPUT->footer();
            } else {
                $coursehashes = str_split($courseid, strlen(md5('coursehash')));
                $metacourseids = array();
                $firstcourse = null;
                $groupcourses = array();

                $coursesummarypattern = get_config('local_coursefisher', 'course_summary');
                $sectionzeronamepattern = get_config('local_coursefisher', 'sectionzero_name');
                $edulinkpattern = get_config('local_coursefisher', 'educationaloffer_link');
                $templatepattern = get_config('local_coursefisher', 'course_template');
                $emailconditionpattern = get_config('local_coursefisher', 'email_condition');
                $linkedcategorypattern = get_config('local_coursefisher', 'linked_course_category');
                $linktypepattern = get_config('local_coursefisher', 'linktype');

                foreach ($coursehashes as $coursehash) {
                    if (isset($teachercourses[$coursehash])) {
                        $hashcourse = $teachercourses[$coursehash];

                        $coursedata = new stdClass();
                        $coursedata->idnumber = '';
                        $coursedata->shortname = $backend->format_fields($courseshortnamepattern, $hashcourse);
                        if (isset($coursecodepattern) && !empty($coursecodepattern)) {
                            $coursedata->idnumber = $backend->format_fields($coursecodepattern, $hashcourse);
                        }
                        $coursedata->code = !empty($coursedata->idnumber) ? $coursedata->idnumber : $coursedata->shortname;
                        $categories = array_filter(explode("\n",
                                                   $backend->format_fields($fieldlevelpattern, $hashcourse)));
                        $categoriesdescriptions = $backend->get_fields_description($categories);
                        $coursedata->path = implode(' / ', $categoriesdescriptions);
                        $coursedata->fullname = $backend->format_fields($coursefullnamepattern, $hashcourse);

                        $userid = $USER->id;
                        if (has_capability('local/coursefisher:addallcourses', $systemcontext)) {
                            $userid = null;
                        }
                        if (!empty($action)) {
                            /* Create course */
                            $coursedata->summary = '';
                            if (!empty($coursesummarypattern)) {
                                $coursedata->summary = $backend->format_fields($coursesummarypattern, $hashcourse);
                            }

                            $coursedata->sectionzero = '';
                            if (!empty($sectionzeronamepattern)) {
                                $coursedata->sectionzero = $backend->format_fields($sectionzeronamepattern, $hashcourse);
                            }

                            $coursedata->educationalofferurl = '';
                            if (!empty($edulinkpattern)) {
                                $coursedata->educationalofferurl = $backend->format_fields($edulinkpattern, $hashcourse);
                            }

                            $coursedata->templateshortname = '';
                            if (!empty($templatepattern)) {
                                $coursedata->templateshortname = $backend->format_fields($templatepattern, $hashcourse);
                            }

                            $coursedata->notifycreation = false;
                            if (!empty($emailconditionpattern)) {
                                $parser = $backend->get_parser();
                                $objects = $parser->substitute_objects($emailconditionpattern, false);
                                $coursedata->notifycreation = $parser->eval_record($objects, (array)$hashcourse);
                            }

                            if ($firstcourse !== null && isset($linkedcategorypattern) && !empty($linkedcategorypattern)) {
                                $categories[] = $backend->format_fields($linkedcategorypattern, $hashcourse);
                            }

                            if (!empty($coursedata->idnumber)) {
                                $oldcourse = $DB->get_record('course', array('idnumber' => $coursedata->idnumber));
                            } else {
                                $oldcourse = $DB->get_record('course', array('shortname' => $coursedata->shortname));
                            }

                            $categoryitems = $backend->get_fields_items($categories);
                            if ($newcourse = local_coursefisher_create_course($coursedata, $userid, $categoryitems,
                                                                              $firstcourse, $existent)) {
                                if ($firstcourse === null) {
                                    $firstcourse = clone($newcourse);
                                } else if (!isset($linktypepattern) || ($linktypepattern == 'meta')) {
                                    $metacourseids[] = $newcourse->id;
                                }
                            } else {
                                notice(get_string('coursecreationerror', 'local_coursefisher'), new moodle_url('/index.php'));
                            }

                            if ($oldcourse) {
                                if ($existent == 'join') {
                                    if ($firstcourse !== null) {
                                        local_coursefisher_add_linkedcourse_url($oldcourse, $firstcourse);
                                        if (!isset($linktypepattern) || ($linktypepattern == 'meta')) {
                                            $metacourseids[] = $oldcourse->id;
                                        }
                                    } else {
                                        $firstcourse = clone($oldcourse);
                                    }
                                }
                            }
                        } else if (count($groupcourses) == 0) {
                            // Get teacher grouped courses.
                            $groupcourses = local_coursefisher_get_groupcourses($teachercourses, $coursehash, $coursedata);
                        }
                    }
                }

                if (!empty($action)) {
                    if ($firstcourse !== null) {
                        if (!empty($metacourseids) && (!isset($linktypepattern) || ($linktypepattern == 'meta'))) {
                             local_coursefisher_add_metacourses($firstcourse, $metacourseids);
                        }
                        switch ($action) {
                            case 'view':
                            case 'edit':
                                redirect(new moodle_url('/course/'.$action.'.php', array('id' => $firstcourse->id)));
                            break;
                            case 'import':
                                redirect(new moodle_url('/backup/'.$action.'.php', array('id' => $firstcourse->id)));
                            break;
                        }
                    } else {
                        print_error('Course hash does not match for course access');
                    }
                } else if (!empty($groupcourses)) {
                    $preferences = new preferences_form(null, array('coursehash' => $coursehash, 'groupcourses' => $groupcourses));
                    echo $OUTPUT->header();
                    echo html_writer::start_tag('div', array('class' => 'teachercourses'));
                    $preferences->display();
                    echo html_writer::end_tag('div');
                    echo $OUTPUT->footer();
                } else {
                    print_error('Course hash does not match for preferences page');
                }
            }
        } else {
             notice(get_string('nocourseavailable', 'local_coursefisher'), new moodle_url('/index.php'));
        }
    }
}
