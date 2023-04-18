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
 * @copyright 2014 and above Roberto Pinna, Diego Fantoma, Angelo Calò
 * @copyright 2016 and above Francesco Carbone
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/locallib.php');
require_once(__DIR__ . '/preferences_form.php');

set_time_limit(300);

$courseid = optional_param('courseid', '', PARAM_ALPHANUM);
$action = optional_param('action', '', PARAM_ALPHANUM);
$existent = optional_param('existent', '', PARAM_ALPHANUM);

$config = get_config('local_coursefisher');
if (isset($config->actions) && !empty($config->actions) && !in_array($action, explode(',', $config->actions))) {
    $action = '';
} else if ((!isset($config->actions) || empty($config->actions)) && ($action != 'view')) {
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
$straddcourse = get_string('addcourses', 'local_coursefisher');

$PAGE->set_title($straddcourse);
$PAGE->set_heading($straddcourse);

if (!local_coursefisher_enabled_user($systemcontext)) {
    notice(get_string('notenabled', 'local_coursefisher'), new moodle_url('/index.php'));
    exit;
}

$backendclassname = '';
if (isset($config->backend) && !empty($config->backend)) {
    $backendclassname = '\coursefisherbackend_' . $config->backend . '\backend';
}

if (!empty($backendclassname) && class_exists($backendclassname)) {

    $backend = new $backendclassname();

    $cancreateallcourses = has_capability('local/coursefisher:addallcourses', $systemcontext);
    $teachercourses = local_coursefisher_get_coursehashes($backend->get_data($cancreateallcourses));

    if (!empty($teachercourses)) {
        if (class_exists('core_course_category')) {
            $categorieslist = core_course_category::make_categories_list();
        } else {
            require_once($CFG->libdir. '/coursecatlib.php');
            $categorieslist = coursecat::make_categories_list();
        }
        if (empty($courseid)) {
            echo $OUTPUT->header();
            echo html_writer::start_tag('div', array('class' => 'teachercourses'));

            $availablecourses = array();
            $existentcourses = array();
            foreach ($teachercourses as $coursehash => $teachercourse) {

                $course = null;
                $courseidnumber = '';
                $courseshortname = $backend->format_fields($config->course_shortname, $teachercourse);
                if (!empty($config->course_code)) {
                    $courseidnumber = $backend->format_fields($config->course_code, $teachercourse);
                    $course = $DB->get_record('course', array('idnumber' => $courseidnumber));
                } else {
                    $course = $DB->get_record('course', array('shortname' => $courseshortname));
                }

                $coursegroup = 0;
                $groupcourses = local_coursefisher_get_groupcourses($teachercourses, $coursehash, $teachercourse);
                if (count($groupcourses) > 1) {
                    reset($groupcourses);
                    $coursegroup = key($groupcourses);
                }

                if (! $course) {
                    $coursecode = !empty($courseidnumber) ? $courseidnumber : $courseshortname;

                    $fieldlist = $backend->format_fields($config->fieldlevel, $teachercourse);
                    $categories = $backend->get_fields_description(array_filter(explode("\n", $fieldlist)));
                    $coursepath = implode(' / ', $categories);
                    $coursefullname = $backend->format_fields($config->course_fullname, $teachercourse);

                    $addcourseurl = new moodle_url('/local/coursefisher/addcourse.php', array('courseid' => $coursehash));
                    $link = '';
                    if (empty($config->forceonlygroups) || count($groupcourses) == 1) {
                        $link = html_writer::tag('a', get_string('addcourse', 'local_coursefisher'),
                                                 array('href' => $addcourseurl, 'class' => 'addcourselink btn btn-primary'));
                    }
                    $coursecategories = html_writer::tag('span', $coursepath, array('class' => 'addcoursecategory'));
                    $coursename = html_writer::tag('span', $coursefullname, array('class' => 'addcoursename'));
                    $courselinkandtext = $link.$coursename.$coursecategories;
                    $coursecodes = '';
                    if (has_capability('local/coursefisher:addallcourses', $systemcontext)) {
                        $coursecodes = html_writer::tag('span', $coursecode.', '.$courseshortname,
                                                        array('class' => 'addcoursecode'));
                    }
                    $coursehtml = html_writer::tag('li', $courselinkandtext.$coursecodes, array('class' => 'addcourseitem'));

                    $isfirst = isset($groupcourses[$coursehash]->first) && !empty($groupcourses[$coursehash]->first);
                    if ($isfirst && isset($availablecourses[$coursegroup])) {
                        $availablecourses[$coursegroup] = array_merge(array($coursehash => $coursehtml),
                                                                      $availablecourses[$coursegroup]);
                    } else {
                        if (!isset($availablecourses[$coursegroup])) {
                            $availablecourses[$coursegroup] = array();
                        }
                        $availablecourses[$coursegroup][$coursehash] = $coursehtml;
                    }
                } else {
                    $coursecode = $course->shortname;
                    if (isset($course->idnumber) && !empty($course->idnumber)) {
                        $coursecode = $course->idnumber;
                    }

                    $alreadyteacher = is_enrolled(context_course::instance($course->id), $user, 'moodle/course:update', true);
                    $canaddall = has_capability('local/coursefisher:addallcourses', $systemcontext);
                    $isfirst = isset($groupcourses[$coursehash]->first) && !empty($groupcourses[$coursehash]->first);
                    $link = '';
                    if (!$alreadyteacher && !$canaddall && (empty($config->forceonlygroups) || $isfirst)) {
                        $courseurl = new moodle_url('/local/coursefisher/addcourse.php',
                                                    array('courseid' => $coursehash, 'action' => 'view'));
                        $link = html_writer::tag('a', get_string('enroltocourse', 'local_coursefisher'),
                                                 array('href' => $courseurl, 'class' => 'enroltocourselink'));
                        $coursecategories = html_writer::tag('span', $categorieslist[$course->category],
                                                             array('class' => 'enroltocoursecategory'));
                        $coursename = html_writer::tag('span', $course->fullname, array('class' => 'enroltocoursename'));
                        $courselinkandtext = $link.$coursename.$coursecategories;

                        if (!isset($existentcourses[$coursegroup])) {
                            $existentcourses[$coursegroup] = array();
                        }
                        $existentcourses[$coursegroup][$coursehash] = html_writer::tag('li', $courselinkandtext,
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
                            if (empty($config->forceonlygroups)) {
                                echo html_writer::tag('span', get_string('coursegroup', 'local_coursefisher'),
                                                      array('class' => 'coursegrouptitle'));
                            } else {
                                $grouphash = implode('', array_keys($availablegroupelements));
                                $groupurl = new moodle_url('/local/coursefisher/addcourse.php', array('courseid' => $grouphash));
                                echo html_writer::tag('a', get_string('addcoursegroup', 'local_coursefisher'),
                                                      array('href' => $groupurl, 'class' => 'addcoursegrouplink'));
                            }
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
                $extra = '';
                if (!empty($config->course_helplink)) {
                    $url = new moodle_url($config->course_helplink, array());
                    $extra = '<br />' . html_writer::tag('a', get_string('help', 'local_coursefisher'), array('href' => $url));
                }
                notice(get_string('nocourseavailable', 'local_coursefisher') . $extra, new moodle_url('/index.php'));
            }

            echo html_writer::end_tag('div');
            echo $OUTPUT->footer();
        } else {
            $coursehashes = str_split($courseid, strlen(md5('coursehash')));
            $metacourseids = array();
            $firstcourse = null;
            $groupcourses = array();

            foreach ($coursehashes as $coursehash) {
                if (isset($teachercourses[$coursehash])) {
                    $hashcourse = $teachercourses[$coursehash];

                    $coursedata = new stdClass();
                    $coursedata->idnumber = '';
                    $coursedata->shortname = $backend->format_fields($config->course_shortname, $hashcourse);
                    if (isset($config->course_code) && !empty($config->course_code)) {
                        $coursedata->idnumber = $backend->format_fields($config->course_code, $hashcourse);
                    }
                    $coursedata->code = !empty($coursedata->idnumber) ? $coursedata->idnumber : $coursedata->shortname;
                    $categories = array_filter(explode("\n",
                                               $backend->format_fields($config->fieldlevel, $hashcourse)));
                    $categoriesdescriptions = $backend->get_fields_description($categories);
                    $coursedata->path = implode(' / ', $categoriesdescriptions);
                    $coursedata->fullname = $backend->format_fields($config->course_fullname, $hashcourse);

                    $userid = $USER->id;
                    if (has_capability('local/coursefisher:addallcourses', $systemcontext)) {
                        $userid = null;
                    }
                    if (!empty($action)) {
                        /* Create course */
                        $coursedata->summary = '';
                        if (!empty($config->course_summary)) {
                            $coursedata->summary = $backend->format_fields($config->course_summary, $hashcourse);
                        }

                        $coursedata->sectionzero = '';
                        if (!empty($config->sectionzero_name)) {
                            $coursedata->sectionzero = $backend->format_fields($config->sectionzero_name, $hashcourse);
                        }

                        $coursedata->educationalofferurl = '';
                        if (!empty($config->educationaloffer_link)) {
                            $coursedata->educationalofferurl = $backend->format_fields($config->educationaloffer_link, $hashcourse);
                        }

                        $coursedata->templateshortname = '';
                        if (!empty($config->course_template)) {
                            $coursedata->templateshortname = $backend->format_fields($config->course_template, $hashcourse);
                        }

                        $coursedata->notifycreation = false;
                        if (!empty($config->email_condition)) {
                            $expression = $this->format_fields($config->email_condition, $hashcourse);

                            $filter = new \local_coursefisher\evaluator();
                            $filterpass = $filter->evaluate($expression);
                            if ($filterpass === false) {
                                debugging($filter->last_error);
                            }
                            $coursedata->notifycreation = $filterpass == 1 ? true : false;
                        }

                        if ($firstcourse !== null
                                && isset($config->linked_course_category) && !empty($config->linked_course_category)) {
                            $categories[] = $backend->format_fields($config->linked_course_category, $hashcourse);
                        }

                        if (!empty($coursedata->idnumber)) {
                            $oldcourse = $DB->get_record('course', array('idnumber' => $coursedata->idnumber));
                        } else {
                            $oldcourse = $DB->get_record('course', array('shortname' => $coursedata->shortname));
                        }

                        $categoryitems = $backend->get_fields_items($categories);
                        if ($newcourse = local_coursefisher_create_course($coursedata, $userid, $categoryitems, $firstcourse)) {
                            if ($firstcourse === null) {
                                $firstcourse = clone($newcourse);
                            } else if (!isset($config->linktype) || ($config->linktype == 'meta')) {
                                if (($newcourse->id != $firstcourse->id) && !in_array($newcourse->id, $metacourseids)) {
                                    $metacourseids[] = $newcourse->id;
                                }
                            }
                        } else {
                            notice(get_string('coursecreationerror', 'local_coursefisher'), new moodle_url('/index.php'));
                        }

                        if ($oldcourse) {
                            if ($existent == 'join') {
                                if ($firstcourse !== null) {
                                    local_coursefisher_add_linkedcourse_url($oldcourse, $firstcourse);
                                    if (!isset($config->linktype) || ($config->linktype == 'meta')) {
                                        if (($oldcourse->id != $firstcourse->id) && !in_array($oldcourse->id, $metacourseids)) {
                                            $metacourseids[] = $oldcourse->id;
                                        }
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
                    if (!empty($metacourseids) && (!isset($config->linktype) || ($config->linktype == 'meta'))) {
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
                }
            } else if (!empty($groupcourses)) {
                $preferences = new preferences_form(null, array('coursehash' => $coursehash, 'groupcourses' => $groupcourses));
                echo $OUTPUT->header();
                echo html_writer::start_tag('div', array('class' => 'teachercourses'));
                $preferences->display();
                echo html_writer::end_tag('div');
                echo $OUTPUT->footer();
            }
        }
    } else {
        $extra = '';
        if (!empty($config->course_helplink)) {
            $url = new moodle_url($config->course_helplink, array());
            $extra = '<br />' . html_writer::tag('a', get_string('help', 'local_coursefisher'), array('href' => $url));
        }
        notice(get_string('nocourseavailable', 'local_coursefisher') . $extra, new moodle_url('/index.php'));
    }
}
