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
 * Course fisher CSV backend
 *
 * @package    coursefisherbackend_csv
 * @copyright  2014 Diego Fantoma
 * @copyright  2022 Roberto Pinna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace coursefisherbackend_csv;

/**
 * Course fisher CSV backend class
 *
 * @package    coursefisherbackend_csv
 * @copyright  2014 Diego Fantoma
 * @copyright  2022 Roberto Pinna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backend extends \local_coursefisher\backend {

    /**
     * Get backend plugin name.
     *
     * @return string The plugin name translation
     */
    public function description() {
        return get_string('pluginname', 'coursefisherbackend_csv');
    }

    /**
     * Get csv row fields.
     *
     * @param string $csvstring The csv row string
     *
     * @return array Array of give row field values
     */
    private function get_record($csvstring) {
        $separator = get_config('local_coursefisher', 'separator');
        if (!empty($separator)) {
            $element = preg_split('/' . $separator . '/', $csvstring);

            $fieldlist = trim(get_config('local_coursefisher', 'fieldlist'));
            $fields = array_flip(preg_split("/\n|\s/", $fieldlist, -1, PREG_SPLIT_NO_EMPTY));

            $row = new \stdClass();
            foreach ($element as $key => $value) {
                if (in_array($key, $fields)) {
                    $row->$key = $value;
                }
            }
        }
        return $row;
    }

    /**
     * Fetch data from csv backend and store it in local cache file.
     *
     * @return integer or false The number of cached records
     */
    public function fetch_to_cache() {
        global $CFG;

        $config = get_config('local_coursefisher');

        $context = stream_context_create(['http' => ['timeout' => 1]]);

        // Opens cache files for writing.
        if (!($fp1 = @fopen($CFG->dataroot.'/temp/local_coursefisher_cache1.tmp', 'w'))) {
            return false;
        }
        if (!($fp2 = @fopen($CFG->dataroot.'/temp/local_coursefisher_cache2.tmp', 'w'))) {
            return false;
        }

        $firstline = true;
        if ($fd = @fopen ($config->locator, 'r', false, $context)) {
            while (!feof ($fd) && $c < 5000000) {
                $buffer = fgets($fd, 4096);
                if (!($config->firstrow && $firstline)) {
                    $ray = $this->get_record(rtrim($buffer));
                    $strecord = $parser->prepare_record($config->parameters, $ray);
                    $fullrecord = serialize($ray);

                    fwrite($fp1, $strecord."\r\n");
                    fwrite($fp2, $fullrecord."\r\n");
                }
                $firstline = false;
            }
            fclose ($fd);
            fclose ($fp1);
            fclose ($fp2);
            return $c;
        }
        return false;
    }

    /**
     * Fetch data from backend local cache file.
     *
     * @param boolean $override If need to override field values
     *
     * @return array or false The cached records
     */
    public function fetch_from_cache($override = false) {
        global $CFG;

        $parser = $this->get_parser();
        $lines = [];

        $strecords = @file($CFG->dataroot.'/temp/local_coursefisher_cache1.tmp');
        if ($strecords === false) {
            return false;
        }

        $fullrecords = @file($CFG->dataroot.'/temp/local_coursefisher_cache2.tmp');
        if ($fullrecords === false) {
            return false;
        }

        $filter = new \local_coursefisher\evaluator();
        foreach ($strecords as $key => $value) {
            $filterpass = $filter->evaluate($parser->substitute_objects($value, $override));
            if ($filterpass === false) {
                $lines[] = (object)unserialize($fullrecords[$key]);
            }
        }

        return $lines;

    }


    /**
     * Fetch data from csv backend.
     *
     * @param boolean $usetestvalue If need to override field values with test values
     *
     * @return array or false The fetched records
     */
    public function http_fetch($usetestvalue = false) {
        $config = get_config('local_coursefisher');

        $parser = $this->get_parser();
        $c = 0;
        $lines = [];
        $context = stream_context_create(['http' => ['timeout' => 1]]);

        $override = false;
        if ($usetestvalue) {
            $override = $parser->parse_field_assign($config->fieldtest);
        }

        if ($fd = fopen($config->locator, 'r', false, $context)) {
            while (!feof($fd) && $c < 500000) {
                $buffer = fgets($fd, 4096);
                if (!($config->firstrow && $c == 0)) {
                    $ray = $this->get_record(rtrim($buffer));
                    if ($parser->eval_record($parser->substitute_objects($config->parameters, $override), $ray) ) {
                        $lines[] = (object)$ray;
                    }
                }
                $c++;
            }
            fclose ($fd);
        }
        return $lines;
    }


    /**
     * Reads informations for teacher courses from csv backend, then returns it in an array of objects.
     *
     * @param boolean $alldata Return all data without query filtering
     *
     * @return array The courses data
     */
    public function get_data($alldata = false) {
        global $CFG;

        if ($this->init()) {
            $parser = $this->get_parser();
            $override = $parser->parse_field_assign($CFG->local_coursefisher_fieldtest);

            $fields = ["local_coursefisher_fieldlevel",
                       "local_coursefisher_course_code",
                       "local_coursefisher_course_fullname",
                       "local_coursefisher_course_shortname",
                       "local_coursefisher_locator",
                       "local_coursefisher_parameters",
                       "local_coursefisher_fieldtest"];

            if (!(false === ($this->check_config("local_coursefisher_fieldlist", $fields, $override)))) {
                $cachename = 'MoodleBlockCourseFisherCSV'.$_COOKIE['MoodleSession'.$CFG->sessioncookie];
                if (isset($_SESSION[$cachename])) {
                    $cachedata = unserialize($_SESSION[$cachename]);
                    if (is_array($cachedata)) {
                        return($cachedata);
                    }
                    return([]);
                } else {
                    $cachedata = $this->fetch_from_cache();
                    $_SESSION[$cachename] = serialize($cachedata);
                    return($cachedata);
                }
                return([]);
            } // CheckCFG.

        } // Init.

        return(false);

    }


    /**
     * The cron function to fetch course records and store in local cache.
     *
     * @return true
     */
    public function cron() {
        global $CFG;

        if ($this->init()) {
            $parser = $this->get_parser();
            $override = $parser->parse_field_assign($CFG->local_coursefisher_fieldtest);

            $fields = ["local_coursefisher_fieldlevel",
                       "local_coursefisher_course_fullname",
                       "local_coursefisher_course_shortname",
                       "local_coursefisher_locator",
                       "local_coursefisher_parameters",
                       "local_coursefisher_fieldtest"];
            if (!(false === ($this->check_config("local_coursefisher_fieldlist", $fields, $override)))) {
                $this->fetch_to_cache();
            } else {
                print "CSVBACK ERROR:: ".$this->getError()."\r\n";
            }
        }

        return(true);
    }

}
