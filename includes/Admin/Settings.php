<?php
/**
 * Handles the settings page.
 *
 * @since   1.1.4
 * @package PluginEver\MinMaxQuantities\Admin
 */

namespace PluginEver\MinMaxQuantities\Admin;

use PluginEver\MinMaxQuantities\B8\Component;

defined( 'ABSPATH' ) || exit;

/**
 * Class Settings.
 *
 * Renders the tabbed settings screen with the plugin's WooCommerce-format
 * fields and persists submissions through `woocommerce_update_options()`.
 *
 * @since 1.1.4
 * @package PluginEver\MinMaxQuantities\Admin
 */
class Settings extends Component {

	/**
	 * Capability required to manage the settings.
	 *
	 * @since 1.1.4
	 * @var string
	 */
	protected string $capability = 'manage_options';

	/**
	 * Admin-post action name for the settings form.
	 *
	 * @since 2.3.2
	 * @var string
	 */
	protected string $action = '';

	/**
	 * Register hooks and the admin-post handler.
	 *
	 * @since 2.3.2
	 * @return void
	 */
	public function register(): void {
		$this->action = $this->app->hook_name( 'save_settings' );

		add_action( 'admin_post_' . $this->action, array( $this, 'handle_save' ) );
		$this->app->on_filter( 'admin_pages', array( $this, 'register_page' ) );
	}

	/**
	 * Register the settings admin page.
	 *
	 * @since 2.3.2
	 * @param array<int, array<string, mixed>> $pages Admin page configurations.
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
	 * The tabs and their WooCommerce-format fields.
	 *
	 * @since 2.3.2
	 * @return array<string, array{title: string, fields: array<int, array<string, mixed>>}>
	 */
	public function get_tabs(): array {
		return $this->register_settings( array() );
	}

