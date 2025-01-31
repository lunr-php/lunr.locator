<?php

/**
 * This file contains the ConfigServiceLocatorSupportTest class.
 *
 * SPDX-FileCopyrightText: Copyright 2013 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Core\Tests;

use Lunr\Halo\CallbackMock;
use stdClass;

/**
 * This class contains the tests for the locator class.
 *
 * @covers     Lunr\Core\ConfigServiceLocator
 */
class ConfigServiceLocatorSupportTest extends ConfigServiceLocatorTestCase
{

    /**
     * Test that load_recipe() does not try to include non-existing files.
     *
     * @covers Lunr\Core\ConfigServiceLocator::load_recipe
     */
    public function testLoadRecipeDoesNotIncludeNonExistingFile(): void
    {
        $filename = 'Core/locator/locate.nonexisting.inc.php';

        $basename = str_replace('src/Lunr/Core/Tests', 'tests/statics/', __DIR__);
        $filename = $basename . $filename;

        $method = $this->getReflectionMethod('load_recipe');

        $this->assertNotContains($filename, get_included_files());
        $method->invokeArgs($this->class, [ 'nonexisting' ]);
        $this->assertNotContains($filename, get_included_files());
    }

    /**
     * Test that load_recipe() includes existing files.
     *
     * @covers Lunr\Core\ConfigServiceLocator::load_recipe
     */
    public function testLoadRecipeIncludesExistingFile(): void
    {
        $filename = 'Core/locator/locate.existing.inc.php';

        $basename = str_replace('src/Lunr/Core/Tests', 'tests/statics/', __DIR__);
        $filename = $basename . $filename;

        $method = $this->getReflectionMethod('load_recipe');

        $this->assertNotContains($filename, get_included_files());
        $method->invokeArgs($this->class, [ 'existing' ]);
        $this->assertContains($filename, get_included_files());
    }

    /**
     * Test that load_recipe() caches valid recipes.
     *
     * @covers Lunr\Core\ConfigServiceLocator::load_recipe
     */
    public function testLoadRecipeCachesWithValidRecipes(): void
    {
        $method = $this->getReflectionMethod('load_recipe');
        $cache  = $this->getReflectionProperty('cache');

        $this->assertArrayNotHasKey('valid', $cache->getValue($this->class));
        $method->invokeArgs($this->class, [ 'valid' ]);
        $this->assertArrayHasKey('valid', $cache->getValue($this->class));
    }

    /**
     * Test that load_recipe() does not cache invalid recipes.
     *
     * @param string $id ID of an invalid recipe.
     *
     * @dataProvider invalidRecipeProvider
     * @covers       Lunr\Core\ConfigServiceLocator::load_recipe
     */
    public function testLoadRecipeDoesNotCacheWithInvalidRecipes($id): void
    {
        $method = $this->getReflectionMethod('load_recipe');
        $cache  = $this->getReflectionProperty('cache');

        $this->assertArrayNotHasKey($id, $cache->getValue($this->class));
        $method->invokeArgs($this->class, [ 'valid' ]);
        $this->assertArrayNotHasKey($id, $cache->getValue($this->class));
    }

    /**
     * Test that process_new_instance() returns the passed instance.
     *
     * @covers Lunr\Core\ConfigServiceLocator::process_new_instance
     */
    public function testProcessNewInstanceReturnsInstance(): void
    {
        $method   = $this->getReflectionMethod('process_new_instance');
        $instance = new stdClass();

        $return = $method->invokeArgs($this->class, [ 'id', $instance ]);

        $this->assertSame($instance, $return);
    }

    /**
     * Test that process_new_instance() does not store non-singleton objects in the registry.
     *
     * @covers Lunr\Core\ConfigServiceLocator::process_new_instance
     */
    public function testProcessNewInstanceDoesNotStoreNonSingletonsInRegistry(): void
    {
        $method   = $this->getReflectionMethod('process_new_instance');
        $registry = $this->getReflectionProperty('registry');
        $instance = new stdClass();

        $recipe = [ 'id' => [ 'singleton' => FALSE ] ];
        $this->setReflectionPropertyValue('cache', $recipe);

        $this->assertArrayNotHasKey('id', $registry->getValue($this->class));
        $method->invokeArgs($this->class, [ 'id', $instance ]);
        $this->assertArrayNotHasKey('id', $registry->getValue($this->class));
    }

    /**
     * Test that process_new_instance() does not store in the registry if the singleton info is missing.
     *
     * @covers Lunr\Core\ConfigServiceLocator::process_new_instance
     */
    public function testProcessNewInstanceDoesNotStoreInRegistryIfSingletonInfoMissing(): void
    {
        $method   = $this->getReflectionMethod('process_new_instance');
        $registry = $this->getReflectionProperty('registry');
        $instance = new stdClass();

        $recipe = [ 'id' => [] ];
        $this->setReflectionPropertyValue('cache', $recipe);

        $this->assertArrayNotHasKey('id', $registry->getValue($this->class));
        $method->invokeArgs($this->class, [ 'id', $instance ]);
        $this->assertArrayNotHasKey('id', $registry->getValue($this->class));
    }

