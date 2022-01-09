<?php
/**
 * Handles WooCommerce admin related functionalities.
 *
 * @version  1.1.0
 * @since    1.1.0
 * @package  WC_Min_Max_Quantities\WC
 */

namespace WC_Min_Max_Quantities\WC;

defined( 'ABSPATH' ) || exit();

/**
 * Class Admin_Manager.
 */
class Admin_Manager {

	/**
	 * Admin_Manager constructor.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function __construct() {
		add_action( 'woocommerce_product_options_general_product_data', array( __CLASS__, 'write_tab_options' ) );
		add_action( 'woocommerce_process_product_meta', [ __CLASS__, 'save_product_meta' ] );
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
		<div class="options_group wc-min-max-quantities-product-settings">
			<?php

			woocommerce_wp_checkbox(
				array(
					'id'          => '_wc_min_max_quantities_excluded',
					'label'       => __( 'Exclude Min/Max Rule', 'wc-min-max-quantities' ),
					'description' => __( 'By enabling, this product will be excluded from all min-max rules', 'wc-min-max-quantities' ),
				)
			);

			woocommerce_wp_checkbox(
				array(
					'id'          => '_wc_min_max_quantities_override',
					'label'       => __( 'Override Global', 'wc-min-max-quantities' ),
					'description' => __( 'Global Min/Max rules will be overridden by local settings if checked.', 'wc-min-max-quantities' ),
				)
			);

			do_action( 'wc_min_max_quantities_before_override_settings' );

			$settings = get_post_meta( $post->ID, '_wc_min_max_quantities_override', true );
			$css      = 'yes' === $settings ? '' : 'display:none;';
			echo '<div class="wc-min-max-override-settings" style="' . esc_attr( $css ) . '">';

			do_action( 'wc_min_max_quantities_override_settings_top' );

			woocommerce_wp_text_input(
				array(
					'id'                => '_wc_min_max_quantities_min_qty',
					'label'             => __( 'Minimum quantity', 'wc-min-max-quantities' ),
					'description'       => __( 'Set a minimum quantity customers are required to purchase for this product.', 'wc-min-max-quantities' ),
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
					'id'                => '_wc_min_max_quantities_max_qty',
					'label'             => __( 'Maximum quantity', 'wc-min-max-quantities' ),
					'description'       => __( 'Set a maximum quantity customers are allowed to purchase for this product.', 'wc-min-max-quantities' ),
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
					'id'          => '_wc_min_max_quantities_step',
					'label'       => __( 'Quantity groups of', 'wc-min-max-quantities' ),
					'description' => __( 'Enter a number that will be counted every time a quantity is increased for this product.', 'wc-min-max-quantities' ),
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
				$( '._wc_min_max_quantities_override_field' ).on( 'change', '#_wc_min_max_quantities_override', function() {
					var wrapper  = $( this ).closest( 'div' ).find( '.wc-min-max-override-settings' );
					if( $( this ).is(':checked') ){
						wrapper.show();
					}else{
						wrapper.hide();
					}
				});

				$( '._wc_min_max_quantities_override_field #_wc_min_max_quantities_override' ).trigger( 'change' );
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

	/**
	 * Save meta fields.
	 *
	 * @param int $post_id product ID.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public static function save_product_meta( $post_id ) {
		$product        = wc_get_product( $post_id );
		$numeric_fields = [
			'_wc_min_max_quantities_min_qty',
			'_wc_min_max_quantities_max_qty',
			'_wc_min_max_quantities_step',
		];
		foreach ( $numeric_fields as $numeric_field ) {
			$value = filter_input( INPUT_POST, $numeric_field, FILTER_SANITIZE_NUMBER_FLOAT );
			$product->update_meta_data( $numeric_field, (float) $value );
		}

		$check_fields = [
			'_wc_min_max_quantities_excluded',
			'_wc_min_max_quantities_override',
		];
		foreach ( $check_fields as $check_field ) {
			$value = filter_input( INPUT_POST, $check_field, FILTER_SANITIZE_STRING );
			$product->update_meta_data( $check_field, empty( $value ) ? 'no' : 'yes' );
		}

		$product->save();
	}
}
