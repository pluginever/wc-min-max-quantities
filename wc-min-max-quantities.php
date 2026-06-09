<?php
/**
 * Plugin Name:          Min Max Quantities
 * Plugin URI:           https://pluginever.com/woocommerce-min-max-quantities-pro/
 * Description:          The plugin allows you to Set minimum and maximum allowable product quantities and price per product and order.
 * Version:              2.4.0
 * Requires at least:    5.2
 * Tested up to:         7.0
 * Requires PHP:         7.4
 * Author:               PluginEver
 * Author URI:           https://pluginever.com/
 * License:              GPL v2 or later
 * License URI:          https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:          wc-min-max-quantities
 * Domain Path:          /languages
 * WC requires at least: 3.0.0
 * WC tested up to:      10.7
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

// Load the Composer autoloader.
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/includes/functions.php';

$data = array(
	'version'      => '2.4.0',
	'short_name'   => 'wcmmq',
	'hook_prefix'  => 'wc_min_max_quantities',
	'settings_url' => admin_url( 'admin.php?page=wc-min-max-quantities' ),
	'pro_basename' => 'wc-min-max-quantities-pro/wc-min-max-quantities-pro.php',
	'upgrade_url'  => 'https://pluginever.com/plugins/woocommerce-min-max-quantities-pro/',
	'docs_url'     => 'https://pluginever.com/docs/min-max-quantities-for-woocommerce/',
	'support_url'  => 'https://pluginever.com/support/',
	'review_url'   => 'https://wordpress.org/support/plugin/wc-min-max-quantities/reviews/#new-post',
);

Plugin::create( __FILE__, $data );

/**
 * Get the main plugin instance.
 *
 * @since 1.0.0
 * @return Plugin Plugin instance.
 */
function wc_min_max_quantities(): Plugin {
	return Plugin::instance();
}

// Backwards-compatible aliases for the pre-b8 namespace. Deprecated; removed after a few releases.
class_alias( Plugin::class, 'WooCommerceMinMaxQuantities\\Plugin' );
class_alias( \PluginEver\MinMaxQuantities\Cart::class, 'WooCommerceMinMaxQuantities\\Cart' );
class_alias( \PluginEver\MinMaxQuantities\Admin\Settings::class, 'WooCommerceMinMaxQuantities\\Admin\\Settings' );

// Register the plugin activation hook.
wc_min_max_quantities()->on_activation( array( Installer::class, 'install' ) );

// Boot the plugin.
wc_min_max_quantities()->bootstrap();
