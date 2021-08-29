<?php
/**
 * Plugin Name: WooCommerce Min Max Quantities
 * Plugin URI:  https://pluginever.com/plugins/woocommerce-min-max-quantities-pro/
 * Description: The plugin allows you to Set minimum and maximum allowable product quantities and price per product and order.
 * Version:     1.0.9
 * Author:      pluginever
 * Author URI:  https://www.pluginever.com
 * Donate link: https://www.pluginever.com
 * License:     GPLv2+
 * Text Domain: wc-minmax-quantities
 * Domain Path: /i18n/languages/
 * Tested up to: 5.4
 * WC requires at least: 3.0.0
 * WC tested up to: 4.0.1
 */

/**
 * Copyright (c) 2019 pluginever (email : support@pluginever.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2 or, at
 * your discretion, any later version, as published by the Free
 * Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */


// don't call the file directly
defined( 'ABSPATH' ) || exit();

// define plugin constants.
if ( ! defined( 'WC_MINMAX_VERSION' ) ) {
	define( 'WC_MINMAX_VERSION', '1.0.9' );
}

if ( ! defined( 'WC_MINMAX_FILE' ) ) {
	define( 'WC_MINMAX_FILE', __FILE__ );
}

// Include autoloader.
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

/**
 * Returns the main instance of the plugin.
 * @return \PluginEver\MinMaxQuantities\Plugin
 * @since 1.0.0
 */
function wc_minmax_quantities() {
	return \PluginEver\MinMaxQuantities\Plugin::get_instance();
}

// Kit start.
wc_minmax_quantities();
