<?php

/**
 * This file contains the ConfigServiceLocatorGetInstanceTest class.
 *
 * @package    Lunr\Core
 * @author     Heinz Wiesinger <heinz@m2mobi.com>
 * @copyright  2013-2018, M2Mobi BV, Amsterdam, The Netherlands
 * @license    http://lunr.nl/LICENSE MIT License
 */

namespace Lunr\Core\Tests;

/**
 * This class contains the tests for the locator class.
 *
 * @covers     Lunr\Core\ConfigServiceLocator
 */
class ConfigServiceLocatorGetInstanceTest extends ConfigServiceLocatorTest
{

    /**
     * Test that get_instance() throws for a non-instantiable class.
     *
     * @covers \Lunr\Core\ConfigServiceLocator::get_instance
     */
    public function testGetInstanceThrowsForNonInstantiableClass(): void
    {
        $this->expectException('Lunr\Core\Exceptions\ContainerException');
        $this->expectExceptionMessage('Not possible to instantiate \'LunrTest\Corona\Controller\'!');

        $cache = [ 'controller' => [ 'name' => 'LunrTest\Corona\Controller' ] ];
        $this->set_reflection_property_value('cache', $cache);

        $method = $this->get_accessible_reflection_method('get_instance');

        $this->assertNull($method->invokeArgs($this->class, [ 'controller' ]));
    }

    /**
     * Test that get_instance() returns an instance if the class doesn't have a Constructor.
     *
     * @covers \Lunr\Core\ConfigServiceLocator::get_instance
     */
    public function testGetInstanceReturnsInstanceForClassWithoutConstructor(): void
    {
        $cache = [ 'stdclass' => [ 'name' => 'stdClass' ] ];
        $this->set_reflection_property_value('cache', $cache);

        $method = $this->get_accessible_reflection_method('get_instance');

        $this->assertInstanceOf('stdClass', $method->invokeArgs($this->class, [ 'stdclass' ]));
    }

    /**
     * Test that get_instance() returns NULL when there are not enough arguments for the Constructor.
     *
     * @covers \Lunr\Core\ConfigServiceLocator::get_instance
     */
    public function testGetInstanceReturnsNullForTooLittleNumberOfConstructorArguments(): void
    {
        $this->expectException('Lunr\Core\Exceptions\ContainerException');
        $this->expectExceptionMessage('Not enough parameters for LunrTest\Corona\Request!');

        $cache = [ 'request' => [ 'name' => 'LunrTest\Corona\Request', 'params' => [] ] ];
        $this->set_reflection_property_value('cache', $cache);

        $method = $this->get_accessible_reflection_method('get_instance');

        $this->assertNull($method->invokeArgs($this->class, [ 'request' ]));
    }

    /**
     * Test that get_instance() returns an instance for a Constructor with arguments.
     *
     * @covers \Lunr\Core\ConfigServiceLocator::get_instance
     */
    public function testGetInstanceReturnsInstanceForConstructorWithArguments(): void
    {
        $cache = [ 'datetime' => [ 'name' => 'LunrTest\Corona\Request', 'params' => [ 'config' ] ] ];
        $this->set_reflection_property_value('cache', $cache);

        $method = $this->get_accessible_reflection_method('get_instance');

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
        $this->set_reflection_property_value('cache', $cache);

        $method = $this->get_accessible_reflection_method('get_instance');

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

        $param->expects($this->exactly(2))
              ->method('getType')
              ->willReturn($type);

        $method = $this->get_accessible_reflection_method('get_parameters');

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

        $type->expects($this->exactly(1))
             ->method('getName')
             ->willReturn('string');

        $param = $this->getMockBuilder('\ReflectionParameter')
                      ->disableOriginalConstructor()
                      ->getMock();

        $param->expects($this->exactly(2))
              ->method('getType')
              ->willReturn($type);

        $method = $this->get_accessible_reflection_method('get_parameters');

        $return = $method->invokeArgs($this->class, [ $params, [ $param ] ]);

        $this->assertIsArray($return);
        $this->assertEquals('string', $return[0]);
    }

    /**
     * Test that get_parameters processes non-ID parameters.
     *
     * @requires PHP >= 8.1
     * @covers Lunr\Core\ConfigServiceLocator::get_parameters
     */
    public function testGetParametersProcessesNonIDParameterAsUnion(): void
    {
        $params = [ 'cow' ];

        $type1 = $this->getMockBuilder('\ReflectionNamedType')
                     ->disableOriginalConstructor()
                     ->getMock();

        $type2 = $this->getMockBuilder('\ReflectionNamedType')
                     ->disableOriginalConstructor()
                     ->getMock();

        $type1->expects($this->once())
              ->method('getName')
              ->willReturn('\Stringable');

        $type2->expects($this->once())
              ->method('getName')
              ->willReturn('string');

        $union = $this->getMockBuilder('\ReflectionUnionType')
                      ->disableOriginalConstructor()
                      ->getMock();

        $union->expects($this->once())
              ->method('getTypes')
              ->willReturn([ $type1, $type2 ]);

        $param = $this->getMockBuilder('\ReflectionParameter')
                      ->disableOriginalConstructor()
                      ->getMock();

        $param->expects($this->exactly(2))
              ->method('getType')
              ->willReturn($union);

        $method = $this->get_accessible_reflection_method('get_parameters');

        $return = $method->invokeArgs($this->class, [ $params, [ $param ] ]);

        $this->assertIsArray($return);
        $this->assertEquals('cow', $return[0]);
    }

    /**
     * Test that get_parameters processes non-string parameters.
     *
     * @covers \Lunr\Core\ConfigServiceLocator::get_parameters
     */
    public function testGetParametersProcessesNonStringParameter(): void
    {
        $params = [ [], 5, NULL ];

        $method = $this->get_accessible_reflection_method('get_parameters');

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

        $method = $this->get_accessible_reflection_method('get_parameters');

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

        $param->expects($this->exactly(4))
              ->method('getType')
              ->willReturn($type);

        $method = $this->get_accessible_reflection_method('get_parameters');

        $return = $method->invokeArgs($this->class, [ $params, [ $param, $param, $param ] ]);

        $this->assertIsArray($return);
        $this->assertInstanceOf('Lunr\Core\Configuration', $return[0]);
        $this->assertEquals('config', $return[1]);
        $this->assertEquals('string', $return[2]);
    }

}

?>
