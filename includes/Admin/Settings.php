<?php
/**
 * Handles the settings page.
 *
 * @since   1.1.4
 * @package PluginEver\MinMaxQuantities\Admin
 */

namespace PluginEver\MinMaxQuantities\Admin;

use PluginEver\MinMaxQuantities\B8\SettingsUI;

defined( 'ABSPATH' ) || exit;

/**
 * Class Settings.
 *
 * @since 1.1.4
 * @package PluginEver\MinMaxQuantities\Admin
 */
class Settings extends SettingsUI {

	/**
	 * Capability required to manage the settings.
	 *
	 * @since 2.3.2
	 * @var string
	 */
	protected string $capability = 'manage_options';

	/**
	 * Register hooks.
	 *
	 * @since 2.3.2
	 * @return void
	 */
	public function register(): void {
		$this->app->on_filter( 'admin_pages', array( $this, 'register_page' ) );
		$this->app->on_filter( 'settings', array( $this, 'register_settings' ) );
		$this->app->on_filter( 'settings_wrap_classes', array( $this, 'wrap_classes' ) );
		$this->app->on_action( 'settings_nav_extras', array( $this, 'render_nav_extras' ) );
	}

	/**
	 * Filter the admin pages.
	 *
	 * @param array<int, array<string, mixed>> $pages Admin page configurations.
	 *
	 * @since 2.3.2
	 * @return array<int, array<string, mixed>>
	 */
	public function register_page( array $pages ): array {
		$pages[] = array(
			'title'      => __( 'Min Max Quantities', 'wc-min-max-quantities' ),
			'slug'       => 'wc-min-max-quantities',
			'capability' => $this->capability,
			'callback'   => array( $this, 'render' ),
			'position'   => 55,
		);

		return $pages;
	}

