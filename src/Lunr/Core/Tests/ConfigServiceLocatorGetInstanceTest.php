<?php

/**
 * This file contains the ConfigServiceLocatorGetInstanceTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2013 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Core\Tests;

use LunrTest\Core\Type;

/**
 * This class contains the tests for the locator class.
 *
 * @covers     Lunr\Core\ConfigServiceLocator
 */
class ConfigServiceLocatorGetInstanceTest extends ConfigServiceLocatorTestCase
{

    /**
     * Test that get_instance() throws for a non-instantiable class.
     *
     * @covers \Lunr\Core\ConfigServiceLocator::get_instance
     */
    public function testGetInstanceThrowsExceptionForNonInstantiableClass(): void
    {
        $this->expectException('Lunr\Core\Exceptions\ContainerException');
        $this->expectExceptionMessage('Not possible to instantiate \'LunrTest\Corona\Controller\'!');

        $cache = [ 'controller' => [ 'name' => 'LunrTest\Corona\Controller' ] ];
        $this->setReflectionPropertyValue('cache', $cache);

        $method = $this->getReflectionMethod('get_instance');

        $method->invokeArgs($this->class, [ 'controller' ]);
    }

    /**
     * Test that get_instance() throws an exception when there are not enough arguments for the Enum.
     *
     * @covers \Lunr\Core\ConfigServiceLocator::get_instance
     */
    public function testGetInstanceThrowsExceptionForTooLittleNumberOfEnumArguments(): void
    {
        $this->expectException('Lunr\Core\Exceptions\ContainerException');
        $this->expectExceptionMessage('Not enough parameters for LunrTest\Core\Type::from()!');

        $cache = [ 'type' => [ 'name' => 'LunrTest\Core\Type', 'params' => [] ] ];
        $this->setReflectionPropertyValue('cache', $cache);

        $method = $this->getReflectionMethod('get_instance');

        $method->invokeArgs($this->class, [ 'type' ]);
    }

    /**
     * Test that get_instance() returns an enum instance.
     *
     * @covers \Lunr\Core\ConfigServiceLocator::get_instance
     */
    public function testGetInstanceReturnsInstanceForEnum(): void
    {
        $cache = [ 'type' => [ 'name' => 'LunrTest\Core\Type', 'params' => [ 'a' ] ] ];
        $this->setReflectionPropertyValue('cache', $cache);

        $method = $this->getReflectionMethod('get_instance');

        $value = $method->invokeArgs($this->class, [ 'type' ]);

        $this->assertInstanceOf('LunrTest\Core\Type', $value);
        $this->assertSame(Type::A, $value);
    }

    /**
     * Test that get_instance() returns an instance if the class doesn't have a Constructor.
     *
     * @covers \Lunr\Core\ConfigServiceLocator::get_instance
     */
    public function testGetInstanceReturnsInstanceForClassWithoutConstructor(): void
    {
        $cache = [ 'stdclass' => [ 'name' => 'stdClass' ] ];
        $this->setReflectionPropertyValue('cache', $cache);

        $method = $this->getReflectionMethod('get_instance');

        $this->assertInstanceOf('stdClass', $method->invokeArgs($this->class, [ 'stdclass' ]));
    }

    /**
     * Test that get_instance() throws an exception when there are not enough arguments for the Constructor.
     *
     * @covers \Lunr\Core\ConfigServiceLocator::get_instance
     */
    public function testGetInstanceThrowsExceptionForTooLittleNumberOfConstructorArguments(): void
    {
        $this->expectException('Lunr\Core\Exceptions\ContainerException');
        $this->expectExceptionMessage('Not enough parameters for LunrTest\Corona\Request!');

        $cache = [ 'request' => [ 'name' => 'LunrTest\Corona\Request', 'params' => [] ] ];
        $this->setReflectionPropertyValue('cache', $cache);

        $method = $this->getReflectionMethod('get_instance');

        $method->invokeArgs($this->class, [ 'request' ]);
    }

    /**
     * Test that get_instance() returns an instance for a Constructor with arguments.
     *
     * @covers \Lunr\Core\ConfigServiceLocator::get_instance
     */
    public function testGetInstanceReturnsInstanceForConstructorWithArguments(): void
    {
        $cache = [ 'datetime' => [ 'name' => 'LunrTest\Corona\Request', 'params' => [ 'config' ] ] ];
        $this->setReflectionPropertyValue('cache', $cache);

        $method = $this->getReflectionMethod('get_instance');

        $this->assertInstanceOf('LunrTest\Corona\Request', $method->invokeArgs($this->class, [ 'datetime' ]));
    }

