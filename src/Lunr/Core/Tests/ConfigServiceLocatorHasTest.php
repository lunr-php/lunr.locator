<?php

/**
 * This file contains the ConfigServiceLocatorHasTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2020 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Core\Tests;

/**
 * This class contains the tests for the locator class.
 *
 * @covers \Lunr\Core\ConfigServiceLocator
 */
class ConfigServiceLocatorHasTest extends ConfigServiceLocatorTestCase
{

    /**
     * Test that has() returns false on non-string.
     *
     * @covers \Lunr\Core\ConfigServiceLocator::has
     */
    public function testHasReturnsFalseOnInt(): void
    {
        $this->assertFalse($this->class->has(1));
    }

    /**
     * Test that has() returns true if an item is in the registry.
     *
     * @covers \Lunr\Core\ConfigServiceLocator::has
     */
    public function testHasReturnsConfig(): void
    {
        $this->assertTrue($this->class->has('config'));
    }

    /**
     * Test that has() returns true if an item is in the registry.
     *
     * @covers \Lunr\Core\ConfigServiceLocator::has
     */
    public function testHasReturnsOnCache(): void
    {
        $this->set_reflection_property_value('cache', [ 'cachehit' => [] ]);

        $this->assertTrue($this->class->has('cachehit'));
    }

    /**
     * Test that has() returns true for an instance in the include path.
     *
     * @covers \Lunr\Core\ConfigServiceLocator::has
     */
    public function testHasReturnsTrueOnInclude(): void
    {
        $this->assertTrue($this->class->has('valid'));
    }

    /**
     * Test that has() returns false on failure.
     *
     * @covers \Lunr\Core\ConfigServiceLocator::has
     */
    public function testHasReturnsFalseOnFail(): void
    {
        $this->assertFalse($this->class->has('fsfsfdshfbhsk'));
    }

}

?>
