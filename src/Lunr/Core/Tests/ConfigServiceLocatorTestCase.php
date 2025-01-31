<?php

/**
 * This file contains the ConfigServiceLocatorTestCase class.
 *
 * SPDX-FileCopyrightText: Copyright 2013 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Core\Tests;

use Lunr\Core\ConfigServiceLocator;
use Lunr\Core\Configuration;
use Lunr\Halo\LunrBaseTestCase;

/**
 * This class contains the tests for the locator class.
 *
 * @covers     \Lunr\Core\ConfigServiceLocator
 */
abstract class ConfigServiceLocatorTestCase extends LunrBaseTestCase
{

    /**
     * Mock instance of the Configuration class.
     * @var Configuration
     */
    protected $configuration;

    /**
     * Instance of the tested class.
     * @var ConfigServiceLocator
     */
    protected ConfigServiceLocator $class;

    /**
     * Testcase Constructor.
     */
    public function setUp(): void
    {
        $this->configuration = $this->getMockBuilder('Lunr\Core\Configuration')->getMock();

        $this->class = new ConfigServiceLocator($this->configuration);

        parent::baseSetUp($this->class);
    }

    /**
     * Testcase Destructor.
     */
    public function tearDown(): void
    {
        unset($this->class);
        unset($this->configuration);

        parent::tearDown();
    }

    /**
     * Unit test data provider for invalid recipe ids.
     *
     * @return array $ids Array of invalid recipe ids.
     */
    public function invalidRecipeProvider(): array
    {
        $ids   = [];
        $ids[] = [ 'nonexisting' ];
        $ids[] = [ 'recipeidnotset' ];
        $ids[] = [ 'recipeidnotarray' ];
        $ids[] = [ 'recipeidparamsnotarray' ];

        return $ids;
    }

    /**
     * Unit test data provider for non-objects.
     *
     * @return array $values Array of non-object values
     */
    public function invalidObjectProvider(): array
    {
        $values   = [];
        $values[] = [ 'String' ];
        $values[] = [ 1 ];
        $values[] = [ 1.1 ];
        $values[] = [ NULL ];
        $values[] = [ TRUE ];

        return $values;
    }

}

?>
