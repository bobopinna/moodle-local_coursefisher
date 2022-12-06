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
 * Course fisher lib
 *
 * @package    local_coursefisher
 * @copyright  2014 Roberto Pinna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/locallib.php');

/**
 * Extend Navigation block
 *
 * @param global_navigation $navigation Navigation
 * @return void
 */
function local_coursefisher_extend_navigation(global_navigation $navigation) {
    global $PAGE, $OUTPUT;

    if ($PAGE->pagelayout == 'mycourses') {
        $coursefisherlinks = local_coursefisher_links($PAGE->context);
        if (!empty($coursefisherlinks)) {
            $PAGE->add_header_action($OUTPUT->render_from_template('local_coursefisher/buttons', $coursefisherlinks));
        }
    }
}

/**
 * Extend Settings/Administration block with unenrol link to meta linked courses
 *
 * @param settings_navigation $nav Setting navigation
 * @param context $context Navigation context
 * @return void
 */
function local_coursefisher_extend_settings_navigation(settings_navigation $nav, context $context) {
    global $DB, $USER, $CFG;

    if ($context->contextlevel == CONTEXT_COURSE) {
        if ($instances = enrol_get_instances($context->instanceid, true)) {
            foreach ($instances as $instance) {
                if ($instance->enrol === 'meta') {
                    $metacoursecontext = context_course::instance($instance->customint1);
                    if (is_enrolled($metacoursecontext)) {
                        $metacourse = $DB->get_record('course', array('id' => $instance->customint1));
                        $activitytype = $DB->get_field('course_format_options', 'value',
                                                       array('courseid' => $instance->customint1, 'name' => 'activitytype'));
                        if (($metacourse->format == 'singleactivity') && ($activitytype == 'url')) {
                            if ($metacourseinstances = enrol_get_instances($metacoursecontext->instanceid, true)) {
                                foreach ($metacourseinstances as $metacourseinstance) {
                                    require_once($CFG->dirroot . '/enrol/' . $metacourseinstance->enrol . '/lib.php');
                                    $enrolclassname = 'enrol_' . $metacourseinstance->enrol . '_plugin';
                                    $enrol = new $enrolclassname();
                                    $unenrolink = $enrol->get_unenrolself_link($metacourseinstance);
                                    if ($unenrolink != null) {
                                        $query = array('enrolid' => $metacourseinstance->id, 'userid' => $USER->id);
                                        if ($userenrolment = $DB->get_record('user_enrolments', $query)) {
                                            $enrolledbefore = $userenrolment->timestart < time();
                                            $stillenrolled = ($userenrolment->timeend == 0) || ($userenrolment->timeend < time());
                                            if ($enrolledbefore && $stillenrolled) {
                                                $node = $nav->get('courseadmin');
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
    local_coursefisher_navigation($nav, $context);
}

/**
 * Add coursefisher navigation links
 *
 * @param object $nav navigation_node
 * @param context $context Navigation context
 * @return void
 */
function local_coursefisher_navigation($nav, $context) {
    global $PAGE;
    if (local_coursefisher_enabled_user($context)) {
        $coursefishertitle = get_config('local_coursefisher', 'title');
        if (empty($coursefishertitle)) {
            $coursefishertitle = get_string('pluginname', 'local_coursefisher');
        }
        $coursefishernav = $nav->prepend($coursefishertitle, null, navigation_node::TYPE_CONTAINER);

        // Open Course Fisher navigation tree in dashboard.
        $pageurl = $PAGE->url->get_path();
        if ($pageurl == '/my/index.php') {
            $coursefishernav->make_active();
        }

        $url = new moodle_url('/local/coursefisher/addcourse.php', array());
        $coursefishertitle = get_config('local_coursefisher', 'title');
        if (empty($coursefishertitle)) {
            $coursefishertitle = get_string('pluginname', 'local_coursefisher');
        }
        $addcoursestr = get_string('addcourse', 'local_coursefisher');
        $nodetype = navigation_node::NODETYPE_LEAF;
        $icon = new pix_icon('t/add', '');
        $node = $coursefishernav->add($addcoursestr, $url, $nodetype, $addcoursestr, 'coursefisher', $icon);

        $helplink = get_config('local_coursefisher', 'course_helplink');
        if (!empty($helplink)) {
            $helpstr = get_string('help', 'local_coursefisher');
            $url = new moodle_url($helplink, array());
            $nodetype = navigation_node::NODETYPE_LEAF;
            $icon = new pix_icon('help', '');
            $coursefishernav->add($helpstr, $url, $nodetype, $helpstr, 'helpcoursefisher', $icon);
        }
    }
}

/**
 * Get coursefisher links
 *
 * @param context $context Navigation context
 * @return array
 */
function local_coursefisher_links($context) {
    $coursefisherlinks = array();

    if (local_coursefisher_enabled_user($context)) {
        $coursefishertitle = get_config('local_coursefisher', 'title');
        if (empty($coursefishertitle)) {
            $coursefishertitle = get_string('pluginname', 'local_coursefisher');
        }
        $coursefisherlinks['coursefishertitle'] = $coursefishertitle;
        $coursefisherlinks['addcourseurl'] = new moodle_url('/local/coursefisher/addcourse.php', array());
        $helplink = get_config('local_coursefisher', 'course_helplink');
        if (!empty($helplink)) {
            $coursefisherlinks['helpurl'] = new moodle_url($helplink, array());
        }
    }
    return $coursefisherlinks;
}