    /**
     * Test that get_instance() returns an instance for a Constructor without arguments.
     *
     * @covers \Lunr\Core\ConfigServiceLocator::get_instance
     */
    public function testGetInstanceReturnsInstanceForConstructorWithoutArguments(): void
    {
        $cache = [ 'datetime' => [ 'name' => 'LunrTest\Core\DateTime', 'params' => [] ] ];
        $this->setReflectionPropertyValue('cache', $cache);

        $method = $this->getReflectionMethod('get_instance');

        $this->assertInstanceOf('LunrTest\Core\DateTime', $method->invokeArgs($this->class, [ 'datetime' ]));
    }

    /**
     * Test that get_parameters processes ID parameters.
     *
     * @covers \Lunr\Core\ConfigServiceLocator::get_parameters
     */
    public function testGetParametersProcessesIDParameter(): void
    {
        $type = $this->getMockBuilder('\ReflectionNamedType')
                     ->disableOriginalConstructor()
                     ->getMock();

        $type->expects($this->exactly(1))
             ->method('getName')
             ->willReturn('Lunr\Core\Configuration');

        $params = [ 'config' ];

        $param = $this->getMockBuilder('ReflectionParameter')
                      ->disableOriginalConstructor()
                      ->getMock();

        $param->expects($this->exactly(1))
              ->method('getType')
              ->willReturn($type);

        $method = $this->getReflectionMethod('get_parameters');

        $return = $method->invokeArgs($this->class, [ $params, [ $param ] ]);

        $this->assertIsArray($return);
        $this->assertInstanceOf('Lunr\Core\Configuration', $return[0]);
    }

    /**
     * Test that get_parameters processes non-ID parameters.
     *
     * @covers Lunr\Core\ConfigServiceLocator::get_parameters
     */
    public function testGetParametersProcessesNonIDParameter(): void
    {
        $params = [ 'string' ];

        $type = $this->getMockBuilder('\ReflectionNamedType')
                     ->disableOriginalConstructor()
                     ->getMock();

        $type->expects($this->once())
             ->method('getName')
             ->willReturn('string');

        $param = $this->getMockBuilder('\ReflectionParameter')
                      ->disableOriginalConstructor()
                      ->getMock();

        $param->expects($this->once())
              ->method('getType')
              ->willReturn($type);

        $method = $this->getReflectionMethod('get_parameters');

        $return = $method->invokeArgs($this->class, [ $params, [ $param ] ]);

        $this->assertIsArray($return);
        $this->assertEquals('string', $return[0]);
    }

    /**
     * Test that get_parameters processes non-string parameters.
     *
     * @covers \Lunr\Core\ConfigServiceLocator::get_parameters
     */
    public function testGetParametersProcessesNonStringParameter(): void
    {
        $params = [ [], 5, NULL ];

        $method = $this->getReflectionMethod('get_parameters');

        $return = $method->invokeArgs($this->class, [ $params, [] ]);

        $this->assertIsArray($return);
        $this->assertSame([], $return[0]);
        $this->assertSame(5, $return[1]);
        $this->assertSame(NULL, $return[2]);
    }

    /**
     * Test that get_parameters processes forced non-ID parameters.
     *
     * @covers \Lunr\Core\ConfigServiceLocator::get_parameters
     */
    public function testGetParametersProcessesForcedNonIDParameter(): void
    {
        $params = [ '!string' ];

        $method = $this->getReflectionMethod('get_parameters');

        $return = $method->invokeArgs($this->class, [ $params, [] ]);

        $this->assertIsArray($return);
        $this->assertEquals('string', $return[0]);
    }

    /**
     * Test that get_parameters processes mixed parameters.
     *
     * @covers \Lunr\Core\ConfigServiceLocator::get_parameters
     */
    public function testGetParametersProcessesMixedParameters(): void
    {
        $params = [ 'config', '!config', 'string' ];

        $type = $this->getMockBuilder('\ReflectionNamedType')
                     ->disableOriginalConstructor()
                     ->getMock();

        $type->expects($this->exactly(2))
             ->method('getName')
             ->willReturnOnConsecutiveCalls('Lunr\Core\Configuration', 'string');

        $param = $this->getMockBuilder('ReflectionParameter')
                      ->disableOriginalConstructor()
                      ->getMock();

        $param->expects($this->exactly(2))
              ->method('getType')
              ->willReturn($type);

        $method = $this->getReflectionMethod('get_parameters');

        $return = $method->invokeArgs($this->class, [ $params, [ $param, $param, $param ] ]);

        $this->assertIsArray($return);
        $this->assertInstanceOf('Lunr\Core\Configuration', $return[0]);
        $this->assertEquals('config', $return[1]);
        $this->assertEquals('string', $return[2]);
    }

}

?>
