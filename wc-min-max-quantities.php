<?php
/**
 * Plugin Name:          WC Min Max Quantities
 * Plugin URI:           https://pluginever.com/plugins/woocommerce-min-max-quantities-pro/
 * Description:          The plugin allows you to Set minimum and maximum allowable product quantities and price per product and order.
 * Version:              1.2.4
 * Author:               PluginEver
 * Author URI:           https://pluginever.com/
 * Text Domain:          wc-min-max-quantities
 * Domain Path:          /languages
 * License:              GPL v2 or later
 * License URI:          https://www.gnu.org/licenses/gpl-2.0.html
 * Requires Plugins:     woocommerce
 * Tested up to:         6.6
 * Requires at least:    5.0
 * Requires PHP:         7.4
 * WC requires at least: 3.0.0
 * WC tested up to:      9.2
 *
 * @package     WooCommerceMinMaxQuantities
 * @author      pluginever
 * @link        https://pluginever.com/plugins/wc-min-max-quantities/
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

use WooCommerceMinMaxQuantities\Plugin;

defined( 'ABSPATH' ) || exit;

// Autoload function.
spl_autoload_register(
	function ( $class_name ) {
		$prefix = 'WooCommerceMinMaxQuantities\\';
		$len    = strlen( $prefix );
		// Bail out if the class name doesn't start with our prefix.
		if ( strncmp( $prefix, $class_name, $len ) !== 0 ) {
			return;
		}

		// Remove the prefix from the class name.
		$relative_class = substr( $class_name, $len );
		// Replace the namespace separator with the directory separator.
		$file = str_replace( '\\', DIRECTORY_SEPARATOR, $relative_class ) . '.php';

		// Look for the file in the src and lib directories.
		$file_paths = array(
			__DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $file,
			__DIR__ . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . $file,
		);

		foreach ( $file_paths as $file_path ) {
			if ( file_exists( $file_path ) ) {
				require_once $file_path;
				break;
			}
		}
	}
);

/**
 * Returns the main instance of plugin.
 *
 * @since  1.1.0
 * @return Plugin
 */
function wc_min_max_quantities() {
	$data = array(
		'file'             => __FILE__,
		'settings_url'     => admin_url( 'admin.php?page=wc-min-max-quantities' ),
		'support_url'      => 'https://pluginever.com/support/',
		'docs_url'         => 'https://pluginever.com/docs/min-max-quantities-for-woocommerce/',
		'premium_url'      => 'https://pluginever.com/plugins/woocommerce-min-max-quantities-pro/',
		'premium_basename' => 'wc-min-max-quantities-pro',
		'review_url'       => 'https://wordpress.org/support/plugin/wc-min-max-quantities/reviews/?filter=5#new-post',
	);

	return Plugin::create( $data );
}

// Initialize the plugin.
wc_min_max_quantities();
