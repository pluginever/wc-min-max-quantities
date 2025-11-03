<?php
/**
 * Admin notice: Halloween offer.
 *
 * @since 2.2.0
 * @return void
 *
 * @package WooCommerceMinMaxQuantities\Admin\Notices
 */

defined( 'ABSPATH' ) || exit;

$plugin_url = defined( 'WCMMQ_PRO_VERSION' ) ? trailingslashit( wc_min_max_quantities()->author_uri ) . 'plugins/' : trailingslashit( wc_min_max_quantities()->plugin_uri );

?>
<div class="notice-body">
	<div class="notice-icon">
		<img src="<?php echo esc_url( wc_min_max_quantities()->get_assets_url( 'images/halloween-icon.svg' ) ); ?>" alt="Min Max Quantities Halloween Offer">
	</div>
	<div class="notice-content">
		<h3>
			<?php esc_html_e( 'Limited Time Offer! PluginEver Halloween Sale: 30% OFF!!', 'wc-min-max-quantities' ); ?>
		</h3>
		<p>
			<?php
			echo wp_kses_post(
				sprintf(
					// translators: 1.Offer Percentage, 2. Coupon Code.
					__( 'Spectacular Halloween Deal! Get %1$s on all premium plugins with code %2$s. Don\'t miss out — this offer vanishes soon! 👻', 'wc-min-max-quantities' ),
					'<strong>' . esc_attr( '30% OFF' ) . '</strong>',
					'<strong>' . esc_attr( 'EVERSAVE30' ) . '</strong>'
				)
			);
			?>
		</p>
	</div>
</div>
<div class="notice-footer">
	<div class="footer-btn">
		<a href="<?php echo esc_url( trailingslashit( $plugin_url ) . '?utm_source=plugin&utm_medium=notice&utm_campaign=halloween-sale&discount=EVERSAVE30' ); ?>" class="primary halloween-upgrade-btn" target="_blank">
			<span class="dashicons dashicons-cart"></span>
			<?php esc_html_e( 'Claim your discount!!', 'wc-min-max-quantities' ); ?>
		</a>
		<a href="#" class="halloween-remind-btn" data-snooze="<?php echo esc_attr( WEEK_IN_SECONDS ); ?>">
			<span class="dashicons dashicons-clock"></span>
			<?php esc_html_e( 'Remind me later', 'wc-min-max-quantities' ); ?>
		</a>
		<a href="#" class="primary halloween-remove-btn" data-dismiss>
			<span class="dashicons dashicons-remove"></span>
			<?php esc_html_e( 'Never show this again!', 'wc-min-max-quantities' ); ?>
		</a>
		<a href="#" class="primary halloween-dismiss-btn" data-dismiss>
			<span class="dashicons dashicons-dismiss"></span>
			<?php esc_html_e( 'DISMISS', 'wc-min-max-quantities' ); ?>
		</a>
	</div>
	<strong class="halloween-footer-text"><?php esc_html_e( 'Valid until November 8, 2025', 'wc-min-max-quantities' ); ?></strong>
</div>
