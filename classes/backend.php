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
 * Course fisher base backend class
 *
 * @package    local_coursefisher
 * @copyright  2014 and above Diego Fantoma e Roberto Pinna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursefisher;

define('BACKENDFIELDMATCHSTRING', '/\[\%(\w+)(([#+-])(\d+))?\%\]/');
define('USERFIELDMATCHSTRING', '/\[\%\!USER\:(\w+)(([#+-])(\d+))?\!\%\]/');
define('ASSIGNFIELDMATCHSTRING', '/(\[\%!\w+:\w+!\%\]):(\w+)/');

/**
 * Class backend
 *
 * All coursefisher backend plugins are based on this class.
 *
 * @package    local_coursefisher
 * @copyright  2014 and above Diego Fantoma e Roberto Pinna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class backend {

    /**
     * @var string The backend name.
     */
    public $name;

    /**
     * @var string Backend error message.
     */
    private $error = '';

    /**
     * Constructor.
     */
    public function __construct() {
        if (!is_subclass_of($this, '\local_coursefisher\backend')) {
            debugging('Woops, wrong class initialized');
            return false;
        }

        $backendname = get_config('local_coursefisher', 'backend');
        if (!$backendname == __CLASS__) {
            debugging('The name of the configured backend does not match the called class');
            return false;
        }

        $this->name = new \lang_string('pluginname', 'coursefisherbackend_'.$backendname);
    }

    /**
     * Destructor.
     */
    public function __destruct() {
    }

    /**
     * Initialize backend comunication.
     *
     * @return boolean
     */
    public function init() {
        return true;
    }

    /**
     * Get backend data.
     *
     * @param boolean $alldata Define if method should not filter data
     * @return boolean
     */
    public function get_data($alldata = false) {
        return null;
    }

    /**
     * Check if backend settings are ok.
     *
     * @return boolean
     */
    public function check_settings() {
        $result = true;
        $fields = [];

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

    /**
     * Get field values from the assign string.
     *
     * @param string $assignstring The assign string
     *
     * @return object or false  With two arrais for names and values
     */
    public function get_fields_assign($assignstring) {
        preg_match_all(ASSIGNFIELDMATCHSTRING, $assignstring, $matches);
        if (isset($matches[1]) && isset($matches[2])) {
            $fields = new \stdClass();;
            $fields->names = [];
            $fields->values = [];
            foreach ($matches[1] as $id => $fieldname) {
                $fields->names[] = '/' . quotemeta($fieldname) . '/';
                $fields->values[] = $matches[2][$id];
            }
            return $fields;
        }
        return false;
    }

    /**
     * Get user profile field value for the matching name.
     *
     * @param array $matches The preg_match array
     *
     * @return string or false The user field value
     */
    public static function user_field_value($matches) {
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

    /**
     * Replace field placeholders with corrisponding values.
     *
     * @param string $formatstring The configuration string
     * @param object $data The current course data
     *
     * @return string The replaced string
     */
    public static function format_fields($formatstring, $data = null) {
        $userfieldvaluecall = function($matches) use ($data) {
            return self::user_field_value($matches, $data);
        };

        $formattedstring = preg_replace_callback(USERFIELDMATCHSTRING, $userfieldvaluecall, $formatstring);
        if (!empty($data)) {
            $tempstring = $formattedstring;

            $getfieldcall = function($matches) use ($data) {
                 return self::get_field($matches, $data);
            };

            $formattedstring = preg_replace_callback(BACKENDFIELDMATCHSTRING, $getfieldcall, $tempstring);
        }

        return $formattedstring;
    }

    /**
     * Replace matched placeholder with corrisponding value.
     *
     * @param array $matches The preg_match array
     * @param object $data The current course data
     *
     * @return string The replaced string
     */
    public static function get_field($matches, $data) {
        $replace = null;

        if (isset($matches[1])) {
            if (isset($data->{$matches[1]}) && !empty($data->{$matches[1]})) {
                if (isset($matches[2])) {
                    switch($matches[3]) {
                        case '#':
                            $replace = substr($data->{$matches[1]}, 0, $matches[4]);
                        break;
                        case '+':
                            $replace = $data->{$matches[1]} + $matches[4];
                        break;
                        case '-':
                            $replace = $data->{$matches[1]} - $matches[4];
                        break;
                    }
                } else {
                    $replace = $data->{$matches[1]};
                }
            }
        }
        return $replace;
    }

    /**
     * Get requested items for the given fields.
     *
     * @param array $field The array of fields or the field name
     * @param array $items The items mapping
     *
     * @return array The array of items
     */
    public static function get_fields_items($field, $items = ['code' => 2, 'description' => 3]) {
        $result = [];
        if (!is_array($field)) {
            $fields = [$field];
        } else {
            $fields = $field;
        }

        foreach ($fields as $element) {
            preg_match('/^((.+)\=\>)?(.+)?$/', $element, $matches);
            $item = new \stdClass();
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

    /**
     * Get description items for the given fields.
     *
     * @param array $field The array of fields or the field name
     *
     * @return array The array of items
     */
    public static function get_fields_description($field) {
        return self::get_fields_items($field, ['description' => 3]);
    }

}
