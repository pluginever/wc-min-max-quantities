<?php
/**
 * WC_Min_Max_Quantities AJAX Event Handlers.
 *
 * @since    1.1.0
 * @package  WC_Min_Max_Quantities
 */

namespace WC_Min_Max_Quantities;

defined( 'ABSPATH' ) || exit();

/**
 * Class Ajax.
 */
class Ajax {

	/**
	 * Ajax constructor.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function __construct() {
		$nopriv_events = array();
		$ajax_events   = array();

		foreach ( $nopriv_events as $nopriv_event ) {
			add_action( 'wp_ajax_wc_min_max_quantities_' . $nopriv_event, array( __CLASS__, $nopriv_event ) );
			add_action( 'wp_ajax_nopriv_wc_min_max_quantities_' . $nopriv_event, array( __CLASS__, $nopriv_event ) );
		}

		foreach ( $ajax_events as $ajax_event ) {
			add_action( 'wp_ajax_wc_min_max_quantities_' . $ajax_event, array( __CLASS__, $ajax_event ) );
		}
	}

	/**
	 * Check permission
	 *
	 * @param string $cap Capability to check.
	 *
	 * @since 1.1.0
	 */
	public static function check_permission( $cap = 'manage_options' ) {
		if ( ! current_user_can( $cap ) ) {
			wp_send_json_error( array( 'message' => __( 'Error: You are not allowed to do this.', 'wc-min-max-quantities' ) ) );
		}
	}

	/**
	 * Verify our ajax nonce.
	 *
	 * @param string $action Action to verify.
	 *
	 * @since 1.1.0
	 */
	public static function verify_nonce( $action ) {
		$nonce = '';
		if ( isset( $_REQUEST['_ajax_nonce'] ) ) {
			$nonce = $_REQUEST['_ajax_nonce'];
		} elseif ( isset( $_REQUEST['_wpnonce'] ) ) {
			$nonce = $_REQUEST['_wpnonce'];
		} elseif ( isset( $_REQUEST['nonce'] ) ) {
			$nonce = $_REQUEST['nonce'];
		}
		if ( false === wp_verify_nonce( $nonce, $action ) ) {
			wp_send_json_error( array( 'message' => __( 'Error: Cheatin&#8217; huh?.', 'wc-min-max-quantities' ) ) );
			wp_die();
		}

	}
}
