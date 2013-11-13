#!/usr/bin/env php
<?php

(@include_once __DIR__ . '/../vendor/autoload.php') || @include_once __DIR__ . '/../../../autoload.php';


$opt = Malenki\Argile\Options::getInstance();

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
