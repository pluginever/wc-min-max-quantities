<?php
/**
 * Plugin Name: WooCommerce Min Max Quantities
 * Plugin URI:  https://pluginever.com/plugins/woocommerce-min-max-quantities-pro/
 * Docs URI:    https://pluginever.com/plugins/woocommerce-min-max-quantities-pro/
 * Support URI: https://pluginever.com/plugins/woocommerce-min-max-quantities-pro/
 * Reviews URI: https://wordpress.org/support/plugin/wc-min-max-quantities/reviews/#new-post
 * Description: The plugin allows you to Set minimum and maximum allowable product quantities and price per product and order.
 * Version:     1.0.9
 * Author:      pluginever
 * Author URI:  https://www.pluginever.com
 * Donate link: https://www.pluginever.com
 * License:     GPLv2+
 * Text Domain: wc-min-max-quantities
 * Domain Path: /i18n/languages/
 * Tested up to: 5.4
 * WC requires at least: 3.0.0
 * WC tested up to: 4.0.1
 *
 * @package PluginEver\WC_Min_Max_Quantities;
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

if ( ! defined( 'WC_MIN_MAX_PLUGIN_FILE' ) ) {
	define( 'WC_MIN_MAX_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'WC_MIN_MAX_PLUGIN_DIR' ) ) {
	define( 'WC_MIN_MAX_PLUGIN_DIR', __DIR__ );
}

// Autoloader.
require_once __DIR__ . '/vendor/autoload.php';

/**
 * Main Plugin Instance.
 *
 * @return \PluginEver\WC_Min_Max_Quantities\Plugin
 */
function wc_min_max_quantities(){
	return \PluginEver\WC_Min_Max_Quantities\Plugin::instance();
}

$GLOBALS['wc_min_max_quantities'] = wc_min_max_quantities();
