<?php

/**
 * This file contains an implementation of the ServiceLocator
 * design pattern. It allows to transparently request class
 * instances without having to care about the instantiation
 * details or sharing.
 *
 * SPDX-FileCopyrightText: Copyright 2013 M2mobi B.V., Amsterdam, The Netherlands
 * SPDX-FileCopyrightText: Copyright 2022 Move Agency Group B.V., Zwolle, The Netherlands
 * SPDX-License-Identifier: MIT
 */

namespace Lunr\Core;

use Lunr\Core\Exceptions\ContainerException;
use Lunr\Core\Exceptions\NotFoundException;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * Class Locator
 *
 * @phpstan-type LocatorRecipe array{
 *     name: class-string<object>,
 *     params: mixed[],
 *     singleton: bool,
 *     methods?: list<array{
 *         name: string,
 *         params?: mixed[],
 *         return_replaces_instance?: bool,
 *     }>,
 * }
 */
class ConfigServiceLocator implements ContainerInterface
{

    /**
     * Registry for storing shared objects.
     * @var object[]
     */
    protected array $registry;

    /**
     * Class bootstrap config cache.
     * @var LocatorRecipe[]
     */
    protected array $cache;

    /**
     * Instance of the Configuration class.
     * @var Configuration
     */
    protected Configuration $config;

    /**
     * Constructor.
     *
     * @param Configuration $config Shared instance of the Configuration class.
     */
    public function __construct(Configuration $config)
    {
        $this->registry = [];
        $this->cache    = [];

        $this->config = $config;

        $this->registry['config']  = $config;
        $this->registry['locator'] = $this;
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        unset($this->registry);
        unset($this->cache);
        unset($this->config);
    }

    /**
     * Instantiate a new object by ID.
     *
     * @param string  $id        ID of the object to instantiate
     * @param mixed[] $arguments Arguments passed on call (Ignored)
     *
     * @return object|null New Object or NULL if the ID is unknown.
     */
    public function __call(string $id, array $arguments): ?object
    {
        return $this->get($id);
    }

    /**
     * Override automatic location by preloading an object manually.
     *
     * This only works with objects that are treated like singletons
     * and won't if the specified ID is already taken.
     *
     * @param string $id     ID for the preloaded object
     * @param object $object Instance of the object to preload
     *
     * @return void
     */
    public function override(string $id, object $object): void
    {
        $this->registry[$id] = $object;
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has(string $id): bool
    {
        if (isset($this->registry[$id]))
        {
            return TRUE;
        }

        if (isset($this->cache[$id]))
        {
            return TRUE;
        }

        $this->loadRecipe($id);

        if (isset($this->cache[$id]))
        {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundException  No entry was found for **this** identifier.
     * @throws ContainerException Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get(string $id)
    {
        if (isset($this->registry[$id]))
        {
            return $this->registry[$id];
        }

        if (isset($this->cache[$id]))
        {
            return $this->processNewInstance($id, $this->getInstance($id));
        }

        $this->loadRecipe($id);

        if (isset($this->cache[$id]))
        {
            return $this->processNewInstance($id, $this->getInstance($id));
        }

        throw new NotFoundException("Failed to locate object for identifier '$id'!");
    }

    /**
     * Load recipe for instantiating a given ID.
     *
     * @param string $id ID of the object to load the recipe for.
     *
     * @return void
     */
    protected function loadRecipe(string $id): void
    {
        /**
         * @var LocatorRecipe $recipe
         */
        $recipe = [];
        $path   = 'locator/locate.' . $id . '.inc.php';
        if (stream_resolve_include_path($path) !== FALSE)
        {
            include $path;
        }

        if (!isset($recipe[$id]) || !is_array($recipe[$id]) || !is_array($recipe[$id]['params']))
        {
            return;
        }

        $this->cache[$id] = $recipe[$id];
    }

    /**
     * Check whether we need to do something special with a newly created object.
     *
     * @param string $id       ID of the object instantiated
     * @param object $instance Newly created object instance
     *
     * @return object The passed object instance.
     */
    protected function processNewInstance(string $id, object $instance): object
    {
        if (isset($this->cache[$id]['singleton']) && ($this->cache[$id]['singleton'] === TRUE))
        {
            $this->registry[$id] = $instance;
        }

        if (isset($this->cache[$id]['methods']))
        {
            foreach ($this->cache[$id]['methods'] as $method)
            {
                if (isset($method['params']))
                {
                    $methodParams = $this->getParameters(
                        $method['params'],
                        (new ReflectionMethod($instance, $method['name']))->getParameters()
                    );
                }
                else
                {
                    $methodParams = [];
                }

                $replacesInstance = $method['return_replaces_instance'] ?? FALSE;
                $methodOutput     = $instance->{$method['name']}(...$methodParams);
                $instance         = $replacesInstance ? $methodOutput : $instance;
            }
        }

        return $instance;
    }

    /**
     * Get a new object instance for a given ID.
     *
     * @param string $id ID of the object to instantiate.
     *
     * @return object|null New Object on success, NULL on error.
     */
    protected function getInstance(string $id): ?object
    {
        $reflection = new ReflectionClass($this->cache[$id]['name']);

        if ($reflection->isInstantiable() !== TRUE && $reflection->isEnum() !== TRUE)
        {
            throw new ContainerException("Not possible to instantiate '{$reflection->name}'!");
        }

        if ($reflection->isEnum() === TRUE)
        {
            if (count($this->cache[$id]['params']) < 1)
            {
                throw new ContainerException("Not enough parameters for $reflection->name::from()!");
            }

            return call_user_func_array([ $this->cache[$id]['name'], 'from' ], $this->cache[$id]['params']);
        }

        $constructor = $reflection->getConstructor();

        if (is_null($constructor))
        {
            return $reflection->newInstance();
        }

        $numberOfTotalParameters    = $constructor->getNumberOfParameters();
        $numberOfRequiredParameters = $constructor->getNumberOfRequiredParameters();

        if (count($this->cache[$id]['params']) < $numberOfRequiredParameters)
        {
            throw new ContainerException("Not enough parameters for $reflection->name!");
        }

        if ($numberOfTotalParameters > 0)
        {
            return $reflection->newInstanceArgs(
                $this->getParameters(
                    $this->cache[$id]['params'],
                    $constructor->getParameters()
                )
            );
        }

        return $reflection->newInstance();
    }

    /**
     * Prepare the parameters in the recipe for object instantiation.
     *
     * @param mixed[]               $params       Array of parameters according to the recipe.
     * @param ReflectionParameter[] $methodParams Array of ReflectionParameters for the method
     *
     * @return mixed[] Array of processed parameters ready for instantiation.
     */
    protected function getParameters(array $params, array $methodParams): array
    {
        $processedParams = [];

        foreach ($params as $key => $value)
        {
            if (!is_string($value))
            {
                $processedParams[] = $value;
                continue;
            }

            if ($value[0] === '!')
            {
                $processedParams[] = substr($value, 1);
                continue;
            }

            if (isset($methodParams[$key]))
            {
                $typeClass = $methodParams[$key]->getType();
                if ($typeClass instanceof ReflectionNamedType && $typeClass->getName() === 'string')
                {
                    $processedParams[] = $value;
                    continue;
                }
            }

            $processedParams[] = $this->get($value);
        }

        return $processedParams;
    }

}

?>
