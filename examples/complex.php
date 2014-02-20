#!/usr/bin/env php
<?php

(@include_once __DIR__ . '/../vendor/autoload.php') || @include_once __DIR__ . '/../../../autoload.php';


$opt = Malenki\Argile\Options::getInstance();

$opt->flexible();
$opt->labelColor('cyan');
$opt->optColor('yellow');
//$opt->bold();
$opt->addUsage('-f FOO');
$opt->addUsage('-s [-b BAR]');
$opt->description('Blahblah about your software. this can have any size, on several lines or can be short, some words. But it is good to have some idea about what your script does, so, do not be shy and explain what this fantastic software does!');
$opt->version('Some App Version 1.0');

$opt->addGroup('one', 'Optional title for first group');
$opt->addGroup('two', 'Optional title for second group');

$opt->newSwitch('switch', 'one')
    ->short('s')
    ->help('I am a very simple switch arg with only short form.')
    ;

$opt->newSwitch('switch2', 'one')
    ->short('n')
    ->long('i-am-very-long-option-baby')
    ->help('I am a very simple switch arg with only short form but my long form is very, very, very long.')
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
