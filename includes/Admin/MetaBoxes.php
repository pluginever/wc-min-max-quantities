<?php

namespace WooCommerceMinMaxQuantities\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Class MetaBoxes
 *
 * @package WooCommerceMinMaxQuantities\Admin
 */
class MetaBoxes {

	/**
	 * MetaBoxes constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_product_options_general_product_data', array( __CLASS__, 'write_tab_options' ) );
	}

	/**
	 * Add tab content in product edit page
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public static function write_tab_options() {
		global $post;
		?>
		<div class="options_group wcmmq-product-settings">
			<?php
			woocommerce_wp_checkbox(
				array(
					'id'          => '_wcmmq_disable',
					'label'       => __( 'Exclude Min/Max Rule', 'wc-min-max-quantities' ),
					'description' => __( 'Exclude this product from all Min/Max rules.', 'wc-min-max-quantities' ),
				)
			);
			woocommerce_wp_checkbox(
				array(
					'id'          => '_wcmmq_enable',
					'label'       => __( 'Override Global', 'wc-min-max-quantities' ),
					'description' => __( 'Global Min/Max rules will be overridden by local settings if checked.', 'wc-min-max-quantities' ),
				)
			);

			do_action( 'wc_min_max_quantities_before_override_settings' );

			$settings = get_post_meta( $post->ID, '_wcmmq_enable', true );
			$css      = 'yes' === $settings ? '' : 'display:none;';
			echo '<div class="wcmmq-override-settings" style="' . esc_attr( $css ) . '">';

			do_action( 'wc_min_max_quantities_override_settings_top' );

			woocommerce_wp_text_input(
				array(
					'id'                => '_wcmmq_min_qty',
					'label'             => __( 'Minimum quantity', 'wc-min-max-quantities' ),
					'description'       => __( 'Set an allowed minimum number of items customers can purchase for this product. For no restrictions, set 0.', 'wc-min-max-quantities' ),
					'desc_tip'          => true,
					'type'              => 'number',
					'custom_attributes' => array(
						'step' => 'any',
						'min'  => '0',
					),
				)
			);

			woocommerce_wp_text_input(
				array(
					'id'                => '_wcmmq_max_qty',
					'label'             => __( 'Maximum quantity', 'wc-min-max-quantities' ),
					'description'       => __( 'Set an allowed maximum number of items customers can purchase for this product. For no restrictions, set 0.', 'wc-min-max-quantities' ),
					'desc_tip'          => true,
					'type'              => 'number',
					'custom_attributes' => array(
						'step' => 'any',
						'min'  => '0',
					),
				)
			);

			woocommerce_wp_text_input(
				array(
					'id'          => '_wcmmq_step',
					'label'       => __( 'Quantity step', 'wc-min-max-quantities' ),
					'description' => __( 'Enter a number that will increment or decrement every time a quantity is changed for this product.', 'wc-min-max-quantities' ),
					'desc_tip'    => true,
					'type'        => 'number',
					'min'         => '0',
				)
			);

			do_action( 'wc_min_max_quantities_override_settings_bottom' );

			echo '</div>';

			do_action( 'wc_min_max_quantities_after_override_settings' );

			$js = "
			jQuery( function( $ ) {
				$( '.wcmmq-product-settings' ).on( 'change', '#_wcmmq_enable', function() {
					var wrapper  = $( this ).closest( 'div' ).find( '.wcmmq-override-settings' );
					if( $( this ).is(':checked') ){
						wrapper.show();
					}else{
						wrapper.hide();
					}
				});

				$( '.wcmmq-product-settings #_wcmmq_enable' ).trigger( 'change' );
			});
		";

			if ( function_exists( 'wc_enqueue_js' ) ) {
				wc_enqueue_js( $js );
			} else {
				WC()->add_inline_js( $js );
			}

			?>
		</div>
		<?php
	}
}
