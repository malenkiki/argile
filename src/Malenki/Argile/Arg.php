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

/**
 * Arg 
 * 
 * @author Michel Petit <petit.michel@gmail.com> 
 */
class Arg 
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
        
        if($type == self::ARG_SWITCH)
        {
            $this->mixed_value = true;
        }
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
    }


    /**
     * @param string $name
     * @return Arg
     */
    public static function createSwitch($name)
    {
        return new self(self::ARG_SWITCH, $name);
    }

    /**
     * @param string $name
     * @return Arg
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
        if(self::$bool_flexible && function_exists('shell_exec'))
        {
            $out = shell_exec('stty -a');
            $arr = array();
            $found = (boolean) preg_match('/([0-9 ]*)columns([0-9 ]*)/', $out, $arr);

            if($found)
            {
                return strlen(trim($arr[1])) ? (int) trim($arr[1]) : (int) trim($arr[2]);
            }
        }

        return self::HELP_LINE_WIDTH;
    }

    /**
     * @param string $str
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
     * @param string $str
     * @return Arg L’objet lui-même est retourné pour chaîner…
     */
    public function short($str)
    {
     //   $this->bool_required = (boolean) preg_match(sprintf('/%s$/', self::ARG_REQUIRED_VALUE), $str);
        $this->str_short = (strlen($str)) ? self::removeColon($str) : null;
        return $this;
    }

    /**
     * @param string $str
     * @return Arg L’objet lui-même est retourné pour chaîner…
     */
    public function long($str)
    {
       // $this->bool_required = (boolean) preg_match(sprintf('/%s$/', self::ARG_REQUIRED_VALUE), $str);
        $this->str_long = (strlen($str)) ? self::removeColon($str) : null;
        return $this;
    }


    /**
     * @param string $str
     * @return Arg L’objet lui-même est retourné pour chaîner…
     */
    public function help($str, $str_var = null)
    {
        $this->str_help = (strlen($str)) ? $str : null;

        if(is_string($str_var) && strlen(trim($str_var)))
        {
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
        if(!$as_getopt)
        {
            return $this->str_short;
        }
        else
        {
            if(!$this->isValue())
            {
                return $this->str_short;
            }

            if($this->isRequiredValue())
            {
                return $this->str_short . self::ARG_REQUIRED_VALUE;
            }
            else
            {
                return $this->str_short . self::ARG_OPTIONAL_VALUE;
            }
        }
    }


    /**
     * @return string
     */
    public function getLong($as_getopt = false)
    {
        if(!$as_getopt)
        {
            return $this->str_long;
        }
        else
        {
            if(!$this->isValue())
            {
                return $this->str_long;
            }

            if($this->isRequiredValue())
            {
                return $this->str_long . self::ARG_REQUIRED_VALUE;
            }
            else
            {
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

        if($this->isValue())
        {
            if($this->isRequiredValue())
            {
                $str_var_help = sprintf('=%s', $this->str_var_help);
            }
            else
            {
                $str_var_help = sprintf('[=%s]', $this->str_var_help);
            }
        }


        if(!is_null($this->str_short) && !is_null($this->str_long))
        {
            $str_arg = sprintf(
                '  -%s, --%s',
                self::removeColon($this->str_short),
                self::removeColon($this->str_long) . $str_var_help
            );
        }
        else if(!is_null($this->str_long))
        {
            $str_arg = sprintf('      --%s', self::removeColon($this->str_long) . $str_var_help);
        }
        else if(!is_null($this->str_short))
        {
            $str_arg = sprintf('  -%s', self::removeColon($this->str_short) . $str_var_help);
        }

        if(mb_strlen($str_arg, 'UTF-8') < self::HELP_START_TEXT - 1)
        {
            $str_arg = $str_arg . str_repeat(' ', self::HELP_START_TEXT - 1 - mb_strlen($str_arg, 'UTF-8'));
        }
        else
        {
            $str_arg = $str_arg . "\n";
        }

        if($this->str_help)
        {
            $str_help = preg_replace(
                '/(?=\s)(.{1,'. (self::getWidth() - self::HELP_START_TEXT - 1) .'})(?:\s|$)/uS',
                "$1\n".str_repeat(' ', self::HELP_START_TEXT - 1),
                $this->str_help
            );
        }

        return $str_arg . $str_help;
    }
}
