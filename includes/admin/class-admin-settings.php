<?php
/**
 * Handles plugin settings page.
 *
 * @since    1.1.0
 * @package  WC_Min_Max_Quantities\Admin
 */

namespace WC_Min_Max_Quantities\Admin;

defined( 'ABSPATH' ) || exit();

/**
 * Admin_Settings class.
 */
class Admin_Settings {
	/**
	 * ID of the class extending the settings API. Used in option names.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	protected $option_key = 'wc_min_max_quantities_settings';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'settings_menu' ), 55 );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	/**
	 * get settings tabs.
	 *
	 * @since 1.1.0
	 * @return  array settings tabs.
	 */
	protected function get_tabs() {
		static $tabs = null;
		if ( is_null( $tabs ) ) {
			$tabs = apply_filters( 'wc_min_max_quantities_settings_tabs', array(
				array(
					'id'    => 'general',
					'title' => __( 'General Settings', 'wc-min-max-quantities' ),
				),
			) );

		}

		return $tabs;
	}

	/**
	 * plugin settings fields.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function get_settings() {
		static $settings_fields = null;
		if ( is_null( $settings_fields ) ) {
			$settings_fields = array(
				'general'  => array(
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
						'title'   => esc_html__( 'Minimum product quantity', 'wc-min-max-quantities' ),
						'id'      => 'min_product_quantity',
						'desc'    => esc_html__( 'Minimum number of items required for each product. Set zero for no restrictions.', 'wc-min-max-quantities' ),
						'type'    => 'number',
						'default' => '0',
					),
					array(
						'title'   => esc_html__( 'Maximum product quantity', 'wc-min-max-quantities' ),
						'id'      => 'max_product_quantity',
						'desc'    => esc_html__( 'Maximum quantity allowed for each single product. Set zero for no restrictions.', 'wc-min-max-quantities' ),
						'type'    => 'number',
						'default' => '0',
					),
					array(
						'title'   => esc_html__( 'Quantity groups of', 'wc-min-max-quantities' ),
						'id'      => 'product_quantity_step',
						'desc'    => esc_html__( 'Enter a quantity to only allow product to be purchased in groups of X. Set zero for no restrictions.', 'wc-min-max-quantities' ),
						'type'    => 'number',
						'default' => '0',
					),
					array(
						'title' => esc_html__( 'Order Restriction', 'wc-min-max-quantities' ),
						'type'  => 'section',
						'desc'  => esc_html__( 'The following options are for cart restrictions', 'wc-min-max-quantities' ),
						'id'    => 'cart_restrictions',
					),
					array(
						'title'   => esc_html__( 'Minimum order quantity', 'wc-min-max-quantities' ),
						'id'      => 'min_order_quantity',
						'desc'    => esc_html__( 'Minimum number of items in cart. Set zero for no restrictions.', 'wc-min-max-quantities' ),
						'type'    => 'number',
						'min'     => 0,
						'default' => '0'
					),
					array(
						'title'   => esc_html__( 'Maximum order quantity', 'wc-min-max-quantities' ),
						'id'      => 'max_order_quantity',
						'desc'    => esc_html__( 'Maximum number of items in cart. Set zero for no restrictions.', 'wc-min-max-quantities' ),
						'type'    => 'number',
						'min'     => 0,
						'default' => '0'
					),
					array(
						'title'   => esc_html__( 'Minimum order amount', 'wc-min-max-quantities' ),
						'id'      => 'min_order_amount',
						'desc'    => esc_html__( 'Minimum order total. Set zero for no restrictions.', 'wc-min-max-quantities' ),
						'type'    => 'number',
						'default' => '0',
					),
					array(
						'title'   => esc_html__( 'Maximum order amount', 'wc-min-max-quantities' ),
						'id'      => 'max_order_amount',
						'desc'    => esc_html__( 'Maximum order total.Set zero for no restrictions.', 'wc-min-max-quantities' ),
						'type'    => 'number',
						'default' => '0',
					),
				)
			);
			$settings_fields = apply_filters( 'wc_min_max_quantities_settings', $settings_fields );
		}

		return $settings_fields;
	}

	/**
	 * Add menu item.
	 *
	 * @since 1.1.0
	 */
	public function settings_menu() {
		add_submenu_page(
			'woocommerce',
			__( 'Min Max Quantities Settings', 'wc-min-max-quantities' ),
			__( 'Min Max Quantities', 'wc-min-max-quantities' ),
			'manage_options',
			'wc-min-max-quantities-settings',
			array( $this, 'output' )
		);
	}

