<?php
/**
 * Bricks theme compatibility
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Bricks\Database;

if ( ! class_exists( 'Cartflows_Bricks_Compatibility' ) ) :

	/**
	 * Class for Bricks theme compatibility
	 */
	class Cartflows_Bricks_Compatibility {

		/**
		 * Member Variable
		 *
		 * @var instance
		 */
		private static $instance;

		/**
		 * Initiator
		 *
		 * @since 1.5.7
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
		 * @since 1.5.7
		 */
		public function __construct() {

			add_filter( 'cartflows_remove_theme_styles', array( $this, 'wcf_load_theme_files' ), 10, 1 );
			add_filter( 'cartflows_remove_theme_scripts', array( $this, 'wcf_load_theme_files' ), 10, 1 );
			add_filter( 'cartflows_allow_to_change_page_template', array( $this, 'allow_to_change_page_template' ), 10 );
			add_action( 'wp', array( $this, 'remove_bricks_render_header_footer' ) );
			add_action( 'wp', array( $this, 'remove_bricks_editor_option_instant_checkout' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'bricks_compatibility_external_css' ), 101 );
			add_filter( 'option_bricks_global_settings', array( $this, 'add_step_post_type_to_supported_post_types' ) );
			add_filter( 'wp_get_attachment_image_attributes', array( $this, 'disable_lazy_loading_for_cartflows_steps' ), 9, 3 );
			add_action( 'admin_bar_menu', array( $this, 'remove_editor_links_from_admin_toolbar' ), 999 );
			add_filter( 'cartflows_instant_checkout_header_logo', array( $this, 'fetch_header_logo_from_theme' ), 10, 2 );

		}

		/**
		 * Fetch the header logo from the theme
		 *
		 * @since x.x.x
		 * @param string $logo           The logo URL.
		 * @param string $site_title     The Site Title.
		 * @return string   $bricks_logo    The modified logo HTML.
		 */
		public function fetch_header_logo_from_theme( $logo, $site_title ) {

			$template_header_id = Database::$active_templates['header'];
			$template_data      = $template_header_id ? Database::get_data( $template_header_id, 'header' ) : array();

			$logo_element = array();

			if ( is_array( $template_data ) && ! empty( $template_data ) ) {

				// Find logo element in header.
				$logo_element = array_filter(
					$template_data,
					function( $element ) {
						return isset( $element['name'] ) && 'logo' === $element['name'];
					}
				);

				$logo_element = reset( $logo_element );
			}

			// Get logo settings if found.
			if ( ! empty( $logo_element ) && ! empty( $logo_element['settings'] ) ) {
				$logo_settings = $logo_element['settings'];

				$logo_id   = ! empty( $logo_settings['logo']['id'] ) ? $logo_settings['logo']['id'] : '';
				$logo_size = ! empty( $logo_settings['logo']['size'] ) ? $logo_settings['logo']['size'] : '';

				$logo_width  = ! empty( $logo_settings['logoWidth'] ) ? intval( $logo_settings['logoWidth'] ) : '';
				$logo_height = ! empty( $logo_settings['logoHeight'] ) ? intval( $logo_settings['logoHeight'] ) : '';
				$logo_text   = ! empty( $logo_settings['logoText'] ) ? $logo_settings['logoText'] : '';

				$logo_image_src = wp_get_attachment_image_src( $logo_id, $logo_size );

				if ( ! empty( $logo_image_src[0] ) ) {
					$style = '';

					if ( ! empty( $logo_width ) ) {
						$style .= 'width:' . intval( $logo_width ) . 'px;';
					}

					if ( ! empty( $logo_height ) ) {
						$style .= 'height:' . intval( $logo_height ) . 'px;';
					}

					$logo = '<img src="' . esc_url( $logo_image_src[0] ) . '" alt="' . esc_attr( ! empty( $logo_text ) ? $logo_text : $site_title ) . '" style="' . $style . '">';
				}
			}

			return $logo;
		}

		/**
		 * Disable the Bricks Builder's Lazy Loading feature on the CartFlows page.
		 *
		 * @param array        $attr Image attributes.
		 * @param object       $attachment WP_POST object of image.
		 * @param string|array $size Requested image size.
		 * @return array       $attr Modified Image Attributes.
		 */
		public function disable_lazy_loading_for_cartflows_steps( $attr, $attachment, $size ) {

			if ( wcf()->utils->is_step_post_type() ) {
				$attr['_brx_disable_lazy_loading'] = 1;
			}

			return $attr;
		}

		/**
		 * Allow to load theme files for bricks. We need JS of bricks theme for editor.
		 *
		 * @param bool $remove_styles Whether to remove styles. Default false.
		 * @return bool Returns false to prevent the loading of theme files.
		 */
		public function wcf_load_theme_files( $remove_styles ) {
			$is_bricks_builder = self::bricks_editor_mode();
			if ( $is_bricks_builder ) {
				$remove_styles = false;
			}
			return $remove_styles;
		}


		/**
		 * Allows or disallows changing the page template for bricks.
		 *
		 * @param bool $is_allow Whether to allow changing the page template.
		 * @return bool Returns false to prevent changing the page template.
		 */
		public function allow_to_change_page_template( $is_allow ) {
			$is_bricks_builder = self::bricks_editor_mode();
			if ( $is_bricks_builder ) {
				$is_allow = false;
			}
			return $is_allow;
		}


		/**
		 * Removes the header rendering action from the Bricks theme.
		 *
		 * This function checks if the Bricks\Frontend class exists. If it does, it creates an instance of it.
		 * It then checks if the 'render_header' action is hooked to the 'render_header' method of the Bricks\Frontend instance.
		 * If the action is hooked, it removes the action.
		 *
		 * @return void
		 */
		public function remove_bricks_render_header_footer() {
			$page_template = self::get_page_template();
			if ( _wcf_supported_template( $page_template ) && class_exists( 'Bricks\Theme' ) ) {
				$theme_instance = Bricks\Theme::instance();

				if ( method_exists( $theme_instance, 'init' ) ) {
					remove_action( 'render_header', array( $theme_instance->frontend, 'render_header' ) );
					remove_action( 'render_footer', array( $theme_instance->frontend, 'render_footer' ) );

				}
			}


		}

		/**
		 * Checks if the current CartFlows step is in Bricks editor mode.
		 *
		 * This function checks if the Bricks\Helpers class exists. If it does, it retrieves the ID of the current
		 * CartFlows step and uses the Bricks\Helpers::get_editor_mode method to determine if the step is in Bricks editor mode.
		 *
		 * @return bool Returns Bricks editor mode.
		 */
		public function bricks_editor_mode() {
			$editor_mode = false;
			if ( class_exists( 'Bricks\Helpers' ) ) {
				$post_id     = _get_wcf_step_id();
				$editor_mode = ( ( isset( $_GET['bricks'] ) && 'run' === $_GET['bricks'] ) || 'bricks' === Bricks\Helpers::get_editor_mode( $post_id ) ) ? true : false; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			}
			return $editor_mode;
		}

		/**
		 * Get page template of current step.
		 *
		 * @return string
		 */
		public function get_page_template() {
			$page_template = get_post_meta( _get_wcf_step_id(), '_wp_page_template', true );
			$page_template = apply_filters( 'cartflows_page_template', $page_template );
			return $page_template;

		}

		/**
		 * Load the CSS
		 *
		 * @since 1.5.7
		 *
		 * @return void
		 */
		public function bricks_compatibility_external_css() {

			wp_enqueue_style( 'wcf-checkout-bricks-compatibility', CARTFLOWS_URL . 'compatibilities/themes/bricks/css/bricks-compatibility.css', '', CARTFLOWS_VER );
		}

		/**
		 * Add the support of CartFlows Post Type in Bricks Builder.
		 *
		 * This function updates the 'postTypes' array in $global_settings with specified post types,
		 * ensuring CartFlows steps can be editable by Bricks.
		 *
		 * @since 2.1.0
		 * @param array $global_settings The global settings of the Bricks Builder.
		 * @return array $global_settings The updated array with CartFlows step post type.
		 */
		public function add_step_post_type_to_supported_post_types( $global_settings ) {

			// Initilize the empty if array is empty.
			if ( empty( $global_settings['postTypes'] ) ) {
				$global_settings['postTypes'] = array();
			}

			// Add CartFlows step post type.
			$global_settings['postTypes'] = array_merge(
				$global_settings['postTypes'],
				array(
					'cartflows_step',
				)
			);

			// Return the updated post type array.
			return $global_settings;
		}


		/**
		 * Remove Bricks Editor option for Instant Checkout.
		 *
		 * @since 2.1.0
		 * @return void
		 */
		public function remove_bricks_editor_option_instant_checkout() {
			$is_bricks_builder = self::bricks_editor_mode();
			$step_id           = _get_wcf_step_id();
			if ( wcf()->utils->is_step_post_type() ) {
				if ( $is_bricks_builder && Cartflows_Helper::is_instant_layout_enabled() && ( _is_wcf_checkout_type() || _is_wcf_thankyou_type() ) ) {
					// Reason: The WordPress is kept in small caps as it's value is used by Bricks in a small caps.
					update_post_meta( $step_id, BRICKS_DB_EDITOR_MODE, 'wordpress' ); // phpcs:ignore WordPress.WP.CapitalPDangit.Misspelled
				}
			}
		}

		/**
		 * Remove Bricks Editor links from admin toolbar.
		 *
		 * @since 2.1.0
		 * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance.
		 * @return void
		 */
		public function remove_editor_links_from_admin_toolbar( $wp_admin_bar ) {
			if ( Cartflows_Helper::is_instant_layout_enabled() && ( _is_wcf_checkout_type() || _is_wcf_thankyou_type() ) ) {
				$wp_admin_bar->remove_node( 'editor_mode' );
				$wp_admin_bar->remove_node( 'edit_with_bricks' );
			}
		}



	}
	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	Cartflows_Bricks_Compatibility::get_instance();

endif;
