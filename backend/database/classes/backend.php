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
 * Course fisher database backend
 *
 * @package coursefisherbackend_database
 * @copyright  2014 Roberto Pinna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace coursefisherbackend_database;

/**
 * Course fisher database backend
 *
 * @package coursefisherbackend_database
 * @copyright  2014 Roberto Pinna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backend extends \local_coursefisher\backend {

    /**
     * Constructor.
     */
    public function __construct() {
        global $CFG;

        require_once($CFG->libdir .'/adodb/adodb.inc.php');

        $this->name = 'database';
    }

    /**
     * Reads informations for teacher courses from external database, then returns it in an array of objects.
     *
     * @param boolean $alldata Return all data without query filtering
     *
     * @return array The courses data
     */
    public function get_data($alldata=false) {
        $result = array();

        if ($this->check_settings()) {
            $parameters = get_config('local_coursefisher', 'parameters');
            if (!empty($parameters)) {
                $sql = $this->format_fields($parameters);
                if ($alldata) {
                    $sql = preg_replace('/\[\%(.+)\%\]/', '%', $parameters);
                }
                if ($coursesdb = $this->db_init()) {
                    $rs = $coursesdb->Execute($sql);
                    if (!$rs) {
                        $coursesdb->Close();
                        debugging(get_string('cantgetdata', 'coursefisherbackend_database'));
                        debugging($sql);
                        debugging($coursesdb->errorMsg());
                        return false;
                    } else {
                        if (!$rs->EOF) {
                            while ($fieldsobj = $rs->FetchRow()) {
                                $fieldsobj = (object)array_change_key_case((array)$fieldsobj , CASE_LOWER);
                                $row = new \stdClass();
                                foreach ($fieldsobj as $name => $value) {
                                    if (mb_detect_encoding($value, mb_detect_order(), true) !== 'UTF-8') {
                                        $value = mb_convert_encoding($value, 'UTF-8');
                                    }
                                    $row->$name = format_string($value);
                                }
                                $result[] = $row;
                            }
                        }
                        $rs->Close();
                    }
                    $coursesdb->Close();
                } else {
                    debugging(get_string('cantconnect', 'coursefisherbackend_database'));
                    return false;
                }
            }
        } else {
            debugging(get_string('configerrors', 'coursefisherbackend_database'));
            return false;
        }

        return $result;
    }

    /**
     * Initialize database comunication.
     *
     * @return object The ADOdb connection object
     */
    private function db_init() {

        $db = false;

        $locator = get_config('local_coursefisher', 'locator');
        if (!empty($locator)) {
            // Connect to the external database (forcing new connection).
            try {
                $db = ADONewConnection($locator);
                if ($db) {
                    $db->SetFetchMode(ADODB_FETCH_ASSOC);
                }
            } catch (Exception $e) {
                debugging($e->getTraceAsString());
            }
        }

        return $db;
    }

}