	/**
	 * Enqueue scripts and styles
	 *
	 * @since 1.1.0
	 */
	function admin_enqueue_scripts() {
//		wp_enqueue_style( 'wp-color-picker' );
//		wp_enqueue_script( 'wp-color-picker' );
//		wp_enqueue_script( 'jquery' );
	}

	/**
	 * Add all settings sections and fields
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function register_settings() {
		foreach ( $this->get_tabs() as $tab ) {
			if ( isset( $tab['desc'] ) && ! empty( $tab['desc'] ) ) {
				$callback = function () use ( $tab ) {
					echo wp_kses_post( wpautop( wptexturize( $tab['desc'] ) ) );
				};
			} else if ( isset( $tab['callback'] ) ) {
				$callback = $tab['callback'];
			} else {
				$callback = null;
			}
			$title = count( $this->get_tabs() ) > 1 ? $tab['title'] : '';
			add_settings_section( $tab['id'], $title, $callback, 'wc_min_max_quantities_' . $tab['id'] );
		}

		foreach ( $this->get_settings() as $section_id => $fields ) {
			foreach ( $fields as $field ) {

				// Bail if no fields.
				if ( empty( $field['id'] ) ) {
					continue;
				}

				$args     = wp_parse_args(
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
						'row_class'   => '',
						'class'       => '',
						'suffix'      => '',
						'callback'    => '',
						'css'         => '',
						'attrs'       => array(),
					) );
				$callback = ! empty( $args['callback'] ) ? $args['callback'] : array( $this, 'output_field' );
				add_settings_field(
					$args['id'],
					$args['title'],
					is_callable( $callback ) ? $callback : '__return_false',
					'wc_min_max_quantities_' . $section_id,
					$section_id,
					$args
				);
			}
		}

		register_setting( $this->option_key, $this->option_key, array( $this, 'sanitize_settings' ) );
	}

	/**
	 * Output sections.
	 *
	 * @since 1.1.0
	 */
	public function output() {
		$tabs = $this->get_tabs();
		ob_start();
		$this->output_style();
		?>
		<div id="wc-min-max-quantities-settings-wrap" class="wrap settings-wrap wcmmq-settings">

			<h1><?php echo get_admin_page_title() ?></h1>

			<?php if ( ! empty( $tabs ) && count( $tabs ) > 1 ) : ?>

				<nav class="nav-tab-wrapper">
					<?php foreach ( $tabs as $tab ) : ?>
						<a href="#<?php echo esc_attr( $tab['id'] ) ?>" class="nav-tab" id="<?php echo esc_attr( $tab['id'] ) ?>-tab"><?php echo esc_html( $tab['title'] ) ?></a>
					<?php endforeach; ?>
				</nav>

			<?php endif; ?>

			<div class="clear"></div>
			<?php settings_errors(); ?>

			<div class="settings-tabs">
				<?php foreach ( $tabs as $tab ) : ?>
					<div id="<?php echo esc_attr( $tab['id'] ); ?>" class="settings-tab" style="display: none;">
						<?php if ( ! empty( $tab['callback'] ) && is_callable( $tab['callback'] ) ) : ?>
							<?php call_user_func( $tab['callback'] ); ?>
						<?php else : ?>
							<form method="post" action="options.php" enctype="multipart/form-data">
								<?php settings_fields( 'wc_min_max_quantities_settings' ); ?>
								<input type="hidden" name="tab" value="<?php echo esc_attr( $tab['id'] ); ?>">
								<?php $this->do_settings_sections( 'wc_min_max_quantities_' . $tab['id'] ); ?>
								<?php if ( ! isset( $tab['hide_save_button'] ) || ! $tab['hide_save_button'] ) : ?>
									<?php submit_button(); ?>
								<?php endif; ?>
							</form>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>

		</div>
		<?php
		$this->output_script();
		echo ob_get_clean();
	}

