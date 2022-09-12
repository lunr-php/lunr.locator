<?php

/**
 * This file contains an imlementation of the ServiceLocator
 * design pattern. It allows to transparently request class
 * instances without having to care about the instantiation
 * details or sharing.
 *
 * @package    Lunr\Core
 * @author     Heinz Wiesinger <heinz@m2mobi.com>
 * @copyright  2013-2018, M2Mobi BV, Amsterdam, The Netherlands
 * @license    http://lunr.nl/LICENSE MIT License
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
 */
class ConfigServiceLocator implements ContainerInterface
{

    /**
     * Registry for storing shared objects.
     * @var array
     */
    protected $registry;

    /**
     * Class bootstrap config cache.
     * @var array
     */
    protected $cache;

    /**
     * Instance of the Configuration class.
     * @var Configuration
     */
    protected $config;

    /**
     * Constructor.
     *
     * @param Configuration $config Shared instance of the Configuration class.
     */
    public function __construct($config)
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
     * @param string $id        ID of the object to instantiate
     * @param array  $arguments Arguments passed on call (Ignored)
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

        $this->load_recipe($id);

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
        if (isset($this->registry[$id]) && is_object($this->registry[$id]))
        {
            return $this->registry[$id];
        }

        if (isset($this->cache[$id]))
        {
            return $this->process_new_instance($id, $this->get_instance($id));
        }

        $this->load_recipe($id);

        if (isset($this->cache[$id]))
        {
            return $this->process_new_instance($id, $this->get_instance($id));
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
    protected function load_recipe(string $id): void
    {
        $recipe = [];
        $path   = 'locator/locate.' . $id . '.inc.php';
        if (stream_resolve_include_path($path) !== FALSE)
        {
            include $path;
        }

        if (isset($recipe[$id]) && is_array($recipe[$id]) && is_array($recipe[$id]['params']))
        {
            $this->cache[$id] = $recipe[$id];
        }
    }

    /**
     * Check whether we need to do something special with a newly created object.
     *
     * @param string $id       ID of the object instantiated
     * @param object $instance Newly created object instance
     *
     * @return object The passed object instance.
     */
    protected function process_new_instance(string $id, object $instance): object
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
                    $method_params = $this->get_parameters(
                        $method['params'],
                        (new ReflectionMethod($instance, $method['name']))->getParameters()
                    );
                }
                else
                {
                    $method_params = [];
                }

                $replaces_instance = $method['return_replaces_instance'] ?? FALSE;
                $method_output     = $instance->{$method['name']}(...$method_params);
                $instance          = $replaces_instance ? $method_output : $instance;
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
    protected function get_instance(string $id): ?object
    {
        $reflection = new ReflectionClass($this->cache[$id]['name']);

        if ($reflection->isInstantiable() !== TRUE)
        {
            throw new ContainerException("Not possible to instantiate '{$reflection->name}'!");
        }

        $constructor = $reflection->getConstructor();

        if (is_null($constructor))
        {
            return $reflection->newInstance();
        }

        $number_of_total_parameters    = $constructor->getNumberOfParameters();
        $number_of_required_parameters = $constructor->getNumberOfRequiredParameters();

        if (count($this->cache[$id]['params']) < $number_of_required_parameters)
        {
            throw new ContainerException("Not enough parameters for $reflection->name!");
        }

        if ($number_of_total_parameters > 0)
        {
            return $reflection->newInstanceArgs(
                $this->get_parameters(
                    $this->cache[$id]['params'],
                    $constructor->getParameters()
                )
            );
        }
        else
        {
            return $reflection->newInstance();
        }
    }

    /**
     * Prepare the parameters in the recipe for object instantiation.
     *
     * @param array                 $params       Array of parameters according to the recipe.
     * @param ReflectionParameter[] $methodParams Array of ReflectionParameters for the method
     *
     * @return array Array of processed parameters ready for instantiation.
     */
    protected function get_parameters(array $params, array $methodParams): array
    {
        $processed_params = [];

        foreach ($params as $key => $value)
        {
            if (!is_string($value))
            {
                $processed_params[] = $value;
                continue;
            }

            if ($value[0] === '!')
            {
                $processed_params[] = substr($value, 1);
                continue;
            }

            if (isset($methodParams[$key]))
            {
                $typeClass = $methodParams[$key]->getType();
                if ($typeClass instanceof ReflectionNamedType && $typeClass->getName() === 'string')
                {
                    $processed_params[] = $value;
                    continue;
                }
            }

            $processed_params[] = $this->get($value);
        }

        return $processed_params;
    }

}

?>
