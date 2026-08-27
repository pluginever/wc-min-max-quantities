<?php
/**
 * Handles the plugin admin.
 *
 * @since   1.1.4
 * @package PluginEver\MinMaxQuantities\Admin
 */

namespace PluginEver\MinMaxQuantities\Admin;

use PluginEver\MinMaxQuantities\B8\Component;

defined( 'ABSPATH' ) || exit;

/**
 * Class Admin
 *
 * @since 1.1.4
 * @package PluginEver\MinMaxQuantities\Admin
 */
class Admin extends Component {

	/**
	 * Child components.
	 *
	 * @since 2.3.2
	 * @var array<int, class-string>
	 */
	public array $components = array(
		Menu::class,
		Settings::class,
		MetaBoxes::class,
		Actions::class,
		Notices::class,
		Feedback::class,
	);

	/**
	 * Whether to load.
	 *
	 * @since 2.3.2
	 * @return bool
	 */
	public function autoload(): bool {
		return is_admin();
	}

	/**
	 * Register hooks.
	 *
	 * @since 2.3.2
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_filter( 'woocommerce_screen_ids', array( $this, 'screen_ids' ) );
		add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), PHP_INT_MAX );
		add_filter( 'update_footer', array( $this, 'update_footer' ), PHP_INT_MAX );
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @param string $hook Hook name.
	 *
	 * @since 1.1.4
	 * @return void
	 */
	public function enqueue_scripts( $hook ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
		$this->app->scripts->enqueue_style( 'wcmmq-admin', 'admin.css', array( 'b8-layout', 'b8-components' ) );
	}

	/**
	 * Add the plugin screens to the WooCommerce screens.
	 * This will load the WooCommerce admin styles and scripts.
	 *
	 * @param array $ids Screen ids.
	 *
	 * @return array
	 */
	public function screen_ids( $ids ) {
		return array_merge( $ids, $this->app->get( Menu::class )->get_screen_ids() );
	}

	/**
	 * Admin footer text.
	 *
	 * @param string $footer_text Footer text.
	 *
	 * @since 1.1.4
	 * @return string
	 */
	public function admin_footer_text( $footer_text ) {
		$screen = get_current_screen();

		if ( $this->app->review_url && $screen && in_array( $screen->id, $this->app->get( Menu::class )->get_screen_ids(), true ) ) {
			$footer_text = sprintf(
			/* translators: 1: Plugin name 2: WordPress */
				__( 'Thank you for using %1$s. If you like it, please leave us a %2$s rating. A huge thank you from PluginEver in advance!', 'wc-min-max-quantities' ),
				'<strong>' . esc_html__( 'Min Max Quantities', 'wc-min-max-quantities' ) . '</strong>',
				'<a href="' . esc_url( $this->app->review_url ) . '" target="_blank" class="wc-min-max-quantities-rating-link" data-rated="' . esc_attr__( 'Thanks :)', 'wc-min-max-quantities' ) . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>'
			);
		}

		return $footer_text;
	}

	/**
	 * Update footer.
	 *
	 * @param string $footer_text Footer text.
	 *
	 * @since 1.1.4
	 * @return string
	 */
	public function update_footer( $footer_text ) {
		$screen = get_current_screen();

		if ( $screen && in_array( $screen->id, $this->app->get( Menu::class )->get_screen_ids(), true ) ) {
			/* translators: 1: Plugin version */
			$footer_text = sprintf( esc_html__( 'Version %s', 'wc-min-max-quantities' ), $this->app->version );
		}

		return $footer_text;
	}
}
