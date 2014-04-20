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

use Malenki\Argile\Options;

class OptionsTest extends PHPUnit_Framework_TestCase
{
    public function testInit()
    {
        $o = Options::getInstance();
        $this->assertTrue(is_object($o));
        $this->assertInstanceOf('\Malenki\Argile\Options', $o);
    }
    
    public function testAddSwitchShouldSuccess()
    {
        $this->assertInstanceOf('\Malenki\Argile\OptionItem', Options::getInstance()->newSwitch('foo'));
        $this->assertInstanceOf('\Malenki\Argile\OptionItem', Options::getInstance()->flag('foo'));
        $this->assertInstanceOf('\Malenki\Argile\OptionItem', Options::getInstance()->f('foo'));
    }
    
    public function testAddValueShouldSuccess()
    {
        $this->assertInstanceOf('\Malenki\Argile\OptionItem', Options::getInstance()->newValue('foo'));
        $this->assertInstanceOf('\Malenki\Argile\OptionItem', Options::getInstance()->val('foo'));
        $this->assertInstanceOf('\Malenki\Argile\OptionItem', Options::getInstance()->v('foo'));
    }
}

