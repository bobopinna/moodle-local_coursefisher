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
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace coursefisherbackend_csv;

/**
 * Course fisher CSV backend class
 *
 * @package    coursefisherbackend_csv
 * @copyright  2014 Diego Fantoma
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backend extends \local_coursefisher\backend {

    /**
     * Get backend plugin name.
     *
     * @return string The plugin name translation
     */
    public function description() {
        return(get_string('pluginname', 'coursefisherbackend_csv'));
    }

    /**
     * Get csv row fields.
     *
     * @param string $csvstring The csv row string
     *
     * @return array Array of give row field values
     */
    private function get_record($csvstring) {
        $ray = array();
        $parser = $this->get_parser();
        $field = array_flip($parser->getFields());
        $t = preg_split('/'.get_config('local_coursefisher', 'separator').'/', $csvstring);

        foreach ($t as $tk => $tv) {
            if (isset($field[$tk])) {
                $ray[$field[$tk]] = $tv;
            }
        }
        return($ray);
    }

    /**
     * Fetch data from csv backend and store it in local cache file.
     *
     * @return integer or false The number of cached records
     */
    public function fetch_to_cache() {
        global $CFG;

        $config = get_config('local_coursefisher');

        $parser = $this->get_parser();
        $c = 0;
        $lines = array();
        $context = stream_context_create(array('http' => array('timeout' => 1)));

        // Opens cache files for writing.
        if (!($fp1 = @fopen($CFG->dataroot.'/temp/local_coursefisher_cache1.tmp', 'w'))) {
            return(false);
        }
        if (!($fp2 = @fopen($CFG->dataroot.'/temp/local_coursefisher_cache2.tmp', 'w'))) {
            return(false);
        }

        if ($fd = @fopen ($config->locator, 'r', false, $context)) {
            while (!feof ($fd) && $c < 5000000) {
                $buffer = fgets($fd, 4096);
                if (!($config->firstrow && $c == 0)) {
                    $ray = $this->get_record(rtrim($buffer));
                    $strecords[$c] = $parser->prepare_record($config->parameters, $ray);
                    $fullrecords[$c] = serialize($ray);

                    fwrite($fp1, $strecords[$c]."\r\n");
                    fwrite($fp2, $fullrecords[$c]."\r\n");
                }
                $c++;
            }
            fclose ($fd);
            fclose ($fp1);
            fclose ($fp2);
            return($c);
        }
        return(false);
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
        $lines = array();

        if (false === ($strecords = @file($CFG->dataroot.'/temp/local_coursefisher_cache1.tmp'))) {
            return false;
        }

        if (false === ($fullrecords = @file($CFG->dataroot.'/temp/local_coursefisher_cache2.tmp'))) {
            return false;
        }

        foreach ($strecords as $key => $value) {
            if (eval($parser->substitute_objects($value, $override))) {
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
        $lines = array();
        $context = stream_context_create(array('http' => array('timeout' => 1)));

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

            $fields = array("local_coursefisher_fieldlevel",
                       "local_coursefisher_course_code",
                       "local_coursefisher_course_fullname",
                       "local_coursefisher_course_shortname",
                       "local_coursefisher_locator",
                       "local_coursefisher_parameters",
                       "local_coursefisher_fieldtest");

            if (!(false === ($this->check_config("local_coursefisher_fieldlist", $fields, $override)))) {
                $cachename = 'MoodleBlockCourseFisherCSV'.$_COOKIE['MoodleSession'.$CFG->sessioncookie];
                if (isset($_SESSION[$cachename])) {
                    $cachedata = unserialize($_SESSION[$cachename]);
                    if (is_array($cachedata)) {
                        return($cachedata);
                    }
                    return(array());
                } else {
                    $cachedata = $this->fetch_from_cache();
                    $_SESSION[$cachename] = serialize($cachedata);
                    return($cachedata);
                }
                return(array());
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

            $fields = array("local_coursefisher_fieldlevel",
                         "local_coursefisher_course_fullname",
                         "local_coursefisher_course_shortname",
                         "local_coursefisher_locator",
                         "local_coursefisher_parameters",
                         "local_coursefisher_fieldtest");
            if (!(false === ($this->check_config("local_coursefisher_fieldlist", $fields, $override)))) {
                $this->fetch_to_cache();
            } else {
                print "CSVBACK ERROR:: ".$this->getError()."\r\n";
            }
        }

        return(true);
    }

}
