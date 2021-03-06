<?php
/*
 * Copyright (c) 2013 Michel Petit <petit.michel@gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Malenki\Argile;

use \Malenki\Ansi;
use \Malenki\Bah\S;

/**
 * Arg
 *
 * @author Michel Petit <petit.michel@gmail.com>
 */
class OptionItem
{
    const ARG_SWITCH = 0;
    const ARG_VALUE = 1;
    const ARG_REQUIRED_VALUE = ':';
    const ARG_OPTIONAL_VALUE = '::';
    const VAR_HELP_DEFAULT = 'VALUE';
    const HELP_LINE_WIDTH = 79;
    const HELP_START_TEXT = 30;

    protected $bool_required = false;
    protected static $bool_flexible = false;

    /**
     * L’argument sous sa forme courte.
     *
     * Si non renseigné, reste à Null.
     *
     * @var string
     * @access protected
     */
    protected $str_short = null;

    /**
     * L’argument sous sa forme longue.
     *
     * Si non renseigné, reste à Null.
     *
     * @var string
     * @access protected
     */
    protected $str_long = null;

    /**
     * Le texte d’aide de l’argument.
     *
     * Si non renseigné, reste à Null.
     *
     * @var string
     * @access protected
     */
    protected $str_help = null;
    protected $str_name = null;
    protected $str_var_help = self::VAR_HELP_DEFAULT;
    protected $str_color = null;
    protected $bool_bold = false;

    protected $int_type = self::ARG_SWITCH;
    protected $mixed_value = null;

    /**
     * @param integer $type
     * @param string  $name
     */
    private function __construct($type, $name)
    {
        $this->int_type = $type;
        $this->str_name = $name;

        if ($type == self::ARG_SWITCH) {
            $this->mixed_value = true;
        }
    }

    public function color($str_color)
    {
        $this->str_color = $str_color;

        return $this;
    }

    public function bold()
    {
        $this->bool_bold = true;

        return $this;
    }

    /**
     * @return boolean
     */
    protected function check()
    {
        return($this->str_short || $this->str_long);
    }

    public function setValue($value)
    {
        $this->mixed_value = value;

        return $this;
    }

    /**
     * @param  string     $name
     * @return OptionItem
     */
    public static function createSwitch($name)
    {
        return new self(self::ARG_SWITCH, $name);
    }

    /**
     * @param  string     $name
     * @return OptionItem
     */
    public static function createValue($name)
    {
        return new self(self::ARG_VALUE, $name);
    }

    public static function flexible()
    {
        self::$bool_flexible = true;
    }

    public static function getWidth()
    {
        if (self::$bool_flexible && function_exists('shell_exec')) {
            // Linux, Mac…
            if (DIRECTORY_SEPARATOR == '/') {
                if (function_exists('shell_exec')) {
                    $str_out = shell_exec('stty -a');
                    $arr = array();
                    $found = (boolean) preg_match('/([0-9 ]*)columns([0-9 ]*)/', $str_out, $arr);

                    if ($found) {
                        return strlen(trim($arr[1])) ? (int) trim($arr[1]) : (int) trim($arr[2]);
                    }
                }
            }
            // Windows
            else {
                if (function_exists('exec')) {
                    $arr_output = array();
                    $str_out = exec('MODE CON', $arr_output);

                    if (isset($arr_output[4])) {
                        $arr = array();
                        $found = (boolean) preg_match('/([0-9]+)/', $arr_output[4], $arr);

                        if ($found) {
                            return (int) $arr[1];
                        }
                    }
                }
            }
        }

        return self::HELP_LINE_WIDTH;
    }

    /**
     * @param  string $str
     * @return string
     */
    protected static function removeColon($str)
    {
        return preg_replace('/:+/', '', $str);
    }

    public function isValue()
    {
        return $this->int_type == self::ARG_VALUE;
    }

    public function required()
    {
        $this->bool_required = true;

        return $this;
    }

    public function isRequiredValue()
    {
        /*

        return(
            preg_match(sprintf('/%s$/', self::ARG_REQUIRED_VALUE), $this->str_short)
            ||
            preg_match(sprintf('/%s$/', self::ARG_REQUIRED_VALUE), $this->str_long)
        );
         */

        return $this->bool_required;
    }

    /**
     * @param  string     $str
     * @return OptionItem L’objet lui-même est retourné pour chaîner…
     */
    public function short($str)
    {
     //   $this->bool_required = (boolean) preg_match(sprintf('/%s$/', self::ARG_REQUIRED_VALUE), $str);
        $this->str_short = (strlen($str)) ? self::removeColon($str) : null;

        return $this;
    }

