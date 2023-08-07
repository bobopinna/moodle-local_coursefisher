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
 * Course fisher Test page
 *
 * @package    local_coursefisher
 * @copyright  2014 Diego Fantoma
 * @copyright  2022 Roberto Pinna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once(__DIR__ . '/../locallib.php');

$confurl = new moodle_url('/admin/settings.php?section=local_coursefisher_backend');
$baseurl = new moodle_url('/local/coursefisher/backend/test.php');
$PAGE->set_url($baseurl);

require_login();

$systemcontext = context_system::instance();
require_capability('moodle/site:config', $systemcontext);

$PAGE->set_context($systemcontext);

$PAGE->set_pagelayout('standard');


$backendtestpagestr = get_string('backendtestpage', 'local_coursefisher');
$PAGE->set_title($backendtestpagestr);
$PAGE->set_heading($backendtestpagestr);
$PAGE->navbar->add(get_string('local'));
$PAGE->navbar->add(get_string('pluginname', 'local_coursefisher'));
$PAGE->navbar->add($backendtestpagestr, $baseurl);
echo $OUTPUT->header();

$backendname = get_config('local_coursefisher', 'backend');
if (!empty($backendname)) {
    $backendclassname = '\coursefisherbackend_' . $backendname . '\backend';
    if (class_exists($backendclassname)) {
        $backend = new $backendclassname();
        if ($backend->init()) {
            if ($backend->check_settings() !== false) {
                $backenddata = $backend->get_data(true);
                if (!empty($backenddata)) {
                    echo '<table>' . "\n";
                    $first = true;
                    foreach ($backenddata as $row) {
                        if (!empty($row)) {
                            if ($first) {
                                echo '<tr>';
                                foreach ($row as $key => $field) {
                                    echo '<th>' . format_string($key) . '</th>';
                                }
                                echo '</tr>' . "\n";
                                $first = false;
                            }
                            echo '<tr>';
                            foreach ($row as $field) {
                                echo '<td>' . format_string($field) . '</td>';
                            }
                            echo '</tr>' . "\n";
                        }
                    }
                    echo '</table>' . "\n";
                } else {
                    $errorstr = get_string('backendemptydata', 'local_coursefisher');
                    throw new moodle_exception($errorstr, 'local_coursefisher', $confurl);
                }
            } else {
                $errorstr = get_string('backendconfigerror', 'local_coursefisher');
                throw new moodle_exception($errorstr, 'local_coursefisher', $confurl);
            }
        } else {
            $errorstr = get_string('backendfailure', 'local_coursefisher');
            throw new moodle_exception($errorstr, 'local_coursefisher', $confurl, $error);
        }
    } else {
        $errorstr = get_string('backendnotinstalled', 'local_coursefisher');
        throw new moodle_exception($errorstr, 'local_coursefisher', $confurl, $error);
    }
} else {
    $errorstr = get_string('backendnotset', 'local_coursefisher');
    throw new moodle_exception($errorstr, 'local_coursefisher', $confurl, $error);
}

notice(get_string('backendready', 'local_coursefisher'), $confurl);
echo $OUTPUT->footer();
