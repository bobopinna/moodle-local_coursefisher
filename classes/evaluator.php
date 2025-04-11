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
 * @copyright  2022 Roberto Pinna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursefisher;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/evalmath/evalmath.class.php');

/**
 * Course fisher filter evaluation class
 *
 * @package    local_coursefisher
 * @copyright  2022 Roberto Pinna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class evaluator extends \EvalMath {

    /**
     * @var string Pattern used for a valid function or variable name. Note, var and func names are case insensitive.
     */
    private static $namepat = '[a-zA-Z][a-zA-Z0-9_]*';

    /**
     * @var array Built-in functions.
     */
    public $fb = [
        'sin', 'sinh', 'asin', 'asinh', 'cos', 'cosh', 'acos', 'acosh', 'tan', 'tanh', 'atan', 'atanh',
        'sqrt', 'abs', 'log', 'log10', 'exp', 'floor', 'ceil', 'is_finite', 'is_infinite', 'is_nan',
        'is_bool', 'is_float', 'is_int', 'is_numeric', 'is_string', 'addslashes', 'stripslashes', 'quotemeta',
        'chr', 'ord', 'lvfirst', 'ucfirst', 'strtolower', 'strtoupper', 'ucfirst', 'strlen', 'strrev',
        'str_shuffle', 'crc32', 'str_rot13', 'utf8_encode', 'utf8_decode', 'intval', 'floatval',
        'bin2hex', 'bindec', 'decbin', 'dechex', 'decoct', 'deg2rad', 'hex2bin', 'hexdec', 'octdec', 'rad2deg'];

    /**
     * @var array Built-in constants.
     */
    public $v = ['true' => 1, 'false' => 0];

    /**
     * Evaluate an expression.
     *
     * @param string $expr The expression to evaluate.
     *
     * @return int or false
     */
    public function evaluate($expr) {
        $this->last_error = null;
        $expr = trim($expr);

        if (substr($expr, -1, 1) == ';') {
            // Strip semicolons at the end.
            $expr = substr($expr, 0, strlen($expr) - 1);
        }

        return $this->pfx($this->nfx($expr));
    }

    /**
     * Convert given expression from infix to postfix notation.
     *
     * @param string $expr The expression to postfix
     *
     * @return array or false
     */
    public function nfx($expr) {

        $index = 0;
        $stack = new \EvalMathStack;
        // Postfix form of expression, to be passed to pfx().
        $output = [];
        $expr = trim($expr);
        // MDL-14274: new operators for comparison added.
        $ops   = ['+', '-', '*', '/', '^', '_', '!', '>', '<', '<=', '>=', '==', '!=', '&&', '||'];
        // Right-associative operator?.
        $opsr = ['+' => 0, '-' => 0, '*' => 0, '/' => 0, '^' => 1];
        // Operator precedence.
        $opsp = [
                '_' => 14,
                '!' => 14,
                '^' => 13,
                '*' => 12,
                '/' => 12,
                '+' => 11,
                '-' => 11,
                '>' => 9,
                '<' => 9,
                '<=' => 9,
                '>=' => 9,
                '==' => 8,
                '!=' => 8,
                '&&' => 4,
                '||' => 3,
        ];

        // We use this in syntax-checking the expression and determining when a - is a negation.
        $expectingop = false;

        // Infinite Loop (1) ;).
        while (1) {
            // MDL-14274 Test two character operators.
            $op = substr($expr, $index, 2);
            if (!in_array($op, $ops)) {
                // MDL-14274 Get one character operator.
                // get the first character at the current index.
                $op = substr($expr, $index, 1);
            }

            // Find out if we're currently at the beginning of a number/variable/function/parenthesis/operand.
            $ex = preg_match('/^('.self::$namepat.'\(?|\d+(?:\.\d*)?(?:(e[+-]?)\d*)?|\.\d+|\()/', substr($expr, $index), $match);

            // Is it a negation instead of a minus?
            if ($op == '-' && !$expectingop) {
                // Put a negation on the stack.
                $stack->push('_');
                $index++;

            } else if ($op == '!' && !$expectingop) {
                // Put boolean negation on the stack.
                $stack->push('!');
                $index++;

            } else if ($op == '_') {
                // We have to explicitly deny this, because it's legal on the stack but not in the input expression.
                return $this->trigger(get_string('illegalcharacterunderscore', 'mathslib'));

            } else if ((in_array($op, $ops) || $ex) && $expectingop) {
                // Are we putting an operator on the stack?
                if ($ex) {
                    // Are we expecting an operator but have a number/variable/function/opening parethesis/openning quote?.
                    if (!$this->allowimplicitmultiplication) {
                        return $this->trigger(get_string('implicitmultiplicationnotallowed', 'mathslib'));
                    } else {
                        // It's an implicit multiplication.
                        $op = '*';
                        $index--;
                    }
                }
                // Heart of the algorithm.
                while ($stack->count > 0 && ($o2 = $stack->last()) && in_array($o2, $ops)
                        && ($opsr[$op] ? $opsp[$op] < $opsp[$o2] : $opsp[$op] <= $opsp[$o2])) {
                    // Pop stuff off the stack into the output.
                    $output[] = $stack->pop();
                }
                // Many thanks: http://en.wikipedia.org/wiki/Reverse_Polish_notation#The_algorithm_in_detail.

                // Finally put OUR operator onto the stack.
                $stack->push($op);
                $index += strlen($op);
                $expectingop = false;

            } else if ($op == ')' && $expectingop) {
                // Ready to close a parenthesis?
                while (($o2 = $stack->pop()) != '(') {
                    // Pop off the stack back to the last (.
                    if (is_null($o2)) {
                        return $this->trigger(get_string('unexpectedclosingbracket', 'mathslib'));
                    } else {
                        $output[] = $o2;
                    }
                }
                if (preg_match('/^('.self::$namepat.')\($/', $stack->last(2), $matches)) {
                    // Did we just close a function?

                    // Get the function name.
                    $fnn = strtolower($matches[1]);
                    // See how many arguments there were (cleverly stored on the stack, thank you).
                    $argcount = $stack->pop();
                    $fn = $stack->pop();
                    // Send function to output.
                    $output[] = ['fn' => $fn, 'fnn' => $fnn, 'argcount' => $argcount];
                    // Check the argument count.
                    if (in_array($fnn, $this->fb)) {
                        if ($argcount > 1) {
                            $a = new stdClass();
                            $a->expected = 1;
                            $a->given = $argcount;
                            return $this->trigger(get_string('wrongnumberofarguments', 'mathslib', $a));
                        }
                    } else if ($this->get_native_function_name($fnn)) {
                        // Resolve synonyms.
                        $fnn = $this->get_native_function_name($fnn);

                        $counts = $this->fc[$fnn];
                        if (in_array(-1, $counts) && $argcount > 0) {
                            break;
                        } else if (!in_array($argcount, $counts)) {
                            $a = new stdClass();
                            $a->expected = implode('/', $this->fc[$fnn]);
                            $a->given = $argcount;
                            return $this->trigger(get_string('wrongnumberofarguments', 'mathslib', $a));
                        }
                    } else if (array_key_exists($fnn, $this->f)) {
                        if ($argcount != count($this->f[$fnn]['args'])) {
                            $a = new stdClass();
                            $a->expected = count($this->f[$fnn]['args']);
                            $a->given = $argcount;
                            return $this->trigger(get_string('wrongnumberofarguments', 'mathslib', $a));
                        }
                    } else {
                        // Did we somehow push a non-function on the stack? this should never happen.
                        return $this->trigger(get_string('internalerror', 'mathslib'));
                    }
                }
                $index++;

            } else if ($op == ',' && $expectingop) {
                // Did we just finish a function argument?
                while (($o2 = $stack->pop()) != '(') {
                    if (is_null($o2)) {
                        // Oops, never had a (.
                        return $this->trigger(get_string('unexpectedcomma', 'mathslib'));
                    } else {
                        // Pop the argument expression stuff and push onto the output.
                        $output[] = $o2;
                    }
                }
                // Make sure there was a function.
                if (!preg_match('/^('.self::$namepat.')\($/', $stack->last(2), $matches)) {
                    return $this->trigger(get_string('unexpectedcomma', 'mathslib'));
                }
                // Increment the argument count.
                $stack->push($stack->pop() + 1);
                // Put the ( back on, we'll need to pop back to it again.
                $stack->push('(');
                $index++;
                $expectingop = false;

            } else if ($op == '(' && !$expectingop) {
                // That was easy.
                $stack->push('(');
                $index++;
                $allowneg = true;

            } else if ($ex && !$expectingop) {
                // Do we now have a function/variable/number?
                $expectingop = true;
                $val = strtolower($match[1]);
                if (preg_match('/^('.self::$namepat.')\($/', $val, $matches)) {
                    // May be func, or variable w/ implicit multiplication against parentheses...
                    if (in_array($matches[1], $this->fb) || array_key_exists($matches[1], $this->f) ||
                            $this->get_native_function_name($matches[1])) {
                        // It's a func.
                        $stack->push($val);
                        $stack->push(1);
                        $stack->push('(');
                        $expectingop = false;
                    } else {
                        // It's a var w/ implicit multiplication.
                        $val = $matches[1];
                        $output[] = $val;
                    }
                } else {
                    // It's a plain old var or num.
                    $output[] = $val;
                }
                $index += strlen($val);

            } else if ($op == '"' && !$expectingop) {
                // Ready for a string?.
                $quotepos = mb_strpos($expr, $op, $index + 1);
                $val = 1;
                if ($quotepos !== false) {
                    $val = mb_substr($expr, $index, $quotepos - $index + 1);
                    $output[] = $val;
                } else {
                    return $this->trigger(get_string('unexpectedclosingquote', 'local_coursefisher'));
                }
                $index += strlen($val);
                $expectingop = true;

            } else if ($op == ')') {
                // It could be only custom function with no params or general error.
                if ($stack->last() != '(' || $stack->last(2) != 1) {
                    return $this->trigger(get_string('unexpectedclosingbracket', 'mathslib'));
                }
                // Did we just close a function?.
                if (preg_match('/^('.self::$namepat.')\($/', $stack->last(3), $matches)) {
                    // Pop (.
                    $stack->pop();
                    // Pop 1.
                    $stack->pop();
                    $fn = $stack->pop();
                    // Get the function name.
                    $fnn = $matches[1];
                    // Resolve synonyms.
                    $fnn = $this->get_native_function_name($fnn);
                    $counts = $this->fc[$fnn];
                    if (!in_array(0, $counts)) {
                        $a = new stdClass();
                        $a->expected = $this->fc[$fnn];
                        $a->given = 0;
                        return $this->trigger(get_string('wrongnumberofarguments', 'mathslib', $a));
                    }
                    // Send function to output.
                    $output[] = ['fn' => $fn, 'fnn' => $fnn, 'argcount' => 0];
                    $index++;
                    $expectingop = true;
                } else {
                    return $this->trigger(get_string('unexpectedclosingbracket', 'mathslib'));
                }

            } else if (in_array($op, $ops) && !$expectingop) {
                // Miscellaneous error checking.
                return $this->trigger(get_string('unexpectedoperator', 'mathslib', $op));
            } else {
                // I don't even want to know what you did to get here.
                return $this->trigger(get_string('anunexpectederroroccured', 'mathslib'));
            }

            if ($index == strlen($expr)) {
                // Did we end with an operator? bad.
                if (in_array($op, $ops)) {
                    return $this->trigger(get_string('operatorlacksoperand', 'mathslib', $op));
                } else {
                    break;
                }
            }
            while (substr($expr, $index, 1) == ' ') {
                // Step index past whitespace (pretty much turns whitespace into implicit multiplication if no operator is there).
                $index++;
            }

        }
        while (!is_null($op = $stack->pop())) {
            // Pop everything off the stack and push onto output.
            if ($op == '(') {
                // If there are (s on the stack, ()s were unbalanced.
                return $this->trigger(get_string('expectingaclosingbracket', 'mathslib'));
            }
            $output[] = $op;
        }
        return $output;
    }

    /**
     * Evaluate postfix notation.
     *
     * @param array $tokens Array of postfixed element of an expression
     * @param array $vars User defined variables
     *
     * @return mixed or false
     */
    public function pfx($tokens, $vars = []) {

        if ($tokens == false) {
            return false;
        }

        $stack = new \EvalMathStack();

        foreach ($tokens as $token) {
            // Nice and easy.

            // If the token is a function, pop arguments off the stack, hand them to the function, and push the result back on.
            if (is_array($token)) {
                // It's a function!
                $fnn = $token['fnn'];
                $count = $token['argcount'];
                if (in_array($fnn, $this->fb)) {
                    // Built-in function.
                    if (is_null($op1 = $stack->pop())) {
                        return $this->trigger(get_string('internalerror', 'mathslib'));
                    }
                    if (is_callable($fnn)) {
                        $stack->push($fnn($op1));
                    }
                } else if ($this->get_native_function_name($fnn)) {
                    // Calc emulation function.

                    // Resolve synonyms.
                    $fnn = $this->get_native_function_name($fnn);
                    // Get args.
                    $args = [];
                    for ($i = $count - 1; $i >= 0; $i--) {
                        if (is_null($args[] = $stack->pop())) {
                            return $this->trigger(get_string('internalerror', 'mathslib'));
                        }
                    }
                    $res = call_user_func_array(['EvalMathFuncs', $fnn], array_reverse($args));
                    if ($res === false) {
                        return $this->trigger(get_string('internalerror', 'mathslib'));
                    }
                    $stack->push($res);
                } else if (array_key_exists($fnn, $this->f)) {
                    // User function.

                    // Get args.
                    $args = [];
                    for ($i = count($this->f[$fnn]['args']) - 1; $i >= 0; $i--) {
                        if (is_null($args[$this->f[$fnn]['args'][$i]] = $stack->pop())) {
                            return $this->trigger(get_string('internalerror', 'mathslib'));
                        }
                    }
                    // Yay ... it's recursion!!!!
                    $stack->push($this->pfx($this->f[$fnn]['func'], $args));
                }
            } else if (in_array($token, ['+', '-', '*', '/', '^', '>', '<', '==', '<=', '>=', '!=', '&&', '||'], true)) {
                // If the token is a binary operator, pop two values off the stack, do the operation, and push the result back on.
                if (is_null($op2 = $stack->pop())) {
                    return $this->trigger(get_string('internalerror', 'mathslib'));
                }
                if (is_null($op1 = $stack->pop())) {
                    return $this->trigger(get_string('internalerror', 'mathslib'));
                }
                switch ($token) {
                    case '+':
                        if ($this->is_a_string($op1) && $this->is_a_string($op2)) {
                            $stack->push(mb_substr($op1, 0, -1) . mb_substr($op2, 1, null));
                        } else {
                            $stack->push($op1 + $op2);
                        }
                        break;
                    case '-':
                        $stack->push($op1 - $op2);
                        break;
                    case '*':
                        $stack->push($op1 * $op2);
                        break;
                    case '/':
                        if ($op2 == 0) {
                            return $this->trigger(get_string('divisionbyzero', 'mathslib'));
                        }
                        $stack->push($op1 / $op2);
                        break;
                    case '^':
                        $stack->push(pow($op1, $op2));
                        break;
                    case '>':
                        $stack->push((int)($op1 > $op2));
                        break;
                    case '<':
                        $stack->push((int)($op1 < $op2));
                        break;
                    case '==':
                        $stack->push((int)($op1 == $op2));
                        break;
                    case '<=':
                        $stack->push((int)($op1 <= $op2));
                        break;
                    case '>=':
                        $stack->push((int)($op1 >= $op2));
                    break;
                    case '!=':
                        $stack->push((int)($op1 != $op2));
                        break;
                    case '&&':
                        $stack->push((int)($op1 && $op2));
                        break;
                    case '||':
                        $stack->push((int)($op1 || $op2));
                        break;
                }
            } else if ($token == "_") {
                // If the token is a unary operator, pop one value off the stack, do the operation, and push it back on.
                $stack->push(-1 * $stack->pop());
            } else if ($token == "!") {
                // If the token is a unary operator, pop one value off the stack, do the operation, and push it back on.
                $stack->push((int) !$stack->pop());
            } else {
                // If the token is a number or variable, push it on the stack.
                if (is_numeric($token) || $this->is_a_string($token)) {
                    $stack->push($token);
                } else if (array_key_exists($token, $this->v)) {
                    $stack->push($this->v[$token]);
                } else if (array_key_exists($token, $vars)) {
                    $stack->push($vars[$token]);
                } else {
                    return $this->trigger(get_string('undefinedvariable', 'mathslib', $token));
                }
            }
        }
        // When we're out of tokens, the stack should have a single element, the final result.
        if ($stack->count != 1) {
            return $this->trigger(get_string('internalerror', 'mathslib'));
        }
        return $stack->pop();
    }

    /**
     * Check if given value is a string.
     *
     * @param string $val
     *
     * @return boolean
     */
    private function is_a_string(string $val) {
        if (mb_substr($val, 0, 1) == '"' && mb_substr($val, -1, 1) == '"') {
            return true;
        }
        return false;
    }
}
