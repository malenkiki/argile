<?php

include(realpath(dirname(__FILE__).DIRECTORY_SEPARATOR.'..').preg_replace('@/@', DIRECTORY_SEPARATOR, '/src/Malenki/Argile/Options.php'));

use Malenki\Argile\Arg as Arg;
use Malenki\Argile\Options as Options;


$opt = Options::getInstance();

$opt->newSwitch('switch')
    ->short('s')
    ->help('I am a very simple switch arg with only short form.')
    ;

$opt->newValue('foo')
    ->required()
    ->short('f')
    ->long('foo')
    ->help('I am a simple required arg with short and long forms.')
    ;

$opt->newValue('bar')
    ->short('b')
    ->long('bar')
    ->help('I am a simple optional arg with short and long forms. I have custom help variable too.', 'something')
    ;


$opt->parse();

if($opt->has('switch'))
{
    printf("\n\"switch\" arg selected!\n\n");
}

if($opt->has('foo'))
{
    printf("\n\"foo\" arg selected! Its value is: \"%s\"\n\n", $opt->get('foo'));
}

if($opt->has('bar'))
{
    if($opt->get('bar'))
    {
        printf("\n\"bar\" arg selected! Its value is: \"%s\"\n\n", $opt->get('bar'));
    }
    else
    {
        printf("\n\"bar\" arg selected! Given without value.\n\n");
    }
}
exit();
