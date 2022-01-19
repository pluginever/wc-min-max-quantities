<?php

namespace PHPSTORM_META {

	override( \WC_Min_Max_Quantities\Plugin::get(), map( [
		'lifecycle'          => \WC_Min_Max_Quantities\Lifecycle::class,
		'background_updater' => \WC_Min_Max_Quantities\Utilities\Background_Updater::class,
		'settings'           => \WC_Min_Max_Quantities\Settings::class,
		'admin_notices'      => \WC_Min_Max_Quantities\Admin\Admin_Notices::class,
		'cart_manager'       => \WC_Min_Max_Quantities\Cart_Manager::class,
	] ) );
}
