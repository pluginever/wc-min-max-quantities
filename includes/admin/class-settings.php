<?php
/**
 * MinMax Settings.
 *
 * @package WCMinMax
 */

defined( 'ABSPATH' ) || exit();

/**
 * Class Settings
 */
class WC_MINMAX_Settings {
	/**
	 * Stores all settings.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $settings = array();

	/**
	 * Settings constructor.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'init_settings' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'register_settings_page' ), 100 );
	}

	/**
	 * Initialize plugin settings.
	 *
	 * @return void
	 */
	public function init_settings() {
		$settings = array(
			'general'  => array(
				'title'    => __( 'General', 'wc-min-max-qunatities' ),
				'sections' => array(
					'main' => array(
						'title'  => __( 'General', 'wc-min-max-qunatities' ),
						'fields' => array(
							array(
								'title' => esc_html__( 'Cart Restriction', 'wc-min-max-qunatities' ),
								'type'  => 'section',
								'desc'  => esc_html__( 'The following options are for cart restrictions', 'wc-min-max-qunatities' ),
								'id'    => 'cart_restrictions',
							),
							array(
								'title'   => esc_html__( 'Minimum Cart Total Price', 'wc-min-max-qunatities' ),
								'id'      => 'min_cart_total_price',
								'desc'    => esc_html__( 'Enter an amount of Price to prevent  users from buying, if they have lower than the allowed price in their cart total.', 'wc-min-max-qunatities' ),
								'type'    => 'number',
								'default' => '0',
							),
							array(
								'title'   => esc_html__( 'Maximum Cart Total Price', 'wc-min-max-qunatities' ),
								'id'      => 'max_cart_total_price',
								'desc'    => esc_html__( 'Enter an amount of Price to prevent users from buying, if they have more than the allowed price in their cart total.', 'wc-min-max-qunatities' ),
								'type'    => 'number',
								'default' => '0',
							),
							array(
								'title'    => esc_html__( 'Minimum Cart Quantity', 'wc-min-max-qunatities' ),
								'id'       => 'min_cart_total_quantity',
								'desc'     => esc_html__( 'Enter a quantity to prevent  user from buying this product if they have fewer than the allowed total quantity in their cart.', 'wc-min-max-qunatities' ),
								'type'     => 'number',
								'min'      => 0,
								'disabled' => true,
							),
							array(
								'title'    => esc_html__( 'Maximum Cart Quantity', 'wc-min-max-qunatities' ),
								'id'       => 'max_cart_total_quantity',
								'desc'     => esc_html__( 'Enter a quantity to prevent  user from buying this product if they have more than the allowed total quantity in their cart.', 'wc-min-max-qunatities' ),
								'type'     => 'number',
								'min'      => 0,
								'disabled' => true,
							),
							array(
								'title' => esc_html__( 'Other Settings', 'wc-min-max-qunatities' ),
								'type'  => 'section',
								'id'    => 'other_settings',
							),
							array(
								'title'   => esc_html__( 'Hide Checkout Button', 'wc-min-max-qunatities' ),
								'id'      => 'hide_checkout',
								'desc'    => esc_html__( 'Hide checkout button if Min/Max condition not passed.', 'wc-min-max-qunatities' ),
								'type'    => 'checkbox',
								'default' => 'yes',
							),
							array(
								'title'    => esc_html__( 'Force Minimum Quantity', 'wc-min-max-qunatities' ),
								'id'       => 'force_add_minimum_quantity',
								'desc'     => esc_html__( 'Force to add minimum quantity in product cart', 'wc-min-max-qunatities' ),
								'type'     => 'checkbox',
								'disabled' => true,
							),
							array(
								'title'    => esc_html__( 'Prevent Add to Cart', 'wc-min-max-qunatities' ),
								'id'       => 'prevent_add_to_cart',
								'desc'     => esc_html__( 'Prevent add product in cart when reach the product quantity/price maximum limit', 'wc-min-max-qunatities' ),
								'type'     => 'checkbox',
								'disabled' => true,
							),
							array(
								'title'    => esc_html__( 'Remove Item from Checkout', 'wc-min-max-qunatities' ),
								'id'       => 'remove_item_checkout',
								'desc'     => esc_html__( 'Enable option for remove item from checkout page', 'wc-min-max-qunatities' ),
								'type'     => 'checkbox',
								'disabled' => true,
							),
							array(
								'title' => esc_html__( 'Product Restrictions', 'wc-min-max-qunatities' ),
								'type'  => 'section',
								'desc'  => esc_html__( 'The following options are for adding minimum maximum rules for products globally', 'wc-min-max-qunatities' ),
								'id'    => 'section_product_restrictions',
							),
							array(
								'title'   => esc_html__( 'Minimum Product Quantity', 'wc-min-max-qunatities' ),
								'id'      => 'min_product_quantity',
								'desc'    => esc_html__( 'Enter a quantity to prevent  user from buying this product if they have fewer than the allowed quantity in their cart.', 'wc-min-max-qunatities' ),
								'type'    => 'number',
								'default' => '0',
							),
							array(
								'title'   => esc_html__( 'Maximum Product Quantity', 'wc-min-max-qunatities' ),
								'id'      => 'max_product_quantity',
								'desc'    => esc_html__( 'Enter a quantity to prevent  user from buying this product if they have more than the allowed quantity in their cart.', 'wc-min-max-qunatities' ),
								'type'    => 'number',
								'default' => '0',
							),
							array(
								'title'   => esc_html__( 'Minimum Product Price', 'wc-min-max-qunatities' ),
								'id'      => 'min_product_price',
								'desc'    => esc_html__( 'Enter an amount of price to prevent  users from buying, if they have lower than the allowed product price in their cart.', 'wc-min-max-qunatities' ),
								'type'    => 'number',
								'default' => '0',
							),
							array(
								'title'   => esc_html__( 'Maximum Product Price', 'wc-min-max-qunatities' ),
								'id'      => 'max_product_price',
								'desc'    => esc_html__( 'Enter an amount of Price to prevent users from buying, if they have more than the allowed product price in their cart.', 'wc-min-max-qunatities' ),
								'type'    => 'number',
								'default' => '0',
							),
						),
					),
				),
			),
			'template' => array(
				'title'    => esc_html__( 'Template', 'wc-min-max-qunatities' ),
				'sections' => array(
					'main' => array(
						'title'  => esc_html__( 'Template', 'wc-min-max-qunatities' ),
						'fields' => array(
							array(
								'title' => esc_html__( 'Translate Settings', 'wc-min-max-qunatities' ),
								'type'  => 'section',
								'id'    => 'section_translate_settings',
							),
							array(
								'title'       => esc_html__( 'Minimum Product Quantity Error Message', 'wc-min-max-qunatities' ),
								'id'          => 'min_product_quantity_error_message',
								'desc'        => esc_html__( 'Must use {min_qty} and {product_name} to display minimum order quantity and product name respectively.', 'wc-min-max-qunatities' ),
								'type'        => 'text',
								'placeholder' => esc_html__( 'You have to buy at least {min_qty} quantities of {product_name}.', 'wc-min-max-qunatities' ),
							),
							array(
								'title'       => esc_html__( 'Minimum Product Price Error Message', 'wc-min-max-qunatities' ),
								'id'          => 'min_order_price_error_message',
								'desc'        => esc_html__( 'Must use {min_price} and {product_name} to display minimum order price and product name respectively.', 'wc-min-max-qunatities' ),
								'type'        => 'text',
								'placeholder' => esc_html__( 'Minimum total price should be {min_price} or more for {product_name}.', 'wc-min-max-qunatities' ),
							),
							array(
								'title'       => esc_html__( 'Maximum Product Price Error Message', 'wc-min-max-qunatities' ),
								'id'          => 'max_order_price_error_message',
								'desc'        => esc_html__( 'Must use {max_price} and {product_name} to display maximum order price and product name respectively.', 'wc-min-max-qunatities' ),
								'type'        => 'text',
								'placeholder' => esc_html__( 'Maximum total price can not be more than {max_price} for {product_name}.', 'wc-min-max-qunatities' ),
							),
							array(
								'title'       => esc_html__( 'Minimum Cart Total Error Message', 'wc-min-max-qunatities' ),
								'id'          => 'min_cart_total_error_message',
								'desc'        => esc_html__( 'Must use {min_cart_total_price} to display minimum cart total price', 'wc-min-max-qunatities' ),
								'type'        => 'text',
								'placeholder' => esc_html__( 'Minimum cart total price should be {min_cart_total_price} or more', 'wc-min-max-qunatities' ),
							),
							array(
								'title'       => esc_html__( 'Maximum Cart Total Error Message', 'wc-min-max-qunatities' ),
								'id'          => 'max_cart_total_error_message',
								'desc'        => esc_html__( 'Must use {max_cart_total_price} to display maximum cart total price', 'wc-min-max-qunatities' ),
								'type'        => 'text',
								'placeholder' => esc_html__( 'Maximum cart total price can not be more than {max_cart_total_price}', 'wc-min-max-qunatities' ),
							),
							array(
								'title'       => esc_html__( 'Minimum Cart Quantity Error Message', 'wc-min-max-qunatities' ),
								'id'          => 'min_cart_quantity_error_message',
								'desc'        => esc_html__( 'Must use {min_cart_qty} to display minimum cart quantity', 'wc-min-max-qunatities' ),
								'type'        => 'text',
								'placeholder' => esc_html__( 'Minimum cart quantity should be {min_cart_qty} or more', 'wc-min-max-qunatities' ),
								'disabled'    => true,
							),
							array(
								'title'       => esc_html__( 'Maximum Cart Quantity Error Message', 'wc-min-max-qunatities' ),
								'id'          => 'max_cart_quantity_error_message',
								'desc'        => esc_html__( 'Must use {max_cart_qty} to display maximum cart quantity', 'wc-min-max-qunatities' ),
								'type'        => 'text',
								'placeholder' => esc_html__( 'Maximum cart total item can not be more than {max_cart_qty}', 'wc-min-max-qunatities' ),
								'disabled'    => true,
							),
						),
					),
				),
			),
			'help'     => array(
				'title'    => esc_html__( 'Help', 'wc-min-max-qunatities' ),
				'sections' => array(
					'main' => array(
						'title'  => esc_html__( 'Help', 'wc-min-max-qunatities' ),
						'fields' => apply_filters( 'wc_minmax_quantities_help_settings_fields', array() ),
					),
				),
			),
		);

		if ( wc_minmax_quantities()::is_pro_installed() ) {
			$settings['template']['sections']['purchase_rule_settings'] = array(
				'title'  => esc_html__( 'Purchase Rules', 'wc-min-max-qunatities' ),
				'fields' => array(
					array(
						'title' => esc_html__( 'Purchase Rules', 'wc-min-max-qunatities' ),
						'type'  => 'section',
						'desc'  => esc_html__( 'The following options are for show purchase rules in product details page', 'wc-min-max-qunatities' ),
						'id'    => 'purchase_rules_show',
					),
					array(
						'title' => esc_html__( 'Show Purchase rules in Product page', 'wc-min-max-qunatities' ),
						'id'    => 'wc_minmax_quantities_show_purchase_rules',
						'desc'  => esc_html__( 'Enable option to show purchase rules in product page', 'wc-min-max-qunatities' ),
						'type'  => 'checkbox',
					),
					array(
						'title'   => esc_html__( 'Position in Product page', 'wc-min-max-qunatities' ),
						'id'      => 'wc_minmax_quantities_purchase_rule_position',
						'desc'    => esc_html__( 'Set the position in product details page where showing rules', 'wc-min-max-qunatities' ),
						'type'    => 'select',
						'options' => array(
							'before_title'       => esc_html__( 'Before Title', 'wc-min-max-qunatities' ),
							'after_price'        => esc_html__( 'After Price', 'wc-min-max-qunatities' ),
							'before_add_to_cart' => esc_html__( 'Before Add to Cart', 'wc-min-max-qunatities' ),
							'before_tabs'        => esc_html__( 'Before Tabs', 'wc-min-max-qunatities' ),
						),
					),
				),
			);
		}

		$this->settings = apply_filters( 'wc_minmax_quantities_setting_fields', $settings );
	}

