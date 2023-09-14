<?php

namespace WooCommerceMinMaxQuantities\Admin;

use WooCommerceMinMaxQuantities\Lib;

defined( 'ABSPATH' ) || exit;

/**
 * Class Settings.
 *
 * @since   1.1.4
 * @package WooCommerceMinMaxQuantities\Admin
 */
class Settings extends Lib\Settings {
	/**
	 * Get settings tabs.
	 *
	 * @since 1.1.4
	 * @return array
	 */
	public function get_tabs() {
		$tabs = array(
			'general'      => __( 'General', 'wc-min-max-quantities' ),
		);

		return apply_filters( 'wc_min_max_quantities_settings_tabs', $tabs );
	}

	/**
	 * Get settings.
	 *
	 * @param string $tab Current tab.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_settings( $tab ) {
		$settings = array();
		switch ( $tab ) {
			case 'general':
				$settings = array(
					array(
						// Product restrictions section.
						'title' => __( 'Product restrictions', 'wc-min-max-quantities' ),
						'type'  => 'title',
						'id'    => 'wcmmq_product_restrictions',
						'desc'  => __( 'Set the minimum and maximum restrictions for products. Restrictions will be applied to every product individually.', 'wc-min-max-quantities' ),
					),
					// set the minimum quantity.
					array(
						'title'   => __( 'Minimum quantity', 'wc-min-max-quantities' ),
						'desc'    => __( 'Set an allowed minimum quantity for each product. Leave empty to disable.', 'wc-min-max-quantities' ),
						'id'      => 'wcmmq_min_qty',
						'default' => 0,
						'type'    => 'number',
					),
					// set the maximum quantity.
					array(
						'title'   => __( 'Maximum quantity', 'wc-min-max-quantities' ),
						'desc'    => __( 'Set an allowed maximum quantity for each product. Leave empty to disable.', 'wc-min-max-quantities' ),
						'id'      => 'wcmmq_max_qty',
						'default' => 0,
						'type'    => 'number',
					),
					// Quantity step.
					array(
						'title'   => __( 'Quantity step', 'wc-min-max-quantities' ),
						'desc'    => __( 'Each time the quantity is changed, it will be increased or decreased by this value. Leave empty to disable.', 'wc-min-max-quantities' ),
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
						'title' => esc_html__( 'Cart restrictions', 'wc-min-max-quantities' ),
						'type'  => 'title',
						'id'    => 'wcmmq_order_restrictions',
						'desc'  => __( 'Set the minimum and maximum restrictions for the order. Restrictions will be applied to the order total.', 'wc-min-max-quantities' ),
					),
					array(
						'title'    => esc_html__( 'Minimum quantity', 'wc-min-max-quantities' ),
						'desc'     => __( 'Set an allowed minimum quantity for the order. Leave empty to disable.', 'wc-min-max-quantities' ),
						'desc_tip' => __( 'This will be calculated by adding the quantity of all products in the cart.', 'wc-min-max-quantities' ),
						'id'       => 'wcmmq_min_cart_qty',
						'default'  => 0,
						'type'     => 'number',
					),
					array(
						'title'    => esc_html__( 'Maximum quantity', 'wc-min-max-quantities' ),
						'desc'     => __( 'Set an allowed maximum quantity for the order. Leave empty to disable.', 'wc-min-max-quantities' ),
						'desc_tip' => __( 'This will be calculated by adding the quantity of all products in the cart.', 'wc-min-max-quantities' ),
						'id'       => 'wcmmq_max_cart_qty',
						'default'  => 0,
						'type'     => 'number',
					),
					array(
						'title'    => esc_html__( 'Minimum total', 'wc-min-max-quantities' ),
						'desc'     => __( 'Set an allowed minimum order total. Leave empty to disable.', 'wc-min-max-quantities' ),
						'desc_tip' => __( 'This will be calculated by adding the total of all products in the cart before any discounts have been applied.', 'wc-min-max-quantities' ),
						'id'       => 'wcmmq_min_cart_total',
						'default'  => 0,
						'type'     => 'number',
					),
					array(
						'title'    => esc_html__( 'Maximum total', 'wc-min-max-quantities' ),
						'desc'     => __( 'Set an allowed maximum order amount. Leave empty to disable.', 'wc-min-max-quantities' ),
						'desc_tip' => __( 'This will be calculated by adding the total of all products in the cart before any discounts have been applied.', 'wc-min-max-quantities' ),
						'id'       => 'wcmmq_max_cart_total',
						'default'  => 0,
						'type'     => 'number',
					),
					array(
						'type' => 'sectionend',
						'id'   => 'wcmmq_order_restrictions',
					),
				);
				break;
		}

		/**
		 * Filter the settings for the plugin.
		 *
		 * @param array $settings The settings.
		 */
		$settings = apply_filters( 'wc_min_max_quantities_' . $tab . '_settings', $settings );

		/**
		 * Filter the settings for the plugin.
		 *
		 * @param array  $settings The settings.
		 * @param string $tab The current tab.
		 *
		 * @since 1.1.4
		 */
		return apply_filters( 'wc_min_max_quantities_settings', $settings, $tab );
	}

	/**
	 * Output premium widget.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function output_premium_widget() {
		// bail if premium is active.
		if ( wc_min_max_quantities()->is_premium_active() ) {
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

		?>
		<div class="pev-panel promo-panel">
			<h3><?php esc_html_e( 'Premium Features', 'wc-min-max-quantities' ); ?></h3>
			<ul>
				<?php foreach ( $features as $feature ) : ?>
					<li>- <?php echo esc_html( $feature ); ?></li>
				<?php endforeach; ?>
			</ul>
			<a href="https://pluginever.com/plugins/woocommerce-min-max-quantities?utm_source=plugin-settings&utm_medium=banner&utm_campaign=upgrade&utm_id=wc-min-max-quantities" target="_blank" class="button"><?php esc_html_e( 'Get Premium', 'wc-min-max-quantities' ); ?></a>
		</div>
		<?php
	}

	/**
	 * Output tabs.
	 *
	 * @param array $tabs Tabs.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function output_tabs( $tabs ) {
		parent::output_tabs( $tabs );
		if ( wc_min_max_quantities()->get_docs_url() ) {
			echo sprintf( '<a href="%s" class="nav-tab" target="_blank">%s</a>', esc_url( wc_min_max_quantities()->get_docs_url() ), esc_html__( 'Documentation', 'wc-min-max-quantities' ) );
		}
	}

	/**
	 * Output settings form.
	 *
	 * @param array $settings Settings.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function output_form( $settings ) {
		$current_tab = $this->get_current_tab();
		do_action( 'wc_min_max_quantities_settings_' . $current_tab );
		parent::output_form( $settings );
	}

}
