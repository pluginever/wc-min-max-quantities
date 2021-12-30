<?php
/**
 * WC_Min_Max_Quantities admin related functionalities.
 *
 * @since    1.1.0
 * @package  WC_Min_Max_Quantities\Admin
 */

namespace WC_Min_Max_Quantities\Admin;

defined( 'ABSPATH' ) || exit();

/**
 * Admin_Manager class.
 */
class Admin_Manager {

	/**
	 * class construct
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Initialize services.
	 *
	 * @since 1.1.0
	 */
	public function init() {
		new Admin_Settings();
	}
}
