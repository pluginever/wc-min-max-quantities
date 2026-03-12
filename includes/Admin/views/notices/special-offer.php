<?php
/**
 * Admin notice for special offer.
 *
 * @since 2.2.5
 * @package WooCommerceMinMaxQuantities\Admin\Views\Notices
 * @return void
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="notice-body">
	<div class="notice-icon">
		<img src="<?php echo esc_attr( wc_min_max_quantities()->assets_url( 'build/images/plugin-icon.png' ) ); ?>" alt="<?php esc_attr_e( 'Min Max Quantities', 'wc-min-max-quantities' ); ?>" />
	</div>
	<div class="notice-content">
		<h3><?php esc_html_e( 'Enjoy 30% Special Discount - Limited Time Offer!', 'wc-min-max-quantities' ); ?></h3>
		<p>
			<?php
			echo wp_kses_post(
				sprintf(
				// translators: %1$s: Min Max Quantities Pro link, %2$s: Coupon code.
					__( 'Upgrade to %1$s & unlock powerful features to take your WooCommerce store to the next level. Get an exclusive <strong>30%% discount</strong> with code %2$s. Don\'t miss out on this limited-time offer!', 'wc-min-max-quantities' ),
					'<a href="https://pluginever.com/plugins/woocommerce-min-max-quantities-pro/?utm_source=plugin-notice&utm_medium=admin-notice&utm_campaign=special-offer-feb26&utm_id=special-offer-feb26&discount=MMLTO30#pricing-list" target="_blank"><strong>Min Max Quantities Pro</strong></a>',
					'<strong>MMLTO30</strong>'
				)
			);
			?>
		</p>
	</div>
</div>
<div class="notice-footer">
	<a class="primary" href="https://pluginever.com/plugins/woocommerce-min-max-quantities-pro/?utm_source=plugin-notice&utm_medium=admin-notice&utm_campaign=special-offer-feb26&utm_id=special-offer-feb26&discount=MMLTO30#pricing-list" target="_blank">
		<span class="dashicons dashicons-cart"></span>
		<?php esc_html_e( 'Grab the Deal', 'wc-min-max-quantities' ); ?>
	</a>
	<a href="#" data-snooze="<?php echo esc_attr( MONTH_IN_SECONDS ); ?>">
		<span class="dashicons dashicons-clock"></span>
		<?php esc_html_e( 'Maybe later', 'wc-min-max-quantities' ); ?>
	</a>
	<a href="#" data-dismiss>
		<span class="dashicons dashicons-no-alt"></span>
		<?php esc_html_e( 'Not interested, close permanently', 'wc-min-max-quantities' ); ?>
	</a>
</div>
