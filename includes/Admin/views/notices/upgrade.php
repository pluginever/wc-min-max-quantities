<?php
/**
 * Admin notice for upgrade.
 *
 * @since 2.0.0
 * @package WooCommerceMinMaxQuantities\Admin\Notices
 * @return void
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="notice-body">
	<div class="notice-icon">
		<img src="<?php echo esc_attr( wc_min_max_quantities()->get_assets_url( 'images/plugin-icon.png' ) ); ?>" alt="Min Max Quantities">
	</div>
	<div class="notice-content">
		<h3><?php esc_attr_e( 'Flash Sale Alert!', 'wc-min-max-quantities' ); ?></h3>
		<p>
			<?php
			echo wp_kses_post(
				sprintf(
					// translators: %1$s: WC Min Max Quantities Pro link, %2$s: Coupon code.
					__( 'Enjoy a <strong>10%% discount</strong> on %1$s! Use coupon code %2$s at checkout to grab the deal. Don’t miss out — this offer won’t last forever!', 'wc-min-max-quantities' ),
					'<a href="https://pluginever.com/plugins/woocommerce-min-max-quantities-pro/?utm_source=plugin&utm_medium=notice&utm_campaign=flash-sale" target="_blank"><strong>WC Min Max Quantities Pro</strong></a>',
					'<strong>FLASH10</strong>'
				)
			);
			?>
		</p>
	</div>
</div>
<div class="notice-footer">
	<a class="primary" href="https://pluginever.com/plugins/woocommerce-min-max-quantities-pro/?utm_source=plugin&utm_medium=notice&utm_campaign=flash-sale" target="_blank">
		<span class="dashicons dashicons-cart"></span>
		<?php esc_attr_e( 'Upgrade now', 'wc-min-max-quantities' ); ?>
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
