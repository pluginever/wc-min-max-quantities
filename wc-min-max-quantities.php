<?php
/**
 * Plugin Name:  WC Min Max Quantities
 * Plugin URI:   https://pluginever.com/plugins/wc-min-max-quantities/
 * Description:  The plugin allows you to Set minimum and maximum allowable product quantities and price per product and order.
 * Version:      1.1.1
 * Author:       pluginever
 * Author URI:   https://pluginever.com/
 * Donate link: https://pluginever.com/contact
 * License:     GPLv2+
 * Text Domain:  wc-min-max-quantities
 * Domain Path: /languages/
 * Requires PHP: 5.6
 * Tested up to: 5.9.3
 * WC requires at least: 3.0.0
 * WC tested up to: 6.5.1
 *
 * @package     PluginEver\WooCommerceMinMaxQuantities
 * @author      pluginever
 * @link        https://pluginever.com/plugins/wc-min-max-quantities/
 * Settings Path: admin.php?page=wc-min-max-quantities-settings
 *
 * Copyright (c) 2019 pluginever (email : support@pluginever.com)
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

use PluginEver\WooCommerceMinMaxQuantities\Plugin;

defined( 'ABSPATH' ) || exit;

// Load framework.
require_once __DIR__ . '/lib/bootstrap.php';

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
	require_once __DIR__ . '/includes/class-plugin.php';

	return Plugin::init( __FILE__ );
}

wc_min_max_quantities()->setup();
