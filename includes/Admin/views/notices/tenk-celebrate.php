<?php
/**
 * Admin notice for 10k celebration.
 *
 * @since 2.2.4
 * @package WooCommerceMinMaxQuantities\Admin\Notices
 * @return void
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="notice-body">
	<div class="notice-icon">
		<img src="<?php echo esc_attr( wc_min_max_quantities()->get_assets_url( 'images/plugin-icon.png' ) ); ?>" alt="<?php esc_attr_e( 'Min Max Quantities', 'wc-min-max-quantities' ); ?>" />
	</div>
	<div class="notice-content">
		<h3><?php esc_html_e( '10,000+ Users Celebration!', 'wc-min-max-quantities' ); ?></h3>
		<p>
			<?php
			echo wp_kses_post(
				sprintf(
				// translators: %1$s: Min Max Quantities Pro link, %2$s: Coupon code.
					__( '🎉 We hit 10,000+ users! To celebrate with you, enjoy <strong>20%% discount</strong> OFF on %1$s our Premium plan. Use code: %2$s at checkout to grab the deal.', 'wc-min-max-quantities' ),
					'<a href="https://pluginever.com/plugins/woocommerce-min-max-quantities-pro/?utm_source=plugin-notice&utm_medium=admin-notice&utm_campaign=10k-celebration&utm_id=10k-celebration&discount=THANKYOU10K#pricing-list" target="_blank"><strong>Min Max Quantities Pro</strong></a>',
					'<strong>THANKYOU10K</strong>'
				)
			);
			?>
		</p>
	</div>
</div>
<div class="notice-footer">
	<a class="primary" href="https://pluginever.com/plugins/woocommerce-min-max-quantities-pro/?utm_source=plugin-notice&utm_medium=admin-notice&utm_campaign=10k-celebration&utm_id=10k-celebration&discount=THANKYOU10K#pricing-list" target="_blank">
		<span class="dashicons dashicons-cart"></span>
		<?php esc_html_e( 'Grab the Deal', 'wc-min-max-quantities' ); ?>
	</a>
	<a href="#" data-snooze>
		<span class="dashicons dashicons-clock"></span>
		<?php esc_html_e( 'Maybe later', 'wc-min-max-quantities' ); ?>
	</a>
	<a href="#" data-dismiss>
		<span class="dashicons dashicons-no-alt"></span>
		<?php esc_html_e( 'Close permanently', 'wc-min-max-quantities' ); ?>
	</a>
</div>
