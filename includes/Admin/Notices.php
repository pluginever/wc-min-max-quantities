<?php

namespace WooCommerceMinMaxQuantities\Admin;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Notices class.
 *
 * @since 2.0.0
 */
class Notices {

	/**
	 * Notices constructor.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'admin_notices' ) );
	}

	/**
	 * Admin notices.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function admin_notices() {
		$installed_time = absint( get_option( 'wc_min_max_quantities_installed' ) );
		$current_time   = absint( wp_date( 'U' ) );

		// 10k celebration offer notice.
		$tenk_celebrate_end_time = strtotime( '2026-01-29 00:00:00' );
		if ( ! defined( 'WCMMQ_PRO_VERSION' ) && $current_time < $tenk_celebrate_end_time ) {
			wc_min_max_quantities()->notices->add(
				array(
					'message'     => __DIR__ . '/views/notices/tenk-celebrate.php',
					'dismissible' => false,
					'notice_id'   => 'wcmmq_tenk_celebrate_jan_2026',
					'style'       => 'border-left-color: #0542fa;',
					'class'       => 'notice-tenk-celebrate',
				)
			);
		}

		// Show after 5 days.
		if ( $installed_time && $current_time > ( $installed_time + ( 5 * DAY_IN_SECONDS ) ) ) {

			// phpcs:disable
			// TODO: Uncomment the below code when other offer is over.
			/*
			if ( ! defined( 'WCMMQ_PRO_VERSION' ) ) {
				// Upgrade notice.
				wc_min_max_quantities()->notices->add(
					array(
						'message'     => __DIR__ . '/views/notices/upgrade.php',
						'notice_id'   => 'wcmmq_upgrade',
						'style'       => 'border-left-color:#0542fa;',
						'dismissible' => false,
					)
				);
			}
			*/
			// phpcs:enable

			// Review notice.
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
