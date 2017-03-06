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
 * @copyright 2014 and above Angelo Calò
 * @copyright 2016 Francesco Carbone
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../lib.php');

class local_coursefisher_backend_json extends local_coursefisher_backend {

    public function description() {
        return(get_string('pluginname', 'coursefisherbackend_json'));
    }


    public function get_data($alldata=false) {
        global $CFG, $USER, $COURSE;

        if ($this->init()) {
            $parser = new local_coursefisher_parser();
            $backendfields = array(
                                 'year'  => date('Y'),
                                 'month' => date('m'),
                                 'day'   => date('d')
                              );  

            $parser->add_object("USER", $USER);
            $parser->add_object("COURSE", $COURSE);
            $parser->add_object("BACKEND", (object) $backendfields);

            $override = $parser->parse_field_assign(get_config('local_coursefisher', 'fieldtest'));

            $fields = array('fieldlevel', 'course_code', 'course_fullname', 'course_shortname',
                            'locator', 'parameters', 'fieldtest');

            if ($parser->check_config(get_config('local_coursefisher', 'filedlist'), $fields, $override) !== false) {

                // Aumento tempo di timeout.
                $opts = array('http' => array(
                        'method'  => 'GET',
                        'header'  => 'Content-Type: application/json; charset=utf-8',
                        'timeout' => 500
                      )
                );

                $context  = stream_context_create($opts);

                // Carico il primo file utile alla lettura dell'offerta formativa.
                $jsondatas = array();
                foreach (preg_split("/((\r?\n)|(\r\n?))/", get_config('local_coursefisher', 'locator')) as $line) {
                    $backend = $parser->substitute_objects($line, false);
                    $backend = str_replace('\'', '', $backend);
                    $jsontext = file_get_contents($backend, false, $context);
                    $jsondecoded = json_decode($jsontext, true);
                    if ($jsontext && $jsondecoded) {
                        break;
                    } else if (!$jsontext) {
                        print_error('l\'URL'.get_config('local_coursefisher', 'locator').' inserito non è corretto');
                    }
                }

                while (list($key, $value) = each($jsondecoded)) {

                    if ($alldata) {
                        $jsondatas[] = (object)$value;
                    } else if ($parser->eval_record($parser->substitute_objects(get_config('local_coursefisher', 'parameters'), false),$value)) {
                        $jsondatas[] = (object)$value;
                    }

                }
                 return($jsondatas);
            }

        }

        return false;

    }

}
