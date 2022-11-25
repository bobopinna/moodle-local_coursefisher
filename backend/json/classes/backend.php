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
 * JSON Backend implementation
 *
 * @package coursefisherbackend_json
 * @copyright  2014 and above Angelo Calò
 * @copyright  2016 Francesco Carbone
 * @copyright  2017 and above Roberto Pinna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace coursefisherbackend_json;

use local_coursefisher_evaluator;

/**
 * JSON Backend implementation
 *
 * @package coursefisherbackend_json
 * @copyright  2014 and above Angelo Calò
 * @copyright  2016 Francesco Carbone
 * @copyright  2017 and above Roberto Pinna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backend extends \local_coursefisher\backend {

    /**
     * Return the backend description
     *
     * @return string
     */
    public function description() {
        return(get_string('pluginname', 'coursefisherbackend_json'));
    }

    /**
     * Get courses data from backend
     *
     * @param boolean $alldata set to return not filtered data from backend
     *
     * @return array or false
     */
    public function get_data($alldata = false) {
        global $CFG;

        require_once($CFG->libdir . '/filelib.php');

        $config = get_config('local_coursefisher');

        $check = $this->check_settings();
        if ($check) {
            // Download the first available json file.
            $result = array();
            $locators = preg_split("/((\r?\n)|(\r\n?))/", $config->locator);
            if (!empty($locators)) {
                $override = null;
                if ($alldata) {
                    $overrides = $this->get_fields_assign($config->fieldtest);
                    $keys = $overrides->names;
                    $values = $overrides->values;
                    $locators = preg_replace($keys, $values, $locators);
                }

                $jsondecoded = null;
                foreach ($locators as $line) {
                    $url = $this->format_fields($line);
                    if ((strpos($line, '[%') !== false) && ($url == $line)) {
                        // A placeholder was present in locator line but no replace is done.
                        return false;
                    }

                    $jsonstring = download_file_content($url, null, null, false, 500);
                    $jsondata = json_decode($jsonstring, true);

                    if ($jsonstring && $jsondata) {
                        break;
                    } else if (empty($jsonstring)) {
                        debugging('JSON file: '.$line.' not found, skip to next if exists.');
                    } else if (!is_array($jsondata)) {
                        switch (json_last_error()) {
                            case JSON_ERROR_NONE:
                                debugging('No errors');
                            break;
                            case JSON_ERROR_DEPTH:
                                debugging('Maximum stack depth exceeded');
                            break;
                            case JSON_ERROR_STATE_MISMATCH:
                                debugging('Underflow or the modes mismatch');
                            break;
                            case JSON_ERROR_CTRL_CHAR:
                                debugging('Unexpected control character found');
                            break;
                            case JSON_ERROR_SYNTAX:
                                debugging('Syntax error, malformed JSON');
                            break;
                            case JSON_ERROR_UTF8:
                                debugging('Malformed UTF-8 characters, possibly incorrectly encoded');
                            break;
                            default:
                                debugging('Unknown error');
                            break;
                        }
                    }
                }

                if (!empty($jsondata)) {
                    $fieldlist = trim($config->fieldlist);
                    $fields = preg_split("/\n|\s/", $fieldlist, -1, PREG_SPLIT_NO_EMPTY);

                    foreach ($jsondata as $element) {
                        if (!empty($element)) {
                            $row = new \stdClass();

                            foreach ($element as $key => $value) {
                                if (in_array($key, $fields)) {
                                    $row->$key = $value;
                                }
                            }

                            $filterpass = true;
                            if (!$alldata) {
                                if (!empty($config->parameters)) {
                                    $expression = $this->format_fields($config->parameters, $row);

                                    $filter = new \local_coursefisher\evaluator();
                                    $filterpass = $filter->evaluate($expression);
                                    if ($filterpass === false) {
                                        debugging($filter->last_error);
                                    }
                                }
                            }

                            if ($filterpass) {
                                $result[] = $row;
                            }
                        }
                    }
                }
            }
            return $result;
        }
        return false;
    }

}
