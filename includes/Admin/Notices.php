<?php

namespace WooCommerceMinMaxQuantities\Admin;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Notices class.
 *
 * @since 1.0.0
 */
class Notices {

	/**
	 * Notices constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'admin_notices' ) );
	}

	/**
	 * Admin notices.
	 *
	 * @since 1.0.0
	 */
	public function admin_notices() {
		$installed_time = get_option( 'wc_min_max_quantities_installed' );
		$current_time   = wp_date( 'U' );

		if ( ! defined( 'WCMMQ_PRO_VERSION' ) ) {
			wc_min_max_quantities()->notices->add(
				array(
					'message'     => __DIR__ . '/views/notices/upgrade.php',
					'notice_id'   => 'wcmmq_upgrade',
					'style'       => 'border-left-color: #0542fa;',
					'dismissible' => false,
				)
			);
		}

		// Show after 5 days.
		if ( $installed_time && $current_time > ( $installed_time + ( 5 * DAY_IN_SECONDS ) ) ) {
			wc_min_max_quantities()->notices->add(
				array(
					'message'     => __DIR__ . '/views/notices/review.php',
					'dismissible' => false,
					'notice_id'   => 'wcmmq_review',
					'style'       => 'border-left-color: #0542fa;',
				)
			);
		}
	}
}