	/**
	 * Register the plugin settings.
	 *
	 * @param array<string, mixed> $settings Settings definition keyed by tab.
	 *
	 * @since 2.3.2
	 * @return array<string, mixed>
	 */
	public function register_settings( array $settings ): array {
		$settings['general'] = array(
			'title'  => __( 'General', 'wc-min-max-quantities' ),
			'fields' => array(
				array(
					// Product restrictions section.
					'title' => __( 'Product Limits', 'wc-min-max-quantities' ),
					'type'  => 'title',
					'id'    => 'wcmmq_product_restrictions',
					'desc'  => __( 'Set the minimum and maximum limits for products. Restrictions will be applied to every product individually.', 'wc-min-max-quantities' ),
				),
				// set the minimum quantity.
				array(
					'title'   => __( 'Minimum quantity', 'wc-min-max-quantities' ),
					'desc'    => __( 'Set minimum quantity for each product. Keep it blank if you don’t want to set any rule for this.', 'wc-min-max-quantities' ),
					'id'      => 'wcmmq_min_qty',
					'default' => 0,
					'type'    => 'number',
				),
				// set the maximum quantity.
				array(
					'title'   => __( 'Maximum quantity', 'wc-min-max-quantities' ),
					'desc'    => __( 'Set maximum quantity for each product. Keep it blank if you don’t want to set any rule for this.', 'wc-min-max-quantities' ),
					'id'      => 'wcmmq_max_qty',
					'default' => 0,
					'type'    => 'number',
				),
				// Quantity step.
				array(
					'title'   => __( 'Quantity step', 'wc-min-max-quantities' ),
					'desc'    => __( 'Each time the quantity is changed, it will be increased or decreased by this value. Keep it blank if you don’t want to set any rule for this.', 'wc-min-max-quantities' ),
					'id'      => 'wcmmq_step',
					'default' => 0,
					'type'    => 'number',
				),
				// end product restrictions section.
				array(
					'type' => 'sectionend',
					'id'   => 'wcmmq_product_restrictions',
				),
				array(
					'title' => esc_html__( 'Cart Limits', 'wc-min-max-quantities' ),
					'type'  => 'title',
					'id'    => 'wcmmq_order_restrictions',
					'desc'  => __( 'Set the minimum and maximum limits for the order. Restrictions will be applied to the order total.', 'wc-min-max-quantities' ),
				),
				array(
					'title'    => esc_html__( 'Minimum quantity', 'wc-min-max-quantities' ),
					'desc'     => __( 'Set minimum quantity for the order. Keep it blank if you don’t want to set any rule for this.', 'wc-min-max-quantities' ),
					'desc_tip' => __( 'This will be calculated by adding the quantity of all products in the cart.', 'wc-min-max-quantities' ),
					'id'       => 'wcmmq_min_cart_qty',
					'default'  => 0,
					'type'     => 'number',
				),
				array(
					'title'    => esc_html__( 'Maximum quantity', 'wc-min-max-quantities' ),
					'desc'     => __( 'Set maximum quantity for the order. Keep it blank if you don’t want to set any rule for this.', 'wc-min-max-quantities' ),
					'desc_tip' => __( 'This will be calculated by adding the quantity of all products in the cart.', 'wc-min-max-quantities' ),
					'id'       => 'wcmmq_max_cart_qty',
					'default'  => 0,
					'type'     => 'number',
				),
				array(
					'title'             => esc_html__( 'Minimum total', 'wc-min-max-quantities' ),
					'desc'              => __( 'Set minimum order total. Keep it blank if you don’t want to set any rule for this.', 'wc-min-max-quantities' ),
					'desc_tip'          => __( 'This will be calculated by adding the total of all products in the cart before any discounts have been applied.', 'wc-min-max-quantities' ),
					'id'                => 'wcmmq_min_cart_total',
					'default'           => 0,
					'type'              => 'number',
					'custom_attributes' => array(
						'step' => 'any',
						'min'  => '0',
					),
				),
				array(
					'title'             => esc_html__( 'Maximum total', 'wc-min-max-quantities' ),
					'desc'              => __( 'Set maximum order amount. Keep it blank if you don’t want to set any rule for this.', 'wc-min-max-quantities' ),
					'desc_tip'          => __( 'This will be calculated by adding the total of all products in the cart before any discounts have been applied.', 'wc-min-max-quantities' ),
					'id'                => 'wcmmq_max_cart_total',
					'default'           => 0,
					'type'              => 'number',
					'custom_attributes' => array(
						'step' => 'any',
						'min'  => '0',
					),
				),
				array(
					'type' => 'sectionend',
					'id'   => 'wcmmq_order_restrictions',
				),
			),
		);

		// Back-compat: let extensions add tabs through the legacy tabs filter.
		$legacy_tabs = apply_filters( 'wc_min_max_quantities_settings_tabs', array( 'general' => __( 'General', 'wc-min-max-quantities' ) ) );
		foreach ( $legacy_tabs as $tab_id => $tab_title ) {
			if ( ! isset( $settings[ $tab_id ] ) && is_string( $tab_id ) && '' !== $tab_id ) {
				$settings[ sanitize_key( $tab_id ) ] = array(
					'title'  => $tab_title,
					'fields' => array(),
				);
			}
		}

		return $settings;
	}

	/**
	 * Add the WooCommerce class to the settings page wrapper.
	 *
	 * @param array<int, string> $classes Wrapper class names.
	 *
	 * @since 2.3.2
	 * @return array<int, string>
	 */
	public function wrap_classes( array $classes ): array {
		$classes[] = 'woocommerce';

		return $classes;
	}

	/**
	 * Render the content area for the current tab.
	 *
	 * Fires the legacy per-tab action before rendering so extensions
	 * hooked to wc_min_max_quantities_settings_{tab} keep working.
	 *
	 * @param string $tab Current tab id.
	 *
	 * @since 2.3.2
	 * @return void
	 */
	protected function render_content( string $tab ): void {
		do_action( 'wc_min_max_quantities_settings_' . $tab );

		parent::render_content( $tab );
	}

	/**
	 * Output the settings fields.
	 *
	 * @param array<int, array<string, mixed>> $fields Prepared field declarations.
	 *
	 * @since 2.3.2
	 * @return void
	 */
	protected function render_fields( array $fields ): void {
		if ( function_exists( 'woocommerce_admin_fields' ) ) {
			woocommerce_admin_fields( $fields );
			return;
		}

		parent::render_fields( $fields );
	}

