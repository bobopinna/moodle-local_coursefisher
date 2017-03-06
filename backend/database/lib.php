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
 * @subpackage coursefisherbackend_database
 * @copyright  2014 Roberto Pinna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir .'/adodb/adodb.inc.php');
require_once(__DIR__.'/../lib.php');

class local_coursefisher_backend_database extends local_coursefisher_backend {

    public function __construct() {
        $this->name = 'database';
    }

    /**
     * Reads informations for teacher courses from external database,
     * then returns it in an array of objects.
     *
     * @return array
     */
    public function get_data($alldata=false) {
        $result = array();

        $parameters = get_config('local_coursefisher', 'parameters');
        if (!empty($parameters)) {
            $sql = preg_replace_callback('/\[\%(\w+)\%\]/', 'parent::get_user_field', $parameters);
            if ($alldata) {
                $sql = preg_replace('/\[\%(\w+)\%\]/', '%', $parameters);
            }
            if ($coursesdb = $this->db_init()) {
                $rs = $coursesdb->Execute($sql);
                if (!$rs) {
                    $coursesdb->Close();
                    debugging(get_string('cantgetdata', 'coursefisherbackend_database'));
                    debugging($sql);
                    return false;
                } else {
                    if (!$rs->EOF) {
                        while ($fieldsobj = $rs->FetchRow()) {
                            $fieldsobj = (object)array_change_key_case((array)$fieldsobj , CASE_LOWER);
                            $row = new stdClass();
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

        return $result;
    }

    private function db_init() {

        $db = false;

        $locator = get_config('local_coursefisher', 'locator');
        if (!empty($locator)) {
            // Connect to the external database (forcing new connection).
            $db = ADONewConnection($locator);
            if ($db) {
                $db->SetFetchMode(ADODB_FETCH_ASSOC);
            }
        }

        return $db;
    }

}
