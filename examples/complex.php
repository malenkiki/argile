<?php

include('../src/Malenki/Argile/Arg.php');
include('../src/Malenki/Argile/Options.php');

use Malenki\Argile\Arg as Arg;
use Malenki\Argile\Options as Options;


$opt = Options::getInstance();

$opt->usage('Explain in short some CLI usage.');
$opt->description('Blahblah about your software.');
$opt->help('Some custom help sentence.');
$opt->version('Some blahblah to change version arg sentence.');

$opt->addGroup('one', 'Optional title for first group');
$opt->addGroup('two', 'Optional title for second group');

$opt->newSwitch('switch', 'one')
    ->short('s')
    ->help('I am a very simple switch arg with only short form.')
    ;

$opt->newValue('foo', 'two')
    ->required()
    ->short('f')
    ->long('foo')
    ->help('I am a simple required arg with short and long forms.')
    ;

$opt->newValue('bar', 'two')
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
