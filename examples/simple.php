<?php

include('../src/Malenki/Argile/Arg.php');
include('../src/Malenki/Argile/Options.php');

use Malenki\Argile\Arg as Arg;
use Malenki\Argile\Options as Options;

Options::add(
    Arg::createSwitch('switch')
    ->setShort('s')
    ->setHelp('I am a very simple switch arg with only short form.')
);

Options::add(
    Arg::createValue('foo')
    ->setShort('f:')
    ->setLong('foo:')
    ->setHelp('I am a simple arg with short and long form.')
);

Options::getInstance()->parse();

if(Options::getInstance()->has('switch')){
    printf("\n\"switch\" arg selected!\n\n");
    exit();
}

if(Options::getInstance()->has('foo')){
    printf("\n\"foo\" arg selected! Its value is: \"%s\"\n\n", Options::getInstance()->get('foo'));
    exit();
}

