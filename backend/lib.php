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

define('BACKENDFIELDMATCHSTRING', '/\[\%(\w+)(([#+-])(\d+))?\%\]/');
define('USERFIELDMATCHSTRING', '/\[\%\!USER\:(\w+)(([#+-])(\d+))?\!\%\]/');

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

        $this->name = new lang_string('pluginname', 'coursefisherbackend_'.$backendname);
    }

    public function init() {
        return true;
    }

    public function get_data($alldata = false) {
        return null;
    }

    public function check_settings() {
        $result = true;
        $fields = array();

        $settings = get_config('local_coursefisher');

        if (!empty($settings->fieldlist)) {
            $fields = array_flip(preg_split("/\n|\s/", trim($settings->fieldlist), -1, PREG_SPLIT_NO_EMPTY));
        }

        $checks = (object) array_fill_keys($fields, 'checked');

        if (!empty($settings)) {
            foreach ($settings as $settingname => $settingvalue) {
                $checkedsetting = $this->format_fields($settingvalue, $checks);
                if ($found = preg_match_all(BACKENDFIELDMATCHSTRING, $checkedsetting, $matches)) {
                    for ($i = 1; $i < $found; $i++) {
                         debugging('Backend field "'.$matches[1][$i].'" not existent');
                         $result = false;
                    }
                }
                if ($found = preg_match_all(USERFIELDMATCHSTRING, $checkedsetting, $matches)) {
                    for ($i = 1; $i < $found; $i++) {
                         debugging('User field "'.$matches[1][$i].'" not existent');
                         $result = false;
                    }
                }
            }
        }

        return $result;
    }

    public function __destruct() {
    }

    static public function user_field_value($matches) {
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

    static public function format_fields($formatstring, $data = null) {

        $formattedstring = preg_replace_callback(USERFIELDMATCHSTRING, 'self::user_field_value', $formatstring);
        if (!empty($data)) {
            $tempstring = $formattedstring;

            $callback = function($matches) use ($data) {
                 return self::get_field($matches, $data);
            };

            $formattedstring = preg_replace_callback(BACKENDFIELDMATCHSTRING, $callback, $tempstring);
        }

        return $formattedstring;
    }

    static public function get_field($matches, $data) {
        $replace = null;

        if (isset($matches[1])) {
            if (isset($data->$matches[1]) && !empty($data->$matches[1])) {
                if (isset($matches[2])) {
                    switch($matches[3]) {
                        case '#':
                            $replace = substr($data->$matches[1], 0, $matches[4]);
                        break;
                        case '+':
                            $replace = $data->$matches[1] + $matches[4];
                        break;
                        case '-':
                            $replace = $data->$matches[1] - $matches[4];
                        break;
                    }
                } else {
                    $replace = $data->$matches[1];
                }
            }
        }
        return $replace;
    }

    static public function get_fields_items($field, $items = array('code' => 2, 'description' => 3)) {
        $result = array();
        if (!is_array($field)) {
            $fields = array($field);
        } else {
            $fields = $field;
        }

        foreach ($fields as $element) {
            preg_match('/^((.+)\=\>)?(.+)?$/', $element, $matches);
            $item = new stdClass();
            foreach ($items as $itemname => $itemid) {
                if (!empty($matches) && !empty($matches[$itemid])) {
                    $item->$itemname = $matches[$itemid];
                }
            }
            if (count((array)$item)) {
                if (count($items) == 1) {
                    reset($items);
                    $result[] = $item->{key($items)};
                } else {
                    $result[] = $item;
                }
            }
        }

        if (!is_array($field)) {
            if (!empty($result)) {
                return $result[0];
            } else {
                return null;
            }
        } else {
            return $result;
        }
    }

    static public function get_fields_description($field) {
        return self::get_fields_items($field, array('description' => 3));
    }

}
