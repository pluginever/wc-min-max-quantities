<?php
/**
 * Admin notice for review.
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
		<h3>
			<?php esc_html_e( 'Enjoying Min Max Quantities?', 'wc-min-max-quantities' ); ?>
		</h3>
		<p>
			<?php
			echo wp_kses_post(
				sprintf(
				// translators: %1$s: WC Min Max Quantities Pro link, %2$s: Coupon code.
					__( 'We hope you had a wonderful experience using %1$s. Please take a moment to show us your support by leaving a 5-star review on <a href="%2$s" target="_blank"><strong>WordPress.org</strong></a>. Thank you! 😊', 'wc-min-max-quantities' ),
					'<a href="https://wordpress.org/plugins/wc-min-max-quantities/" target="_blank"><strong>WC Min Max Quantities</strong></a>',
					'https://wordpress.org/support/plugin/wc-min-max-quantities/reviews/?filter=5#new-post'
				)
			);
			?>
		</p>
	</div>
</div>
<div class="notice-footer">
	<a class="primary" href="https://wordpress.org/support/plugin/wc-min-max-quantities/reviews/?filter=5#new-post" target="_blank">
		<span class="dashicons dashicons-heart"></span>
		<?php esc_html_e( 'Sure, I\'d love to help!', 'wc-min-max-quantities' ); ?>
	</a>
	<a href="#" data-snooze>
		<span class="dashicons dashicons-clock"></span>
		<?php esc_html_e( 'Maybe later', 'wc-min-max-quantities' ); ?>
	</a>
	<a href="#" data-dismiss>
		<span class="dashicons dashicons-smiley"></span>
		<?php esc_html_e( 'I\'ve already left a review', 'wc-min-max-quantities' ); ?>
	</a>
</div>
