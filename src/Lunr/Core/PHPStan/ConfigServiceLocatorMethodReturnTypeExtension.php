<?php

/**
 * PHPStan Method return type extension
 *
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 *
 * @see https://phpstan.org/developing-extensions/dynamic-return-type-extensions
 */

namespace Lunr\Core\PHPStan;

use Lunr\Core\ConfigServiceLocator;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

/**
 * Return type extension for ConfigServiceLocator recipes
 */
class ConfigServiceLocatorMethodReturnTypeExtension implements DynamicStaticMethodReturnTypeExtension
{

    /**
     * The class this extension applies to.
     * @return string
     */
    public function getClass(): string
    {
        return ConfigServiceLocator::class;
    }

    /**
     * Block dynamic return type for ConfigServiceLocator::has().
     *
     * @param MethodReflection $methodReflection Reflection of the method
     *
     * @return bool if the method is supported
     */
    public function isStaticMethodSupported(MethodReflection $methodReflection): bool
    {
        return !in_array($methodReflection->getName(), [ 'has', 'override' ]);
    }

    /**
     * Return the type for this config service locator instance.
     *
     * @param MethodReflection $methodReflection Reflection of the method
     * @param StaticCall       $methodCall       Call for this instance
     * @param Scope            $scope            Scope of this call
     *
     * @return Type|null The class or null if none is found
     */
    public function getTypeFromStaticMethodCall(MethodReflection $methodReflection, StaticCall $methodCall, Scope $scope): ?Type
    {
        if ($methodReflection->getName() !== 'get')
        {
            $id = $methodReflection->getName();
        }
        else
        {
            $arg = $methodCall->getArgs()[0]->value;

            if ($arg instanceof String_)
            {
                $id = $arg->value;
            }
            else
            {
                return NULL;
            }
        }

        /** @var LocatorRecipe $recipe */
        $recipe = [];
        $path   = 'locator/locate.' . $id . '.inc.php';
        if (stream_resolve_include_path($path) === FALSE)
        {
            return NULL;
        }

        include_once $path;

        if (!isset($recipe[$id]['name']))
        {
            return NULL;
        }

        return new ObjectType($recipe[$id]['name']);
    }

}

?>
