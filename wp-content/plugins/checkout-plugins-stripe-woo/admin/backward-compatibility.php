<?php
/**
 * Update Backward Compatibility
 *
 * @package checkout-plugins-stripe-woo
 *
 * @since 1.9.0
 */

namespace CPSW\Admin;

use CPSW\Inc\Traits\Get_Instance;

/**
 * Class that update compatibility.
 *
 * @since 1.9.0
 */
class Backward_Compatibility {

	use Get_Instance;

	/**
	 *  Constructor
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'init' ) );
	}

	/**
	 * Init function.
	 * Storing the version number in options.
	 *
	 * @return void
	 * @since 1.9.0
	 */
	public function init() {
		// Function to store the version number to handle backward compatibility.
		$this->update_plugin_version();
	}

	/**
	 * Function to store the plugin's version number in the option.
	 *
	 * @return void
	 */
	public function update_plugin_version() {
		// Get auto saved version number.
		$saved_version = get_option( 'cpsw-version', false );

		// Update auto saved version number.
		if ( ! $saved_version ) {
			update_option( 'cpsw-version', CPSW_VERSION );
			return;
		}

		// If equals then return.
		if ( version_compare( $saved_version, CPSW_VERSION, '=' ) ) {
			return;
		}

		// Update auto saved version number.
		update_option( 'cpsw-version', CPSW_VERSION );
	}
}