    /**
     * @param  string     $str
     * @return OptionItem L’objet lui-même est retourné pour chaîner…
     */
    public function long($str)
    {
       // $this->bool_required = (boolean) preg_match(sprintf('/%s$/', self::ARG_REQUIRED_VALUE), $str);
        $this->str_long = (strlen($str)) ? self::removeColon($str) : null;

        return $this;
    }

    /**
     * @param  string     $str
     * @return OptionItem L’objet lui-même est retourné pour chaîner…
     */
    public function help($str, $str_var = null)
    {
        $this->str_help = (strlen($str)) ? $str : null;

        if (is_string($str_var) && strlen(trim($str_var))) {
            $this->str_var_help = mb_strtoupper($str_var, 'UTF-8');
        }

        return $this;
    }

    /**
     * @return boolean
     */
    public function hasShort()
    {
        return(is_string($this->str_short));
    }

    /**
     * @return boolean
     */
    public function hasLong()
    {
        return(is_string($this->str_long));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->str_name;
    }

    /**
     * @return string
     */
    public function getShort($as_getopt = false)
    {
        if (!$as_getopt) {
            return $this->str_short;
        } else {
            if (!$this->isValue()) {
                return $this->str_short;
            }

            if ($this->isRequiredValue()) {
                return $this->str_short . self::ARG_REQUIRED_VALUE;
            } else {
                return $this->str_short . self::ARG_OPTIONAL_VALUE;
            }
        }
    }

    /**
     * @return string
     */
    public function getLong($as_getopt = false)
    {
        if (!$as_getopt) {
            return $this->str_long;
        } else {
            if (!$this->isValue()) {
                return $this->str_long;
            }

            if ($this->isRequiredValue()) {
                return $this->str_long . self::ARG_REQUIRED_VALUE;
            } else {
                return $this->str_long . self::ARG_OPTIONAL_VALUE;
            }
        }
    }

    /**
     * @return string
     */
    public function getHelp()
    {
        return $this->str_help;
    }

    public function getValue()
    {
        return $this->mixed_value;
    }

    /**
     * Dans un contexte de chaîne, affiche l’aide de l’argument.
     *
     * @access public
     * @return string
     */
    public function __toString()
    {
        $arr_prov = array();
        $str_var_help = '';
        $str_arg = '';
        $str_help = '';

        if ($this->isValue()) {
            if ($this->isRequiredValue()) {
                $str_var_help = sprintf('=%s', $this->str_var_help);
            } else {
                $str_var_help = sprintf('[=%s]', $this->str_var_help);
            }
        }

        if (!is_null($this->str_short) && !is_null($this->str_long)) {
            $str_arg = sprintf(
                '  -%s, --%s',
                self::removeColon($this->str_short),
                self::removeColon($this->str_long) . $str_var_help
            );
        } elseif (!is_null($this->str_long)) {
            $str_arg = sprintf('      --%s', self::removeColon($this->str_long) . $str_var_help);
        } elseif (!is_null($this->str_short)) {
            $str_arg = sprintf('  -%s', self::removeColon($this->str_short) . $str_var_help);
        }

        $int_arg_length = mb_strlen($str_arg, 'UTF-8');

        if ($this->str_color || $this->bool_bold) {
            $arg = new Ansi($str_arg);

            if ($this->str_color) {
                $arg->fg($this->str_color);
            }

            if ($this->bool_bold) {
                $arg->bold;
            }
        }

        if (isset($arg)) {
            $str_arg = $arg;
        }

        $help = new S($this->str_help);

        if (self::getWidth() > (self::HELP_START_TEXT * 2)) {
            $help = $help->wrap(self::getWidth() - self::HELP_START_TEXT - 1);

            if ($int_arg_length < self::HELP_START_TEXT - 1) {
                $str_arg = $str_arg . str_repeat(' ', self::HELP_START_TEXT - 1 - $int_arg_length);

                return $str_arg . $help->margin(self::HELP_START_TEXT - 1, 0, -(self::HELP_START_TEXT - 1));
            } else {
                $str_arg = $str_arg . PHP_EOL;

                return $str_arg . $help->margin(self::HELP_START_TEXT - 1);
            }
        } else {
            $str_arg = $str_arg . PHP_EOL;

            return $str_arg . $help->wrap(self::getWidth());
        }
    }
}
