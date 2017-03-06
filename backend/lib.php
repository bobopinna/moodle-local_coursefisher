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
 * @subpackage coursefisher
 * @copyright  2014 Diego Fantoma
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

class local_coursefisher_backend {

    public $name;

    private $error = '';

    public function __construct() {
        if (!is_subclass_of($this, 'local_coursefisher_backend')) {
            $this->error = 'Woops, wrong class initialized';
            return false;
        }

        $backendname = get_config('local_coursefisher', 'backend');
        if (!$backendname == __CLASS__) {
            $this->error = 'The name of the configured backend does not match the called class';
            return false;
        }

        $this->name = new lang_string('backend', 'coursefisher_'.$backendname);
    }

    public function init() {
        return true;
    }

    public function description() {
        return false;
    }

    public function get_data($alldata = false) {
        return null;
    }

    public function get_user_field($matches) {
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

    public function __destruct() {
    }

    public function get_config() {
        global $CFG;

        if (is_subclass_of($this, 'local_coursefisher_backend')) {
            return("Yes it is the right subclass");
        }
        return("Ops, it seems no to be the right subclass");
    }

}


class local_coursefisher_parser {

    private $objects = array();
    private $fields = array();
    private $objvalues = array();
    private $parseresult = false;
    private $parseresultstring = "";

    private $LeftSep = "\[\%";
    private $RightSep = "\%\]";
    private $LeftObjSep = "!";
    private $RightObjSep = "!";
    private $ObjSep = ":";

    public function add_object($name, $Obj) {
        if (is_object($Obj) && strlen($name)) {
            $this->objects[$name] = $Obj;
        } else {
            return(false);
        }
    }

    public function get_objects() {
        return(count($this->objects));
    }

    public function set_fields($fld = "") {
        if (strlen($fld)) {
            $this->fields = array_flip(preg_split("/\n|\s/",trim($fld),-1,PREG_SPLIT_NO_EMPTY));
            if (count($this->fields)) {
                return(count($this->fields));
            }
        }
        return(false);
    }

    public function getLeftObjSep() {
        return($this->LeftObjSep);
    }

    public function getRightObjSep() {
        return($this->RightObjSep);
    }

    public function getObjSep() {
        return($this->ObjSep);
    }

    public function getLeftSep() {
        return($this->LeftSep);
    }

    public function getRightSep() {
        return($this->RightSep);
    }

    public function get_fields() {
        return($this->fields);
    }

    public function get_result() {
        return($this->parseresult);
    }

    public function get_resultstring() {
        return($this->parseresultstring);
    }

    public function get_objvalues() {
        return($this->objvalues);
    }

    private function parse_object_variable($Var, $override = false) {
        preg_match_all("/".$this->LeftObjSep."(\w+)".$this->ObjSep."(\w+)".$this->RightObjSep."/", $Var, $R, PREG_PATTERN_ORDER);

        if (is_array($R)) {
            if (is_array($R[1]) && is_array($R[2])) {
                if (isset($R[1][0]) && isset($R[2][0])) {
                    if (is_object($this->objects[$R[1][0]])) {
                        if (isset($this->objects[$R[1][0]]->$R[2][0])) {

                            if (is_array($override)) {
                                if (isset($override[$this->LeftObjSep.$R[1][0].$this->ObjSep.$R[2][0].$this->RightObjSep])) {
                                    if (strlen(strval($override[$this->LeftObjSep.$R[1][0].$this->ObjSep.$R[2][0].$this->RightObjSep]))) {
                                        return($override[$this->LeftObjSep.$R[1][0].$this->ObjSep.$R[2][0].$this->RightObjSep]);
                                    }
                                }
                            }

                            if (strlen(strval($this->objects[$R[1][0]]->$R[2][0]))) {
                                return($this->objects[$R[1][0]]->$R[2][0]);
                            }

                         }
                     }
                 }
             }
         }
         return(false);
    }

