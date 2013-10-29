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
use Malenki\Argile\Arg as Arg;

class Options
{
    protected static $obj_instance = null;
    protected static $arr_arg = array();
    protected static $arr_group = array();
    protected static $arr_prohibited = array('h', 'help', 'version');
    protected $arr_parsed = array();
    
    protected $arr_switch = array();
    protected $arr_values = array();

    protected $str_usage = null;
    protected $str_description = null;
    protected $str_help = 'Display this help message and exit';
    protected $str_version = 'Display version information and exit';

        
    public static function getInstance()
    {
        if(is_null(self::$obj_instance))
        {
            self::$obj_instance = new self();
        }

        return self::$obj_instance;
    }

    public function flexible()
    {
        Arg::flexible();
    }

    /**
     * Examine les paramètres fournis en ligne de commande.
     *
     * Si aucun paramètre n’est fourni et si cette méthode est appelée sans 
     * argument, alors l’aide sera afficher d’office. Si un argument vallant 
     * false est passé, alors l’aide ne sera pas affichée, ce qui permettra 
     * d’exécuter le reste du programme. 
     * 
     * @param boolean $bool_display_help 
     * @access public
     * @return void
     */
    public function parse($bool_display_help = true)
    {
        $this->arr_parsed = getopt(
            $this->getShort(),
            $this->getLong()
        );

        if(count($this->arr_parsed) == 0 && $bool_display_help)
        {
            $this->displayHelp();
        }
    }


    public function usage($str)
    {
        $this->str_usage = $str;
    }

    public function description($str)
    {
        $this->str_description = $str;
    }
    
    
    public function help($str)
    {
        $this->str_help = $str;
    }
    
    
    public function version($str)
    {
        $this->str_version = $str;
    }

    public function addGroup($str_alias, $str_name = null)
    {
        if(!isset(self::$arr_group[$str_alias]))
        {
            $grp = new \stdClass();
            $grp->name = (strlen($str_name)) ? $str_name : null;
            $grp->args = array();

            self::$arr_group[$str_alias] = $grp;
        }
    }

    /**
     * add 
     * 
     * @param Arg $arg 
     * @param mixed $str_alias 
     * @static
     * @access public
     * @return void
     */
    public static function add(Arg $arg, $str_alias = null)
    {
        // tester ici si version ou aide : à ne pas mettre
        if(
            !in_array($arg->getShort(true), self::$arr_prohibited, true)
            &&
            !in_array($arg->getLong(true), self::$arr_prohibited, true)
        )
        {
            if(is_string($str_alias) && isset(self::$arr_group[$str_alias]))
            {
                self::$arr_group[$str_alias]->args[$arg->getName()] = $arg;
            }
            else
            {
                self::$arr_arg[$arg->getName()] = $arg;
            }
        }
    }


    protected function getShort()
    {
        $str_out = '';

        foreach(self::$arr_arg as $arg)
        {
            if($arg->hasShort())
            {
                $str_out .= $arg->getShort(true);
            }
        }

        foreach(self::$arr_group as $group)
        {
            foreach($group->args as $arg)
            {
                if($arg->hasShort())
                {
                    $str_out .= $arg->getShort(true);
                }
            }

        }
        return $str_out;
    }

    protected function getLong()
    {
        $arr_out = array();

        foreach(self::$arr_arg as $arg)
        {
            if($arg->hasLong())
            {
                $arr_out[] = $arg->getLong(true);
            }
        }

        foreach(self::$arr_group as $group)
        {
            foreach($group->args as $arg)
            {
                $arr_out[] = $arg->getLong(true);
            }

        }

        return $arr_out;
    }


    public function newSwitch($name, $group = null)
    {
        self::add(Arg::createSwitch($name), $group);
        return self::getArg($name);
    } 



    public function newValue($name, $group = null)
    {
        self::add(Arg::createValue($name), $group);
        return self::getArg($name);
    } 


    /**
     * Créer la chaîne d’utilisation.
     * 
     * @access public
     * @return string
     */
    public function getUsage()
    {
        global $argv;
        $str_usage = "[OPTIONS]…";

        if(is_string($this->str_usage))
        {
            $str_usage = $this->str_usage;
        }

        return sprintf('Usage: %s %s', basename($argv[0]), $str_usage);
    }

    /**
     * Retourne la description du programme 
     * 
     * @access public
     * @return string
     */
    public function getDescription()
    {
        if(is_string($this->str_description))
        {
            return $this->str_description;
        }
        else
        {
            return null;
        }
    }

    public function displayHelp()
    {
        printf("%s\n", $this->getUsage());
        printf("%s\n", $this->getDescription());
        

        self::addGroup('helpversion');

        // On ajoute les options spéciales Help et Version
        self::$arr_group['helpversion']->args[] = Arg::createSwitch('help')
            ->short('h')
            ->long('help')
            ->help($this->str_help)
        ;

        self::$arr_group['helpversion']->args[] = Arg::createSwitch('version')
            ->long('version')
            ->help($this->str_version)
        ;

        // Les options non incluses dans un groupe
        if(count(self::$arr_arg))
        {
            foreach(self::$arr_arg as $arg)
            {
                printf("%s\n", rtrim($arg));
            }
        }

        // Options faisant partie d’un groupe
        if(count(self::$arr_group))
        {
            foreach(self::$arr_group as $group)
            {
                if(count($group->args))
                {
                    print("\n\n");

                    if($group->name)
                    {
                        printf("%s\n", $group->name);
                    }
                    foreach($group->args as $arg)
                    {
                        printf("%s\n", rtrim($arg));
                    }
                }
            }
        }

        exit();
    }


    /**
     * has 
     * 
     * @param mixed $str 
     * @access public
     * @return void
     */
    public function has($str)
    {
        $arg = self::getArg($str);
        return
            is_object($arg)
            &&
            (
                isset($this->arr_parsed[$arg->getLong()])
                ||
                (isset($this->arr_parsed[$arg->getShort()])
            )
        );
    }

    /**
     * getArg 
     * 
     * @param mixed $str 
     * @static
     * @access public
     * @return void
     */
    public static function getArg($str)
    {
        $arg = false;

        if(isset(self::$arr_arg[$str]))
        {
            $arg = self::$arr_arg[$str];
        }
        else
        {
            foreach(self::$arr_group as $group)
            {
                if(isset($group->args[$str]))
                {
                    $arg = $group->args[$str];
                    break;
                }
            }
        }

        return $arg;
    }

    /**
     * @param string $str
     * @return mixed
     */
    public function get($str)
    {
        $arg = self::getArg($str);

        if(isset($this->arr_parsed[$arg->getLong()]))
        {
            return $this->arr_parsed[$arg->getLong()];
        }
        else
        {
            return $this->arr_parsed[$arg->getShort()];
        }
    }	
}
