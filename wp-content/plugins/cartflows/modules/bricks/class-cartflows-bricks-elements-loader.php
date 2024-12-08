<?php
/**
 * Bricks modules loader
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

	/**
	 * Class for Bricks theme modules
	 */
class Cartflows_Bricks_Elements_Loader {

	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $instance;

	/**
	 * Initiator
	 *
	 * @return object instance
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		// Register Elements.
		add_action( 'init', array( $this, 'register_elements' ), 99 );
		add_action( 'wp', array( $this, 'load_bricks_styles' ) );
		add_filter( 'woocommerce_locate_template', array( $this, 'override_bricks_woocommerce_template' ), 10, 3 );
		add_action( 'bricks/frontend/after_render_data', array( $this, 'update_required_step_meta_data' ), 10, 2 );
	}

	/**
	 * Returns Script array.
	 *
	 * @return array()
	 * @since 1.6.15
	 */
	public static function get_module_list() {

		$widget_list = array(
			'class-cartflows-bricks-checkout-form',
			'class-cartflows-bricks-optin-form',
			'class-cartflows-bricks-order-details-form',
		);

		return $widget_list;
	}

	/**
	 * Include Widgets files
	 *
	 * Load widgets files
	 *
	 * @since 2.1.0
	 * @return void
	 */
	public function register_elements() {
		// Register Dynamic Content Tags.
		require_once CARTFLOWS_DIR . 'modules/bricks/class-cartflows-bricks-dynamic-data.php';
		
		$element_files = $this->get_module_list();
		foreach ( $element_files as $file ) {

			$file = CARTFLOWS_DIR . 'modules/bricks/elements/' . $file . '.php';

			if ( file_exists( $file ) ) {
				\Bricks\Elements::register_element( $file );
			}
		}

	}

	/**
	 * Load Bricks styles
	 *
	 * @since 2.1.0
	 * @return void
	 */
	public function load_bricks_styles() {
		if ( wcf()->utils->is_step_post_type() && file_exists( CARTFLOWS_DIR . 'modules/bricks/widgets-css/frontend.css' ) ) {
			wp_enqueue_style( 'cartflows-bricks-style', CARTFLOWS_URL . 'modules/bricks/widgets-css/frontend.css', array(), CARTFLOWS_VER );
		}
	}

	/**
	 * Override WooCommerce template
	 *
	 * @since 2.1.0
	 * @param string $template Template path.
	 * @param string $template_name Template name.
	 * @param string $template_path Template path.
	 * @return string
	 */
	public function override_bricks_woocommerce_template( $template, $template_name, $template_path ) {
		$plugin_path = CARTFLOWS_DIR . 'woocommerce/template/';
		$file        = $plugin_path . $template_name;
		if ( file_exists( $file ) ) {
			$template = $file;
		}
	
		return $template;
	}

	/**
	 * Before checkout shortcode actions.
	 *
	 * @since 2.1.0
	 * @param array  $elements array elements data.
	 * @param string $area string area.
	 * @return void
	 */
	public function update_required_step_meta_data( $elements, $area ) {
		global $post;
		$post_ID    = $post->ID;
		$step_type  = get_post_meta( $post_ID, 'wcf-step-type', true );
		$block_data = '';
		$meta_keys  = array();
		switch ( $step_type ) {

			case 'checkout':
				$block_data = $this->bricks_find_block_recursive( $elements );
				$meta_keys  = array(
					'layout' => 'wcf-checkout-layout',
				);
				break;

			case 'optin':
				break;

			default:
		}

		if ( is_array( $block_data ) && ! empty( $block_data ) ) {

			foreach ( $meta_keys as $key => $meta_key ) {

				if ( isset( $block_data['settings'][ $key ] ) ) {
					update_post_meta( $post_ID, $meta_key, $block_data['settings'][ $key ] );
				}
			}
		}
	}

	/**
	 * Get the bricks widget data.
	 *
	 * @param array $elements elements data.
	 * @return bool|array
	 */
	public function bricks_find_block_recursive( $elements ) {
		foreach ( $elements as $element ) {
			if ( ! empty( $element['settings'] ) ) {
				if ( isset( $element['name'] ) && 'bricks-checkout-form' === $element['name'] ) {
					return $element;
				}
			}
		}
		return false;
	}
 


}
/**
 * Kicking this off by calling 'get_instance()' method
 */
Cartflows_Bricks_Elements_Loader::get_instance();
