# Argile

Create, get, write options and autogenerated help messages for your CLI PHP applications…

## Basic usage about values and switches

Quick first simple example with only one switch:

``` php
use Malenki\Argile\Options as Options;

$opt = Options::getInstance();

$opt->newSwitch('switch')
    ->short('s')
    ->help('I am a very simple switch arg with only short form.')
    ;

$opt->parse();

if($opt->has('switch'))
{
    printf("\"switch\" arg selected!\n");
}
exit();
```

So, if your PHP CLI application is into `yourapp` executable file, then you can call it like that:

```
$ yourapp -s
"switch\" arg selected!
```

Without options, calling it will give help message:

```
$ yourapp
Usage: complex.php [OPTIONS]…

  -s                         I am a very simple switch arg with only short
                             form.


  -h, --help                 Display this help message and exit

```

Second example this switch, values, short, long and required option:

```php
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

$opt->parse();

if($opt->has('switch'))
{
    printf("\"switch\" arg selected!\n");
}

if($opt->has('foo'))
{
    printf("\"foo\" arg selected! Its value is: \"%s\"\n", $opt->get('foo'));
}

exit();
```

## Advanced usage of values and switches

You can customize variable name into help text of each option.

TODO: example

You can group options together. Easy, you have to create group, and when you create options, you put the group name hassecond argument:

```php
use Malenki\Argile\Options as Options;

$opt = Options::getInstance();

$opt->addGroup('one', 'Optional title for first group');
$opt->addGroup('two', 'Optional title for second group');

$opt->newSwitch('switch', 'one')
    ->short('s')
    ->help('I am a very simple switch arg with only short form.')
    ;
$opt->newValue('foo', 'one')
    ->required()
    ->short('f')
    ->long('foo')
    ->help('I am a simple required arg with short and long forms.')
    ;
$opt->newValue('bar', 'two')
    ->short('b')
    ->long('bar')
    ->help('I am a simple arg with short and long forms.')
    ;

//...

```

Later, if you can help, options will be grouped and group's name is shown if it has one.


## Detect and get arguments

TODO

## Meta information about CLI app

You can define some informations about your app that will be used into help message:

 - Synopsis
 - Version
 - Description
 
## More

You can have "flexible" help output message, that fit your terminal width, or you can leave at default with of 80 columns.

If you want this text adjustment, do just following:

```php
use Malenki\Argile\Options as Options;

$opt = Options::getInstance();
$opt->flexible(); // yes, that's all

// and add your options and other thing…
```
