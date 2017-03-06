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
 * @subpackage coursefisherbackend_csv
 * @copyright  2014 Diego Fantoma
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../lib.php');

class local_coursefisher_backend_csv extends local_coursefisher_backend {

    public function description() {
        return(get_string('pluginname', 'coursefisherbackend_csv'));
    }

    private function get_record($csvstring) {
        global $CFG;
        $ray = array();
        $parser = $this->get_parser();
        $field = array_flip($parser->getFields());
        $t = preg_split("/".$CFG->local_coursefisher_separator."/", $csvstring);

        while (list($tk, $tv) = each($t)) {
            if (isset($field[$tk])) {
                $ray[$field[$tk]] = $tv;
            }
        }
        return($ray);
    }

    public function fetch_to_cache() {
        global $CFG;

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

        if ($fd = @fopen ($CFG->local_coursefisher_locator, "r", false, $context)) {
            while (!feof ($fd) && $c < 5000000) {
                $buffer = fgets($fd, 4096);
                if (!($CFG->local_coursefisher_firstrow && $c == 0)) {
                    $ray = $this->get_record(rtrim($buffer));
                    $strecords[$c] = $parser->prepare_record($CFG->local_coursefisher_parameters, $ray);
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

    public function fetch_from_cache($override = false) {
        global $CFG;

        $parser = $this->get_parser();
        $lines = array();

        if (false === ($strecords = @file($CFG->dataroot.'/temp/local_coursefisher_cache1.tmp'))) {
            return(false);
        }

        if (false === ($fullrecords = @file($CFG->dataroot.'/temp/local_coursefisher_cache2.tmp'))) {
            return(false);
        }

        $found = 0;
        while ((list($key, $value) = each($strecords)) && $found == 0) {
            if (eval($parser->substitute_objects($value, $override))) {
                $lines[] = (object)unserialize($fullrecords[$key]);
            }
        }

        return($lines);

    }


    public function http_fetch($usetestvalue = false) {
        global $CFG;

        $parser = $this->get_parser();
        $c = 0;
        $lines = array();
        $context = stream_context_create(array('http' => array('timeout' => 1)));

        $override = false;
        if ($usetestvalue) {
            $override = $parser->parse_field_assign($CFG->local_coursefisher_fieldtest);
        }

        if ($fd = fopen($CFG->local_coursefisher_locator, "r", false, $context)) {
            while (!feof($fd) && $c < 500000) {
                $buffer = fgets($fd, 4096);
                if (!($CFG->local_coursefisher_firstrow && $c == 0)) {
                    $ray = $this->get_record(rtrim($buffer));
                    if ($parser->eval_record($parser->substitute_objects($CFG->local_coursefisher_parameters, $override), $ray) ) {
                        $lines[] = (object)$ray;
                    }
                }
                $c++;
            }
            fclose ($fd);
        }
        return($lines);
    }


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
