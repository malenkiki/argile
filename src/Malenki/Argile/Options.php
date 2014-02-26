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
 * Defines options, catches options, outputs help, gets arguments for PHP CLI 
 * scripts.
 * 
 * @copyright 2014 Michel Petit
 * @author Michel Petit <petit.michel@gmail.com> 
 * @license MIT
 */
class Options
{
    /**
     * Instance of the singleton.
     *
     * @var Options 
     */
    protected static $obj_instance = null;


    /**
     * Defined available options.
     *  
     * @var array
     */
    protected static $arr_opt = array();


    /**
     * Defined options that belong to group. 
     */
    protected static $arr_group = array();

    /**
     * Prohibited option internal names.
     * @static
     * @var array
     */
    protected static $arr_prohibited = array('h', 'help', 'version');

    /**
     * Detected options after parsing.
     * 
     * @var array
     * @access protected
     */
    protected $arr_parsed = array();


    /**
     * Found argument after parsing. 
     * 
     * @var array
     * @access protected
     */
    protected $arr_argument = array();
    protected $obj_color = null;
    
    protected $arr_switch = array();
    protected $arr_values = array();

    protected $arr_usage = array();
    protected $str_description = null;
    protected $str_version = null;

        
    /**
     * Load the singleton instance. 
     * 
     * @static
     * @access public
     * @return Options
     */
    public static function getInstance()
    {
        if(is_null(self::$obj_instance))
        {
            self::$obj_instance = new self();
        }

        return self::$obj_instance;
    }




    private function __construct()
    {
        $this->obj_color = new \stdClass();
        $this->obj_color->label = null;
        $this->obj_color->opt = null;
        $this->obj_color->bold = false;
    }



    /**
     * Allow flexible output.
     *
     * If this method is called, then generated help will fit to the terminal 
     * width. 
     * 
     * @access public
     * @return Options
     */
    public function flexible()
    {
        OptionItem::flexible();

        return $this;
    }



    /**
     * Set color's name to use for label rendering. 
     * 
     * @param string $str_color Color name, one of available foreground colors 
     * defined in \Malenki\Ansi
     *
     * @access public
     * @return Options
     */
    public function labelColor($str_color)
    {
        $this->obj_color->label = $str_color;

        return $this;
    }

    
    
    /**
     * Set color's name to use for options' label rendering. 
     * 
     * @param string $str_color Color name, one of available foreground colors 
     * defined in \Malenki\Ansi
     *
     * @access public
     * @return Options
     */
    public function optColor($str_color)
    {
        $this->obj_color->opt = $str_color;

        return $this;
    }



    /**
     * Set labels as bold. 
     * 
     * @access public
     * @return Options
     */
    public function bold()
    {
        $this->obj_color->bold = true;

        return $this;
    }



