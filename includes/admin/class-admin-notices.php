<?php
/**
 * Handle admin notice related functionalities.
 *
 * @version	1.1.0
 * @since	1.1.0
 * @package WC_Min_Max_Quantities\Admin
 */

namespace WC_Min_Max_Quantities\Admin;

use WC_Min_Max_Quantities\Plugin;

defined( 'ABSPATH' ) || exit();

/**
 * Admin_Notices class.
 */
class Admin_Notices {
	/**
	 * Stores notices.
	 *
	 * @since 1.1.0
	 * @var array
	 */
	private static $notices = array();

	/**
	 * Admin_Notices construct.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function __construct() {
		self::$notices = get_option( 'wc_min_max_quantities_admin_notices', array() );
		add_filter( 'wp_redirect', array( __CLASS__, 'redirect' ), 1 );
		add_action( 'admin_notices', array( __CLASS__, 'render_notices' ), 15 );
		add_action( 'wp_ajax_dismiss_admin_notice_' . Plugin::get('basename'), array( __CLASS__, 'handle_dismiss_notice' ) );
	}

	/**
	 * Adds the given $message as a dismissible notice identified by $notice_id,
	 * unless the notice has been dismissed, or we're on the plugin settings page
	 *
	 * @param string|array $notice Notice array data.
	 *
	 * @since 1.1.0
	 */
	public static function add_notice( $notice ) {
		$defaults = array(
			'id'          => null,
			'type'        => 'notice-success', // | 'notice-warning' | 'notice-success' | 'notice-error' | 'notice-info',
			'message'     => '',
			'dismissible' => false,
			'dismissed'   => false,
			'class'       => '',
			'capability'  => 'manage_options',
			'display_on'  => array(),
			'start_time'  => null,
			'end_time'    => null,
		);

		if ( is_string( $notice ) ) {
			$notice = array( 'message' => $notice );
		}

		$notice      = (array) wp_parse_args( $notice, $defaults );
		$dismissible = filter_var( $notice['dismissible'], FILTER_VALIDATE_BOOLEAN );
		$notice_id   = trim( $notice['id'] );
		if ( empty( $notice_id ) ) {
			$notice_id    = substr( md5( $notice['message'] ), 0, 33 );
			$notice['id'] = $notice_id;
		}

		if ( ! isset( self::$notices[ $notice_id ] ) ) {
			self::$notices[ $notice_id ] = $notice;
		}

		if ( $dismissible ) {
			self::save_notices();
		}
	}

	/**
	 * Add an error message.
	 *
	 * @param string $message error message
	 *
	 * @since 1.1.0
	 */
	public static function add_error( $message, $dismissible = false ) {
		self::add_notice( array(
			'message'     => $message,
			'type'        => 'notice-error',
			'dismissible' => $dismissible,
		) );
	}


	/**
	 * Adds a warning message.
	 *
	 * @param string $message warning message to add
	 * @param bool $dismissible If message is dismissible.
	 *
	 * @since 1.1.0
	 */
	public static function add_warning( $message, $dismissible = false ) {
		self::add_notice( array(
			'message'     => $message,
			'type'        => 'notice-warning',
			'dismissible' => $dismissible,
		) );
	}


	/**
	 * Adds an info message.
	 *
	 * @param string $message info message to add
	 * @param bool $dismissible If message is dismissible.
	 *
	 * @since 1.1.0
	 */
	public static function add_info( $message, $dismissible = false ) {
		self::add_notice( array(
			'message'     => $message,
			'type'        => 'notice-info',
			'dismissible' => $dismissible,
		) );
	}

	/**
	 * Remove notice.
	 *
	 * @param $notice_id
	 *
	 * @since 1.1.0
	 */
	public static function remove_notice( $notice_id ) {
		if ( array_key_exists( $notice_id, self::$notices ) ) {
			unset( self::$notices[ $notice_id ] );
			self::save_notices();
		}
	}

