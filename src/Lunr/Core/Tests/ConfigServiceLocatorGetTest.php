<?php

/**
 * This file contains the ConfigServiceLocatorLocateTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2013 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Core\Tests;

use Lunr\Halo\CallbackMock;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

/**
 * This class contains the tests for the locator class.
 *
 * @covers Lunr\Core\ConfigServiceLocator
 */
class ConfigServiceLocatorGetTest extends ConfigServiceLocatorTestCase
{

    use MockeryPHPUnitIntegration;

    /**
     * Test that locate() returns an instance from the registry.
     *
     * @covers \Lunr\Core\ConfigServiceLocator::get
     */
    public function testLocateReturnsInstanceFromRegistry(): void
    {
        $this->assertInstanceOf('Lunr\Core\Configuration', $this->class->get('config'));
    }

    /**
     * Test that locate() reinstantiates an object from the config cache.
     *
     * @covers \Lunr\Core\ConfigServiceLocator::get
     */
    public function testLocateReinstantiatesInstanceFromCache(): void
    {
        $cache = [ 'datetime' => [ 'name' => 'LunrTest\Core\DateTime', 'params' => [ 'config' ] ] ];
        $this->setReflectionPropertyValue('cache', $cache);

        $this->assertInstanceOf('LunrTest\Core\DateTime', $this->class->get('datetime'));
    }

    /**
     * Test that locate() processes an object from the config cache.
     *
     * @covers \Lunr\Core\ConfigServiceLocator::__call
     */
    public function testLocateProcessesInstanceFromCache(): void
    {
        $cache = [
            'id' => [
                'methods' => [
                    [
                        'name'   => 'test',
                        'params' => [ '!param1' ],
                    ],
                    [
                        'name'   => 'test',
                        'params' => [ '!param2', '!param3' ],
                    ],
                ],
            ],
        ];

        $this->setReflectionPropertyValue('cache', $cache);

        $mock = Mockery::mock(CallbackMock::class);

        $mock->shouldReceive('test')
             ->once()
             ->with('param1');

        $mock->shouldReceive('test')
             ->once()
             ->with('param2', 'param3');

        $this->mockMethod([ $this->class, 'getInstance' ], function () use ($mock) { return $mock; });

        $this->class->get('id');

        $this->unmockMethod([ $this->class, 'getInstance' ]);
    }

    /**
     * Test that locate() processes a totally new object instance.
     *
     * @covers \Lunr\Core\ConfigServiceLocator::get
     */
    public function testLocateProcessesTotallyNewInstance(): void
    {
        $this->assertArrayNotHasKey('datetime', $this->getReflectionPropertyValue('registry'));

        $this->class->get('datetime');

        $return = $this->getReflectionPropertyValue('registry');
        $this->assertArrayHasKey('datetime', $return);
        $this->assertInstanceOf('LunrTest\Core\DateTime', $return['datetime']);
    }

    /**
     * Test that locate() returns totally new instance.
     *
     * @covers \Lunr\Core\ConfigServiceLocator::get
     */
    public function testLocateReturnsTotallyNewInstance(): void
    {
        $this->assertInstanceOf('LunrTest\Core\DateTime', $this->class->get('datetime'));
    }

    /**
     * Test that locate() throws for an unknown ID.
     *
     * @covers \Lunr\Core\ConfigServiceLocator::get
     */
    public function testLocateThrowsForUnknownID(): void
    {
        $this->expectException('Lunr\Core\Exceptions\NotFoundException');
        $this->expectExceptionMessage('Failed to locate object for identifier \'string\'!');

        $this->class->get('string');
    }

    /**
     * Test that __call() returns an instance from the registry.
     *
     * @covers \Lunr\Core\ConfigServiceLocator::__call
     */
    public function testMagicCallReturnsInstanceFromRegistry(): void
    {
        $this->assertInstanceOf('Lunr\Core\Configuration', $this->class->config());
    }

    /**
     * Test that __call() reinstantiates an object from the config cache.
     *
     * @covers \Lunr\Core\ConfigServiceLocator::__call
     */
    public function testMagicCallReinstantiatesInstanceFromCache(): void
    {
        $cache = [ 'datetime' => [ 'name' => 'LunrTest\Core\DateTime', 'params' => [ 'config' ] ] ];
        $this->setReflectionPropertyValue('cache', $cache);

        $this->assertInstanceOf('LunrTest\Core\DateTime', $this->class->datetime());
    }

    /**
     * Test that __call() processes an object from the config cache.
     *
     * @covers \Lunr\Core\ConfigServiceLocator::__call
     */
    public function testMagicCallProcessesInstanceFromCache(): void
    {
        $cache = [
            'id' => [
                'methods' => [
                    [
                        'name'   => 'test',
                        'params' => [ '!param1' ],
                    ],
                    [
                        'name'   => 'test',
                        'params' => [ '!param2', '!param3' ],
                    ],
                ],
            ],
        ];

        $this->setReflectionPropertyValue('cache', $cache);

        $mock = Mockery::mock(CallbackMock::class);

        $mock->shouldReceive('test')
             ->once()
             ->with('param1');

        $mock->shouldReceive('test')
             ->once()
             ->with('param2', 'param3');

        $this->mockMethod([ $this->class, 'getInstance' ], function () use ($mock) { return $mock; });

        $this->class->id();

        $this->unmockMethod([ $this->class, 'getInstance' ]);
    }

    /**
     * Test that __call() processes a totally new object instance.
     *
     * @covers \Lunr\Core\ConfigServiceLocator::__call
     */
    public function testMagicCallProcessesTotallyNewInstance(): void
    {
        $registry = $this->getReflectionProperty('registry');

        $this->assertArrayNotHasKey('datetime', $registry->getValue($this->class));
        $this->class->datetime();

        $return = $registry->getValue($this->class);

        $this->assertArrayHasKey('datetime', $return);
        $this->assertInstanceOf('LunrTest\Core\DateTime', $return['datetime']);
    }

    /**
     * Test that __call() returns totally new instance.
     *
     * @covers \Lunr\Core\ConfigServiceLocator::__call
     */
    public function testMagicCallReturnsTotallyNewInstance(): void
    {
        $this->assertInstanceOf('LunrTest\Core\DateTime', $this->class->datetime());
    }

    /**
     * Test that __call() returns NULL for an unknown ID.
     *
     * @covers \Lunr\Core\ConfigServiceLocator::__call
     */
    public function testMagicCallReturnsNullForUnknownID(): void
    {
        $this->expectException('Lunr\Core\Exceptions\NotFoundException');
        $this->expectExceptionMessage('Failed to locate');

        $this->class->string();
    }

}

?>
