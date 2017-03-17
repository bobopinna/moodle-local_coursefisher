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
 * Course fisher filter evaluation class
 *
 * @package    local_coursefisher
 * @subpackage evaluator
 * @copyright  2017 and above Roberto Pinna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

class local_coursefisher_evaluator {

    private $tree = array();

    public function __construct($expression) {
        if (!empty($expression)) {
            $expressiontokens = $this->lex($expression);
error_log(print_r($expressiontokens, true));
            if ($expressiontokens !== false) {
                $expressiontree = $this->parse($expressiontokens); 
                if ($expressiontree !== false) {
                    $this->tree = $expressiontree;
                }
            }
        }
    }

    public function evaluate($data) {
        return true;
    }

    private function parse($tokens) {
        return false;
    }

    private function lex($expression) {
        $tokens = array();
        
        $charindex = 0;
        while ($charindex < strlen($expression)) {
            $subexpression = substr($expression, $charindex);
            if ($this->is_whitespace($subexpression)) {
                $charindex++;
            } else if ($len = $this->is_operator($subexpression)) {
                $token = new stdClass();
                $token->type = 'operator';
                $token->value = substr($expression, $charindex, $len);
                array_push($tokens, $token);
                $charindex += $len;
            } else if ($len = $this->is_function($subexpression)) {
                $token = new stdClass();
                $token->type = 'function';
                $token->value = substr($expression, $charindex, $len);
                array_push($tokens, $token);
                $charindex += $len;
            } else if ($len = $this->is_digit($subexpression)) {
                $token = new stdClass();
                $token->type = 'digit';
                $token->value = substr($expression, $charindex, $len);
                array_push($tokens, $token);
                $charindex += $len;
            } else if ($len = $this->is_field($subexpression)) {
                $token = new stdClass();
                $token->type = 'field';
                $token->value = substr($expression, $charindex, $len);
                array_push($tokens, $token);
                $charindex += $len;
            } else if ($len = $this->is_value($subexpression)) {
                $token = new stdClass();
                $token->type = 'value';
                $token->value = substr($expression, $charindex, $len);
                array_push($tokens, $token);
                $charindex += $len;
            } else {
                $token = new stdClass();
                $token->type = 'unknown';
                $token->value = substr($expression, $charindex, 1);
                array_push($tokens, $token);
                $charindex++;
            }
        }
        return $tokens;
    }


    private function is_element($subexpression, $pattern) {
        if (preg_match($pattern, $subexpression, $matches) === 1) {
            return strlen($matches[0]);
        }
        return false;
    }

    private function is_whitespace($subexpression) {
        return $this->is_element($subexpression, '/^\s/');
    }
   
    private function is_operator($subexpression) {
        $operators = array();
        $operators[] = preg_quote('===');
        $operators[] = preg_quote('!==');
        $operators[] = preg_quote('==');
        $operators[] = preg_quote('!=');
        $operators[] = preg_quote('>=');
        $operators[] = preg_quote('<=');
        $operators[] = preg_quote('>');
        $operators[] = preg_quote('<');
        $operators[] = preg_quote('||');
        $operators[] = preg_quote('&&');
        $operators[] = preg_quote('!');
        $operators[] = preg_quote('(');
        $operators[] = preg_quote(')');

        $pattern = '/^(' . implode(')|^(', $operators) . ')/';
        
        return $this->is_element($subexpression, $pattern);
    }

    private function is_function($subexpression) {
        $functions = array();
        $functions[] = preg_quote('empty(');
        $functions[] = preg_quote('is_numeric(');
        $functions[] = preg_quote('intval(');
        $functions[] = preg_quote('is_string(');
        $functions[] = preg_quote('strlen(');

        $pattern = '/^(' . implode(')|^(', $functions) . ')/';

        return $this->is_element($subexpression, $pattern);
    }

    private function is_field($subexpression) {
        $pattern = '/^(\[\%((\!USER\:\w+[\w-_]+\w\!)|(\w[\w_-]+\w))\%\])/';

        return $this->is_element($subexpression, $pattern);
    }

    private function is_digit($subexpression) {
        $pattern = '/^(\d+)/';

        return $this->is_element($subexpression, $pattern);
    }

    private function is_value($subexpression) {
        $i = 0;
        $quoted = false;
        $endfound = false;
        $quotepattern = '/^(\")|^(\')/';
        while (($i < strlen($subexpression)) && (!$endfound)) {
            $newsubexpression = substr($subexpression, $i);
            if ($this->is_element($newsubexpression, $quotepattern)) {
                $i++;
                if (!$quoted) {
                    $quoted = true;
                } else {
                    $endfound = true;
                }
            } else {
                if ($quoted) {
                    if (!$endfound) {
                        $i++;
                    }
                } else {
                    $iswhitespace = $this->is_whitespace($newsubexpression);
                    $isoperator = $this->is_operator($newsubexpression);
                    $isfunction = $this->is_function($newsubexpression);
                    $isfield = $this->is_field($newsubexpression);
                    $isdigit = $this->is_digit($newsubexpression);
                    if (!$iswhitespace && !$isoperator && !$isfunction && !$isfield && !$isdigit) {
                        $i++;
                    } else {
                        $endfound = true;
                    }
                }
            }
        }
        if ($endfound) {
            if ($quoted) {
                $newpattern = preg_quote(substr($subexpression, 0, $i));
                return $this->is_element($subexpression, '/^' . $newpattern . '/');
            }
        } else {
            if (!$quoted) {
                $newpattern = preg_quote(substr($subexpression, 0, $i));
                return $this->is_element($subexpression, '/^' . $newpattern . '/');
            }
        }
        return false;
    }
  
}
