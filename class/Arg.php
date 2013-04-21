<?php
/*
 * This file is part of Argile.
 *
 * Argile is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * Argile is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with Argile.  If not, see <http://www.gnu.org/licenses/>.
 */


namespace Malenki\Opt;

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

    /**
     * @param string $str
     * @return string
     */
    protected function removeColon($str)
    {
        return preg_replace('/:+/', '', $str);
    }

    public function isValue()
    {
        return $this->int_type == self::ARG_VALUE;
    }

    public function isOptionalValue()
    {
        return(
            preg_match(sprintf('/%s$/', self::ARG_OPTIONAL_VALUE), $this->str_short)
            ||
            preg_match(sprintf('/%s$/', self::ARG_OPTIONAL_VALUE), $this->str_long)
        );
    }
    
    
    public function isRequiredValue()
    {
        return(
            preg_match(sprintf('/%s$/', self::ARG_REQUIRED_VALUE), $this->str_short)
            ||
            preg_match(sprintf('/%s$/', self::ARG_REQUIRED_VALUE), $this->str_long)
        );
    }

    /**
     * @param string $str
     * @return Arg L’objet lui-même est retourné pour chaîner…
     */
    public function setShort($str)
    {
        $this->str_short = (strlen($str)) ? $str : null;
        return $this;
    }

    /**
     * @param string $str
     * @return Arg L’objet lui-même est retourné pour chaîner…
     */
    public function setLong($str)
    {
        $this->str_long = (strlen($str)) ? $str : null;
        return $this;
    }

    public function setVarHelp($str)
    {
        if(is_string($str) && strlen(trim($str)))
        {
            $this->str_var_help = mb_strtoupper($str, 'UTF-8');
        }
        return $this;
    }


    /**
     * @param string $str
     * @return Arg L’objet lui-même est retourné pour chaîner…
     */
    public function setHelp($str)
    {
        $this->str_help = (strlen($str)) ? $str : null;
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
     * @param boolean $without_colon
     * @return string
     */
    public function getShort($without_colon = false)
    {
        if($without_colon)
        {
            return $this->removeColon($this->str_short);
        }
        return $this->str_short;
    }


    /**
     * @param boolean $without_colon
     * @return string
     */
    public function getLong($without_colon = false)
    {
        if($without_colon)
        {
            return $this->removeColon($this->str_long);
        }
        return $this->str_long;
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
            if($this->isOptionalValue())
            {
                $str_var_help = sprintf('[=%s]', $this->str_var_help);
            }
            else
            {
                $str_var_help = sprintf('=%s', $this->str_var_help);
            }
        }


        if(!is_null($this->str_short) && !is_null($this->str_long))
        {
            $str_arg = sprintf(
                '  -%s, --%s',
                $this->removeColon($this->str_short),
                $this->removeColon($this->str_long) . $str_var_help
            );
        }
        else if(!is_null($this->str_long))
        {
            $str_arg = sprintf('      --%s', $this->removeColon($this->str_long) . $str_var_help);
        }
        else if(!is_null($this->str_short))
        {
            $str_arg = sprintf('  -%s', $this->removeColon($this->str_short) . $str_var_help);
        }

        if(mb_strlen($str_arg, 'UTF-8') < 29)
        {
            $str_arg = $str_arg . str_repeat(' ', 29 - mb_strlen($str_arg, 'UTF-8'));
        }
        else
        {
            $str_arg = $str_arg . "\n";
        }

        if($this->str_help)
        {
            $str_help = preg_replace(
                '/(?=\s)(.{1,'. (79 - 29) .'})(?:\s|$)/uS',
                "$1\n".str_repeat(' ', 29),
                $this->str_help
            );
        }

        return $str_arg . $str_help;
    }
}