	/**
	 * Persist the submitted settings fields.
	 *
	 * @param array<int, array<string, mixed>> $fields Field declarations for the current tab.
	 * @param array<string, mixed>             $data   Unslashed request data.
	 *
	 * @since 2.3.2
	 * @return bool True when the fields were saved.
	 */
	protected function save_fields( array $fields, array $data ): bool {
		if ( ! function_exists( 'woocommerce_update_options' ) ) {
			return false;
		}

		woocommerce_update_options( $fields );

		return true;
	}

	/**
	 * Render the settings sidebar.
	 *
	 * Shows the premium features card while the Pro add-on is inactive.
	 *
	 * @since 2.3.2
	 * @return void
	 */
	protected function render_sidebar(): void {
		if ( ! $this->app->is_pro_active() ) {
			$this->show_premium_card();
		}

		if ( ! is_plugin_active( 'wc-key-manager/wc-key-manager.php' ) ) {
			$install_url = wp_nonce_url(
				admin_url( 'update.php?action=install-plugin&plugin=wc-key-manager' ),
				'install-plugin_wc-key-manager'
			);

			$this->app->template->render(
				'admin.promo-panel',
				array(
					'title'       => __( 'Key Manager', 'wc-min-max-quantities' ),
					'description' => __( 'Manage WooCommerce product keys and licenses with ease.', 'wc-min-max-quantities' ),
					'install_url' => $install_url,
					'label'       => __( 'Install Now', 'wc-min-max-quantities' ),
				)
			);
		}

		$this->app->template->render(
			'admin.support-panel',
			array(
				'title'         => __( 'Need Help?', 'wc-min-max-quantities' ),
				'community_url' => 'https://www.facebook.com/groups/pluginever',
				'contact_url'   => 'https://www.pluginever.com/contact/',
			)
		);
	}

	/**
	 * Render the premium card.
	 *
	 * @return void
	 * @throws \Exception If the premium card template cannot be rendered.
	 */
	protected function show_premium_card(): void {
		if ( $this->app->is_pro_active() ) {
			return;
		}

		$features = array(
			__( 'Set restrictions for each product individually.', 'wc-min-max-quantities' ),
			__( 'Set restrictions for each product variation.', 'wc-min-max-quantities' ),
			__( 'Set restrictions for all products from a category.', 'wc-min-max-quantities' ),
			__( 'Set restrictions based on product categories.', 'wc-min-max-quantities' ),
			__( 'Set restrictions for the order total.', 'wc-min-max-quantities' ),
			__( 'Set restrictions for based on the user role.', 'wc-min-max-quantities' ),
			__( 'Allow your vendors to set their own minimum and maximum restrictions. Supports MultiVendorX and WCFM Marketplace.', 'wc-min-max-quantities' ),
		);

		$this->app->template->render(
			'admin.pro-panel',
			array(
				'title'    => __( 'Premium Features', 'wc-min-max-quantities' ),
				'features' => $features,
				'url'      => 'https://pluginever.com/plugins/woocommerce-min-max-quantities-pro/?utm_source=plugin-settings&utm_medium=banner&utm_campaign=upgrade&utm_id=wc-min-max-quantities',
				'label'    => __( 'Get Premium', 'wc-min-max-quantities' ),
				'links'    => array(
					array(
						'label' => __( 'Join our Community', 'wc-min-max-quantities' ),
						'url'   => esc_url( 'https://www.facebook.com/groups/pluginever' ),
					),
					array(
						'label' => __( 'Request a Feature', 'wc-min-max-quantities' ),
						'url'   => $this->app->get( 'support_url' ),
					),
					array(
						'label' => __( 'Report a Bug', 'wc-min-max-quantities' ),
						'url'   => $this->app->get( 'support_url' ),
					),
				),
			)
		);
	}

	/**
	 * Render extra links in the settings navigation.
	 *
	 * @since 2.3.2
	 * @return void
	 */
	public function render_nav_extras(): void {
		if ( '' !== $this->app->docs_url ) {
			printf( '<a href="%s" class="nav-tab" target="_blank">%s</a>', esc_url( $this->app->docs_url ), esc_html__( 'Documentation', 'wc-min-max-quantities' ) );
		}
	}
}