    /**
     * Parse given options and arguments to the CLI script.
     *
     * @param boolean $bool_display_help Display or not help message if no options
     * @access public
     * @return Options
     */
    public function parse($bool_display_help = true)
    {
        self::addGroup('helpversion');

        // On ajoute les options spéciales Help et Version
        self::$arr_group['helpversion']->args['help'] = OptionItem::createSwitch('help')
            ->short('h')
            ->long('help')
            ->help('Display this help message and exit')
        ;


        if($this->hasVersion())
        {
            self::$arr_group['helpversion']->args['version'] = OptionItem::createSwitch('version')
                ->long('version')
                ->help('Display version information and exit')
                ;
        }
        
        if($this->obj_color->opt)
        {
            self::$arr_group['helpversion']->args['help']->color($this->obj_color->opt);
            self::$arr_group['helpversion']->args['version']->color($this->obj_color->opt);
        }
        
        if($this->obj_color->bold)
        {
            self::$arr_group['helpversion']->args['help']->bold();
            self::$arr_group['helpversion']->args['version']->bold();
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
                $bool_equal = (boolean) preg_match('/^[a-zA-Z0-9-]+=/', $v);

                $v = preg_replace('/(^[a-zA-Z0-9-]+)=.*/', '\1', $v);
                
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

                        if(!$bool_equal)
                        {
                            unset($arr_argv[$k + 1]);
                        }
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


        return $this;
    }



    /**
     * Sets script without version information. 
     * 
     * @access public
     * @return Options
     */
    public function noVersion()
    {
        $this->has_version = false;

        return $this;
    }



    /**
     * Add one usage case for the synopsis part. 
     * 
     * @param string $str A use case with arg/opt
     * @access public
     * @return Options
     */
    public function addUsage($str)
    {
        $this->arr_usage[] = $str;

        return $this;
    }



    /**
     * Set global description for the help output.
     * 
     * @param string $str Description's content
     * @access public
     * @return Options
     */
    public function description($str)
    {
        $this->str_description = $str;

        return $this;
    }
    


    /**
     * Set version information about the script. 
     * 
     * @param string $str Version information.
     * @access public
     * @return Options
     */
    public function version($str)
    {
        $this->str_version = $str;

        return $this;
    }



    /**
     * Adds a new group for options. 
     * 
     * @param string $str_alias Conding name of the group, to identify it when defining options.
     * @param string $str_name Optional name to display while rendering help.
     * @access public
     * @return Options
     */
    public function addGroup($str_alias, $str_name = null)
    {
        if(!isset(self::$arr_group[$str_alias]))
        {
            $grp = new \stdClass();
            $grp->name = (strlen($str_name)) ? $str_name : null;
            $grp->args = array();

            self::$arr_group[$str_alias] = $grp;
        }

        return $this;
    }



    /**
     * Adds one new option.
     * 
     * @param OptionItem $opt The option.
     * @param mixed $str_alias Its optional group's alias.
     * @static
     * @access public
     * @return void
     */
    public static function add(OptionItem $opt, $str_alias = null)
    {
        // tester ici si version ou aide : à ne pas mettre
        if(
            !in_array($opt->getShort(true), self::$arr_prohibited, true)
            &&
            !in_array($opt->getLong(true), self::$arr_prohibited, true)
        )
        {
            if(is_string($str_alias) && isset(self::$arr_group[$str_alias]))
            {
                self::$arr_group[$str_alias]->args[$opt->getName()] = $opt;
            }
            else
            {
                self::$arr_opt[$opt->getName()] = $opt;
            }
        }
    }



    protected function getShort()
    {
        $str_out = '';

        foreach(self::$arr_opt as $arg)
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

        foreach(self::$arr_opt as $arg)
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


    /**
     * Adds a new option switch.  
     * 
     * @param string $name The string to identify and call this option.
     * @param string $group Optional group's name
     * @access public
     * @return OptionItem The newly created option, to chain methods.
     */
    public function newSwitch($name, $group = null)
    {
        $arg = OptionItem::createSwitch($name);
        
        if($this->obj_color->opt)
        {
            $arg->color($this->obj_color->opt);
        }

        if($this->obj_color->bold)
        {
            $arg->bold();
        }

        self::add($arg, $group);
        return self::getOpt($name);
    } 



    /**
     * Adds a new option's value.
     * 
     * @param string $name Option's name.
     * @param string $group Optional group's name.
     * @access public
     * @return OptionItem
     */
    public function newValue($name, $group = null)
    {
        $arg = OptionItem::createValue($name);
        
        if($this->obj_color->opt)
        {
            $arg->color($this->obj_color->opt);
        }

        if($this->obj_color->bold)
        {
            $arg->bold();
        }

        self::add($arg, $group);
        return self::getOpt($name);
    } 


    /**
     * Gets the synopsis part.
     * 
     * @access public
     * @return string
     */
    public function getUsage()
    {
        $str_prog = basename($_SERVER['argv'][0]);

        $label_usage = 'Usage:';
        
        if($this->obj_color->label || $this->obj_color->bold)
        {
            $label_usage = new Ansi($label_usage);

            if($this->obj_color->label)
            {
                $label_usage->fg($this->obj_color->label);
            }
            
            if($this->obj_color->bold)
            {
                $label_usage->bold;
            }
        }

        $first = new S(sprintf('%s %s %s', $label_usage, $str_prog, "[OPTIONS]…"));

        $arr_out = array(
            $first->wrap(OptionItem::getWidth() - 7)->margin(7, 0, -7)
        );

        foreach($this->arr_usage as $item)
        {
            $item = new S($str_prog.' '.$item);
            $arr_out[] = $item->wrap(OptionItem::getWidth() - 7)->margin(7);
        }

        return implode(PHP_EOL, $arr_out);
    }



    /**
     * Getsthe description part.
     *
     * This follows terminal size or not if `flexible()` method was called or not before.
     * 
     * @access public
     * @return string
     */
    public function getDescription()
    {
        if(is_string($this->str_description))
        {
            $description = new S($this->str_description);
            return $description->wrap(OptionItem::getWidth());
        }
        else
        {
            return null;
        }
    }



    /**
     * Displays full help message. 
     * 
     * This follows terminal size or not if `flexible()` method was called or not before.
     *
     * @access public
     * @return void
     */
    public function displayHelp()
    {
        printf("%s\n", $this->getUsage());
        printf("%s\n", $this->getDescription());
        

        // Les options non incluses dans un groupe
        if(count(self::$arr_opt))
        {
            foreach(self::$arr_opt as $arg)
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
                        $name = $group->name;

                        if($this->obj_color->label || $this->obj_color->bold)
                        {
                            $name = new Ansi($name);

                            if($this->obj_color->label)
                            {
                                $name->fg($this->obj_color->label);
                            }

                            if($this->obj_color->bold)
                            {
                                $name->bold;
                            }
                        }

                        printf("%s\n", $name);
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
            $version = new S($this->str_version);
            printf($version->wrap(OptionItem::getWidth()) . PHP_EOL);
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
        $arg = self::getOpt($str);
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
     * @param mixed $str 
     * @static
     * @access public
     * @return void
     */
    public static function getOpt($str)
    {
        $arg = false;

        if(isset(self::$arr_opt[$str]))
        {
            $arg = self::$arr_opt[$str];
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
        $arg = self::getOpt($str);

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
