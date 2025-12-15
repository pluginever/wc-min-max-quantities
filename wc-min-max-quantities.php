<?php
/**
 * Plugin Name:          Min Max Quantities
 * Plugin URI:           https://pluginever.com/woocommerce-min-max-quantities-pro/
 * Description:          The plugin allows you to Set minimum and maximum allowable product quantities and price per product and order.
 * Version:              2.2.2
 * Requires at least:    5.2
 * Tested up to:         6.9
 * Requires PHP:         7.4
 * Author:               PluginEver
 * Author URI:           https://pluginever.com/
 * License:              GPL v2 or later
 * License URI:          https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:          wc-min-max-quantities
 * Domain Path:          /languages
 * WC requires at least: 3.0.0
 * WC tested up to:      10.4
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
 * @copyright           2025 ByteEver
 * @license             GPL-2.0+
 * @package             WooCommerceMinMaxQuantities
 */

defined( 'ABSPATH' ) || exit;

// Autoloader.
require_once __DIR__ . '/vendor/autoload.php';

// Instantiate the plugin.
WooCommerceMinMaxQuantities\Plugin::create(
	array(
		'file'             => __FILE__,
		'settings_url'     => admin_url( 'admin.php?page=wc-min-max-quantities' ),
		'support_url'      => 'https://pluginever.com/support/',
		'docs_url'         => 'https://pluginever.com/docs/min-max-quantities-for-woocommerce/',
		'premium_url'      => 'https://pluginever.com/plugins/woocommerce-min-max-quantities-pro/',
		'premium_basename' => 'wc-min-max-quantities-pro',
		'review_url'       => 'https://wordpress.org/support/plugin/wc-min-max-quantities/reviews/#new-post',
	)
);
