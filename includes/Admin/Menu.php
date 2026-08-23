<?php
/**
 * Handles the admin menu.
 *
 * @since   2.3.2
 * @package PluginEver\MinMaxQuantities\Admin
 */

namespace PluginEver\MinMaxQuantities\Admin;

use PluginEver\MinMaxQuantities\B8\Component;

defined( 'ABSPATH' ) || exit;

/**
 * Class Menu.
 *
 * Registers the plugin admin pages from the admin_pages filter.
 *
 * @since 2.3.2
 * @package PluginEver\MinMaxQuantities\Admin
 */
class Menu extends Component {

	/**
	 * Parent menu slug.
	 *
	 * @since 2.3.2
	 * @var string
	 */
	protected string $parent = 'woocommerce';

	/**
	 * Registered screen IDs.
	 *
	 * @since 2.3.2
	 * @var array<int, string>
	 */
	protected array $screen_ids = array();

	/**
	 * Register hooks.
	 *
	 * @since 2.3.2
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_menu', array( $this, 'register_menu' ), 55 );
	}

	/**
	 * Register the admin menu.
	 *
	 * @since 2.3.2
	 * @return void
	 */
	public function register_menu(): void {
		/**
		 * Filters the admin pages configuration.
		 *
		 * @since 2.3.2
		 * @param array<int, array<string, mixed>> $pages Admin page configurations.
		 */
		$pages = $this->app->apply_filters( 'admin_pages', array() );

		foreach ( $pages as $page ) {
			$page = wp_parse_args(
				$page,
				array(
					'title'      => '',
					'slug'       => '',
					'capability' => 'manage_options',
					'position'   => null,
					'handle'     => null,
					'callback'   => '',
				)
			);

			if ( empty( $page['title'] ) || empty( $page['slug'] ) || ! is_callable( $page['callback'] ) ) {
				continue;
			}

			$hook = add_submenu_page(
				$this->parent,
				$page['title'],
				$page['title'],
				$page['capability'],
				$page['slug'],
				$this->app->callback( $page['callback'] ),
				$page['position']
			);

			if ( ! $hook ) {
				continue;
			}

			$this->screen_ids[] = $hook;

			if ( is_callable( $page['handle'] ) ) {
				add_action( "load-{$hook}", $this->app->callback( $page['handle'] ) );
			}
		}
	}

	/**
	 * Get the screen ids.
	 *
	 * @since 1.1.4
	 * @return array<int, string> Screen IDs.
	 */
	public function get_screen_ids(): array {
		/**
		 * Filters the plugin admin screen ids.
		 *
		 * @since 1.1.4
		 * @param array<int, string> $screen_ids Screen IDs.
		 */
		return apply_filters( 'wc_min_max_quantities_screen_ids', $this->screen_ids );
	}
}
