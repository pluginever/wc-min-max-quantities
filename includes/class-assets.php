<?php
/**
 * Plugin Assets handlers.
 *
 * @since    1.1.0
 * @package  WC_Min_Max_Quantities
 */

namespace WC_Min_Max_Quantities;

defined( 'ABSPATH' ) || exit();

/**
 * Class Assets
 */
class Assets {

	/**
	 * Register action & filter hooks.
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @version 1.1.0
	 */
	public function enqueue_admin_scripts() {
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @version 1.1.0
	 */
	public function enqueue_public_scripts() {
	}

	/**
	 * Registers a script according to `wp_register_script`, additionally loading the translations for the file.
	 *
	 * @param string $handle Name of the script. Should be unique.
	 * @param string $relative_url Relative file path from dist directory.
	 * @param array $deps Optional. An array of registered script handles this script depends on. Default empty array.
	 * @param bool $has_i18n Optional. Whether to add a script translation call to this file. Default 'true'.
	 *
	 * @since 1.1.0
	 */
	public function register_script( $handle, $relative_url = null, $deps = array(), $has_i18n = false ) {
		$file      = basename( $relative_url );
		$filename  = pathinfo( $file, PATHINFO_FILENAME );
		$version   = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : Plugin::instance()->version;
		$file_path = $this->get_asset_path( str_replace( $file, "$filename.asset.php", $relative_url ) );

		if ( file_exists( $file_path ) ) {
			$asset   = require $file_path;
			$deps    = isset( $asset['dependencies'] ) ? array_merge( $asset['dependencies'], $deps ) : $deps;
			$version = ! empty( $asset['version'] ) ? $asset['version'] : $version;
		}

		wp_register_script( $handle, $this->get_asset_url( $relative_url ), $deps, $version, true );

		if ( $has_i18n && function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( $handle, 'wc-min-max-quantities', dirname( Plugin::instance()->basename ) . '/i18n/languages/' );
		}
	}

	/**
	 * Register style.
	 *
	 * @param string $handle style handler.
	 * @param string $relative_url Relative file path from dist directory.
	 * @param array $deps style dependencies.
	 * @param bool $has_rtl support RTL.
	 *
	 * @since 1.1.0
	 */
	public function register_style( $handle, $relative_url, $deps = array(), $has_rtl = true ) {
		$file      = basename( $relative_url );
		$filename  = pathinfo( $file, PATHINFO_FILENAME );
		$version   = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : Plugin::instance()->version;
		$file_path = $this->get_asset_path( str_replace( $file, "$filename.asset.php", $relative_url ) );


		if ( file_exists( $file_path ) ) {
			$asset   = require $file_path;
			$version = ! empty( $asset['version'] ) ? $asset['version'] : $version;
		}

		wp_register_style( $handle, $this->get_asset_url( $relative_url ), $deps, $version );

		if ( $has_rtl && function_exists( 'wp_style_add_data' ) ) {
			wp_style_add_data( $handle, 'rtl', 'replace' );
		}
	}

	/**
	 * Get the Path to the plugin dist folder, with trailing slash.
	 *
	 * @param string $path Asset path.
	 * @param string $relative Relative path.
	 *
	 * @since 1.1.0
	 * @return string (URL)
	 */
	public function get_asset_path( $path = '', $relative = '/dist/' ) {
		return Plugin::instance()->get_path( $relative . $path );
	}

	/**
	 * Get the URL to the plugin dist folder, with trailing slash.
	 *
	 * @param string $path Asset path.
	 * @param string $relative Relative path.
	 *
	 * @since 1.1.0
	 * @return string (URL)
	 */
	public function get_asset_url( $path = '', $relative = '/dist/' ) {
		return Plugin::instance()->get_url( $relative . $path );
	}
}
