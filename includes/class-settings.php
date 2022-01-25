<?php
/**
 * Handles plugin settings page.
 *
 * @since    1.1.0
 * @package  WC_Min_Max_Quantities\Admin
 */

namespace WC_Min_Max_Quantities;

defined( 'ABSPATH' ) || exit();

/**
 * Settings class.
 */
class Settings {
	/**
	 * Settings key/identifier
	 *
	 * @since 1.1.0
	 * @var string
	 */
	protected $option_key;

	/**
	 * Class constructor.
	 *
	 */
	public function __construct() {
		$this->option_key = 'wc_min_max_quantities_settings';
		add_action( 'wc_min_max_quantities_output_settings', array( $this, 'output_settings' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Get settings tabs.
	 *
	 * @since 1.1.0
	 * @return array
	 */
	protected function get_tabs() {
		$tabs = array(
			'general'      => array(
				'title' => esc_html__( 'General', 'wc-min-max-quantities' ),
			),
			'translations' => array(
				'title' => esc_html__( 'Translations', 'wc-min-max-quantities' ),
			)
		);

		return apply_filters( 'wc_min_max_quantities_settings_tabs', $tabs );
	}

	/**
	 * Return settings fields
	 *
	 * @return array
	 */
	protected function get_fields() {
		$fields = array(
			'general'      => apply_filters( 'wc_min_max_quantities_general_settings_fields',
				array(
					array(
						'id'    => 'section_product_restrictions',
						'title' => esc_html__( 'Product Restrictions', 'wc-min-max-quantities' ),
						'type'  => 'section',
						'desc'  => esc_html__( 'The following options are for adding minimum maximum rules for products globally.', 'wc-min-max-quantities' ),
					),
					array(
						'title' => esc_html__( 'Product Restrictions', 'wc-min-max-quantities' ),
						'type'  => 'section',
						'desc'  => esc_html__( 'The following options are for adding minimum maximum rules for products globally.', 'wc-min-max-quantities' ),
						'id'    => 'section_product_restrictions',
					),
					array(
						'title'             => esc_html__( 'Minimum product quantity', 'wc-min-max-quantities' ),
						'id'                => 'min_product_quantity',
						'desc'              => esc_html__( 'Set an allowed minimum number of items for each product. For no restrictions, set 0.', 'wc-min-max-quantities' ),
						'type'              => 'number',
						'default'           => '0',
						'sanitize_callback' => 'floatval',
					),
					array(
						'title'             => esc_html__( 'Maximum product quantity', 'wc-min-max-quantities' ),
						'id'                => 'max_product_quantity',
						'desc'              => esc_html__( 'Set an allowed maximum number of items for each product. For no restrictions, set 0.', 'wc-min-max-quantities' ),
						'type'              => 'number',
						'default'           => '0',
						'sanitize_callback' => 'floatval',
					),
					array(
						'title'             => esc_html__( 'Quantity groups of', 'wc-min-max-quantities' ),
						'id'                => 'product_quantity_step',
						'desc'              => esc_html__( 'Enter a number that will increment or decrement every time a quantity is changed.', 'wc-min-max-quantities' ),
						'type'              => 'number',
						'default'           => '0',
						'sanitize_callback' => 'floatval',
					),
					array(
						'title' => esc_html__( 'Order Restriction', 'wc-min-max-quantities' ),
						'type'  => 'section',
						'desc'  => esc_html__( 'The following options can be applied to the cart only.', 'wc-min-max-quantities' ),
						'id'    => 'cart_restrictions',
					),
					array(
						'title'             => esc_html__( 'Minimum order quantity', 'wc-min-max-quantities' ),
						'id'                => 'min_order_quantity',
						'desc'              => esc_html__( 'Set an allowed minimum number of products customers can add to the cart. For no restrictions, set 0.', 'wc-min-max-quantities' ),
						'type'              => 'number',
						'default'           => '0',
						'sanitize_callback' => 'floatval',
					),
					array(
						'title'             => esc_html__( 'Maximum order quantity', 'wc-min-max-quantities' ),
						'id'                => 'max_order_quantity',
						'desc'              => esc_html__( 'Set an allowed maximum number of products customers can add to the cart. For no restrictions, set 0.', 'wc-min-max-quantities' ),
						'type'              => 'number',
						'min'               => 0,
						'default'           => '0',
						'sanitize_callback' => 'floatval',
					),
					array(
						'title'             => esc_html__( 'Minimum order amount', 'wc-min-max-quantities' ),
						'id'                => 'min_order_amount',
						'desc'              => esc_html__( 'Set an allowed minimum total order amount customers can add to the cart. For no restrictions, set 0.', 'wc-min-max-quantities' ),
						'type'              => 'number',
						'default'           => '0',
						'sanitize_callback' => 'floatval',
					),
					array(
						'title'             => esc_html__( 'Maximum order amount', 'wc-min-max-quantities' ),
						'id'                => 'max_order_amount',
						'desc'              => esc_html__( 'Set an allowed maximum total order amount customers can add to the cart. For no restrictions, set 0.', 'wc-min-max-quantities' ),
						'type'              => 'number',
						'default'           => '0',
						'sanitize_callback' => 'floatval',
					),
				)
			),
			'translations' => apply_filters( 'wc_min_max_quantities_translations_settings_fields', array() ),
		);

		/**
		 * Filter allows for modification of options fields
		 *
		 * @return array  Array of option fields
		 */
		return apply_filters( 'wc_min_max_quantities_settings_fields', $fields );
	}

	/**
	 * Output settings page.
	 */
	public static function output() {
		do_action( 'wc_min_max_quantities_output_settings' );
	}

	/**
	 * Output settings content.
	 *
	 * @since 1.1.0
	 */
	public function output_settings() {
		$tabs   = $this->get_tabs();
		$fields = $this->get_fields();
		ob_start();
		wp_enqueue_script( 'jquery' );
		self::output_style();
		foreach ( $tabs as $tab_id => $tab ) {
			if ( empty( $fields[ $tab_id ] ) || ( ! empty( $tab['callback'] ) && ! is_callable( $tab['callback'] ) ) ) {
				unset( $tabs[ $tab_id ] );
			}
		}
		?>
		<div id="wc-min-max-quantities-settings-wrap" class="wrap settings-wrap wcmmq-settings">
			<h1><?php echo get_admin_page_title() ?></h1>

			<?php if ( ! empty( $tabs ) && count( $tabs ) > 1 ) : ?>

				<nav class="nav-tab-wrapper">
					<?php foreach ( $tabs as $tab_id => $tab ) : ?>
						<a href="#<?php echo esc_attr( $tab_id ) ?>" class="nav-tab" id="<?php echo esc_attr( $tab_id ) ?>-tab"><?php echo esc_html( $tab['title'] ) ?></a>
					<?php endforeach; ?>
				</nav>

			<?php endif; ?>

			<div class="clear"></div>
			<?php settings_errors(); ?>

			<div class="settings-tabs">
				<?php foreach ( $tabs as $tab_id => $tab ) : ?>
					<div id="<?php echo esc_attr( $tab_id ); ?>" class="settings-tab" style="display: none;">
						<?php if ( ! empty( $tab['callback'] ) && is_callable( $tab['callback'] ) ) : ?>
							<?php call_user_func( $tab['callback'] ); ?>
						<?php else : ?>
							<form method="post" action="options.php" enctype="multipart/form-data">
								<?php settings_fields( 'wc_min_max_quantities_settings' ); ?>
								<input type="hidden" name="tab" value="<?php echo esc_attr( $tab_id ); ?>">
								<table class="form-table" role="presentation">
									<?php $this->do_settings_fields( $this->option_key, $tab_id ); ?>
								</table>
								<?php if ( ! isset( $tab['hide_save_button'] ) || ! $tab['hide_save_button'] ) : ?>
									<?php submit_button( '', 'primary large', $tab_id . '-submit' ); ?>
								<?php endif; ?>
							</form>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
		self::output_script();
		echo ob_get_clean();
	}

	/**
	 * Output script.
	 *
	 * @since 1.1.0
	 */
	protected static function output_script() {
		?>
		<script>
			jQuery(document).ready(function ($) {
				//Initiate Color Picker
				//$('.wp-color-picker').wpColorPicker();

				// Switches option sections
				$('.settings-tab').hide();
				var activetab = '';
				if (typeof (localStorage) != 'undefined') {
					activetab = localStorage.getItem("activetab");
				}

				//if url has section id as hash then set it as active or override the current local storage value
				if (window.location.hash) {
					activetab = window.location.hash;
					if (typeof (localStorage) != 'undefined') {
						localStorage.setItem("activetab", activetab);
					}
				}

				if (activetab !== '' && $(activetab).length) {
					$(activetab).fadeIn();
				} else {
					$('.settings-tab:first').fadeIn();
				}
				$('.settings-tab .collapsed').each(function () {
					$(this).find('input:checked').parent().parent().parent().nextAll().each(
						function () {
							if ($(this).hasClass('last')) {
								$(this).removeClass('hidden');
								return false;
							}
							$(this).filter('.hidden').removeClass('hidden');
						});
				});

				if (activetab !== '' && $(activetab + '-tab').length) {
					$(activetab + '-tab').addClass('nav-tab-active');
				} else {
					$('.nav-tab-wrapper a:first').addClass('nav-tab-active');
				}
				$('.nav-tab-wrapper a').click(function (evt) {
					$('.nav-tab-wrapper a').removeClass('nav-tab-active');
					$(this).addClass('nav-tab-active').blur();
					var clicked_group = $(this).attr('href');
					if (typeof (localStorage) != 'undefined') {
						localStorage.setItem("activetab", $(this).attr('href'));
					}
					$('.settings-tab').hide();
					$(clicked_group).fadeIn();
					evt.preventDefault();
				});
			});
		</script>
		<?php
	}

	/**
	 * Output style.
	 *
	 * @since 1.1.0
	 */
	protected static function output_style() {
		?>
		<style type="text/css">
			.settings-wrap .nav-tab-wrapper {
				margin: 1.5em 0 1em !important;
			}

			.settings-wrap .form-table .field-section td {
				padding-left: 0;
				padding-right: 0;
			}

			.settings-wrap .form-table .field-section td h2 {
				margin-top: 0;
			}

			.settings-wrap .form-table fieldset ul,
			.settings-wrap .form-table fieldset li {
				margin: 0;
				padding: 0;
			}

			.settings-wrap .form-table .wp-picker-holder {
				position: absolute;
				z-index: 99;
			}

			.settings-wrap .form-table .iris-picker {
				box-shadow: rgb(0 0 0 / 24%) 0 3px 8px;
			}
		</style>
		<?php
	}


	/**
	 * Registers settings fields and sections
	 *
	 * @return void
	 */
	public function register_settings() {
		register_setting(
			$this->option_key,
			$this->option_key,
			array(
				$this,
				'sanitize_settings',
			)
		);

		foreach ( $this->get_fields() as $tab_id => $tab ) {
			if ( empty( $tab ) || ! is_array( $tab ) ) {
				continue;
			}

			add_settings_section(
				$tab_id,
				null,
				'__return_false',
				$this->option_key
			);

			foreach ( $tab as $field ) {
				// No field id associated, skip.
				if ( ! isset( $field['id'] ) ) {
					continue;
				}

				$field = wp_parse_args(
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
						'multiple'    => null,
						'placeholder' => null,
						'required'    => '',
						'disabled'    => '',
						'class'       => '',
						'suffix'      => '',
						'callback'    => '',
						'css'         => '',
						'attrs'       => array(),
						'tab_id'      => $tab_id,
					) );

				add_settings_field(
					$field['id'],
					$field['title'],
					is_callable( $field['callback'] ) ? $field['callback'] : array( $this, 'render_field' ),
					$this->option_key,
					$tab_id,
					$field
				);
			}
		}
	}

	/**
	 * Compile HTML needed for displaying the field
	 *
	 * @param array $field Field settings.
	 *
	 * @return string HTML to be displayed
	 */
	protected function render_field( $field ) {
		if ( ! isset( $field['type'] ) ) {
			return;
		}

		if ( ! empty( $field['value'] ) && is_callable( $field['value'] ) ) {
			$field['value'] = call_user_func( $field['value'] );
		} else {
			$field['value'] = $this->get_option( implode( '_', [ $field['tab_id'], $field['id'] ] ), $field['default'] );
		}

		// Custom attribute handling.
		$attrs = array();
		if ( ! empty( $field['attrs'] ) && is_array( $field['attrs'] ) ) {
			foreach ( $field['attrs'] as $attr => $attr_value ) {
				$attrs[] = esc_attr( $attr ) . '="' . esc_attr( $attr_value ) . '"';
			}
		}

		// Description handling.
		$description = $field['desc'];
		if ( $description && $field['type'] === 'radio' ) {
			$description = '<p class="description" style="margin-top:0;margin-bottom: 4px;">' . wp_kses_post( $description ) . '</p>';
		} elseif ( $description && in_array( $field['type'], array( 'checkbox', 'section' ), true ) ) {
			$description = wp_kses_post( $description );
		} elseif ( $description ) {
			$description = '<p class="description">' . wp_kses_post( $description ) . '</p>';
		}

		$output = '';

		// Switch based on type.
		switch ( $field['type'] ) {
			case 'title':
			case 'section':
				$output = '';
				if ( ! empty( $field['title'] ) ) {
					$output .= '<h2>' . esc_html( $field['title'] ) . '</h2>';
				}
				if ( ! empty( $field['desc'] ) ) {
					$output .= '<div id="' . esc_attr( sanitize_title( $field['id'] ) ) . '-description">';
					$output .= wp_kses_post( wpautop( wptexturize( $field['desc'] ) ) );
					$output .= '</div>';
				}
				break;
			case 'text':
			case 'password':
			case 'datetime':
			case 'datetime-local':
			case 'date':
			case 'month':
			case 'time':
			case 'week':
			case 'number':
			case 'email':
			case 'url':
			case 'tel':
				$output = sprintf(
					'<input type="%1$s" name="%2$s[%3$s_%4$s]" id="%2$s_%3$s_%4$s" class="field-type-%1$s %5$s-text %6$s" style="%7$s" placeholder="%8$s" value="%9$s" %10$s/> %11$s %12$s',
					esc_attr( $field['type'] ),
					esc_attr( $this->option_key ),
					esc_attr( $field['tab_id'] ),
					esc_attr( $field['id'] ),
					esc_attr( $field['size'] ),
					esc_attr( $field['class'] ),
					esc_attr( $field['css'] ),
					esc_attr( $field['placeholder'] ),
					esc_attr( $field['value'] ),
					esc_attr( implode( ' ', $attrs ) ),
					wp_kses_post( $field['suffix'] ),
					wp_kses_post( $description )
				);

				break;
			// Textarea.
			case 'textarea':
				$output = sprintf( '<textarea name="%1$s[%2$s_%3$s]" id="%1$s_%2$s_%3$s" class="field-type-textarea %4$s-text %5$s" style="%6$s" placeholder="%7$s"  %8$s>%9$s</textarea>%10$s %11$s',
					esc_attr( $this->option_key ),
					esc_attr( $field['tab_id'] ),
					esc_attr( $field['id'] ),
					esc_attr( $field['size'] ),
					esc_attr( $field['class'] ),
					esc_attr( $field['css'] ),
					esc_attr( $field['placeholder'] ),
					esc_attr( implode( ' ', $attrs ) ),
					esc_textarea( $field['value'] ),
					wp_kses_post( $field['suffix'] ),
					wp_kses_post( $description )
				);
				break;
			// Select boxes.
			case 'select':
			case 'multiselect':
				$options = '';
				if ( 'multiselect' === $field['type'] ) {
					$attrs[] = 'multiple="multiple"';
				}

				foreach ( $field['options'] as $key => $val ) {
					if ( is_array( $field['value'] ) ) {
						$value = selected( in_array( (string) $key, $field['value'], true ), true, false );
					} else {
						$value = selected( $field['value'], (string) $key, false );
					}
					$options .= sprintf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $key ), $value, esc_html( $val ) );
				}


				$output = sprintf(
					'<select name="%1$s[%2$s_%3$s]%4$s" id="%1$s_%2$s_%3$s" class="field-type-select %5$s-text %6$s" style="%7$s"  %8$s/> %9$s %10$s</select> %11$s',
					esc_attr( $this->option_key ),
					esc_attr( $field['tab_id'] ),
					esc_attr( $field['id'] ),
					( 'multiselect' === $field['type'] ) ? '[]' : '',
					esc_attr( $field['size'] ),
					esc_attr( $field['class'] ),
					esc_attr( $field['css'] ),
					esc_attr( implode( ' ', $attrs ) ),
					$options,
					wp_kses_post( $field['suffix'] ),
					wp_kses_post( $description )
				);

				break;
			case 'multicheck':
				$options = '';
				$value   = wp_parse_list( $field['value'] );
				foreach ( $field['options'] as $key => $title ) {
					$checked = isset( $value[ $key ] ) ? $value[ $key ] : 'no';
					$options .= sprintf( '<li><label><input type="checkbox" name="%1$s[%2$s_%3$s][%4$s]" id="%1$s_%2$s_%3$s_%4$s" class="field-type-checkbox %5$s" style="%6$s" value="yes" %7$s %8$s/>%9$s</label></li>',
						esc_attr( $this->option_key ),
						esc_attr( $field['tab_id'] ),
						esc_attr( $field['id'] ),
						esc_attr( $key ),
						esc_attr( $field['class'] ),
						esc_attr( $field['css'] ),
						checked( 'yes', $checked, false ),
						esc_attr( implode( ' ', $attrs ) ),
						esc_html( $title )
					);
				}

				$output .= sprintf( '<fieldset>%1$s<ul>%2$s</ul>%3$s</fieldset>',
					wp_kses_post( $description ),
					$options,
					wp_kses_post( $field['suffix'] )
				);

				break;
			case 'radio':
				$options = '';
				foreach ( $field['options'] as $key => $title ) {
					$options .= sprintf( '<li><label><input type="radio" name="%1$s[%2$s_%3$s]" id="%1$s_%2$s_%3$s_%6$s" class="field-type-radio %4$s" style="%5$s" value="%6$s" %7$s  %8$s/>%9$s</label></li>',
						esc_attr( $this->option_key ),
						esc_attr( $field['tab_id'] ),
						esc_attr( $field['id'] ),
						esc_attr( $field['class'] ),
						esc_attr( $field['css'] ),
						esc_attr( $key ),
						checked( $key, $field['value'], false ),
						esc_attr( implode( ' ', $attrs ) ),
						esc_html( $title )
					);
				}
				$output .= sprintf( '<fieldset>%1$s<ul>%2$s</ul>%3$s</fieldset>',
					wp_kses_post( $description ),
					$options,
					wp_kses_post( $field['suffix'] )
				);

				break;
			case 'checkbox':
				$output = sprintf( '<label><input type="checkbox" name="%1$s[%2$s_%3$s]" id="%1$s_%2$s_%3$s" class="field-type-checkbox %4$s" style="%5$s" value="yes" %6$s %7$s/>%8$s</label> %9$s',
					esc_attr( $this->option_key ),
					esc_attr( $field['tab_id'] ),
					esc_attr( $field['id'] ),
					esc_attr( $field['class'] ),
					esc_attr( $field['css'] ),
					checked( $field['value'], 'yes', false ),
					esc_attr( implode( ' ', $attrs ) ),
					esc_html( $description ),
					wp_kses_post( $field['suffix'] )
				);
				break;
			case 'wysiwyg':
				$field    = (array) wp_parse_args( $field, array(
					'settings' => array()
				) );
				$settings = wp_parse_args( $field['settings'], array(
					'textarea_rows' => 10,
				) );
				ob_start();
				wp_editor(
					stripslashes( $field['value'] ),
					sprintf( '%1$s_%2$s_%3$s',
						esc_attr( $this->option_key ),
						esc_attr( $field['tab_id'] ),
						esc_attr( $field['id'] )
					),
					array_merge(
						$settings,
						array(
							'textarea_name' => sprintf( '%1$s[%2$s_%3$s]',
								esc_attr( $this->option_key ),
								esc_attr( $field['tab_id'] ),
								esc_attr( $field['id'] )
							)
						)
					)
				);
				echo ob_get_clean();

				break;
			default:
				$output = '';
				break;
		}


		echo $output;
	}