	/**
	 * Add all settings sections and fields
	 *
	 * @return void
	 * @since 1.0.2
	 */
	public function register_settings() {
		$whitelisted = array();
		foreach ( $this->settings as $tab => $setting ) {
			if ( ! empty( $setting['fields'] ) ) {
				$setting['sections']['']['fields'] = $setting['fields'];
			}
			// Bail if no sections.
			if ( empty( $setting['sections'] ) ) {
				continue;
			}

			foreach ( $setting['sections'] as $section_id => $section ) {
				// Bail if no fields.
				if ( empty( $section['fields'] ) ) {
					continue;
				}

				add_settings_section(
					$section_id,
					__return_null(),
					'__return_false',
					'wc_minmax_settings_' . $tab . '_' . $section_id
				);

				foreach ( $section['fields'] as $field ) {
					// Bail if no fields.
					if ( empty( $field['id'] ) ) {
						continue;
					}
					// Restrict duplicate.
					if ( in_array( $field['id'], $whitelisted, true ) ) {
						continue;
					}

					$args = wp_parse_args(
						$field,
						array(
							'id'          => $field['id'],
							'title'       => '',
							'type'        => 'text',
							'desc'        => '',
							'tooltip'     => '',
							'size'        => 'regular',
							'options'     => array(),
							'default'     => '',
							'min'         => null,
							'max'         => null,
							'step'        => null,
							'multiple'    => null,
							'placeholder' => null,
							'required'    => '',
							'disabled'    => '',
							'input_class' => '',
							'class'       => '',
							'callback'    => '',
							'style'       => '',
							'html'        => '',
							'attrs'       => array(),
							'args'        => array(),
						)
					);

					$tooltip = wp_kses(
						html_entity_decode( $args['tooltip'] ),
						array(
							'br'     => array(),
							'em'     => array(),
							'strong' => array(),
							'small'  => array(),
							'span'   => array(),
							'ul'     => array(),
							'li'     => array(),
							'ol'     => array(),
							'p'      => array(),
						)
					);

					if ( ! in_array(
						$args['type'],
						array(
							'checkbox',
							'multicheck',
							'radio',
						),
						true
					) && ! empty( $tooltip ) ) {
						$args['title']     = sprintf( '%s<span class="wc_minmax-help-tip" title="%s"></span>', $args['title'], $tooltip );
						$args['label_for'] = $args['id'];
					}
					if ( 'section' === $args['type'] && ! empty( $args['title'] ) ) {
						$args['title'] = sprintf( '<h3>%s</h3>', $args['title'] );
					}

					$callback = ! empty( $args['callback'] ) ? $args['callback'] : array( $this, 'render_field' );
					add_settings_field(
						$args['id'],
						$args['title'],
						is_callable( $callback ) ? $callback : '__return_false',
						'wc_minmax_settings_' . $tab . '_' . $section_id,
						$section_id,
						$args
					);

				}
			}
		}

		register_setting( 'wc_minmax_settings', 'wc_minmax_settings', array( $this, 'sanitize_settings' ) );
	}

