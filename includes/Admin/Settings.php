<?php

namespace PluginEver\MinMaxQuantities\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Handles the settings page.
 *
 * Renders the tabbed settings screen and registers its admin page. Field values
 * are persisted through WooCommerce so the legacy `wcmmq_*` option keys are preserved.
 *
 * @since   2.3.0
 * @package PluginEver\MinMaxQuantities\Admin
 */
class Settings extends \PluginEver\MinMaxQuantities\B8\SettingsUI {

	/**
	 * Capability required to manage the settings.
	 *
	 * @since 2.3.0
	 * @var string
	 */
	protected string $capability = 'manage_woocommerce';

	/**
	 * Register hooks.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	public function register(): void {
		$this->app->on_filter( 'admin_pages', array( $this, 'register_page' ) );
		$this->app->on_filter( 'settings_wrap_classes', array( $this, 'wrap_classes' ) );
		$this->app->on_filter( 'settings', array( $this, 'register_settings' ) );
	}

	/**
	 * Register the settings admin page.
	 *
	 * @since 2.3.0
	 * @param array<int, array<string, mixed>> $pages Admin page configurations.
	 * @return array<int, array<string, mixed>>
	 */
	public function register_page( array $pages ): array {
		$pages[] = array(
			'title'    => __( 'Min Max Quantities', 'wc-min-max-quantities' ),
			'slug'     => 'wc-min-max-quantities',
			'callback' => array( $this, 'render' ),
			'position' => 90,
		);

		return $pages;
	}

	/**
	 * Add the WooCommerce class to the settings page wrapper.
	 *
	 * @since 2.3.0
	 * @param array<int, string> $classes Wrapper class names.
	 * @return array<int, string>
	 */
	public function wrap_classes( array $classes ): array {
		$classes[] = 'woocommerce';

		return $classes;
	}

	/**
	 * Register the plugin settings.
	 *
	 * @since 2.3.0
	 * @param array<string, mixed> $settings Settings definition keyed by tab.
	 * @return array<string, mixed>
	 */
	public function register_settings( array $settings ): array {
		$fields = array(
			array(
				'title' => __( 'Product Limits', 'wc-min-max-quantities' ),
				'type'  => 'title',
				'id'    => 'wcmmq_product_restrictions',
				'desc'  => __( 'Set the minimum and maximum limits for products. Restrictions will be applied to every product individually.', 'wc-min-max-quantities' ),
			),
			array(
				'title'   => __( 'Minimum quantity', 'wc-min-max-quantities' ),
				'desc'    => __( 'Set minimum quantity for each product. Keep it blank if you don’t want to set any rule for this.', 'wc-min-max-quantities' ),
				'id'      => 'wcmmq_min_qty',
				'default' => 0,
				'type'    => 'number',
			),
			array(
				'title'   => __( 'Maximum quantity', 'wc-min-max-quantities' ),
				'desc'    => __( 'Set maximum quantity for each product. Keep it blank if you don’t want to set any rule for this.', 'wc-min-max-quantities' ),
				'id'      => 'wcmmq_max_qty',
				'default' => 0,
				'type'    => 'number',
			),
			array(
				'title'   => __( 'Quantity step', 'wc-min-max-quantities' ),
				'desc'    => __( 'Each time the quantity is changed, it will be increased or decreased by this value. Keep it blank if you don’t want to set any rule for this.', 'wc-min-max-quantities' ),
				'id'      => 'wcmmq_step',
				'default' => 0,
				'type'    => 'number',
			),
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
		);

		/**
		 * Filter the general settings fields.
		 *
		 * Preserved from the pre-b8 release so the Pro add-on can inject fields.
		 *
		 * @since 1.1.4
		 * @param array<int, array<string, mixed>> $fields The general settings fields.
		 */
		$fields = apply_filters( 'wc_min_max_quantities_general_settings', $fields );

		$settings['general'] = array(
			'title'  => __( 'General', 'wc-min-max-quantities' ),
			'fields' => $fields,
		);

		/**
		 * Filter the full settings definition.
		 *
		 * Preserved from the pre-b8 release for the Pro add-on (which adds its own tabs).
		 *
		 * @since 1.1.4
		 * @param array<string, mixed> $settings The settings definition keyed by tab.
		 */
		return apply_filters( 'wc_min_max_quantities_settings', $settings );
	}

	/**
	 * Output the settings fields through WooCommerce so the `wcmmq_*` option keys persist.
	 *
	 * @since 2.3.0
	 * @param array<int, array<string, mixed>> $fields Prepared field declarations.
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
	 * Persist the submitted settings fields through WooCommerce.
	 *
	 * @since 2.3.0
	 * @param array<int, array<string, mixed>> $fields Field declarations for the current tab.
	 * @param array<string, mixed>             $data   Unslashed request data.
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
	 * Output the settings sidebar with the premium upsell.
	 *
	 * @since 2.3.0
	 * @return void
	 */
	protected function render_sidebar(): void {
		if ( $this->app->is_pro_active() ) {
			return;
		}

		$features = array(
			__( 'Set restrictions for each product individually.', 'wc-min-max-quantities' ),
			__( 'Set restrictions for each product variation.', 'wc-min-max-quantities' ),
			__( 'Set restrictions for all products from a category.', 'wc-min-max-quantities' ),
			__( 'Set restrictions based on product categories.', 'wc-min-max-quantities' ),
			__( 'Set restrictions for the order total.', 'wc-min-max-quantities' ),
			__( 'Set restrictions based on the user role.', 'wc-min-max-quantities' ),
			__( 'Allow your vendors to set their own minimum and maximum restrictions. Supports MultiVendorX and WCFM Marketplace.', 'wc-min-max-quantities' ),
		);
		?>
		<div class="b8-card promo-panel">
			<div class="b8-card__header">
				<h3><?php esc_html_e( 'Premium Features', 'wc-min-max-quantities' ); ?></h3>
			</div>
			<div class="b8-card__body">
				<ul>
					<?php foreach ( $features as $feature ) : ?>
						<li>- <?php echo esc_html( $feature ); ?></li>
					<?php endforeach; ?>
				</ul>
				<a href="https://pluginever.com/plugins/woocommerce-min-max-quantities-pro/?utm_source=plugin-settings&utm_medium=banner&utm_campaign=upgrade&utm_id=wc-min-max-quantities" target="_blank" class="button"><?php esc_html_e( 'Get Premium', 'wc-min-max-quantities' ); ?></a>
			</div>
		</div>
		<?php
	}
}
