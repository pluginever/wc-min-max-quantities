<?php

namespace PluginEver\MinMaxQuantities\Admin;

use PluginEver\MinMaxQuantities\B8\Component;

defined( 'ABSPATH' ) || exit;

/**
 * Handles the admin menu.
 *
 * @since   2.3.0
 * @package PluginEver\MinMaxQuantities\Admin
 */
class Menu extends Component {

	/**
	 * Parent menu slug.
	 *
	 * @since 2.3.0
	 * @var string
	 */
	protected string $parent = 'woocommerce';

	/**
	 * Registered screen IDs.
	 *
	 * @since 2.3.0
	 * @var array<int, string>
	 */
	protected array $screen_ids = array();

	/**
	 * Register hooks.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
	}

	/**
	 * Register the admin menu.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	public function register_menu(): void {
		/**
		 * Filters the admin pages configuration.
		 *
		 * @since 2.3.0
		 * @param array<int, array<string, mixed>> $pages Admin page configurations.
		 */
		$pages = $this->app->apply_filters( 'admin_pages', array() );

		usort( $pages, static fn( $a, $b ) => ( $a['position'] ?? 10 ) <=> ( $b['position'] ?? 10 ) );

		foreach ( $pages as $page ) {
			$page = wp_parse_args(
				$page,
				array(
					'title'      => '',
					'slug'       => '',
					'capability' => 'manage_woocommerce',
					'position'   => 10,
					'callback'   => array( $this, 'render' ),
				)
			);

			if ( ! $page['title'] || ! $page['slug'] ) {
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

			if ( $hook ) {
				$this->screen_ids[] = $hook;
			}
		}
	}

	/**
	 * Render a default mount node.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	public function render(): void {
		echo '<div id="app"></div>';
	}

	/**
	 * Get the screen ids.
	 *
	 * @since 2.3.0
	 * @return array<int, string> Screen IDs.
	 */
	public function get_screen_ids(): array {
		return $this->screen_ids;
	}
}
