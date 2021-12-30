<?php
/**
 * Handles WooCommerce meta box related functionalities.
 *
 * @since    1.1.0
 * @package  WC_Min_Max_Quantities\WC
 */

namespace WC_Min_Max_Quantities\WC;

defined( 'ABSPATH' ) || exit();

/**
 * Class Metabox_Manager.
 */
class Metabox_Manager {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_box' ) );
		add_action( 'save_post', array( __CLASS__, 'save_meta_box' ) );
	}

	/**
	 * Add tab content in product edit page
	 *
	 * @return void
	 * @since  1.0.0
	 */
	public static function write_tab_options() {
		global $post;
		?>
		<div class="options_group wc-min-max-quantities-product-settings">
			<?php
			do_action( 'wc_min_max_quantities_data_panel_top' );

			woocommerce_wp_checkbox(
				array(
					'id'          => '_minmax_quantities_exclude',
					'label'       => __( 'Ignore min/max rules', 'wc-min-max-quantities' ),
					'description' => __( 'Do not apply any of the min max restrictions to this product.', 'wc-min-max-quantities' ),
				)
			);

			woocommerce_wp_checkbox(
				array(
					'id'          => '_minmax_quantities_override',
					'label'       => esc_html__( 'Override global', 'wc-min-max-quantities' ),
					'description' => esc_html__( 'Global settings will be overridden by per product settings. Set zero for no restrictions.', 'wc-min-max-quantities' ),
				)
			);

			woocommerce_wp_text_input(
				array(
					'id'                => '_minmax_quantities_min_qty',
					'label'             => __( 'Minimum quantity', 'wc-min-max-quantities' ),
					'description'       => __( 'Enter a quantity to prevent the user buying this product if they have fewer than the allowed quantity in their cart.', 'wc-min-max-quantities' ),
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
					'id'                => '_minmax_quantities_max_qty',
					'label'             => __( 'Maximum quantity', 'wc-min-max-quantities' ),
					'description'       => __( 'Enter a quantity to prevent the user buying this product if they have more than the allowed quantity in their cart.', 'wc-min-max-quantities' ),
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
					'id'          => '_minmax_quantities_step',
					'label'       => __( 'Quantity groups of', 'wc-min-max-quantities' ),
					'description' => __( 'Enter a quantity to only allow this product to be purchased in groups of X.', 'wc-min-max-quantities' ),
					'desc_tip'    => true,
					'type'        => 'number',
					'min'         => '0',
				)
			);

			do_action( 'wc_min_max_quantities_data_panel_bottom' );
			?>
		</div>
		<?php
	}

	/**
	 * Save meta fields.
	 *
	 * @param int $post_id product ID.
	 *
	 * @return void
	 * @since 1.1.0
	 */
	public static function save_product_meta( $post_id ) {
		$product        = wc_get_product( $post_id );
		$numeric_fields = [
			'_minmax_quantities_min_qty',
			'_minmax_quantities_max_qty',
			'_minmax_quantities_step',
		];
		foreach ( $numeric_fields as $numeric_field ) {
			$value = filter_input( INPUT_POST, $numeric_field, FILTER_SANITIZE_NUMBER_FLOAT );
			$product->update_meta_data( $numeric_field, (float) $value );
		}
		$boolean_fields = [
			'_minmax_quantities_exclude',
			'_minmax_quantities_override',
		];
		foreach ( $boolean_fields as $boolean_field ) {
			$value = filter_input( INPUT_POST, $boolean_field, FILTER_SANITIZE_STRING );
			$product->update_meta_data( $boolean_field, 'yes' === $value ? 'yes' : 'no' );
		}
		$product->save();
	}
}
