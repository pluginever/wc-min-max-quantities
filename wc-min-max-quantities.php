<?php
/**
 * Plugin Name:  WC Min Max Quantities
 * Description:  The plugin allows you to Set minimum and maximum allowable product quantities and price per product and order.
 * Version:      1.1.4
 * Plugin URI:   https://pluginever.com/plugins/woocommerce-min-max-quantities-pro/
 * Author:       PluginEver
 * Author URI:   https://pluginever.com/
 * Text Domain:  wc-min-max-quantities
 * Domain Path: /i18n/languages/
 * Requires PHP: 5.6
 * WC requires at least: 3.0.0
 * WC tested up to: 7.1
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

use \WooCommerceMinMaxQuantities\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Auto load function.
 *
 * @param string $class_name Class name.
 *
 * @since 1.1.4
 * @return void
 */
function wc_min_max_quantities_autoload( $class_name ) {
	// WC_Min_Max_Quantities or WooCommerceMinMaxQuantities.
	if ( strpos( $class_name, 'WC_Min_Max_Quantities\\' ) !== 0 && strpos( $class_name, 'WooCommerceMinMaxQuantities\\' ) !== 0 ) {
		return;
	}

	// If the class name starts with WC_Min_Max_Quantities, remove it.
	if ( strpos( $class_name, 'WC_Min_Max_Quantities\\' ) === 0 ) {
		$class_name = substr( $class_name, strlen( 'WC_Min_Max_Quantities\\' ) );
	}
	// If the class name starts with WooCommerceMinMaxQuantities, remove it.
	if ( strpos( $class_name, 'WooCommerceMinMaxQuantities\\' ) === 0 ) {
		$class_name = substr( $class_name, strlen( 'WooCommerceMinMaxQuantities\\' ) );
	}

	// Replace the namespace separator with the directory separator.
	$class_name = str_replace( '\\', DIRECTORY_SEPARATOR, $class_name );
	// Add the .php extension.
	$class_name = $class_name . '.php';

	$file_paths = array(
		__DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $class_name,
		__DIR__ . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . $class_name,
	);

	foreach ( $file_paths as $file_path ) {
		if ( file_exists( $file_path ) ) {
			require_once $file_path;
			break;
		}
	}
}

spl_autoload_register( 'wc_min_max_quantities_autoload' );

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