	/**
	 * @param $field
	 */
	public function render_field( $field ) {
		// Custom attribute handling.
		$attributes = array();
		$attrs      = array( 'min', 'max', 'step', 'multiple', 'placeholder', 'required', 'disabled' );
		foreach ( $attrs as $key ) {
			if ( ! empty( $field[ $key ] ) ) {
				$field['attrs'][ $key ] = esc_attr( $field[ $key ] );
			}
		}
		if ( ! empty( $field['attrs'] ) && is_array( $field['attrs'] ) ) {
			foreach ( $field['attrs'] as $attribute => $attribute_value ) {
				$attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}
		$description = '';
		if ( ! empty( $field['desc'] ) ) {
			if ( in_array( $field['type'], array( 'textarea', 'radio' ), true ) ) {
				$description = '<p style="margin-top:0">' . wp_kses_post( $field['desc'] ) . '</p>';
			} elseif ( $field['type'] === 'checkbox' ) {
				$description = wp_kses_post( $field['desc'] );
			} else {
				$description = '<p class="description">' . wp_kses_post( $field['desc'] ) . '</p>';
			}
		}
		$option = get_option( 'wc_minmax_settings', [] );
		$value  = ! empty( $option[ $field['id'] ] ) ? $option[ $field['id'] ] : $field['default'];
		// Switch based on type.
		switch ( $field['type'] ) {
			// Standard text inputs and subtypes like 'number'.
			case 'text':
			case 'password':
			case 'datetime':
			case 'date':
			case 'month':
			case 'time':
			case 'week':
			case 'number':
			case 'email':
			case 'url':
			case 'tel':
				?>
				<input name="wc_minmax_settings[<?php echo esc_attr( $field['id'] ); ?>]" id="<?php echo esc_attr( $field['id'] ); ?>" type="<?php echo esc_attr( $field['type'] ); ?>"
					   style="<?php echo esc_attr( $field['style'] ); ?>" value="<?php echo esc_attr( wp_unslash( $value ) ); ?>"
					   class="<?php echo esc_attr( sprintf( '%s-text %s', $field['size'], $field['input_class'] ) ); ?>"
					<?php echo implode( ' ', $attributes ); ?>/>
				<?php echo $description; ?>
				<?php
				break;
			case 'textarea':
				echo $description;
				?>
				<textarea name="wc_minmax_settings[<?php echo esc_attr( $field['id'] ); ?>]"
						  id="<?php echo esc_attr( $field['id'] ); ?>"
						  style="<?php echo esc_attr( $field['style'] ); ?>"
						  class="<?php echo esc_attr( sprintf( '%s-text %s', $field['size'], $field['input_class'] ) ); ?>"
					<?php echo implode( ' ', $attributes ); ?>><?php echo esc_textarea( wp_unslash( $value ) ); ?></textarea>
				<?php
				break;
			case 'select':
				?>
				<select
						name="wc_minmax_settings[<?php echo esc_attr( $field['id'] ); ?><?php echo ( 'multiselect' === $field['type'] ) ? '[]' : ''; ?>]"
						id="<?php echo esc_attr( $field['id'] ); ?>"
						style="<?php echo esc_attr( $field['style'] ); ?>"
						class="<?php echo esc_attr( sprintf( '%s-text %s', $field['size'], $field['input_class'] ) ); ?>"
					<?php echo implode( ' ', $attributes ); ?>
				>
					<?php
					foreach ( $field['options'] as $key => $val ) {
						?>
						<option value="<?php echo esc_attr( $key ); ?>"
							<?php
							if ( is_array( $value ) ) {
								selected( in_array( (string) $key, $value, true ), true );
							} else {
								selected( $value, (string) $key );
							}
							?>
						><?php echo esc_html( $val ); ?></option>
						<?php
					}
					?>
				</select> <?php echo $description; ?>
				<?php
				break;
			case 'checkbox':
				?>
				<label for="<?php echo esc_attr( $field['id'] ); ?>">
					<input
							name="wc_minmax_settings[<?php echo esc_attr( $field['id'] ); ?>]"
							id="<?php echo esc_attr( $field['id'] ); ?>"
							type="checkbox"
							value="yes"
						<?php checked( $value, 'yes' ); ?>
						<?php echo implode( ' ', $attributes ); ?>
					/> <?php echo $description; ?>
				</label>
				<?php
				break;
			case 'multicheck':
				$value = ! is_array( $value ) ? array() : $value;
				$type  = 'multicheck' === $field['type'] ? 'checkbox' : $field['type'];
				?>
				<fieldset>
					<?php echo $description; ?>
					<ul>
						<?php
						foreach ( $field['options'] as $key => $option ) {
							$checked = isset( $value[ $key ] ) ? $value[ $key ] : 'no';
							?>
							<li>
								<label><input
											name="wc_minmax_settings[<?php echo esc_attr( $field['id'] ); ?>][<?php echo esc_attr( $key ); ?>]"
											value="yes"
											type="<?php echo esc_attr( $type ); ?>"
											style="<?php echo esc_attr( $field['style'] ); ?>"
											class="<?php echo esc_attr( $field['class'] ); ?>"
										<?php echo implode( ' ', $attributes ); ?>
										<?php checked( 'yes', $checked ); ?>
									/> <?php echo esc_html( $option ); ?></label>
							</li>
							<?php
						}
						?>
					</ul>
				</fieldset>
				<?php
				break;
			case 'radio':
				?>
				<fieldset>
					<?php echo $description; ?>
					<ul>
						<?php
						foreach ( $field['options'] as $key => $option ) {
							?>
							<li>
								<label><input
											name="wc_minmax_settings[<?php echo esc_attr( $field['id'] ); ?>]"
											value="<?php echo sanitize_key( $key ); ?>"
											type="radio"
											style="<?php echo esc_attr( $field['style'] ); ?>"
											class="<?php echo esc_attr( $field['class'] ); ?>"
										<?php echo implode( ' ', $attributes ); ?>
										<?php checked( $value, $key ); ?>
									/> <?php echo esc_html( $option ); ?></label>
							</li>
							<?php
						}
						?>
					</ul>
				</fieldset>
				<?php
				break;
			case 'wysiwyg':
				ob_start();
				wp_editor( stripslashes( $value ), 'wc_minmax_settings_' . $field['id'], array( 'textarea_name' => 'wc_minmax_settings[' . $field['id'] . ']' ) );
				echo ob_get_clean();

				break;

			case 'file_upload':
				?>
				<?php echo $description; ?>
				<input name="wc_minmax_settings[<?php echo esc_attr( $field['id'] ); ?>]"
					   id="<?php echo esc_attr( $field['id'] ); ?>"
					   type="text"
					   style="<?php echo esc_attr( $field['style'] ); ?>"
					   value="<?php echo esc_attr( wp_unslash( $value ) ); ?>"
					   class="<?php echo esc_attr( sprintf( '%s-text %s', $field['size'], $field['input_class'] ) ); ?>"
					<?php echo implode( ' ', $attributes ); ?>/>
				<span>&nbsp;
					<button type="button" class="wc_minmax_settings_upload_button button-secondary"><?php esc_html_e( 'Upload File', 'wc-min-max-qunatities' ); ?></button></span>
				<?php
				break;
			case 'html':
			case 'section':
				if ( ! empty( $field['desc'] ) ) {
					echo wp_kses_post( wpautop( wptexturize( $field['desc'] ) ) );
				}
				break;
			// Default: run an action.
			default:
				do_action( 'wc_minmax_settings_admin_field_' . $field['type'], $field );
				break;
		}
	}

	/**
	 * Retrieve the array of plugin settings
	 *
	 * @return array
	 * @since 1.0.2
	 */
	public function sanitize_settings( $input = array() ) {
		if ( empty( $_POST['_wp_http_referer'] ) ) {
			return $input;
		}
		parse_str( $_POST['_wp_http_referer'], $referrer );

		$fields          = array();
		$tabs            = array_keys( $this->settings );
		$current_tab     = isset( $referrer['tab'] ) && in_array( $referrer['tab'], $tabs, true ) ? sanitize_title( $referrer['tab'] ) : current( $tabs );
		$sections        = $this->settings[ $current_tab ];
		$sections        = ! empty( $sections['sections'] ) ? $sections['sections'] : array();
		$current_section = isset( $referrer['section'] ) && array_key_exists( $referrer['section'], $sections ) ? sanitize_title( $referrer['section'] ) : current( array_keys( $sections ) );

		if ( ! empty( $this->settings[ $current_tab ]['sections'][ $current_section ]['fields'] ) ) {
			$fields = $this->settings[ $current_tab ]['sections'][ $current_section ]['fields'];
		}

		$input = $input ? $input : array();
		foreach ( $fields as $field ) {
			if ( ! isset( $field['id'] ) || ! isset( $field['type'] ) ) {
				continue;
			}
			$raw_value = isset( $input[ $field['id'] ] ) ? wp_unslash( $input[ $field['id'] ] ) : null;
			$name      = $field['id'];
			// Format the value based on option type.
			switch ( $field['type'] ) {
				case 'checkbox':
					$value = ! empty( $raw_value ) ? 'yes' : 'no';
					break;
				case 'textarea':
					$value = wp_kses_post( trim( $raw_value ) );
					break;
				case 'multicheck':
					$raw_value      = ! is_array( $raw_value ) || empty( $raw_value ) ? array() : $raw_value;
					$allowed_values = empty( $field['options'] ) ? array() : array_map( 'strval', array_keys( $field['options'] ) );
					$value          = array();
					foreach ( $allowed_values as $allowed_value ) {
						$value[ $allowed_value ] = array_key_exists( $allowed_value, $raw_value ) ? 'yes' : 'no';
					}
					break;
				case 'select':
				default:
					$value = sanitize_text_field( $raw_value );
					break;
			}

			$sanitize_callback = isset( $field['sanitize_callback'] ) ? $field['sanitize_callback'] : false;
			if ( $sanitize_callback && is_callable( $sanitize_callback ) ) {
				$value = call_user_func( $sanitize_callback, $value );
			}

			/**
			 * Sanitize the value of an option.
			 *
			 * @since 1.1.3
			 */
			$value = apply_filters( 'wc_minmax_admin_settings_sanitize_option', $value, $field, $raw_value );

			/**
			 * Sanitize the value of an option by option name.
			 *
			 * @since 1.1.3
			 */
			$value = apply_filters( "wc_minmax_admin_settings_sanitize_option_$name", $value, $field, $raw_value );
			if ( is_null( $value ) ) {
				continue;
			}

			$input[ $name ] = $value;
		}

		$saved = get_option( 'wc_minmax_settings', array() );
		if ( ! is_array( $saved ) ) {
			$saved = array();
		}

		return array_merge( $saved, $input );
	}

	/**
	 * Registers the page.
	 */
	public function register_settings_page() {
		add_submenu_page(
			'options-general.php',
			__( 'WC Min Max Settings', 'wc-min-max-qunatities' ),
			__( 'WC Min Max Settings', 'wc-min-max-qunatities' ),
			'manage_options',
			'wc-minmax-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Displays the settings page.
	 *
	 * @return void
	 * @since 1.1.0
	 */
	public function render_settings_page() {
		$settings        = $this->settings;
		$tabs            = wp_list_pluck( $settings, 'title' );
		$tab             = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING );
		$section         = filter_input( INPUT_GET, 'section', FILTER_SANITIZE_STRING );
		$current_tab     = isset( $tab ) && array_key_exists( $tab, $tabs ) ? $tab : current( array_keys( $tabs ) );
		$sections        = array_key_exists( 'sections', $settings[ $current_tab ] ) && ! empty( $settings[ $current_tab ]['sections'] ) ? $settings[ $current_tab ]['sections'] : array();
		$sections        = wp_list_pluck( $sections, 'title' );
		$current_section = isset( $section ) && array_key_exists( $section, $sections ) ? sanitize_title( $section ) : current( array_keys( $sections ) );
		$menu_tabs       = apply_filters( 'wc_minmax_settings_menu_tabs', $tabs );
		foreach ( array_keys( $menu_tabs ) as $tab ) {
			if ( empty( $settings[ $tab ]['sections'] ) && empty( $settings[ $tab ]['fields'] ) && ! has_action( 'wc_minmax_settings_tab_' . $tab ) ) {
				unset( $tabs[ $tab ] );
			}
		}

		// Section have name but not in url then redirect
		if ( ! empty( $current_section ) && empty( $section ) ) {
			wp_safe_redirect( add_query_arg( [ 'section' => $current_section ] ) );
			exit();
		}

		$subsub_links = array();
		foreach ( $sections as $section_slug => $section_title ) {
			if ( empty( $settings[ $current_tab ]['sections'][ $current_section ]['fields'] ) && ! has_action( 'wc_minmax_settings_tab_' . $current_tab . '_' . $current_section . '_content' ) ) {
				unset( $sections[ $section_slug ] );
			}
			$link           = add_query_arg(
				array(
					'tab'     => $current_tab,
					'section' => $section_slug,
				)
			);
			$active         = ( $current_section === $section_slug ) ? 'current' : '';
			$subsub_links[] = sprintf( '<a href="%s" class="%s">%s</a>', esc_url( $link ), $active, esc_html( $section_title ) );
		}
		ob_start();
		?>
		<div class="wrap wc_minmax-settings">
			<h2><?php esc_html_e( 'WooCommerce Min Max Quantities - Settings', 'wc-min-max-qunatities' ); ?></h2>
			<?php if ( count( $menu_tabs ) > 1 ) : ?>
				<h2 class="nav-tab-wrapper wcminmax-tab-wrapper">
					<?php foreach ( $tabs as $tab_slug => $tab_title ) : ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-minmax-settings' . ( empty( $tab_slug ) ? '' : '&tab=' . $tab_slug ) ) ); ?>"
						   class="nav-tab <?php echo sanitize_html_class( ( $current_tab == $tab_slug ) ? 'nav-tab-active' : '' ); ?>">
							<?php echo esc_html( $tab_title ); ?>
						</a>
					<?php endforeach; ?>
				</h2>
			<?php endif; ?>
			<?php if ( count( $sections ) > 1 ) : ?>
				<ul class="subsubsub">
					<?php echo implode( ' | </li><li>', $subsub_links ); ?>
				</ul>
			<?php endif; ?>
			<br class="clear"/>
			<h1 class="screen-reader-text"><?php echo esc_html( $tabs[ $current_tab ] ); ?></h1>
			<?php do_action( 'wc_minmax_settings_page_before_' . $current_tab . '_' . $current_section . '_content' ); ?>
			<?php
			if ( has_action( 'wc_minmax_settings_tab_' . $current_tab ) ) {
				do_action( 'wc_minmax_settings_tab_' . $current_tab );
			} elseif ( has_action( 'wc_minmax_settings_tab_' . $current_tab . '_' . $current_section . '_content' ) ) {
				do_action( 'wc_minmax_settings_tab_' . $current_tab . '_' . $current_section . '_content' );
			} else {
				?>
				<form method="post" id="mainform" action="options.php" enctype="multipart/form-data">
					<?php settings_errors( 'wc_minmax_settings' ); ?>
					<?php settings_fields( 'wc_minmax_settings' ); ?>
					<?php do_settings_sections( "wc_minmax_settings_{$current_tab}_{$current_section}" ); ?>
					<?php if ( empty( $GLOBALS['hide_save_button'] ) ) : ?>
						<?php submit_button(); ?>
					<?php endif; ?>

				</form>
			<?php } ?>

		</div>
		<?php
		echo ob_get_clean();
	}
}

new WC_MINMAX_Settings();
