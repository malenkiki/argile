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

use Malenki\Argile\OptionItem;

class OptionItemTest extends PHPUnit_Framework_TestCase
{
    public function testInstanciateShouldSuccess()
    {
        $a = OptionItem::createSwitch('bar');
        $this->assertInstanceOf('\Malenki\Argile\OptionItem', $a);
        $a = OptionItem::createValue('foo');
        $this->assertInstanceOf('\Malenki\Argile\OptionItem', $a);
    }

    public function testSwitchInit()
    {
        $a = OptionItem::createSwitch('bar');
        $this->assertFalse($a->isValue());
    }
    
    public function testValueInit()
    {
        $a = OptionItem::createValue('foo');
        $this->assertTrue($a->isValue());
    }

    public function testSettingAsRequiredShouldSuccess()
    {
        $a = OptionItem::createValue('foo')->required();
        $this->assertInstanceOf('\Malenki\Argile\OptionItem', $a);
        $this->assertTrue($a->isRequired());
        $a = OptionItem::createValue('foo')->required;
        $this->assertInstanceOf('\Malenki\Argile\OptionItem', $a);
        $this->assertTrue($a->isRequired());
    }

    public function testSettingShortShouldSuccess()
    {
        $a = OptionItem::createValue('foo')->short('f');
        $this->assertInstanceOf('\Malenki\Argile\OptionItem', $a);
        $this->assertTrue($a->hasShort());

        $a = OptionItem::createSwitch('foo')->short('f');
        $this->assertInstanceOf('\Malenki\Argile\OptionItem', $a);
        $this->assertTrue($a->hasShort());
        
        $a = OptionItem::createValue('foo')->s('f');
        $this->assertInstanceOf('\Malenki\Argile\OptionItem', $a);
        $this->assertTrue($a->hasShort());

        $a = OptionItem::createSwitch('foo')->s('f');
        $this->assertInstanceOf('\Malenki\Argile\OptionItem', $a);
        $this->assertTrue($a->hasShort());
    }



    public function testGettingShortShouldSuccess()
    {
        $a = OptionItem::createValue('foo')->short('f');
        $this->assertEquals('f', $a->getShort());
    }


    public function testSettingLongShouldSuccess()
    {
        $a = OptionItem::createValue('foo')->long('foo');
        $this->assertInstanceOf('\Malenki\Argile\OptionItem', $a);
        $this->assertTrue($a->hasLong());

        $a = OptionItem::createSwitch('foo')->long('foo');
        $this->assertInstanceOf('\Malenki\Argile\OptionItem', $a);
        $this->assertTrue($a->hasLong());
        
        $a = OptionItem::createValue('foo')->l('foo');
        $this->assertInstanceOf('\Malenki\Argile\OptionItem', $a);
        $this->assertTrue($a->hasLong());

        $a = OptionItem::createSwitch('foo')->l('foo');
        $this->assertInstanceOf('\Malenki\Argile\OptionItem', $a);
        $this->assertTrue($a->hasLong());
    }


    public function testGettingLongShouldSuccess()
    {
        $a = OptionItem::createValue('foo')->long('foo');
        $this->assertEquals('foo', $a->getLong());
    }



    public function testGettingNameShouldSuccess()
    {
        $a = OptionItem::createValue('foo');
        $this->assertEquals('foo', $a->getName());
    }

}
