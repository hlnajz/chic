<?php
/**
 * SureTriggers compatibility
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use CartflowsAdmin\AdminCore\Inc\AdminMenu;
use CartflowsAdmin\AdminCore\Ajax\AjaxErrors;

if ( ! class_exists( 'Cartflows_SureTriggers_Compatibility' ) ) :

	/**
	 * Class for Beaver Builder page builder compatibility
	 */
	class Cartflows_SureTriggers_Compatibility {

		/**
		 * Member Variable
		 *
		 * @var instance
		 */
		private static $instance;

		/**
		 * Initiator
		 *
		 * @since 1.1.4
		 * @return self
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
		 * @since 1.1.4
		 */
		public function __construct() {

			add_filter( 'cartflows_admin_global_settings_data', array( $this, 'add_suretriggers_data' ) );
			add_action( 'wp_ajax_cartflows_get_suretriggers_data', array( $this, 'get_suretriggers_data' ) );
		}

		/**
		 * Generate the data required for SureTriggers integration.
		 *
		 * @since 2.1.0
		 * @return array $settings The modified array of admin settings tabs.
		 */
		public function prepare_suretriggers_data() {

			$embedded_url = apply_filters( 'suretriggers_get_iframe_url', CARTFLOWS_SURETRIGGERS_INTEGRATION_BASE_URL );

			return array(
				'data'        => array(
					'client_id'                      => 'CartFlows',
					'st_embed_url'                   => $embedded_url,
					'embedded_identifier'            => 'cf',
					'target'                         => 'suretriggers-iframe-wrapper',
					'trigger_allowed_apps'           => array( 'CartFlows', 'WooCommerce' ),
					'event'                          => array(),
					'integration'                    => 'CartFlows',
					'summary'                        => 'Create new workflow for CartFlows',
					'selected_options'               => array(),
					'sample_response'                => array(),
					'configure_trigger'              => true,
					'trigger_reset_selected_options' => false,
				),
				'redirection' => admin_url( 'admin.php?page=suretriggers' ),
				'status'      => AdminMenu::get_instance()->get_plugin_status( 'suretriggers/suretriggers.php' ),
				'connected'   => _is_suretriggers_connected(),

			);
		}

		/**
		 * Add data required for suretriggers integration in common setting API call.
		 *
		 * @since 2.1.0
		 *
		 * @param array $settings The array of admin settings tabs.
		 * @return array $settings The modified array of admin settings tabs.
		 */
		public function add_suretriggers_data( $settings ) {

			// If the current user don't have the manage setting access, then return.
			if ( ! current_user_can( 'cartflows_manage_settings' ) ) {
				return $settings;
			}

			if ( isset( $settings['automations'] ) ) {
				$settings['automations']['suretriggers'] = $this->prepare_suretriggers_data();
			}

			return $settings;
		}

		/**
		 * Get data required for suretriggers integration.
		 *
		 * @since 2.1.0
		 *
		 * @return void
		 */
		public function get_suretriggers_data() {

			$response_data = array( 'message' => AjaxErrors::get_instance()->get_error_msg( 'permission' ) );

			// If the current user don't have the manage setting access, then return.
			if ( ! current_user_can( 'cartflows_manage_settings' ) ) {
				return;
			}

			/**
			 * Nonce verification
			 */
			if ( ! check_ajax_referer( 'cartflows_get_suretriggers_data', 'security', false ) ) {
				$response_data = array( 'message' => AjaxErrors::get_instance()->get_error_msg( 'nonce' ) );
				wp_send_json_error( $response_data );
			}

			wp_send_json_success( $this->prepare_suretriggers_data() );
		}
	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	Cartflows_SureTriggers_Compatibility::get_instance();

endif;
