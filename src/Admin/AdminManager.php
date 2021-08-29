<?php
/**
 * PluginScaffold Admin
 *
 * @class    AdminManager
 * @package  ByteEver\PluginScaffold\Admin
 * @version  1.0.0
 */

namespace PluginEver\MinMaxQuantities\Admin;

use ByteEver\Container\ServiceProvider;
use ByteEver\Plugin\Admin\PluginLinks;

/**
 * Class AdminManager
 *
 * @package ByteEver\PluginScaffold
 */
class AdminManager extends ServiceProvider {
	/**
	 * Register hooks & services.
	 */
	public function register() {
		$this->container->register( PluginLinks::class, wc_minmax_quantities() );
		$this->container->register( PluginSettings::class, wc_minmax_quantities()->options );
		$this->container->register( MetaBoxes::class );
	}
}