	/**
	 * Render the settings page.
	 *
	 * @since 2.3.2
	 * @return void
	 */
	public function render(): void {
		$tabs = $this->get_tabs();
		if ( empty( $tabs ) ) {
			return;
		}

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only tab navigation.
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : 'wc-min-max-quantities';
		$tab  = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		if ( ! isset( $tabs[ $tab ] ) ) {
			$tab = (string) array_key_first( $tabs );
		}

		/**
		 * Filters the settings page wrapper classes.
		 *
		 * @since 2.3.2
		 * @param array<int, string> $classes Wrapper class names.
		 */
		$classes = (array) apply_filters( 'settings_wrap_classes', array( 'wrap', 'woocommerce', 'b8-wrap' ) );
		?>
		<div class="<?php echo esc_attr( implode( ' ', array_map( 'sanitize_html_class', $classes ) ) ); ?>">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<nav class="nav-tab-wrapper b8-navbar">
				<?php
				foreach ( $tabs as $id => $data ) {
					printf(
						'<a href="%1$s" class="nav-tab %2$s">%3$s</a>',
						esc_url( admin_url( 'admin.php?page=' . $page . '&tab=' . $id ) ),
						esc_attr( $tab === $id ? 'nav-tab-active' : '' ),
						esc_html( $data['title'] )
					);
				}

				$this->render_nav_extras();
				?>
			</nav>
			<hr class="wp-header-end">

			<div class="b8-poststuff">
				<div class="column-1">
					<?php $this->render_content( (string) $tab, $tabs[ $tab ]['fields'] ); ?>
				</div>
				<div class="column-2">
					<?php $this->render_sidebar(); ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the content area for a tab.
	 *
	 * @since 2.3.2
	 * @param string                           $tab    Current tab id.
	 * @param array<int, array<string, mixed>> $fields Field declarations for the tab.
	 * @return void
	 */
	protected function render_content( string $tab, array $fields ): void {
		/**
		 * Fires inside the content area for a settings tab.
		 *
		 * @since 2.3.2
		 * @param string $tab Current tab id.
		 */
		do_action( 'wc_min_max_quantities_settings_' . $tab );

		if ( empty( $fields ) ) {
			return;
		}
		?>
		<form method="post" id="mainform" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
			<input type="hidden" name="action" value="<?php echo esc_attr( $this->action ); ?>" />
			<input type="hidden" name="tab" value="<?php echo esc_attr( $tab ); ?>" />
			<?php
			wp_nonce_field( $this->action );
			$this->render_fields( $fields );
			submit_button();
			?>
		</form>
		<?php
	}

	/**
	 * Persist the submitted tab.
	 *
	 * @since 2.3.2
	 * @return void
	 */
	public function handle_save(): void {
		if ( ! current_user_can( $this->capability ) ) {
			wp_die( esc_html__( 'You are not allowed to save these settings.', 'wc-min-max-quantities' ) );
		}

		check_admin_referer( $this->action );

		$tab = isset( $_POST['tab'] ) ? sanitize_key( wp_unslash( $_POST['tab'] ) ) : '';

		if ( '' !== $tab && $this->save_fields( $tab ) ) {
			$this->app->flash->success( __( 'Settings saved.', 'wc-min-max-quantities' ) );
		}

		wp_safe_redirect( add_query_arg( 'tab', $tab, wp_get_referer() ) );
		exit;
	}

	/**
	 * Persist the fields of a tab through WooCommerce.
	 *
	 * @since 2.3.2
	 * @param string $tab Current tab id.
	 * @return bool True when the fields were saved.
	 */
	protected function save_fields( string $tab ): bool {
		if ( ! function_exists( 'woocommerce_update_options' ) ) {
			return false;
		}

		$tabs = $this->get_tabs();
		if ( ! isset( $tabs[ $tab ] ) ) {
			return false;
		}

		woocommerce_update_options( $tabs[ $tab ]['fields'] );

		return true;
	}

	/**
	 * Output the settings fields.
	 *
	 * @since 2.3.2
	 * @param array<int, array<string, mixed>> $fields Field declarations.
	 * @return void
	 */
	protected function render_fields( array $fields ): void {
		if ( function_exists( 'woocommerce_admin_fields' ) ) {
			woocommerce_admin_fields( $fields );
			return;
		}

		echo '<table class="form-table" role="presentation">';
		foreach ( $fields as $field ) {
			$type = isset( $field['type'] ) ? (string) $field['type'] : 'text';

			if ( 'sectionend' === $type ) {
				continue;
			}

			if ( 'title' === $type ) {
				printf(
					'<tr><td colspan="2" style="padding-left:0;"><h2 style="margin-bottom:5px;">%1$s</h2>%2$s</td></tr>',
					esc_html( isset( $field['title'] ) ? $field['title'] : '' ),
					isset( $field['desc'] ) ? '<p>' . wp_kses_post( $field['desc'] ) . '</p>' : ''
				);
				continue;
			}

			$id    = isset( $field['id'] ) ? (string) $field['id'] : '';
			$value = $this->app->options->get( $id, isset( $field['default'] ) ? $field['default'] : '' );

			printf(
				'<tr><th scope="row"><label for="%1$s">%2$s</label></th><td><input type="%3$s" name="%1$s" id="%1$s" value="%4$s" class="regular-text" /></td></tr>',
				esc_attr( $id ),
				esc_html( isset( $field['title'] ) ? $field['title'] : '' ),
				esc_attr( in_array( $type, array( 'text', 'number', 'email', 'url', 'tel', 'password' ), true ) ? $type : 'text' ),
				esc_attr( (string) $value )
			);
		}
		echo '</table>';
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

		// Back-compat: fire the per-group field filters used by extensions (e.g. the Pro add-on).
		foreach ( $settings as $group => $data ) {
			$fields = isset( $data['fields'] ) && is_array( $data['fields'] ) ? $data['fields'] : array();

			$settings[ $group ]['fields'] = (array) apply_filters(
				$this->app->hook_name( sanitize_key( (string) $group ) . '_settings' ),
				$fields
			);
		}

		return $settings;
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
