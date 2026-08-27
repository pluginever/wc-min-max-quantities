<?php
/**
 * Plugin Name:          Min Max Quantities
 * Plugin URI:           https://pluginever.com/woocommerce-min-max-quantities-pro/
 * Description:          The plugin allows you to Set minimum and maximum allowable product quantities and price per product and order.
 * Version:              2.4.0
 * Requires at least:    5.2
 * Tested up to:         7.1
 * Requires PHP:         7.4
 * Author:               PluginEver
 * Author URI:           https://pluginever.com/
 * License:              GPL v2 or later
 * License URI:          https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:          wc-min-max-quantities
 * Domain Path:          /languages
 * WC requires at least: 3.0.0
 * WC tested up to:      11.0
 * Requires Plugins:     woocommerce
 *
 * @link                 https://pluginever.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 * @author              Sultan Nasir Uddin <manikdrmc@gmail.com>
 * @copyright           2026 ByteEver
 * @license             GPL-2.0+
 * @package             PluginEver\MinMaxQuantities
 */

use PluginEver\MinMaxQuantities\Installer;
use PluginEver\MinMaxQuantities\Plugin;

defined( 'ABSPATH' ) || exit;

// Autoloader.
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/includes/functions.php';

$data = array(
	'version'       => '2.4.0',
	'name'          => 'Min Max Quantities',
	'option_prefix' => 'wcmmq',
	'hook_prefix'   => 'wc_min_max_quantities',
	'settings_url'  => admin_url( 'admin.php?page=wc-min-max-quantities' ),
	'support_url'   => 'https://pluginever.com/support/',
	'docs_url'      => 'https://pluginever.com/docs/min-max-quantities-for-woocommerce/',
	'upgrade_url'   => 'https://pluginever.com/plugins/woocommerce-min-max-quantities-pro/',
	'pro_basename'  => 'wc-min-max-quantities-pro/wc-min-max-quantities-pro.php',
	'review_url'    => 'https://wordpress.org/support/plugin/wc-min-max-quantities/reviews/#new-post',
);

Plugin::create( __FILE__, $data );

// Register the plugin activation and deactivation hooks.
wc_min_max_quantities()->on_activation( array( Installer::class, 'install' ) );
wc_min_max_quantities()->on_deactivation( array( Installer::class, 'deactivate' ) );

// Declare WooCommerce feature compatibility.
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
		}
	}
);

// Boot the plugin.
wc_min_max_quantities()->bootstrap();
