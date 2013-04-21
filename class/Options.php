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
use Malenki\Opt\Arg as Arg;

class Options
{
    protected static $obj_instance = null;
    protected static $arr_arg = array();
    protected static $arr_group = array();
    protected static $arr_prohibited = array('h', 'help', 'version');
    protected $arr_parsed = array();
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


    public function setUsage($str)
    {
        $this->str_usage = $str;
    }

    public function setDescription($str)
    {
        $this->str_description = $str;
    }
    
    
    public function setHelp($str)
    {
        $this->str_help = $str;
    }
    
    
    public function setVersion($str)
    {
        $this->str_version = $str;
    }

    public function addGroup($str_alias, $str_name = null)
    {
        if(!isset(self::$arr_group[$str_alias]))
        {
            self::$arr_group[$str_alias] = (object) array(
                'name' => (strlen($str_name)) ? $str_name : null,
                'args' => array()
            );
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
                $str_out .= $arg->getShort();
            }
        }

        foreach(self::$arr_group as $group)
        {
            foreach($group->args as $arg)
            {
                $str_out .= $arg->getShort();
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
                $arr_out[] = $arg->getLong();
            }
        }

        foreach(self::$arr_group as $group)
        {
            foreach($group->args as $arg)
            {
                $arr_out[] = $arg->getShort();
            }

        }

        return $arr_out;
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
            $usage = $this->str_usage;
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
            ->setShort('h')
            ->setLong('help')
            ->setHelp($this->str_help)
        ;

        self::$arr_group['helpversion']->args[] = Arg::createSwitch('version')
            ->setLong('version')
            ->setHelp($this->str_version)
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
                isset($this->arr_parsed[$arg->getLong(true)])
                ||
                (isset($this->arr_parsed[$arg->getShort(true)])
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

        if(isset($this->arr_parsed[$arg->getLong(true)]))
        {
            return $this->arr_parsed[$arg->getLong(true)];
        }
        else
        {
            return $this->arr_parsed[$arg->getShort(true)];
        }
    }	
}
