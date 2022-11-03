<?php

/**
 * PHPStan Method return type extension
 *
 * @package    Lunr\Core
 * @author     Sean Molenaar <sean.molenaar@moveagency.com>
 * @copyright  2022, Move Agency BV, Amsterdam, The Netherlands
 * @license    http://lunr.nl/LICENSE MIT License
 *
 * @see https://phpstan.org/developing-extensions/dynamic-return-type-extensions
 */

namespace Lunr\Core\PHPStan;

use Lunr\Core\ConfigServiceLocator;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

/**
 * Return type extension for ConfigServiceLocator recipes
 */
class ConfigServiceLocatorMethodReturnTypeExtension implements DynamicMethodReturnTypeExtension
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
    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return !in_array($methodReflection->getName(), ['has', 'override']);
    }

    /**
     * Return the type for this config service locator instance.
     *
     * @param MethodReflection $methodReflection Reflection of the method
     * @param MethodCall       $methodCall       Call for this instance
     * @param Scope            $scope            Scope of this call
     *
     * @return Type|null The class or null if none is found
     */
    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        $id = $methodReflection->getName() !== 'get' ? $methodReflection->getName() : $methodCall->getArgs()[0]->value->value;

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
