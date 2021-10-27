<?php

namespace PluginEver\WC_Min_Max_Quantities;

use \ByteEver\PluginFramework\v1_0_0 as Framework;

class Options extends Framework\Options {

	/**
	 * Options constructor.
	 */
	public function __construct() {
		parent::__construct('wc_min_max_quantities_settings');
	}
}