	/**
	 * Output script.
	 *
	 * @since 1.1.0
	 */
	public function output_script() {
		?>
		<script>
			jQuery(document).ready(function ($) {
				//Initiate Color Picker
				$('.wp-color-picker').wpColorPicker();

				// Switches option sections
				$('.settings-tab').hide();
				var activetab = '';
				if (typeof (localStorage) != 'undefined') {
					activetab = localStorage.getItem("activetab");
				}
				console.log(activetab)
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
	public function output_style() {
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
	 * Retrieve the array of plugin settings
	 *
	 * @since 1.1.0
	 * @return array
	 */
	function sanitize_settings( $input = array() ) {
		$tab      = filter_input( INPUT_POST, 'tab', FILTER_SANITIZE_STRING );
		$settings = $this->get_settings();
		if ( ! isset( $settings[ $tab ] ) || empty( $settings[ $tab ] ) ) {
			return $input;
		}

		$input = $input ? $input : array();

		foreach ( $settings[ $tab ] as $field ) {
			if ( ! isset( $field['id'], $field['type'] ) ) {
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
					$value = $this->clean( $raw_value );
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
	 * Prints out all settings sections added to a particular settings page
	 *
	 * Part of the Settings API. Use this in a settings page callback function
	 * to output all the sections and fields that were added to that $page with
	 * add_settings_section() and add_settings_field()
	 *
	 * @param string $page The slug name of the page whose settings section you want to output.
	 *
	 * @since 1.1.0
	 *
	 * @global array $wp_settings_sections Storage array of all settings sections added to admin pages.
	 * @global array $wp_settings_fields Storage array of settings fields and info about their pages/sections.
	 */
	protected function do_settings_sections( $page ) {
		global $wp_settings_sections, $wp_settings_fields;
		if ( ! isset( $wp_settings_sections[ $page ] ) ) {
			return;
		}

		foreach ( (array) $wp_settings_sections[ $page ] as $section ) {
			if ( $section['title'] ) {
				echo "<h2>{$section['title']}</h2>\n";
			}

			if ( $section['callback'] ) {
				call_user_func( $section['callback'], $section );
			}

			if ( ! isset( $wp_settings_fields ) || ! isset( $wp_settings_fields[ $page ] ) || ! isset( $wp_settings_fields[ $page ][ $section['id'] ] ) ) {
				continue;
			}
			echo '<table class="form-table" role="presentation">';
			$this->do_settings_fields( $page, $section['id'] );
			echo '</table>';
		}
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
			$row_id    = ! empty( $field['args']['id'] ) ? esc_attr( $field['args']['id'] ) . '-row' : '';
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

	/**
	 * Generate field.
	 *
	 * @param array $field Field config.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function output_field( $field ) {
		if ( ! isset( $field['type'] ) ) {
			return;
		}
		if ( ! isset( $field['value'] ) ) {
			$field['value'] = $this->get_option( $field['id'], $field['default'] );
		}

		// Custom attribute handling.
		$attrs = array();
		if ( ! empty( $field['attrs'] ) && is_array( $field['attrs'] ) ) {
			foreach ( $field['attrs'] as $attr => $attr_value ) {
				$attrs[] = esc_attr( $attr ) . '="' . esc_attr( $attr_value ) . '"';
			}
		}

		// Description handling.
		$description  = $field['desc'];
		if ( $description && $field['type'] === 'radio' ) {
			$description = '<p class="description" style="margin-top:0;margin-bottom: 4px;">' . wp_kses_post( $description ) . '</p>';
		} elseif ( $description && in_array( $field['type'], array( 'checkbox', 'section' ), true ) ) {
			$description = wp_kses_post( $description );
		} elseif ( $description ) {
			$description = '<p class="description">' . wp_kses_post( $description ) . '</p>';
		}

		// Switch based on type.
		switch ( $field['type'] ) {
			// Section
			case 'section':
			case 'title':
				if ( ! empty( $field['title'] ) ) {
					echo '<h2>' . esc_html( $field['title'] ) . '</h2>';
				}
				if ( ! empty( $field['desc'] ) ) {
					echo '<div id="' . esc_attr( sanitize_title( $field['id'] ) ) . '-description">';
					echo wp_kses_post( wpautop( wptexturize( $field['desc'] ) ) );
					echo '</div>';
				}
				break;
			// Standard text inputs and subtypes like 'number'.
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
				?>
				<input
					name="<?php echo esc_attr( $this->get_field_name( $field['id'] ) ); ?>"
					id="<?php echo esc_attr( $field['id'] ); ?>"
					type="<?php echo esc_attr( $field['type'] ); ?>"
					style="<?php echo esc_attr( $field['css'] ); ?>"
					value="<?php echo esc_attr( $field['value'] ); ?>"
					class="<?php echo esc_attr( $field['class'] ); ?> <?php echo esc_attr( $field['size'] ); ?>-text"
					placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
					<?php echo implode( ' ', $attrs ); ?>
				/>
				<?php echo esc_html( $field['suffix'] ); ?>
				<?php echo $description; ?>
				<?php
				break;

			// Textarea.
			case 'textarea':
				?>
				<textarea
					name="<?php echo esc_attr( $this->get_field_name( $field['id'] ) ); ?>"
					id="<?php echo esc_attr( $field['id'] ); ?>"
					style="<?php echo esc_attr( $field['css'] ); ?>"
					class="<?php echo esc_attr( $field['class'] ); ?> <?php echo esc_attr( $field['size'] ); ?>-text"
					placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
					<?php echo implode( ' ', $attrs ); ?>><?php echo esc_textarea( $field['value'] ); ?></textarea>
				<?php echo $description; ?>
				<?php
				break;

			// Select boxes.
			case 'select':
			case 'multiselect':
				?>
				<select
					name="<?php echo esc_attr( $this->get_field_name( $field['id'] ) ); ?><?php echo ( 'multiselect' === $field['type'] ) ? '[]' : ''; ?>"
					id="<?php echo esc_attr( $field['id'] ); ?>"
					style="<?php echo esc_attr( $field['css'] ); ?>"
					class="<?php echo esc_attr( $field['class'] ); ?> <?php echo esc_attr( $field['size'] ); ?>-text"
					<?php echo implode( ' ', $attrs ); ?>
					<?php echo 'multiselect' === $field['type'] ? 'multiple="multiple"' : ''; ?>
				>
					<?php
					foreach ( $field['options'] as $key => $val ) {
						?>
						<option value="<?php echo esc_attr( $key ); ?>"
							<?php

							if ( is_array( $field['value'] ) ) {
								selected( in_array( (string) $key, $field['value'], true ), true );
							} else {
								selected( $field['value'], (string) $key );
							}

							?>
						><?php echo esc_html( $val ); ?></option>
						<?php
					}
					?>
				</select>
				<?php echo $description; ?>
				<?php
				break;
			case 'multicheck':
				$value = ! is_array( $field['value'] ) ? array() : $field['value'];
				$type = 'multicheck' === $field['type'] ? 'checkbox' : $field['type'];
				?>
				<fieldset>
					<?php echo $description; ?>
					<ul>
						<?php
						foreach ( $field['options'] as $key => $option ) {
							$checked = isset( $value[ $key ] ) ? $value[ $key ] : 'no';
							?>
							<li>
								<label>
									<input
										name="<?php echo esc_attr( $this->get_field_name( $field['id'] ) ); ?>[<?php echo esc_attr( $key ); ?>]"
										value="yes"
										type="<?php echo esc_attr( $type ); ?>"
										style="<?php echo esc_attr( $field['css'] ); ?>"
										class="<?php echo esc_attr( $field['class'] ); ?>"
										<?php echo implode( ' ', $attrs ); ?>
										<?php checked( 'yes', $checked ); ?>
									/>
									<?php echo esc_html( $option ); ?>
								</label>
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
					<?php echo $description; // WPCS: XSS ok.
					?>
					<ul>
						<?php
						foreach ( $field['options'] as $key => $val ) {
							?>
							<li>
								<label><input
										name="<?php echo esc_attr( $this->get_field_name( $field['id'] ) ); ?>"
										value="<?php echo esc_attr( $key ); ?>"
										type="radio"
										style="<?php echo esc_attr( $field['css'] ); ?>"
										class="<?php echo esc_attr( $field['class'] ); ?>"
										<?php echo implode( ' ', $attrs ); // WPCS: XSS ok. ?>
										<?php checked( $key, $field['value'] ); ?>
									/> <?php echo esc_html( $val ); ?></label>
							</li>
							<?php
						}
						?>
					</ul>
				</fieldset>
				<?php
				break;
			case 'checkbox':
				?>
				<label for="<?php echo esc_attr( $field['id'] ); ?>">
					<input
						name="<?php echo esc_attr( $this->get_field_name( $field['id'] ) ); ?>"
						id="<?php echo esc_attr( $field['id'] ); ?>"
						type="checkbox"
						value="yes"
						<?php checked( $field['value'], 'yes' ); ?>
						<?php echo implode( ' ', $attrs ); ?>
					/>
					<?php echo $description; ?>
				</label>
				<?php
				break;

			case 'wysiwyg':
				$field        = (array) wp_parse_args( $field, array(
					'settings' => array()
				) );
				$option_value = $field['value'];
				$settings     = wp_parse_args( $field['settings'], array(
					'textarea_rows' => 10,
				) );
				ob_start();
				wp_editor( stripslashes( $option_value ), $field['id'], array_merge( $settings, array( 'textarea_name' => esc_attr( $this->get_field_name( $field['id'] ) ) ) ) );
				echo ob_get_clean();

				break;

			case 'color':
				?>
				<input
					name="<?php echo esc_attr( $this->get_field_name( $field['id'] ) ); ?>"
					id="<?php echo esc_attr( $field['id'] ); ?>"
					type="text"
					style="<?php echo esc_attr( $field['css'] ); ?>"
					value="<?php echo esc_attr( $field['value'] ); ?>"
					class="<?php echo esc_attr( $field['class'] ); ?> wp-color-picker"
					placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"
					<?php echo implode( ' ', $attrs ); ?>
				/>
				<?php echo esc_html( $field['suffix'] ); ?>
				<?php echo $description; ?>
				<?php
				break;

		}

	}

	/**
	 * @param $field_id
	 *
	 * @return string
	 */
	public function get_field_name( $field_id ) {
		return $this->option_key . '[' . $field_id . ']';
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
		$options = get_option( $this->option_key, array() );

		// Get option default if unset.
		if ( ! isset( $options[ $key ] ) || empty( $options[ $key ] ) ) {
			$options[ $key ] = isset( $options[ $key ] ) ? $default : '';
		}

		return $options[ $key ];
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
	public function clean( $var ) {
		if ( is_array( $var ) ) {
			return array_map( array( $this, 'clean' ), $var );
		}

		return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
	}

}
