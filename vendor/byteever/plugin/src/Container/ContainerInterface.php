<?php

namespace WooCommerceMinMaxQuantities\B8\Plugin\Container;

use Closure;
defined('ABSPATH') || exit;
/**
 * Container interface for dependency injection.
 *
 * @since 1.0.0
 * @package \B8\Plugin
 */
interface ContainerInterface
{
    /**
     * Get service from container.
     *
     * @param string $key Service key.
     * @param mixed  $fallback Fallback value.
     *
     * @since 1.0.0
     * @return mixed
     */
    public function get($key, $fallback = null);
    /**
     * Set service in container.
     *
     * @param string $key Service key.
     * @param mixed  $value Service value.
     *
     * @since 1.0.0
     * @return void
     */
    public function set($key, $value): void;
    /**
     * Check if service exists in container.
     *
     * @param string $key Service key.
     *
     * @since 1.0.0
     * @return bool
     */
    public function has($key): bool;
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
    public function bind($id, $concrete = null, $shared = true);
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
    public function share($id, $instance = null): object;
    /**
     * Generate a callable that resolves a class and invokes the specified method.
     *
     * @since 1.0.0
     *
     * @param string|array|callable $callback The callback to convert to callable.
     *
     * @return Closure A closure that resolves the instance and calls the specified method.
     * @throws ContainerException If the class cannot be resolved or the method does not exist.
     */
    public function callback($callback): Closure;
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
    public function make($id, $parameters = array());
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
    public function alias($id, $alias): void;
    /**
     * Determine whether the given abstract type is bound in the container.
     *
     * @since 1.0.0
     *
     * @param string $id The service identifier.
     *
     * @return bool
     */
    public function bound($id): bool;
}