<?php
/**
 * Plugin Name:  WooCommerce Min Max Quantities
 * Description:  The plugin allows you to Set minimum and maximum allowable product quantities and price per product and order.
 * Version:      1.1.3
 * Plugin URI:   https://pluginever.com/plugins/wc-min-max-quantities/
 * Author:       pluginever
 * Author URI:   https://pluginever.com/
 * Text Domain:  wc-min-max-quantities
 * Domain Path: /languages/
 * Requires PHP: 5.6
 * WC requires at least: 3.0.0
 * WC tested up to: 7.0
 *
 * @package WooCommerceMinMaxQuantities
 * @author  pluginever
 * Support URI:     http://pluginever.com/support
 * Document URI:    https://pluginever.com/docs/min-max-quantities-for-woocommerce/
 * Review URI:      https://wordpress.org/support/plugin/wc-min-max-quantities/reviews/?filter=5
 * Settings Path:   admin.php?page=wc-min-max-quantities-settings
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

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

// Load files.
require_once __DIR__ . '/includes/class-autoloader.php';

/**
 * Missing WooCommerce notice.
 *
 * @since 1.0.0
 * @return void
 */
function wc_min_max_quantities_missing_wc_notice() {
	$notice = sprintf(
	/* translators: tags */
		__( '%1$sWooCommerce Min Max Quantities%2$s is inactive. %3$sWooCommerce%4$s plugin must be active for the plugin to work. Please activate WooCommerce on the %5$splugin page%6$s once it is installed.', 'wc-min-max-quantities' ),
		'<strong>',
		'</strong>',
		'<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">',
		'</a>',
		'<a href="' . esc_url( admin_url( 'plugins.php' ) ) . '">',
		'</a>'
	);

	echo '<div class="notice notice-error"><p>' . wp_kses_post( $notice ) . '</p></div>';
}

// Check if WooCommerce is active.
if ( ! Plugin::is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	add_action( 'admin_notices', 'wc_min_max_quantities_missing_wc_notice' );

	return;
}

/**
 * Main instance of the plugin.
 *
 * Returns the main instance of the plugin to prevent the need to use globals.
 *
 * @since 1.0.0
 *
 * @return Plugin
 */
function wc_min_max_quantities() {
	return Plugin::create( __FILE__ );
}

// Initialize the plugin.
wc_min_max_quantities();
