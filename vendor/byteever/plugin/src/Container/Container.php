<?php

namespace WooCommerceMinMaxQuantities\B8\Plugin\Container;

use ArrayAccess;
use BadMethodCallException;
use Closure;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionUnionType;
use ReflectionException;
use ReflectionParameter;
defined('ABSPATH') || exit;
/**
 * Simple dependency injection container.
 *
 * @since 1.0.0
 * @package \B8\Plugin
 */
class Container implements ContainerInterface, ArrayAccess
{
    /**
     * Registry of service bindings.
     *
     * @since 1.0.0
     * @var array
     */
    private $bindings = array();
    /**
     * Cache for shared instances.
     *
     * @since 1.0.0
     * @var array
     */
    private $instances = array();
    /**
     * Registry of aliases.
     *
     * @since 1.0.0
     * @var array
     */
    private $aliases = array();
    /**
     * The stack of concretions currently being built.
     *
     * @since 1.0.0
     * @var array
     */
    private $build_stack = array();
    /**
     * A map from class name and static methods to the built callback.
     *
     * @since 1.0.0
     * @var array
     */
    private $callbacks = array();
    /**
     * Magic method for property access - delegates to container.
     *
     * Enables: $app->utilities, $app->request, etc.
     *
     * @param string $name Property name.
     *
     * @since 1.0.0
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name, null);
    }
    /**
     * Magic method for property setting - delegates to container.
     *
     * Enables: $app->utilities = $value, $app->request = $value, etc.
     *
     * @param string $name Property name.
     * @param mixed  $value Property value.
     *
     * @since 1.0.0
     * @return void
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }
    /**
     * Magic method for method calls - delegates to container.
     *
     * Enables: $app->utilities(), $app->request(), etc.
     *
     * @param string $name Method name.
     * @param array  $arguments Method arguments.
     *
     * @since 1.0.0
     * @return mixed
     * @throws BadMethodCallException If method not found in container.
     */
    public function __call($name, $arguments)
    {
        if ($this->bound($name)) {
            $resolved = $this->make($name);
            return is_callable($resolved) ? $resolved(...$arguments) : $resolved;
        }
        throw new BadMethodCallException(esc_html("Method {$name} not found"));
    }
    /**
     * Get service from container.
     *
     * @param string $key Service key.
     * @param mixed  $fallback Fallback value.
     *
     * @since 1.0.0
     * @return mixed
     */
    public function get($key, $fallback = null)
    {
        return $this->has($key) ? $this->make($key) : $fallback;
    }
    /**
     * Set service in container.
     *
     * @param string $key Service key.
     * @param mixed  $value Service value.
     *
     * @since 1.0.0
     * @return void
     */
    public function set($key, $value): void
    {
        if (!$value instanceof Closure) {
            $value = function () use ($value) {
                return $value;
            };
        }
        $this->bind($key, $value);
    }
    /**
     * Check if service exists in container.
     *
     * @param string $key Service key.
     *
     * @since 1.0.0
     * @return bool
     */
    public function has($key): bool
    {
        return $this->bound($key);
    }
    /**
     * Register a binding with the container.
     *
     * @since 1.0.0
     *
     * @param Closure|string|array $id The abstract type or array with alias (e.g., array('alias' => AbstractClass::class)).
     * @param Closure|string|null  $concrete The concrete implementation.
     * @param bool                 $shared Whether the binding should be shared (default: true).
     *
     * @return static Static instance for method chaining.
     * @throws ContainerException If the concrete type is not a string or Closure.
     */
    public function bind($id, $concrete = null, $shared = true)
    {
        if ($id instanceof Closure) {
            $reflection = new \ReflectionFunction($id);
            if ($reflection->getReturnType() === null) {
                $abstracts = array();
            } else {
                $return_type = $reflection->getReturnType();
                if (PHP_VERSION_ID >= 80000) {
                    $types = $return_type instanceof ReflectionUnionType ? $return_type->getTypes() : array($return_type);
                    $filtered_types = array();
                    foreach ($types as $type) {
                        if (!$type->isBuiltin() && !in_array($type->getName(), array('static', 'self'), true)) {
                            $filtered_types[] = $type->getName();
                        }
                    }
                    $abstracts = array_values($filtered_types);
                } elseif ($return_type instanceof ReflectionNamedType && !$return_type->isBuiltin()) {
                    $abstracts = array($return_type->getName());
                } else {
                    $abstracts = array();
                }
            }
            $concrete = $id;
            foreach ($abstracts as $abstract) {
                $this->bind($abstract, $concrete, $shared);
            }
            return $this;
        }
        if (is_array($id)) {
            list($alias, $abstract) = array(key($id), current($id));
            if (is_string($alias) && is_string($abstract)) {
                $this->alias($abstract, $alias);
            }
            $id = $abstract;
        }
        unset($this->instances[$id], $this->aliases[$id]);
        if (is_null($concrete)) {
            $concrete = $id;
        }
        if (!$concrete instanceof Closure) {
            if (!is_string($concrete)) {
                throw new ContainerException('Concrete type must be a string or Closure.');
            }
            $concrete = function ($container, $parameters = array()) use ($id, $concrete) {
                if ($id === $concrete) {
                    return $container->build($concrete);
                }
                if (isset($container->instances[$concrete]) && empty($parameters)) {
                    return $container->instances[$concrete];
                }
                return $container->build($concrete);
            };
        }
        $this->bindings[$id] = array('concrete' => $concrete, 'shared' => $shared);
        return $this;
    }
    /**
     * Register a non-shared binding (fresh instance each time).
     *
     * @since 1.0.0
     *
     * @param string              $id       The abstract type.
     * @param Closure|string|null $concrete The concrete implementation.
     *
     * @return static
     */
    public function factory($id, $concrete = null)
    {
        return $this->bind($id, $concrete, false);
    }
    /**
     * Share an existing instance in the container.
     *
     * @since 1.0.0
     *
     * @param string|object      $id The abstract type or the instance itself.
     * @param object|string|null $instance The instance to register (optional).
     *
     * @return object The registered or resolved instance.
     */
    public function share($id, $instance = null): object
    {
        if (is_object($id) && null === $instance) {
            $instance = $id;
            $id = get_class($instance);
        }
        if (null === $instance) {
            $instance = $this->make($id);
        }
        unset($this->aliases[$id]);
        $this->instances[$id] = $instance;
        return $instance;
    }
    /**
     * Generate a callable that resolves a class and invokes the specified method.
     *
     * @param string|array|callable $callback The callback to convert to callable.
     *
     * @return Closure A closure that resolves the instance and calls the specified method.
     * @throws ContainerException If the callback format is invalid.
     */
    public function callback($callback): Closure
    {
        if (is_callable($callback)) {
            return Closure::fromCallable($callback);
        }
        if (is_string($callback) && str_contains($callback, '@')) {
            [$class, $method] = explode('@', $callback, 2);
        } elseif (is_array($callback) && count($callback) === 2) {
            [$class, $method] = array($callback[0], $callback[1]);
        } else {
            throw new ContainerException('Invalid callback format provided.');
        }
        $class_key = is_object($class) ? spl_object_hash($class) : $class;
        $key = $class_key . '::' . $method;
        if (isset($this->callbacks[$key])) {
            return $this->callbacks[$key];
        }
        $this->callbacks[$key] = function (...$args) use ($class, $method) {
            $instance = is_object($class) ? $class : $this->make($class);
            return $instance->{$method}(...$args);
        };
        return $this->callbacks[$key];
    }
    /**
     * Resolve the given type from the container.
     *
     * @since 1.0.0
     *
     * @param string $id The abstract type to resolve.
     * @param array  $parameters Optional parameters to pass to the constructor.
     *
     * @return mixed The resolved instance.
     *
     * @throws ContainerException If the type cannot be resolved.
     */
    public function make($id, $parameters = array())
    {
        return $this->resolve($id, $parameters);
    }
    /**
     * Register an alias for an abstract type in the container.
     *
     * @since 1.0.0
     *
     * @param string $id The abstract type to alias.
     * @param string $alias The alias to register.
     *
     * @return void
     * @throws ContainerException If the alias would create a circular reference.
     */
    public function alias($id, $alias): void
    {
        if ($alias === $id) {
            return;
        }
        $visited = array();
        $current = $id;
        $would_loop = false;
        while (isset($this->aliases[$current])) {
            if (in_array($current, $visited, true)) {
                $would_loop = true;
                break;
            }
            if ($this->aliases[$current] === $alias) {
                $would_loop = true;
                break;
            }
            $visited[] = $current;
            $current = $this->aliases[$current];
        }
        if ($would_loop) {
            throw new ContainerException(esc_html("Alias [{$alias}] would create a circular reference to [{$id}]."));
        }
        $this->aliases[$alias] = $id;
    }
    /**
     * Determine whether the given abstract type is bound in the container.
     *
     * @since 1.0.0
     *
     * @param string $id The service identifier.
     *
     * @return bool
     */
    public function bound($id): bool
    {
        return isset($this->bindings[$id]) || isset($this->instances[$id]) || isset($this->aliases[$id]);
    }
    /**
     * Resolve the given type from the container.
     *
     * @since 1.0.0
     *
     * @param string $id The abstract type to resolve.
     * @param array  $parameters Optional parameters to pass to the constructor.
     *
     * @return mixed The resolved instance.
     * @throws ContainerException If the type cannot be resolved.
     */
    private function resolve($id, array $parameters = array())
    {
        $id = $this->get_alias($id);
        if (in_array($id, $this->build_stack, true)) {
            throw new ContainerException(esc_html("Circular dependency detected when resolving [{$id}]."));
        }
        if (isset($this->instances[$id]) && empty($parameters)) {
            return $this->instances[$id];
        }
        $concrete = !isset($this->bindings[$id]) ? $id : $this->bindings[$id]['concrete'];
        $this->build_stack[] = $id;
        try {
            $object = $concrete === $id || $concrete instanceof Closure ? $this->build($concrete, $parameters) : $this->make($concrete);
        } finally {
            array_pop($this->build_stack);
        }
        if ($this->is_shared($id) && empty($parameters)) {
            if (!isset($this->instances[$id])) {
                $this->instances[$id] = $object;
            } else {
                $object = $this->instances[$id];
            }
        }
        return $object;
    }
    /**
     * Instantiate a concrete implementation of the given type.
     *
     * @since 1.0.0
     *
     * @param string|Closure $concrete The class name or closure to instantiate.
     * @param array          $parameters Optional parameters to pass to the constructor.
     *
     * @return mixed The instantiated object.
     *
     * @throws ContainerException If the class is not instantiable or dependencies cannot be resolved.
     */
    private function build($concrete, array $parameters = array())
    {
        if ($concrete instanceof Closure) {
            return $concrete($this, $parameters);
        }
        try {
            $reflector = new ReflectionClass($concrete);
        } catch (ReflectionException $e) {
            throw new ContainerException(esc_html("Target class [{$concrete}] does not exist."));
        }
        if (!$reflector->isInstantiable()) {
            $message = $reflector->isInterface() ? "Target [{$concrete}] is an interface and cannot be instantiated." : "Target [{$concrete}] is not instantiable.";
            throw new ContainerException(esc_html($message));
        }
        $constructor = $reflector->getConstructor();
        if (is_null($constructor)) {
            return new $concrete();
        }
        $dependencies = $constructor->getParameters();
        $instances = array();
        foreach ($dependencies as $dependency) {
            if (array_key_exists($dependency->getName(), $parameters)) {
                $instances[] = $parameters[$dependency->getName()];
                continue;
            }
            $class_name = $this->get_parameter_class_name($dependency);
            if (is_null($class_name)) {
                if ($dependency->isDefaultValueAvailable()) {
                    $instances[] = $dependency->getDefaultValue();
                } else {
                    $this->unresolvable_primitive($dependency);
                }
            } else {
                $instances[] = $this->resolve_class($dependency);
            }
        }
        return $reflector->newInstanceArgs($instances);
    }
    /**
     * Get the alias for an abstract if available.
     *
     * @since 1.0.0
     *
     * @param string $id The service identifier.
     *
     * @return string
     * @throws ContainerException If a circular alias reference is detected.
     */
    private function get_alias($id)
    {
        $visited = array();
        $current = $id;
        while (isset($this->aliases[$current])) {
            if (in_array($current, $visited, true)) {
                throw new ContainerException(esc_html("Circular alias reference detected for [{$id}]."));
            }
            $visited[] = $current;
            $current = $this->aliases[$current];
        }
        return $current;
    }
    /**
     * Get the class name for the given parameter.
     *
     * @since 1.0.0
     *
     * @param ReflectionParameter $parameter The reflection parameter instance.
     * @return string|null
     */
    private function get_parameter_class_name($parameter)
    {
        $type = $parameter->getType();
        if (!$type) {
            return null;
        }
        if (PHP_VERSION_ID >= 80000) {
            if ($type instanceof ReflectionUnionType) {
                foreach ($type->getTypes() as $union_type) {
                    if ($union_type instanceof ReflectionNamedType && !$union_type->isBuiltin()) {
                        return $union_type->getName();
                    }
                }
                return null;
            }
            if (PHP_VERSION_ID >= 80100 && $type instanceof \ReflectionIntersectionType) {
                $types = $type->getTypes();
                if (count($types) > 0 && $types[0] instanceof ReflectionNamedType && !$types[0]->isBuiltin()) {
                    return $types[0]->getName();
                }
                return null;
            }
            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                return $type->getName();
            }
        } elseif ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
            return $type->getName();
        }
        return null;
    }
    /**
     * Determine if a given type is shared.
     *
     * @since 1.0.0
     *
     * @param string $id The service identifier.
     *
     * @return bool
     */
    private function is_shared($id)
    {
        return isset($this->instances[$id]) || !isset($this->bindings[$id]) || isset($this->bindings[$id]['shared']) && true === $this->bindings[$id]['shared'];
    }
    /**
     * Throw an exception for an unresolvable primitive.
     *
     * @since 1.0.0
     *
     * @param ReflectionParameter $parameter The reflection parameter instance.
     *
     * @return void
     * @throws ContainerException If the primitive cannot be resolved.
     */
    private function unresolvable_primitive($parameter)
    {
        $message = "Unresolvable dependency resolving [{$parameter}] in class {$parameter->getDeclaringClass()->getName()}";
        throw new ContainerException(esc_html($message));
    }
    /**
     * Resolve a class based dependency from the container.
     *
     * @since 1.0.0
     *
     * @param ReflectionParameter $parameter The reflection parameter instance.
     *
     * @return mixed
     * @throws ContainerException  If the class cannot be resolved.
     */
    private function resolve_class($parameter)
    {
        try {
            return $this->make($this->get_parameter_class_name($parameter));
        } catch (ContainerException $e) {
            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }
            if ($parameter->hasType() && $parameter->allowsNull()) {
                return null;
            }
            throw $e;
        }
    }
    /**
     * Get the value at a given offset.
     *
     * @param string $offset The key to get.
     *
     * @since 1.0.0
     * @return mixed
     * @throws ContainerException If the offset is not a string or integer.
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        if (!is_string($offset) && !is_int($offset)) {
            throw new ContainerException('Container array key must be string or integer.');
        }
        return $this->make((string) $offset);
    }
    /**
     * Set the value at a given offset.
     *
     * @param string $offset The key to set.
     * @param mixed  $value The value to set. If it's not a Closure, it will be wrapped in one.
     *
     * @since 1.0.0
     * @return void
     * @throws ContainerException If the offset is not a string or integer.
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value): void
    {
        if (!is_string($offset) && !is_int($offset)) {
            throw new ContainerException('Container array key must be string or integer.');
        }
        $wrapped_value = $value instanceof Closure ? $value : function () use ($value) {
            return $value;
        };
        $this->bind((string) $offset, $wrapped_value);
    }
    /**
     * Remove the value at a given offset.
     *
     * @param string $offset The key to remove.
     *
     * @since 1.0.0
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset): void
    {
        if (!is_string($offset) && !is_int($offset)) {
            return;
        }
        $offset = (string) $offset;
        unset($this->bindings[$offset], $this->instances[$offset], $this->aliases[$offset]);
    }
    /**
     * Whether an offset exists.
     *
     * @param mixed $offset The key to check.
     *
     * @since 1.0.0
     * @return bool True if the offset exists, false otherwise.
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset): bool
    {
        if (!is_string($offset) && !is_int($offset)) {
            return false;
        }
        return $this->bound((string) $offset);
    }
}