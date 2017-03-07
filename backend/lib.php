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
 * Course fisher backend class
 *
 * @package    local
 * @subpackage coursefisher
 * @copyright  2014 and above Diego Fantoma e Roberto Pinna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

class local_coursefisher_backend {

    public $name;

    private $error = '';

    public function __construct() {
        if (!is_subclass_of($this, 'local_coursefisher_backend')) {
            debugging('Woops, wrong class initialized');
            return false;
        }

        $backendname = get_config('local_coursefisher', 'backend');
        if (!$backendname == __CLASS__) {
            debugging('The name of the configured backend does not match the called class');
            return false;
        }

        $this->name = new lang_string('backend', 'coursefisher_'.$backendname);
    }

    public function init() {
        return true;
    }

    public function get_data($alldata = false) {
        return null;
    }

    public function __destruct() {
    }

    public function user_field_value($matches) {
        global $USER, $DB;

        if (isset($matches[1])) {
            $userfieldvalue = '';
            $customfields = $DB->get_records('user_info_field');
            if (!empty($customfields)) {
                foreach ($customfields as $customfield) {
                    if ($customfield->shortname == $matches[1]) {
                        if (isset($USER->profile[$customfield->shortname]) && !empty($USER->profile[$customfield->shortname])) {
                            $userfieldvalue = $USER->profile[$customfield->shortname];
                        }
                    }
                }
            }
            if (empty($userfieldvalue)) {
                if (isset($USER->{$matches[1]})) {
                    $userfieldvalue = $USER->{$matches[1]};
                }
            }
            return $userfieldvalue;
        }

        return null;
    }

    private function get_user_field($fieldname) {
        return user_field_value(array(1 => $fieldname));
    }

    public function check_settings() {
        $result = true;
        $fields = array();

        $settings = get_config('local_coursefisher');

        if (!empty($settings->fieldlist)) {
            $fields = array_flip(preg_split("/\n|\s/", trim($settings->fieldlist), -1, PREG_SPLIT_NO_EMPTY));
        }

        if (!empty($settings)) {
            foreach ($settings as $settingname => $settingvalue) {
                if ($found = preg_match_all('/\[\%(\w+)\%\]/', $settingvalue, $matches)) {
                    if (in_array($settingname, array('locator', 'parameters'))) {
                        for ($i = 1; $i < $found; $i++) {
                            if (get_user_field($matches[1][$i]) === null) {
                               debugging('User field "'.$matches[1][$i].'" not existent');
                               $result = false;
                            }
                        }
                    } else {
                        if (!empty($fields)) {
                            for ($i = 1; $i < $found; $i++) {
                                if (!in_array($matches[1][$i], $fields)) {
                                    $notfoundfieldname = $matches[1][$i];
                                    debugging('Backend field "'.$notfoundfieldname.'" not defined in fieldlist');
                                    $result = false;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }

     
}

