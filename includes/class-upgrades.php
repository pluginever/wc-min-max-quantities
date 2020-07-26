<?php

/**
 * Plugin Upgrade Routine
 *
 * @since 1.0.0
 */
class WCMinMaxQuantities_Upgrades {

    /**
     * The upgrades
     *
     * @var array
     */
    private static $upgrades = array(
         '1.0.9'    => 'updates/update-1.0.9.php',
    );

    /**
     * Get the plugin version
     *
     * @return string
     */
    public function get_version() {
        return get_option( 'wpcp_version' );
    }

    /**
	 * Installer constructor.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'maybe_update' ) );
	}

    /**
     * Check if the plugin needs any update
     *
     * @return boolean
     */
    public function needs_update() {

        // may be it's the first install
        if ( ! $this->get_version() ) {
            return false;
        }

        if ( version_compare( $this->get_version(), WC_MINMAX_VERSION, '<' ) ) {
            return true;
        }

        return false;
    }

    /**
     * Perform all the necessary upgrade routines
     *
     * @return void
     */
    public function maybe_update() {
        if( $this->needs_update() ){
            $installed_version = $this->get_version();
            $path              = trailingslashit( dirname( __FILE__ ) );
            foreach ( self::$upgrades as $version => $file ) {
                if ( version_compare( $installed_version, $version, '<' ) ) {
                    include $path . $file;
                    update_option( 'wpcp_version', $version );
                }
            }
            update_option( 'wpcp_version', WC_MINMAX_VERSION );
        }
    }
}

new WCMinMaxQuantities_Upgrades();