	/**
	 * Marks the identified admin notice as dismissed for the given user
	 *
	 * @param string $notice_id the message identifier
	 *
	 * @since 1.1.0
	 */
	public static function dismiss_notice( $notice_id ) {
		if ( array_key_exists( $notice_id, self::$notices ) ) {
			self::$notices[ $notice_id ]['dismissed'] = time();
			self::save_notices();
		}
	}

	/**
	 * Marks the identified admin notice as not dismissed for the identified user
	 *
	 * @param string $notice_id the message identifier
	 *
	 * @since 1.1.0
	 */
	public function undismiss_notice( $notice_id ) {
		if ( array_key_exists( $notice_id, self::$notices ) ) {
			self::$notices[ $notice_id ]['dismissed'] = false;
			self::save_notices();
		}
	}


	/**
	 * Returns true if the identified admin notice has been dismissed for the
	 * given user
	 *
	 * @param string $notice_id the message identifier
	 *
	 * @since 1.1.0
	 * @return boolean true if the message has been dismissed by the admin user
	 */
	public static function is_notice_dismissed( $notice_id ) {
		return array_key_exists( $notice_id, self::$notices ) && false !== self::$notices[ $notice_id ]['dismissed'];
	}

	/**
	 * Store notices to DB
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public static function save_notices() {
		update_option( 'wc_min_max_quantities_admin_notices', self::$notices, true );
	}

	/**
	 * Redirection hook which persists messages into session data.
	 *
	 * @param string $location the URL to redirect to.
	 *
	 * @since 1.1.0
	 * @return string the URL to redirect to
	 */
	public static function redirect( $location ) {
		// add the admin message id param to the
		if ( ! empty( self::$notices ) ) {
			self::save_notices();
		}

		return $location;
	}

	/**
	 * Render all notices.
	 *
	 * @since 1.1.0
	 */
	public static function render_notices() {
		global $pagenow;
		if ( ! empty( self::$notices ) ) {
			foreach ( self::$notices as $notice ) {

				if ( empty( $notice['message'] ) || empty( $notice['type'] ) ) {
					continue;
				}

				if ( $notice['dismissible'] && self::is_notice_dismissed( $notice['id'] ) ) {
					continue;
				}

				if ( ! empty( $notice['start_time'] ) && current_time( 'timestamp' ) < absint( $notice['start_time'] ) ) {
					continue;
				}

				if ( ! empty( $notice['end_time'] ) && current_time( 'timestamp' ) > absint( $notice['end_time'] ) ) {
					self::dismiss_notice( $notice['id'] );
					continue;
				}

				// bail out if user is not the set one.
				if ( ! empty( $params['capability'] ) && ! current_user_can( $params['capability'] ) ) {
					continue;
				}

				if ( ! empty( $params['display_on'] ) && ! in_array( $pagenow, wp_parse_list( $params['display_on'] ), true ) ) {
					continue;
				}

				self::render_notice( $notice );

				if ( ! $notice['dismissible'] ) {
					self::remove_notice( $notice['id'] );
				}
			}
		}
	}


	/**
	 * Render a single notice.
	 *
	 * @param array $notice Notice data.
	 *
	 * @since 1.1.0
	 */
	public static function render_notice( $notice ) {
		$output            = '';
		$plugin_class      = Plugin::get('slug');
		$dismissible_class = $notice['dismissible'] ? 'is-dismissible' : '';
		$nonce             = $notice['dismissible'] ? wp_create_nonce( Plugin::get('id') . '_dismiss_notice' ) : '';

		$output .= sprintf( '<div class="notice %2$s %3$s %4$s %5$s %7$s-admin-notice" data-notice-id="%5$s" data-plugin-id="%6$s" data-nonce="%8$s">%1$s</div>',
			wpautop( $notice['message'] ),
			esc_attr( $notice['type'] ),
			esc_attr( $dismissible_class ),
			esc_attr( $notice['class'] ),
			esc_attr( $notice['id'] ),
			esc_attr( Plugin::get('basename') ),
			esc_attr( $plugin_class ),
			esc_attr( $nonce )
		);
		echo wp_kses_post( $output );

		if ( ! did_action( 'wc_min_max_quantities_render_admin_notice_js' ) ) {
			self::render_admin_notice_js();
			do_action( 'wc_min_max_quantities_render_admin_notice_js' );
		}
	}

