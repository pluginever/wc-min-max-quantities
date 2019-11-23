<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class WC_MINMAX_Promotion {

	public function __construct() {
		add_action( 'admin_notices', array( $this, 'promotional_offer' ) );
		add_action( 'wp_ajax_wc_minmax-dismiss-promotional-offer-notice', array( $this, 'dismiss_promotional_offer' ) );
	}

	/**
	 *
	 * since 1.0.0
	 */
	public function promotional_offer() {
		// Show only to Admins
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// check if it has already been dismissed
		$hide_notice = get_option( 'wc_minmax_initial_upsell_promotion', 'no' );

		if ( 'hide' == $hide_notice ) {
			return;
		}

		?>
		<div class="notice notice-info is-dismissible" id="wc_minmax-promotional-offer-notice">
			<p>Thank you for installing
				<strong><a href="https://pluginever.com/plugins/woocommerce-min-max-quantities-pro/" target="_blank">WC Min Max Quantities</a></strong>,
				Use the coupon code <strong>WCMINMAXFREE2PRO</strong> for 20% discount on PRO.
				<a href="https://pluginever.com/plugins/woocommerce-min-max-quantities-pro/?utm_source=plugin_activation_notice&utm_medium=link&utm_campaign=wc-minmax-numbers&utm_content=Get%20the%20Offer" target="_blank" style="text-decoration: none;">
					<span class="dashicons dashicons-smiley" style="margin-left: 10px;"></span> Get the Offer</a></p>
			<span class="dashicons dashicons-megaphone"></span>
		</div><!-- #wc_minmax-promotional-offer-notice -->

		<style>

			#wc_minmax-promotional-offer-notice p{
				color: #000;
				font-size: 14px;
				margin-bottom: 10px;
				-webkit-text-shadow: 0.1px 0.1px 0px rgba(250, 250, 250, 0.24);
				-moz-text-shadow: 0.1px 0.1px 0px rgba(250, 250, 250, 0.24);
				-o-text-shadow: 0.1px 0.1px 0px rgba(250, 250, 250, 0.24);
				text-shadow: 0.1px 0.1px 0px rgba(250, 250, 250, 0.24);
				padding-left: 30px;
			}


			#wc_minmax-promotional-offer-notice span.dashicons-megaphone {
				position: absolute;
				top: 8px;
				left: 0;
				color: #0073aa;
				font-size: 36px;
				transform: rotate(-21deg);
			}
		</style>

		<script type='text/javascript'>
			jQuery('body').on('click', '#wc_minmax-promotional-offer-notice .notice-dismiss', function(e) {
				e.preventDefault();

				wp.ajax.post('wc_minmax-dismiss-promotional-offer-notice', {
					dismissed: true
				});
			});
		</script>
		<?php
	}


	/**
	 * Dismiss promotion notice
	 *
	 * @since  2.5
	 *
	 * @return void
	 */
	public function dismiss_promotional_offer() {
		if ( ! empty( $_POST['dismissed'] ) ) {
			$offer_key = 'wc_minmax_initial_upsell_promotion';
			update_option( $offer_key, 'hide' );
		}
	}
}

new WC_MINMAX_Promotion();
