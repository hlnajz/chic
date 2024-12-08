<?php
/**
 * Astra Addon compatibility.
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Cartflows_Astra_Addon_Compatibility' ) ) :

	/**
	 * Class for Astra Addon compatibility.
	 */
	class Cartflows_Astra_Addon_Compatibility {

		/**
		 * Member Variable
		 *
		 * @var instance
		 */
		private static $instance;

		/**
		 * Initiator
		 *
		 * @since 2.1.0
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 2.1.0
		 */
		public function __construct() {
			add_filter( 'astra_addon_enable_modern_checkout', array( $this, 'override_order_review_checkout' ) );
			add_action( 'wp', array( $this, 'cartflows_page_template_specific_action' ), 11 );
		}

		/**
		 * Function to remove page template specific actions.
		 * Used to remove undesigned menu from the footer of the CartFlows pages only.
		 *
		 * @since 2.1.0
		 *
		 * @return void
		 */
		public function cartflows_page_template_specific_action() {

			// Return if not the CartFlows page.
			if ( ! wcf()->utils->is_step_post_type() ) {
				return;
			}

			$page_template = Cartflows_Helper::get_current_page_template();

			if ( _wcf_supported_template( $page_template ) ) {

				// Remove Scroll to top icon.
				add_filter( 'astra_get_option_scroll-to-top-enable', '__return_false' );

				// Remove the Modern Input Style CSS.
				add_filter( 'astra_get_option_woo-input-style-type', '__return_empty_string' );
			}
		}

		/**
		 * Overrides Astra's actions for the CartFlows checkout page.
		 *
		 * @param bool $bool Determines whether to override Astra's actions. If true, actions are overridden.
		 * @return bool Returns the passed boolean parameter.
		 *
		 * @since 2.1.0
		 */
		public function override_order_review_checkout( $bool ) {
			if ( _is_wcf_checkout_type() ) {
				$bool = false;
			}
			return $bool;
		}
	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	Cartflows_Astra_Addon_Compatibility::get_instance();

endif;