    public function parse_field_assign($string2check, $allowVars = false) {
        $M = array();
        $result = true;
        preg_match_all("/".$this->LeftSep."(".$this->LeftObjSep."\w+".$this->ObjSep."\w+".$this->RightObjSep.")".$this->RightSep."".$this->ObjSep."(\w+)"."/", $string2check, $M,PREG_PATTERN_ORDER);

        if (isset($M[1]) && isset($M[2])) {
            $Muniq = array();
            while (list($Mk, $Mv) = each($M[1])) {
                $Muniq[$Mv]=$M[2][$Mk];
            }
            return($Muniq);
        }

        return(false);
    }


    public function parse_fields($string2check, $allowVars = false) {
        $M = array();
        $errIdx = 0;
        $this->objvalues = array();
        $this->parseresult = true;
        $this->parseresultstring = "";

        preg_match_all("/".$this->LeftSep."(\w+|".$this->LeftObjSep."\w+".$this->ObjSep."\w+".$this->RightObjSep.")".$this->RightSep."/", $string2check, $M,PREG_PATTERN_ORDER);
        if (isset($M[1])) {
            $Muniq = array();
            $F = @array_flip($M[1]);

            while (list($Mk, $Mv) = each($F)) {
                if (strlen($Mk)) {
                    $Muniq[$Mk] = false;
                    if (isset($this->fields[$Mk])) {
                        $Muniq[$Mk] = true;
                    } else {
                        if ($allowVars) {
                            if ($this->parse_object_variable($Mk, $allowVars)) {
                                $Muniq[$Mk] = $this->parse_object_variable($Mk, $allowVars);
                                $this->objvalues[$Mk] = $Muniq[$Mk];
                            }
                        }
                    } 

                    if ($Muniq[$Mk] === false) {
                        $this->parseresult = false;
                        $this->parseresultstring = "Not a valid field $Mk::$Mv -";
                    }
                }
            }
        }
        if ($this->parseresult) {
            return($Muniq);
        }
        return(false);
    }


    public function substitute_objects($string2check, $override = false) {
        $S = $string2check;
        $Muniq = $this->parse_fields($S, $this->fields);
        if (is_array($Muniq) ) {
            while (list($Mk, $Mv) = each($Muniq)) {
                if (!($Mv === false)) {
                    if (substr($Mk,0,1) == $this->LeftObjSep && substr($Mk,-1) == $this->RightObjSep) {
                        $setVal = $Mv;
                        if (is_array($override)) {
	                    if (isset($override[$Mk])) {
                                if (strlen(strval($override[$Mk]))) {
                                    $setVal = $override[$Mk];
                                }
                            }
                        }
                        $S = preg_replace('/'.$this->LeftSep.$Mk.$this->RightSep.'/',"'".$setVal."'", $S);
                    }
                }
            }
        }
        return($S);
    }


    public function prepare_record($string2check, $record, $override = false) {
        $validation = false;
        $S = $string2check;

        if (is_array($record)) {
            while (list($Fk, $Fv) = each($record)) {
                $S = preg_replace('/'.$this->LeftSep.$Fk.$this->RightSep.'/',"'".$Fv."'", $S);
            }
            $validation = 'return (' . trim($S) . ') ? true : false;';
        }

        return($validation);

    }

    public function eval_record($string2check, $record, $override = false) {
        return(eval(prepare_record($string2check, $record, $override)));
    }

    public function check_config($definefields, $fieldstocheck, $override = false) {
        $result = true;
        $this->error = '';
  
        if (!empty($definefields)) {
            if ($this->set_fields($definedfields)) {
  
                if (!is_array($fieldstocheck)) {
                    $fieldstocheck = array($fieldstocheck);
                }
                foreach ($fieldstocheck as $fieldname) {
                    $fieldvalue = get_config('local_coursefisher', $fieldname);
                    if (!empty($fieldvalue)) {
                        $this->parse_fields($CFG->$C, $override);
                        if ($this->get_result() === false) {
                           $result = false;
                           $this->error = $fieldname.": ".$this->get_result_string();
                        }
                    }
                }
            }
        }
        return $result;
    }


    public function init() {
        $this->error = '';
        return true;
    }

    public function get_result_string() {
        return($this->result);
    }

    public function get_parser() {
        return($this->parser);
    }

    public function get_error() {
        return($this->error);
    }


} // Class local_coursefisher_parser.

