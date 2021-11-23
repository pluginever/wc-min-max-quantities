<?php

namespace PluginEver\WC_Min_Max_Quantities\Admin;

use ByteEver\PluginFramework\v1_0_0 as Framework;

class Admin {
	use Framework\Traits\Plugin;

	/**
	 * Initialize admin services.
	 * @since 1.0.0
	 * @return void
	 */
	public function init_services() {
		$this->get_plugin()->init_service( Settings::class, $this->get_plugin() );
		$this->get_plugin()->init_service( Metaboxes::class );
	}
}