	/**
	 * Sanitization callback for settings field values before save
	 *
	 * @since 1.1.0
	 * @return array
	 */
	public function sanitize_settings( $input = array() ) {
		$tab    = filter_input( INPUT_POST, 'tab', FILTER_SANITIZE_STRING );
		$fields = $this->get_fields();
		if ( ! isset( $fields[ $tab ] ) || empty( $fields[ $tab ] ) ) {
			return $input;
		}
		$settings = $fields[ $tab ];

		$input = $input ? $input : array();

		foreach ( $settings as $field ) {
			if ( ! isset( $field['id'], $field['type'] ) ) {
				continue;
			}

			$name      = sprintf( '%s_%s', $tab, $field['id'] );
			$raw_value = isset( $input[ $name ] ) ? wp_unslash( $input[ $name ] ) : null;

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
					$value = self::clean( $raw_value );
					break;
			}

			$sanitize_callback = isset( $field['sanitize_callback'] ) ? $field['sanitize_callback'] : false;
			if ( $sanitize_callback && is_callable( $sanitize_callback ) ) {
				$value = call_user_func( $sanitize_callback, $value );
			}

			if ( is_null( $value ) ) {
				continue;
			}

			$input[ $name ] = $value;
		}

		$saved = get_option( $this->option_key, array() );
		if ( ! is_array( $saved ) ) {
			$saved = array();
		}

