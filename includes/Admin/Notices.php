<?php

namespace WooCommerceMinMaxQuantities\Admin;

defined( 'ABSPATH' ) || exit();

/**
 * Class Admin Notices.
 *
 * @since 1.1.4
 * @package WooCommerceMinMaxQuantities\Admin
 */
class Notices {
	/**
	 * Notices container.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $notices = array();


	/**
	 * Notices constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'add_notices' ) );
		add_action( 'admin_notices', array( $this, 'output_notices' ) );
		add_action( 'wp_ajax_wcmmq_dismiss_notice', array( $this, 'dismiss_notice' ) );
		add_action( 'admin_footer', array( $this, 'add_notice_script' ) );
	}

	/**
	 * Admin notices.
	 *
	 * @since 1.0.0
	 */
	public function add_notices() {
		$is_outdated_pro = wc_min_max_quantities()->is_plugin_active( 'wc-min-max-quantities-pro/wc-min-max-quantities-pro.php' ) && ! defined( 'WCMMQ_PRO_VERSION' );
		if ( $is_outdated_pro ) {
			$this->notices[] = array(
				'type'    => 'error', // add notice-alt and notice-large class.
				'message' => sprintf(
				/* translators: %1$s: link to the plugin page, %2$s: link to the plugin page */
					__( '%s is not functional because you are using outdated version of the plugin, please update to the version 1.0.5 or higher.', 'wc-min-max-quantities' ),
					'<a href="' . esc_url( wc_min_max_quantities()->data['premium_url'] ) . '" target="_blank">WC Min Max Quantities Pro</a>'
				),
			);
		}

		if ( ! $this->is_notice_dismissed( 'wcmmq_upgrade_to_pro10' ) && ! function_exists( 'wc_min_max_quantities_pro' ) ) {
			$this->notices[] = array(
				'type'        => 'info',
				'classes'     => 'notice-alt notice-large',
				'dismissible' => true,
				'id'          => 'wcmmq_upgrade_to_pro10',
				'message'     => sprintf(
				/* translators: %1$s: link to the plugin page, %2$s: link to the plugin page */
					__( '🚀 Maximize your revenue with %1$sWC Min Max Quantities%2$s and take it to the next level! %3$sUpgrade today%4$s to unlock the full potential and enjoy an exclusive %5$s limited-time discount using promo code FREE2PRO.', 'wc-min-max-quantities' ),
					'<strong>',
					'</strong>',
					'<a href="' . esc_url( wc_min_max_quantities()->data['premium_url'] ) . '" target="_blank">',
					'</a>',
					'<strong>10%</strong>'
				),
			);
		}
	}

	/**
	 * Admin notices.
	 *
	 * @since 1.0.0
	 */
	public function output_notices() {
		foreach ( $this->notices as $notice ) {
			$notice = wp_parse_args(
				$notice,
				array(
					'id'          => wp_generate_password( 12, false ),
					'type'        => 'info',
					'classes'     => '',
					'message'     => '',
					'dismissible' => false,
				)
			);

			$notice_classes = array( 'notice', 'notice-' . $notice['type'] );
			if ( $notice['dismissible'] ) {
				$notice_classes[] = 'is-dismissible';
			}
			if ( $notice['classes'] ) {
				$notice_classes[] = $notice['classes'];
			}
			?>
			<div class="wcmmq-notice <?php echo esc_attr( implode( ' ', $notice_classes ) ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'wcmmq_dismiss_notice' ) ); ?>" data-notice-id="<?php echo esc_attr( $notice['id'] ); ?>">
				<p><?php echo wp_kses_post( $notice['message'] ); ?></p>
			</div>
			<?php
		}
	}

	/**
	 * Dismiss notice.
	 *
	 * @since 1.0.0
	 */
	public function dismiss_notice() {
		check_ajax_referer( 'wcmmq_dismiss_notice', 'nonce' );
		$notice_id = isset( $_POST['notice_id'] ) ? sanitize_text_field( wp_unslash( $_POST['notice_id'] ) ) : '';
		if ( $notice_id ) {
			update_option( 'wcmmq_dismissed_notices', array_merge( get_option( 'wcmmq_dismissed_notices', array() ), array( $notice_id ) ) );
		}
		wp_die();
	}

	/**
	 * Check if notice is dismissed.
	 *
	 * @param string $notice_id Notice ID.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_notice_dismissed( $notice_id ) {
		return in_array( $notice_id, get_option( 'wcmmq_dismissed_notices', array() ), true );
	}

	/**
	 * Add notice script.
	 *
	 * @since 1.0.0
	 */
	public function add_notice_script() {
		?>
		<script type="text/javascript">
			jQuery(function ($) {
				$('.wcmmq-notice').on('click', '.notice-dismiss', function () {
					var $notice = $(this).closest('.wcmmq-notice');
					$.ajax({
						url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
						method: 'POST',
						data: {
							action: 'wcmmq_dismiss_notice',
							nonce: $notice.data('nonce'),
							notice_id: $notice.data('notice-id'),
						},
					});
				});
			});
		</script>
		<?php
	}
}
