<?php

/**
 * This file contains the ConfigServiceLocatorTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2013 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Core\Tests;

use stdClass;

/**
 * This class contains the tests for the locator class.
 *
 * @covers     Lunr\Core\ConfigServiceLocator
 */
class ConfigServiceLocatorBaseTest extends ConfigServiceLocatorTestCase
{

    /**
     * Test that the cache is initialized as empty array.
     */
    public function testCacheIsEmptyArray(): void
    {
        $this->assertArrayEmpty($this->getReflectionPropertyValue('cache'));
    }

    /**
     * Test that the registry is initialized correctly.
     */
    public function testRegistryIsSetupCorrectly(): void
    {
        $registry = $this->getReflectionPropertyValue('registry');

        $this->assertIsArray($registry);
        $this->assertCount(2, $registry);
        $this->assertArrayHasKey('config', $registry);
        $this->assertArrayHasKey('locator', $registry);
    }

    /**
     * Test that the Configuration class is passed correctly.
     */
    public function testConfigurationIsPassedCorrectly(): void
    {
        $this->assertSame($this->configuration, $this->getReflectionPropertyValue('config'));
    }

    /**
     * Test that override() overwrites an index when ID is already taken.
     *
     * @covers Lunr\Core\ConfigServiceLocator::override
     */
    public function testOverrideWhenIDAlreadyTaken(): void
    {
        $registry = [ 'id' => 'Foo' ];
        $class    = new stdClass();

        $this->setReflectionPropertyValue('registry', $registry);

        $this->class->override('id', $class);

        $registry = $this->getReflectionPropertyValue('registry');

        $this->assertArrayHasKey('id', $registry);
        $this->assertSame($class, $registry['id']);
    }

    /**
     * Test that get() locates a class.
     *
     * @covers \Lunr\Core\ConfigServiceLocator::get
     */
    public function testGetCallsLocate()
    {
        $this->expectException('Lunr\Core\Exceptions\NotFoundException');
        $this->expectExceptionMessage('Failed to locate object for identifier \'1\'!');

        $this->class->get(1);
    }

    /**
     * Test that override() returns TRUE when successful.
     *
     * @covers Lunr\Core\ConfigServiceLocator::override
     */
    public function testSuccessfulOverride(): void
    {
        $class = new stdClass();

        $this->class->override('id', $class);

        $registry = $this->getReflectionPropertyValue('registry');

        $this->assertArrayHasKey('id', $registry);
        $this->assertSame($class, $registry['id']);
    }

}

?>
