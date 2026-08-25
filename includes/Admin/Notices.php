<?php
/**
 * Handles the admin notices.
 *
 * @since   2.0.0
 * @package PluginEver\MinMaxQuantities\Admin
 */

namespace PluginEver\MinMaxQuantities\Admin;

use PluginEver\MinMaxQuantities\B8\Component;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Class Notices.
 *
 * @since 2.0.0
 * @package PluginEver\MinMaxQuantities\Admin
 */
class Notices extends Component {

	/**
	 * Register hooks.
	 *
	 * @since 2.3.2
	 * @return void
	 */
	public function register(): void {
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

		// Limited time special offer!
		$special_offer_end_time = strtotime( '2026-02-28 00:00:00' );
		if ( ! $this->app->is_pro_active() && $current_time < $special_offer_end_time ) {
			$this->app->notices->add(
				array(
					'message'     => $this->app->templates_path( 'admin/notices/special-offer.php' ),
					'dismissible' => false,
					'notice_id'   => 'wcmmq_special_offer_feb_2026',
					'style'       => 'border-left-color: #0542fa;',
					'class'       => 'notice-special-offer',
				)
			);
		}

		// Show after 5 days.
		if ( $installed_time && $current_time > ( $installed_time + ( 5 * DAY_IN_SECONDS ) ) ) {

			if ( ! $this->app->is_pro_active() ) {
				// Upgrade notice.
				$this->app->notices->add(
					array(
						'message'     => $this->app->templates_path( 'admin/notices/upgrade.php' ),
						'notice_id'   => 'wcmmq_upgrade',
						'style'       => 'border-left-color:#0542fa;',
						'dismissible' => false,
					)
				);
			}

			// Review notice.
			$this->app->notices->add(
				array(
					'message'     => $this->app->templates_path( 'admin/notices/review.php' ),
					'dismissible' => false,
					'notice_id'   => 'wcmmq_review',
					'style'       => 'border-left-color: #0542fa;',
				)
			);
		}
	}
}
