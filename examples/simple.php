<?php

include('../src/Malenki/Argile/Arg.php');
include('../src/Malenki/Argile/Options.php');

use Malenki\Argile\Arg as Arg;
use Malenki\Argile\Options as Options;


$opt = Options::getInstance();

$opt->switch('switch')
    ->short('s')
    ->help('I am a very simple switch arg with only short form.')
    ;

$opt->value('foo')
    ->required()
    ->short('f')
    ->long('foo')
    ->help('I am a simple arg with short and long forms.')
    ;


$opt->parse();

if($opt->has('switch')){
    printf("\n\"switch\" arg selected!\n\n");
    exit();
}

if($opt->has('foo')){
    printf("\n\"foo\" arg selected! Its value is: \"%s\"\n\n", $opt->get('foo'));
    exit();
}