		return array_merge( $saved, $input );
	}

	/**
	 * Iterate through registered fields and extract default values
	 *
	 * @return array
	 */
	public function get_defaults() {
		$defaults = array();

		foreach ( $this->get_fields() as $tab_id => $tab ) {
			if ( empty( $tab ) || ! is_array( $tab ) ) {
				continue;
			}
			foreach ( $tab as $field ) {
				if ( empty( $field['id'] ) || ! isset( $field['default'] ) ) {
					continue;
				}
				$id    = implode( '_', [ $tab_id, $field['id'] ] );
				$value = isset( $field['default'] ) ? $field['default'] : null;
				if ( ! empty( $field['sanitize_callback'] ) && is_callable( $field['sanitize_callback'] ) ) {
					$value = call_user_func( $field['sanitize_callback'], $value );
				}
				$defaults[ $id ] = $value;
			}
		}

		return (array) $defaults;
	}

	/**
	 * Returns a list of options based on the current screen.
	 *
	 * @return array
	 */
	public function get_options() {
		$option_key = $this->option_key;
		$defaults   = $this->get_defaults();

		return wp_parse_args( (array) get_option( $option_key, array() ), $defaults );
	}

	/**
	 * Get option from DB.
	 *
	 * Gets an option from the settings API, using defaults if necessary to prevent undefined notices.
	 *
	 * @param string $key Option key.
	 * @param mixed $default Value when empty.
	 *
	 * @since 1.1.0
	 * @return string The value specified for the option or a default value for the option.
	 */
	public function get_option( $key, $default = null ) {
		$options = $this->get_options();
		// Get option default if unset.
		if ( ! isset( $options[ $key ] ) || empty( $options[ $key ] ) ) {
			$options[ $key ] = isset( $options[ $key ] ) ? $default : '';
		}

		return $options[ $key ];
	}

	/**
	 * Save new option.
	 *
	 * @param string $key Option key.
	 * @param mixed $value Option value.
	 *
	 * @since 1.1.0
	 */
	public function update_option( $key, $value ) {
		$options         = $this->get_options();
		$options[ $key ] = self::clean( $value );
		update_option( $this->option_key, $options );
	}

	/**
	 * Save default settings.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function save_settings() {
		$options = $this->get_options();
		update_option( $this->option_key, $options );
	}

	/**
	 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
	 * Non-scalar values are ignored.
	 *
	 * @param string|array $var Data to sanitize.
	 *
	 * @since 1.1.0
	 * @return string|array
	 */
	protected static function clean( $var ) {
		if ( is_array( $var ) ) {
			return array_map( array( __CLASS__, 'clean' ), $var );
		}

		return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
	}

	/**
	 * Print out the settings fields for a particular settings section.
	 *
	 * Part of the Settings API. Use this in a settings page to output
	 * a specific section. Should normally be called by do_settings_sections()
	 * rather than directly.
	 *
	 * @param string $page Slug title of the admin page whose settings fields you want to show.
	 * @param string $section Slug title of the settings section whose fields you want to show.
	 *
	 * @since 1.1.0
	 *
	 * @global array $wp_settings_fields Storage array of settings fields and their pages/sections.
	 */
	protected function do_settings_fields( $page, $section ) {
		global $wp_settings_fields;

		if ( ! isset( $wp_settings_fields[ $page ][ $section ] ) ) {
			return;
		}

		foreach ( (array) $wp_settings_fields[ $page ][ $section ] as $field ) {
			$row_class = ! empty( $field['args']['row_class'] ) ? esc_attr( $field['args']['row_class'] ) : '';
			$row_id    = ! empty( $field['args']['id'] ) ? $section . '_' . esc_attr( $field['args']['id'] ) : '';
			$row_class .= 'field-' . esc_attr( $field['args']['type'] );

			printf( '<tr id="%1$s" class="%2$s">', $row_id, $row_class );

			if ( isset( $field['args']['type'] ) && 'section' === $field['args']['type'] ) {
				echo '<td colspan="2">';
			} else {
				if ( ! empty( $field['args']['id'] ) ) {
					echo '<th scope="row"><label for="' . esc_attr( $field['args']['id'] ) . '">' . $field['title'] . '</label></th>';
				} else {
					echo '<th scope="row">' . $field['title'] . '</th>';
				}

				echo '<td>';
			}
			call_user_func( $field['callback'], $field['args'] );
			echo '</td>';
			echo '</tr>';
		}
	}
}
