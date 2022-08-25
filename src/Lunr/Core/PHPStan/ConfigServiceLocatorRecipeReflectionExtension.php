<?php

/**
 * PHPStan Method reflection
 *
 * @package    Lunr\Core
 * @author     Sean Molenaar <s.molenaar@m2mobi.com>
 * @copyright  2022, M2Mobi BV, Amsterdam, The Netherlands
 * @license    http://lunr.nl/LICENSE MIT License
 *
 * @see https://phpstan.org/developing-extensions/class-reflection-extensions
 */

namespace Lunr\Core\PHPStan;

use Lunr\Core\ConfigServiceLocator;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\MethodsClassReflectionExtension;

/**
 * Method reflection extension for ConfigServiceLocator recipes
 */
class ConfigServiceLocatorRecipeReflectionExtension implements MethodsClassReflectionExtension
{
    /**
     * List of locators that are pre-set
     * @var array
     */
    private array $presetList = [ 'config' ];

    /**
     * Constructor for ConfigServiceLocatorRecipeReflectionExtension.
     *
     * @param array $presetList List of pre-set locator recipes.
     */
    public function __construct(array $presetList = [])
    {
        $this->presetList = array_merge($this->presetList, $presetList);
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        unset($this->presetList);
    }

    /**
     * Check if the recipe exists or is one of the special cases
     *
     * @param  ClassReflection $classReflection The class to check
     * @param  string          $methodName      Name of the method
     *
     * @return bool if the method is present in the class
     */
    public function hasMethod(ClassReflection $classReflection, string $methodName): bool
    {
        if ($classReflection->getName() !== ConfigServiceLocator::class)
        {
            return FALSE;
        }

        if (in_array($methodName, $this->presetList))
        {
            return TRUE;
        }

        return stream_resolve_include_path('locator/locate.' . $methodName . '.inc.php') !== FALSE;
    }

    /**
     * Get a reflection of the class
     *
     * @param  ClassReflection $classReflection The class of the reflection
     * @param  string          $methodName      Name of the reflected method
     *
     * @return MethodReflection The reflected method
     */
    public function getMethod(ClassReflection $classReflection, string $methodName): MethodReflection
    {
        return new ConfigServiceLocatorRecipeReflection($classReflection, $methodName);
    }

}