	/**
	 * Render the javascript to handle the notice "dismiss" functionality
	 *
	 * @since 1.1.0
	 */
	public static function render_admin_notice_js() {
		$plugin_class = Plugin::get('slug');
		// if there were no notices, or we've already rendered the js, there's nothing to do
		ob_start();
		?>
		<script type="text/javascript">
			(function ($) {
				$('.is-dismissible.<?php echo esc_js( $plugin_class );?>-admin-notice').on('click', '.notice-dismiss', function (e) {
					var $notice = $(this).closest('.is-dismissible');
					log_dismissed_notice(
						$($notice).data('plugin-id'),
						$($notice).data('notice-id'),
						$($notice).data('nonce'),
					);
				});

				function log_dismissed_notice(pluginID, messageID, nonce) {
					$.get(
						window.ajaxurl,
						{
							action: 'dismiss_admin_notice_' + pluginID,
							plugin_id: pluginID,
							notice_id: messageID,
							nonce: nonce,
						}
					);
				}
			})(jQuery);
		</script>
		<?php
		$javascript = ob_get_clean();

		echo $javascript;
	}


	/**
	 * Dismiss the identified notice
	 *
	 * @since 1.1.0
	 */
	public static function handle_dismiss_notice() {
		$notice_id    = filter_input( INPUT_GET, 'notice_id', FILTER_SANITIZE_STRING );
		$plugin_id    = filter_input( INPUT_GET, 'plugin_id', FILTER_SANITIZE_STRING );
		$nonce        = filter_input( INPUT_GET, 'nonce', FILTER_SANITIZE_STRING );
		$nonce_action = Plugin::get('id') . '_dismiss_notice';

		if ( empty( $notice_id ) || $plugin_id !== esc_attr( Plugin::get('basename') ) || ! wp_verify_nonce( $nonce, $nonce_action ) ) {
			wp_send_json_error( esc_html__( 'Something does not look appropriate.', 'wc-min-max-quantities' ) );
		}

		self::dismiss_notice( $notice_id );
		wp_send_json_success( 'Notice dismissed' );
		exit();
	}


	public static function add_welcome_notice() {
		if ( ! Plugin::has( 'docs_url' ) ) {
			return;
		}

		$message = sprintf(
		/** translators: Placeholders: %1$s - plugin name, %2$s - <a> tag, %3$s - </a> tag */
			__( 'Thanks for installing %1$s! To get started, take a minute to %2$sread the documentation%3$s :)', 'wc-min-max-quantities' ),
			'<strong>' . esc_html( Plugin::get('name') ) . '</strong>',
			'<a href="' . esc_url( Plugin::get('docs_url') ) . '" target="_blank">', '</a>'
		);

		self::add_notice( array(
			'id'          => 'welcome_notice',
			'type'        => 'notice-success',
			'message'     => $message,
			'dismissible' => true,
			'capability'  => 'manage_options',
			'display_on'  => array(),
		) );
	}


	public static function add_review_notice() {
		if ( ! Plugin::has( 'reviews_url' ) ) {
			return;
		}

		$message = sprintf(
		/** translators: Placeholders: %1$s - plugin name, %2$s - <a> tag, %3$s - </a> tag */
			__( 'We hope you\'re enjoying %1$s! Could you please do us a BIG favor and give it a 5-star rating to help us spread the word and boost our motivation? %2$s Sure! You deserve it! %3$s', 'wc-min-max-quantities' ),
			'<strong>' . esc_html( Plugin::get('name') ) . '</strong>',
			'<a href="' . esc_url( Plugin::get('reviews_url') ) . '" target="_blank" style="text-decoration: none;"><span class="dashicons dashicons-external" style="margin-top: -2px;"></span>', '</a>'
		);

		self::add_notice( array(
			'id'          => 'reviews_url',
			'type'        => 'notice-success',
			'message'     => $message,
			'dismissible' => true,
			'capability'  => 'manage_options',
			'display_on'  => array(),
		) );
	}
}
