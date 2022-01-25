<?php
/**
 * Includes the classes.
 *
 * @version  1.1.0
 * @since    1.1.0
 * @package  WC_Min_Max_Quantities
 */

namespace WC_Min_Max_Quantities;

defined( 'ABSPATH' ) || exit();

class Autoloader {

	/**
	 * Autoloader constructor.
	 *
	 * @since 1.1.0
	 */
	public function __construct() {
		// Register autoloader.
		spl_autoload_register( array( $this, 'autoload' ), true );
	}

	/**
	 * Autoloader for classes
	 *
	 * @param string $class Fully qualified classname to be loaded.
	 *
	 * @since 1.1.0
	 */
	public function autoload( $class ) {
		$class     = ltrim( $class, '\\' );
		$namespace = 'WC_Min_Max_Quantities\\';
		$len       = strlen( $namespace );
		if ( strncmp( $namespace, $class, $len ) !== 0 || ! preg_match( '/^(?P<namespace>.+)\\\\(?P<class_name>[^\\\\]+)$/', $class, $matches ) ) {
			return;
		}

		$include_path  = untrailingslashit( __DIR__ );
		$class_name    = strtolower( $matches['class_name'] );
		$file_name     = 'class-' . str_replace( '_', '-', $class_name ) . '.php';
		$relative_path = str_replace( array( $namespace, '\\', $matches['class_name'] ), array( '', DIRECTORY_SEPARATOR, $file_name ), $class );
		$file          = trailingslashit( $include_path ) . strtolower( $relative_path );

		// if the file exists, require it.
		if ( file_exists( $file ) ) {
			require $file;
		}
	}

}

return new Autoloader();
