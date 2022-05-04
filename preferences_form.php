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
 * Course fisher - Course creation preferences form.
 *
 * @package    local_coursefisher
 * @copyright  2016 Roberto Pinna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

/**
 * Course fisher - Course creation preferences form class.
 *
 * @package    local_coursefisher
 * @copyright  2016 Roberto Pinna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class preferences_form extends moodleform {

    /**
     * The preferences form definition.
     */
    public function definition() {
        $mform = $this->_form;

        $selectedcoursehash = $this->_customdata['coursehash'];
        $groupcourses = $this->_customdata['groupcourses'];

        if (!empty($groupcourses)) {
            $coursehashes = array_keys($groupcourses);

            $courseidchoices = array();
            $existscourse = false;

            $singletext = '';
            $grouptext = '';

            $hiddencoursehash = '';

            $addsinglecoursestr = get_string('addsinglecourse', 'local_coursefisher');
            $addcoursegroupstr = get_string('addcoursegroup', 'local_coursefisher');

            $groupsonly = get_config('local_coursefisher', 'forceonlygroups');
            if ((count($coursehashes) == 1) || empty($groupsonly)) {
                $coursecategories = html_writer::tag('span', $groupcourses[$selectedcoursehash]->path,
                                                      array('class' => 'addcoursecategory'));
                $coursename = html_writer::tag('span', $groupcourses[$selectedcoursehash]->fullname,
                                               array('class' => 'addcoursename'));
                $singletext .= html_writer::tag ('span', $coursename.$coursecategories, array('class' => 'singlecourse'));
                $courseidchoices[] = &$mform->createElement('radio', 'courseid', null, $addsinglecoursestr.$singletext,
                                                            $selectedcoursehash);
            }
            if (count($coursehashes) > 1) {
                $grouphash = implode('', $coursehashes);
                $grouptext .= html_writer::start_tag ('span', array('class' => 'groupcourses'));
                $first = true;
                foreach ($groupcourses as $groupcourse) {
                    $class = 'groupcourse';
                    $alertmessage = '';
                    if ($first) {
                        $class .= ' groupfirstcourse';
                        $first = false;
                    }
                    $coursecategories = html_writer::tag('span', $groupcourse->path, array('class' => 'addcoursecategory'));
                    $coursename = html_writer::tag('span', $groupcourse->fullname, array('class' => 'addcoursename'));
                    if (isset($groupcourse->exists) && $groupcourse->exists) {
                        $class .= ' existentcourse';
                        $alertmessage = html_writer::tag('span', get_string('existentcourse', 'local_coursefisher'),
                                                         array('class' => 'existentcourse'));
                        $existscourse = true;
                        $courseurl = new moodle_url('/course/view.php', array('id' => $groupcourse->id));
                        $courselink = html_writer::tag('a', $groupcourse->fullname,
                                                       array('href' => $courseurl, 'target' => '_blank'));
                        $coursename = html_writer::tag('span', $courselink, array('class' => 'addcoursename'));
                    }
                    $grouptext .= html_writer::tag ('span', $coursename.$alertmessage.$coursecategories,
                                                    array('class' => $class));
                }
                $grouptext .= html_writer::end_tag ('span');
                $courseidchoices[] = &$mform->createElement('radio', 'courseid', null, $addcoursegroupstr.$grouptext,
                                                            $grouphash);
            }
            if (count($courseidchoices) == 2) {
                $mform->addGroup($courseidchoices, 'coursegrp', get_string('choosewhatadd', 'local_coursefisher'),
                                 array(''), false);
                $mform->setDefault('courseid', $grouphash);
            } else {
                if (count($coursehashes) == 1) {
                    $mform->addElement('static', 'coursegrp', $addsinglecoursestr, $singletext);
                    $hiddencoursehash = $selectedcoursehash;
                } else {
                    $grouphash = implode('', $coursehashes);
                    $mform->addElement('static', 'coursegrp', $addcoursegroupstr, $grouptext);
                    $hiddencoursehash = $grouphash;
                }
                $mform->addElement('hidden', 'courseid',  $hiddencoursehash);
                $mform->setType('courseid',  PARAM_ALPHANUM);
            }

            $existentchoices = array();
            if ($existscourse) {
                $existentactions = array('join', 'separated');
                foreach ($existentactions as $existentaction) {
                    $existentchoices[] = &$mform->createElement('radio', 'existent', null, get_string($existentaction,
                                                                'local_coursefisher'), $existentaction);
                }
                if (!empty($existentchoices)) {
                    $mform->addGroup($existentchoices, 'exitentgrp', get_string('chooseexistsaction', 'local_coursefisher'),
                                     array(''), false);
                    $mform->disabledIf('exitentgrp', 'courseid', 'neq', $grouphash);
                }
            }

            $actionchoices = array();
            $actions = get_config('local_coursefisher', 'actions');
            if (!empty($actions)) {
                $permittedactions = explode(',', $actions);
                foreach ($permittedactions as $permittedaction) {
                    $actionchoices[] = &$mform->createElement('radio', 'action', null, get_string($permittedaction,
                                                              'local_coursefisher'), $permittedaction);
                }
                if (!empty($actionchoices)) {
                    $mform->addGroup($actionchoices, 'actiongrp', get_string('choosenextaction', 'local_coursefisher'),
                                     array(''), false);
                }
            }

            $manycourseid = (count($courseidchoices) > 1);
            $manyaction = (!empty($actionchoices) && (count($actionchoices) > 1));
            $manyexistentchoice = (!empty($existentchoices) && (count($existentchoices) > 1));
            if ($manycourseid || $manyaction || $manyexistentchoice) {
                // Normally you use add_action_buttons instead of this code.
                $mform->addElement('submit', 'submitbutton', get_string('execute', 'local_coursefisher'));
            } else if (!empty($actionchoices) && (count($actionchoices) == 1)) {
                redirect(new moodle_url('/local/coursefisher/addcourse.php',
                                        array('courseid' => $hiddencoursehash, 'action' => $permittedactions[0])));
            } else {
                redirect(new moodle_url('/local/coursefisher/addcourse.php',
                                        array('courseid' => $hiddencoursehash, 'action' => 'view')));
            }
        } else {
            redirect(new moodle_url('/local/coursefisher/addcourse.php'));
        }
    }
}
