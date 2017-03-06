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
 * Course fisher course redirect.
 *
 * @package    local
 * @subpackage coursefisher
 * @copyright  2014 Roberto Pinna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__. '/../../config.php');

$coursecode = optional_param('course', '', PARAM_TEXT);

$url = new moodle_url('/local/coursefisher/index.php', array('course' => $coursecode));

$PAGE->set_url($url);

$systemcontext = context_system::instance();

$PAGE->set_context($systemcontext);

if (get_config('local_coursefisher', 'course_code')) {
    $course = $DB->get_record('course', array('idnumber' => $coursecode));
} else {
    $course = $DB->get_record('course', array('shortname' => $coursecode));
}

if ($course) {
    redirect(new moodle_url('/course/view.php', array('id' => $course->id)));
} else {
    print_error(get_string('coursenotfound', 'local_coursefisher'));
}