    /**
     * Test that process_new_instance() stores singleton objects in the registry.
     *
     * @covers Lunr\Core\ConfigServiceLocator::process_new_instance
     */
    public function testProcessNewInstanceStoresSingletonsInRegistry(): void
    {
        $method   = $this->getReflectionMethod('process_new_instance');
        $registry = $this->getReflectionProperty('registry');
        $instance = new stdClass();

        $recipe = [ 'id' => [ 'singleton' => TRUE ] ];
        $this->setReflectionPropertyValue('cache', $recipe);

        $this->assertArrayNotHasKey('id', $registry->getValue($this->class));
        $method->invokeArgs($this->class, [ 'id', $instance ]);
        $this->assertArrayHasKey('id', $registry->getValue($this->class));
    }

    /**
     * Test that process_new_instance() calls defined methods with params.
     *
     * @covers \Lunr\Core\ConfigServiceLocator::process_new_instance
     */
    public function testProcessNewInstanceCallsMethodsWithParams(): void
    {
        $recipe = [
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

        $this->setReflectionPropertyValue('cache', $recipe);

        $mock = $this->getMockBuilder('Lunr\Halo\CallbackMock')->getMock();

        $mock->expects($this->exactly(2))
             ->method('test')
             ->withConsecutive(
                 [ 'param1' ],
                 [ 'param2', 'param3' ]
             );

        $method = $this->getReflectionMethod('process_new_instance');
        $this->assertSame($mock, $method->invokeArgs($this->class, [ 'id', $mock ]));
    }

    /**
     * Test that process_new_instance() calls defined methods with params and replaces the instance if immutable.
     *
     * @covers \Lunr\Core\ConfigServiceLocator::process_new_instance
     */
    public function testProcessNewInstanceCallsMethodsWithParamsReplacesImmutable(): void
    {
        $recipe = [
            'id' => [
                'methods' => [
                    [
                        'name'   => 'test',
                        'params' => [ '!param1' ],
                        'return_replaces_instance' => TRUE,
                    ]
                ],
            ],
        ];

        $this->setReflectionPropertyValue('cache', $recipe);

        $mock = $this->getMockBuilder('Lunr\Halo\CallbackMock')->getMock();

        $mock->expects($this->exactly(1))
             ->method('test')
             ->with('param1')
             ->willReturn(new CallbackMock());

        $method = $this->getReflectionMethod('process_new_instance');
        $result = $method->invokeArgs($this->class, [ 'id', $mock ]);
        $this->assertNotSame($mock, $result);
        $this->assertInstanceOf(CallbackMock::class, $result);
    }

    /**
     * Test that process_new_instance() calls defined methods with no params.
     *
     * @covers \Lunr\Core\ConfigServiceLocator::process_new_instance
     */
    public function testProcessNewInstanceCallsMethodsWithNoParams(): void
    {
        $recipe = [
            'id' => [
                'methods' => [
                    [ 'name' => 'test' ],
                ],
            ],
        ];

        $this->setReflectionPropertyValue('cache', $recipe);

        $mock = $this->getMockBuilder('Lunr\Halo\CallbackMock')->getMock();

        $mock->expects($this->exactly(1))
             ->method('test');

        $method = $this->getReflectionMethod('process_new_instance');
        $this->assertSame($mock, $method->invokeArgs($this->class, [ 'id', $mock ]));
    }

    /**
     * Test that process_new_instance() calls defined methods with located params.
     *
     * @covers \Lunr\Core\ConfigServiceLocator::process_new_instance
     */
    public function testProcessNewInstanceCallsMethodsWithLocatedParams(): void
    {
        $recipe = [
            'id' => [
                'methods' => [
                    [
                        'name'   => 'test',
                        'params' => [ 'object1_id', '!param2' ],
                    ],
                ],
            ],
        ];

        $object1 = (object) [ 'key1' => 'value1' ];

        $this->setReflectionPropertyValue('cache', $recipe);
        $this->setReflectionPropertyValue('registry', [ 'object1_id' => $object1 ]);

        $mock = $this->getMockBuilder('Lunr\Halo\CallbackMock')->getMock();

        $mock->expects($this->exactly(1))
             ->method('test')
             ->with($this->identicalTo($object1), 'param2');

        $method = $this->getReflectionMethod('process_new_instance');
        $this->assertSame($mock, $method->invokeArgs($this->class, [ 'id', $mock ]));
    }

}

?>
