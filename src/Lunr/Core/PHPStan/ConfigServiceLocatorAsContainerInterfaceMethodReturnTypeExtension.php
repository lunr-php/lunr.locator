<?php

/**
 * PHPStan Method return type extension
 *
 * SPDX-FileCopyrightText: Copyright 2023 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 *
 * @see https://phpstan.org/developing-extensions/dynamic-return-type-extensions
 */

namespace Lunr\Core\PHPStan;

use PHPStan\Reflection\MethodReflection;
use Psr\Container\ContainerInterface;

/**
 * Return type extension for ConfigServiceLocator recipes
 */
class ConfigServiceLocatorAsContainerInterfaceMethodReturnTypeExtension extends ConfigServiceLocatorMethodReturnTypeExtension
{

    /**
     * The class this extension applies to.
     * @return string
     */
    public function getClass(): string
    {
        return ContainerInterface::class;
    }

    /**
     * Block dynamic return type for ContainerInterface::has().
     *
     * @param MethodReflection $methodReflection Reflection of the method
     *
     * @return bool if the method is supported
     */
    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return !in_array($methodReflection->getName(), [ 'has' ]);
    }

}

?>
