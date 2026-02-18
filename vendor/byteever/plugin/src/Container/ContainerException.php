<?php

namespace WooCommerceMinMaxQuantities\B8\Plugin\Container;

use Exception;
defined('ABSPATH') || exit;
/**
 * Exception thrown by the container when it cannot resolve a dependency.
 *
 * @since 1.0.0
 * @package \B8\Plugin
 */
class ContainerException extends Exception
{
}