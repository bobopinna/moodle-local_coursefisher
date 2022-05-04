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
 * Courses autocreation task.
 *
 * @package   local_coursefisher
 * @copyright 2022 Roberto Pinna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursefisher\task;

/**
 * Courses autocreation task.
 *
 * @package   local_coursefisher
 * @copyright 2022 Roberto Pinna
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class courses_autocreation extends \core\task\scheduled_task {

    /**
     * Name for this task.
     *
     * @return string
     */
    public function get_name() {
        return get_string('coursesautocreationtask', 'local_coursefisher');
    }

    /**
     * Run task for automatically create courses from backend data.
     */
    public function execute() {
        global $DB, $CFG;

        require_once($CFG->dirroot . '/local/coursefisher/locallib.php');

        $config = get_config('local_coursefisher');

        $backendclassname = '\coursefisherbackend_' . $config->backend . '\backend';
        if (class_exists($backendclassname)) {
            $backend = new $backendclassname();

            if (!empty($config->autocreation)) {
                mtrace('Processing courses autocreation...');
                $allcourses = \local_coursefisher_get_coursehashes($backend->get_data(true));

                if (!empty($allcourses)) {
                    foreach ($allcourses as $coursehash => $hashcourse) {
                        $metacourseids = array();
                        $firstcourse = null;

                        /* Create course */
                        $coursedata = new \stdClass();
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

                        $categoryitems = $backend->get_fields_items($categories);

                        $oldcourse = null;
                        if (!empty($coursedata->idnumber)) {
                            $oldcourse = $DB->get_record('course', array('idnumber' => $coursedata->idnumber));
                        } else {
                            $oldcourse = $DB->get_record('course', array('shortname' => $coursedata->shortname));
                        }

                        if (! $oldcourse) {
                            mtrace('... adding course ' . $coursedata->fullname);
                            if ($newcourse = \local_coursefisher_create_course($coursedata, 0, $categoryitems)) {
                                mtrace('done.');
                            } else {
                                mtrace(get_string('coursecreationerror', 'local_coursefisher'));
                            }
                        }
                    }
                }
            }
        }
    }

}
