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


class Options
{
    protected static $obj_instance = null;
    protected static $arr_arg = array();
    protected static $arr_group = array();
    protected static $arr_prohibited = array('h', 'help', 'version');
    protected $arr_parsed = array();
    protected $arr_argument = array();
    
    protected $arr_switch = array();
    protected $arr_values = array();

    protected $arr_usage = array();
    protected $str_description = null;
    protected $str_version = null;

        
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
        self::addGroup('helpversion');

        // On ajoute les options spéciales Help et Version
        self::$arr_group['helpversion']->args['help'] = Arg::createSwitch('help')
            ->short('h')
            ->long('help')
            ->help('Display this help message and exit')
        ;

        if($this->hasVersion())
        {
            self::$arr_group['helpversion']->args['version'] = Arg::createSwitch('version')
                ->long('version')
                ->help('Display version information and exit')
                ;
        }
        
        
        $this->arr_parsed = getopt(
            $this->getShort(),
            $this->getLong()
        );


        $arr_argv = $_SERVER['argv'];
        array_shift($arr_argv);

        foreach($arr_argv as $k => $v)
        {
            if(preg_match('/^-{1,2}/', $v))
            {
                $opt_name = preg_replace('/^[-]+/', '', $v);

                if(array_key_exists($opt_name, $this->arr_parsed))
                {
                    if(is_bool($this->arr_parsed[$opt_name]))
                    {
                        unset($arr_argv[$k]);
                    }
                    else
                    {
                        unset($arr_argv[$k]);
                        unset($arr_argv[$k + 1]);
                    }
                }
            }
        }

        $this->arr_argument = array_values($arr_argv);


        // Checks invalid options
        $invalid_opt = null;

        foreach($arr_argv as $v)
        {
            if(preg_match('/^-{1,2}/', $v))
            {
                $invalid_opt = $v;
                break;
            }
        }

        if(!is_null($invalid_opt))
        {
            fwrite(STDERR, sprintf('The given "%s" option is not valid!', $invalid_opt));
            fwrite(STDERR, "\n");
            exit(1);
        }



        $this->displayVersion();

        if($this->has('help'))
        {
            $this->displayHelp();
        }

        if(count($this->arr_parsed) == 0 && $bool_display_help)
        {
            $this->displayHelp();
        }

    }


    public function noVersion()
    {
        $this->has_version = false;
    }


    public function addUsage($str)
    {
        $this->arr_usage[] = $str;
    }

    public function description($str)
    {
        $this->str_description = $str;
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
        $str_prog = basename($_SERVER['argv'][0]);

        $first = new \Malenki\Bah\S(sprintf('Usage: %s %s', $str_prog, "[OPTIONS]…"));

        $arr_out = array(
            $first->wrap(Arg::getWidth() - 7)->margin(7, 0, -7)
        );

        foreach($this->arr_usage as $item)
        {
            $item = new \Malenki\Bah\S($str_prog.' '.$item);
            $arr_out[] = $item->wrap(Arg::getWidth() - 7)->margin(7);
        }

        return implode(PHP_EOL, $arr_out);
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
            $description = new \Malenki\Bah\S($this->str_description);
            return $description->wrap(Arg::getWidth());
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



    public function hasVersion()
    {
        if(is_string($this->str_version) && strlen(trim($this->str_version)))
        {
            return true;
        }

        return false;
    }


    public function displayVersion()
    {
        if($this->has('version') && $this->hasVersion())
        {
            $version = new \Malenki\Bah\S($this->str_version);
            printf($version->wrap(Arg::getWidth()) . PHP_EOL);
        }
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



    public function hasArgument()
    {
        return (boolean) count($this->arr_argument);
    }


    public function getArguments()
    {
        return $this->arr_argument;
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
