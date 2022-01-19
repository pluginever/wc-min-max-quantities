<?php

namespace PHPSTORM_META {

	override( \WC_Min_Max_Quantities\Plugin::get(), map( [
		'lifecycle'          => \WC_Min_Max_Quantities\Lifecycle::class,
		'background_updater' => \Starter_Plugin\Utilities\Background_Updater::class,
		'settings'           => \WC_Min_Max_Quantities\Settings::class,
		'cart_manager'       => \WC_Min_Max_Quantities\Cart_Manager::class,
	] ) );
}
