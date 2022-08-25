<?php

/**
 * This file contains the ConfigServiceLocatorHasTest class.
 *
 * @package    Lunr\Core
 * @author     Sean Molenaar <s.molenaar@m2mobi.com>
 * @copyright  2020, M2Mobi BV, Amsterdam, The Netherlands
 * @license    http://lunr.nl/LICENSE MIT License
 */

namespace Lunr\Core\Tests;

/**
 * This class contains the tests for the locator class.
 *
 * @covers \Lunr\Core\ConfigServiceLocator
 */
class ConfigServiceLocatorHasTest extends ConfigServiceLocatorTest
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
