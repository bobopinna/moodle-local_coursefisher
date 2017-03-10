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
 * Version details
 *
 * @package    local
 * @subpackage coursefisherbackend_json
 * @copyright  2014 and above Angelo Calò
 * @copyright  2016 Francesco Carbone
 * @copyright  2017 Roberto Pinna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');

class local_coursefisher_backend_json extends local_coursefisher_backend {

    public function description() {
        return(get_string('pluginname', 'coursefisherbackend_json'));
    }

    public function get_data($alldata = false) {
        global $CFG;

        require_once($CFG->libdir . '/filelib.php');

        $check = $this->check_settings();
        if ($check) {
            $curl = new curl();

            $curl->setHeader(array('Accept: application/json', 'Expect:'));
            $options = array(
                    'FRESH_CONNECT'     => true,
                    'RETURNTRANSFER'    => true,
                    'FORBID_REUSE'      => true,
                    'HEADER'            => 0,
                    'CONNECTTIMEOUT'    => 3,
                    // Follow redirects with the same type of request when sent 301, or 302 redirects.
                    'CURLOPT_POSTREDIR' => 3
            );

            // Download the first available json file.
            $result = array();
            $locators = preg_split("/((\r?\n)|(\r\n?))/", get_config('local_coursefisher', 'locator'));
            if (!empty($locators)) {
                $jsondecoded = null;
                foreach ($locators as $line) {
                    $url = $this->format_fields($line);
                    $json = $curl->get($url, array(), $options);

                    if (!empty($json)) {
                        $jsondecoded = json_decode($json, true);
                        if (!empty($jsondecoded)) {
                            break;
                        }
                    } else {
                        debugging('JSON file: '.$line.' not found, skip to next if exists.');
                    }
                }

                if (!empty($jsondecoded)) {
                    $fieldlist = trim(get_config('local_coursefisher', 'fieldlist'));
                    $fields = array_flip(preg_split("/\n|\s/", $fieldlist), -1, PREG_SPLIT_NO_EMPTY);

                    $parameters = get_config('local_coursefisher', 'parameters');
                    foreach ($jsondecoded as $element) {
                        if (!empty($element)) {
                            $row = new stdClass();

                            foreach ($element as $key => $value) {
                                if (in_array($key, $fields)) {
                                    $row->$key = $value;
                                }
                            }

                            $filter = $this->format_fields($parameters, $row);

                            if (($alldata) || $this->is_filtered($filter, $row)) {
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

    private function is_filtered($filter, $data) {

        if (!empty($filters) && !empty($data)) {
            $filters = array_flip(preg_split("/\n|\s/", $filter), -1, PREG_SPLIT_NO_EMPTY);
            $decodedfilters = $this->get_filter_items($filters);
            foreach ($decodedfilters as $filterrow) {
                if ($this->is_verified($filterrow)) {
                    return true;
                }
            }
        }
        return false;
    }

}
