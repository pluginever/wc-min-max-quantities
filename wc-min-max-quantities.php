<?php
/**
 * Plugin Name:          WC Min Max Quantities
 * Plugin URI:           https://pluginever.com/woocommerce-min-max-quantities-pro/
 * Description:          The plugin allows you to Set minimum and maximum allowable product quantities and price per product and order.
 * Version:              2.1.7
 * Requires at least:    5.2
 * Requires PHP:         7.4
 * Author:               PluginEver
 * Author URI:           https://pluginever.com/
 * Text Domain:          wc-min-max-quantities
 * Domain Path:          /languages
 * License:              GPL v2 or later
 * License URI:          https://www.gnu.org/licenses/gpl-2.0.html
 * Tested up to:         6.8
 * WC requires at least: 3.0.0
 * WC tested up to:      10.0
 * Requires Plugins:     woocommerce
 *
 * @package WooCommerceMinMaxQuantities
 * @author  PluginEver
 * @link    https://pluginever.com/plugins/woocommerce-min-max-quantities-pro/
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

// Don't call the file directly.
defined( 'ABSPATH' ) || exit;

// Require the autoloader.
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/libraries/autoload.php';

// Instantiate the plugin.
WooCommerceMinMaxQuantities\Plugin::create(
	array(
		'file'             => __FILE__,
		'settings_url'     => admin_url( 'admin.php?page=wc-min-max-quantities' ),
		'support_url'      => 'https://pluginever.com/support/',
		'docs_url'         => 'https://pluginever.com/docs/min-max-quantities-for-woocommerce/',
		'premium_url'      => 'https://pluginever.com/plugins/woocommerce-min-max-quantities-pro/',
		'premium_basename' => 'wc-min-max-quantities-pro',
		'review_url'       => 'https://wordpress.org/support/plugin/wc-min-max-quantities/reviews/?filter=5#new-post',
	)
);
