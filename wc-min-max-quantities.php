<?php
/**
 * Plugin Name:  WC Min Max Quantities
 * Description:  The plugin allows you to Set minimum and maximum allowable product quantities and price per product and order.
 * Version:      1.1.0
 * Plugin URI:   https://pluginever.com/plugins/wc-min-max-quantities/
 * Author:       pluginever
 * Author URI:   https://pluginever.com/
 * Text Domain:  wc-min-max-quantities
 * Domain Path: /i18n/languages/
 * Requires PHP: 5.6
 * WC requires at least: 3.0.0
 * WC tested up to: 6.1.0
 *
 * @package     WC_Min_Max_Quantities
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

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'WC_MIN_MAX_QUANTITIES_PLUGIN_FILE' ) ) {
	define( 'WC_MIN_MAX_QUANTITIES_PLUGIN_FILE', __FILE__ );
}

/**
 * Missing WooCommerce notice.
 *
 * @since 1.1.0
 * @return void
 */
function wc_min_max_quantities_missing_wc_notice() {
	/* translators: %s Plugin Name, %s Missing Plugin Name, %s Download URL link. */
	$notice = '<div class="notice notice-error">';
	$notice .= '<p>';
	$notice .= sprintf(
		__( '%1$s requires %2$s to be installed and active. You can download %3$s here.', 'wc-min-max-quantities' ),
		'<strong>WC Min Max Quantities</strong>',
		'<strong>WooCommerce</strong>',
		'<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">WooCommerce</a>'
	);
	$notice .= '</p>';
	$notice .= '</div>';
	echo wp_kses_post( $notice );
}

/**
 * Returns the main instance of plugin.
 *
 * @since  1.1.0
 * @return WC_Min_Max_Quantities\Plugin
 */
function wc_min_max_quantities() {
	require_once __DIR__ . '/includes/class-plugin.php';
	return WC_Min_Max_Quantities\Plugin::instance();
}

/**
 * Initialize the plugin.
 *
 * @since 1.1.0
 * @return void
 */
function wc_min_max_quantities_init() {
	if ( ! class_exists( '\WooCommerce' ) ) {
		add_action( 'admin_notices', 'wc_min_max_quantities_missing_wc_notice' );

		return;
	}

	// Kick off the plugin.
	$GLOBALS['wc_min_max_quantities'] = wc_min_max_quantities();
}

// Kick off the plugin.
add_action( 'plugins_loaded', 'wc_min_max_quantities_init', -1 );
