<?php
/**
 * WC_Min_Max_Quantities admin related functionalities.
 *
 * @since    1.1.0
 * @package  WC_Min_Max_Quantities\Admin
 */

namespace WC_Min_Max_Quantities\Admin;

use WC_Min_Max_Quantities\Plugin;

defined( 'ABSPATH' ) || exit();

/**
 * Admin_Manager class.
 */
class Admin_Manager {

	/**
	 * Admin_Manager construct.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function __construct() {
		$this->init_classes();
	}

	/**
	 * Initialize services.
	 *
	 * @since 1.1.0
	 */
	public function init_classes() {
		Plugin::instance()->admin_settings = new Admin_Settings();
	}
}
