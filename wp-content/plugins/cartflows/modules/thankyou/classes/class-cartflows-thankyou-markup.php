<?php
/**
 * Front end and markup
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Checkout Markup
 *
 * @since 1.0.0
 */
class Cartflows_Thankyou_Markup {



	/**
	 * Member Variable
	 *
	 * @var object instance
	 */
	private static $instance;

	/**
	 *  Initiator
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 *  Constructor
	 */
	public function __construct() {
		/* Downsell Shortcode */
		add_shortcode( 'cartflows_order_details', array( $this, 'cartflows_order_details_shortcode_markup' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'thank_you_scripts' ), 21 );

		add_action( 'woocommerce_is_order_received_page', array( $this, 'set_order_received_page' ) );

		/* Set is checkout flag */
		add_filter( 'woocommerce_is_checkout', array( $this, 'woo_checkout_flag' ), 9999 );

		/* Custom redirection of thank you page */
		add_action( 'template_redirect', array( $this, 'redirect_tq_page_to_custom_url' ) );

		add_action( 'cartflows_thank_you_scripts', array( $this, 'add_divi_compatibility_css' ) );

		add_action( 'wp', array( $this, 'secure_thank_you_page' ), 10 );

		add_action( 'wp', array( $this, 'register_instant_thankyou_actions' ), 10, 1 );
		add_action( 'cartflows_thank_you_scripts', array( $this, 'load_instant_thankyou_scripts' ), 10, 1 );
	}

	/**
	 * Load instant thankyou actions.
	 */
	public function load_instant_thankyou_scripts() {

		$flow_id = wcf()->utils->get_flow_id();

		if ( ! empty( $flow_id ) && Cartflows_Helper::is_instant_layout_enabled( (int) $flow_id ) ) {

			add_action( 'body_class', array( $this, 'add_body_class_for_instant_thankyou' ), 10, 1 );
			// Load the required styles and scripts.
			wp_enqueue_style( 'wcf-instant-thankyou', wcf()->utils->get_css_url( 'instant-thankyou-styles' ), '', CARTFLOWS_VER );
		}
	}

	/**
	 * Register actions for instant thankyou layout.
	 *
	 * @since 2.1.0
	 * @return void
	 */
	public function register_instant_thankyou_actions() {

		$flow_id = wcf()->utils->get_flow_id();

		$thank_you_id = _get_wcf_thankyou_id();

		// Returns false if no thank you page ID is found.
		if ( ! $thank_you_id ) {
			return;
		}

		if ( _is_wcf_thankyou_type() && ! empty( $flow_id ) && Cartflows_Helper::is_instant_layout_enabled( (int) $flow_id ) ) {

			add_action( 'body_class', array( $this, 'add_body_class_for_instant_thankyou' ), 10, 1 );
			add_filter( 'cartflows_thankyou_template_data', array( $this, 'add_template_data' ), 10, 1 );
		}

	}

	/**
	 * Add body classes for instant thankyou layout.
	 *
	 * @param array $body_classes The body classes.
	 * @return array $body_classes Modified body added classes/
	 */
	public function add_body_class_for_instant_thankyou( $body_classes ) {
		$body_classes[] = 'cartflows-instant-checkout';
		return $body_classes;
	}

	/**
	 * Add template data.
	 *
	 * @param array $template_data template data.
	 * @return array $template_data Modified template data of instant layout.
	 */
	public function add_template_data( $template_data ) {

		if ( Cartflows_Helper::is_instant_layout_enabled( (int) $template_data['flow_id'] ) ) {
			$template_data['load_custom']   = true;
			$template_data['template']      = 'templates/instant-thankyou.php';
			$template_data['template_path'] = CARTFLOWS_THANKYOU_DIR;
		}

		return $template_data;
	}

	/**
	 * Prepare Instant Thank You Header Template.
	 *
	 * @since 2.1.0
	 * @return void
	 */
	public function instant_checkout_header_template() {
		ob_start();
		include CARTFLOWS_CHECKOUT_DIR . 'templates/checkout/page-template/wcf-ic-header-template.php';
		$header = ob_get_clean();
		echo wp_kses_post( $header );
	}

	/**
	 * Restrict users to access the thank you page directly.
	 */
	public function secure_thank_you_page() {

		$thank_you_id = _get_wcf_thankyou_id();

		// Returns false if no thank you page ID is found.
		if ( ! $thank_you_id ) {
			return;
		}

		if ( ! apply_filters( 'cartflows_thankyou_direct_access', wcf()->flow->is_flow_testmode(), $thank_you_id ) && _is_wcf_thankyou_type() && ( ! is_user_logged_in() || ! current_user_can( 'cartflows_manage_flows_steps' ) ) ) {
			//phpcs:disable WordPress.Security.NonceVerification.Recommended
			if ( isset( $_GET['wcf-key'] ) && isset( $_GET['wcf-order'] ) ) {

				$order_id  = intval( $_GET['wcf-order'] );
				$order_key = wc_clean( wp_unslash( $_GET['wcf-key'] ) );
			//phpcs:enable WordPress.Security.NonceVerification.Recommended
				$order = wc_get_order( $order_id );

				if ( ! $order || $order->get_order_key() !== $order_key ) {
					wp_die( esc_html__( 'We can\'t seem to find an order for you.', 'cartflows' ) );
				}
			} else {
				wp_die( esc_html__( 'We can\'t seem to find an order for you.', 'cartflows' ) );
			}
		}
	}

	/**
	 *  Redirect to custom url instead of thank you page.
	 */
	public function redirect_tq_page_to_custom_url() {
		global $post;

		if ( _is_wcf_thankyou_type() ) {

			$thank_you_id       = $post->ID;
			$enable_redirection = wcf()->options->get_thankyou_meta_value( $thank_you_id, 'wcf-show-tq-redirect-section' );
			$redirect_link      = wp_http_validate_url( wcf()->options->get_thankyou_meta_value( $thank_you_id, 'wcf-tq-redirect-link' ) );

			if ( 'yes' === $enable_redirection && ! empty( $redirect_link ) ) {
				// Reason for ignore: URL will be manually added by the admin user in the thank you page setting.
				wp_redirect( esc_url_raw( $redirect_link ) ); //phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
				exit;
			}
		}
	}
	/**
	 * Order shortcode markup
	 *
	 * @param array $atts attributes.
	 * @since 1.0.0
	 */
	public function cartflows_order_details_shortcode_markup( $atts ) {
		$output = '';

		$show_demo_order = false;

		// Allow to execute the order detais shortcode for modules.
		if ( current_user_can( 'manage_options' ) ) {
			$show_demo_order = apply_filters( 'cartflows_show_demo_order_details', false );
		}

		if ( _is_wcf_thankyou_type() || $show_demo_order ) {
			/* Remove order item link */
			add_filter( 'woocommerce_order_item_permalink', '__return_false' );

			/* Change order text */
			add_filter( 'woocommerce_thankyou_order_received_text', array( $this, 'custom_tq_text' ), 10, 2 );

			if ( ! function_exists( 'wc_print_notices' ) ) {

				$notice_out  = '<p class="woocommerce-notice">' . __( 'WooCommerce functions do not exist. If you are in an IFrame, please reload it.', 'cartflows' ) . '</p>';
				$notice_out .= '<button onClick="location.reload()">' . __( 'Click Here to Reload', 'cartflows' ) . '</button>';

				return $notice_out;
			}

			$order               = false;
			$thank_you_step_id   = _get_wcf_thankyou_id();
			$is_instant_checkout = Cartflows_Helper::is_instant_layout_enabled();

			$id_param  = 'wcf-order';
			$key_param = 'wcf-key';
			//phpcs:disable WordPress.Security.NonceVerification.Recommended
			if ( isset( $_GET['wcf-opt-order'] ) ) {
				$id_param  = 'wcf-opt-order';
				$key_param = 'wcf-opt-key';
			}

			if ( ! isset( $_GET[ $id_param ] ) && ( wcf()->flow->is_flow_testmode() || $show_demo_order ) ) {
				$args = array(
					'limit'     => 1,
					'order'     => 'DESC',
					'post_type' => 'shop_order',
					'status'    => array( 'completed', 'processing' ),
				);

				$latest_order = wc_get_orders( $args );

				$order_id = ( ! empty( $latest_order ) ) ? current( $latest_order )->get_id() : 0;

				if ( $order_id > 0 ) {
					$order = wc_get_order( $order_id );

					if ( ! $order ) {
						$order = false;
					}
				} else {
					if ( $is_instant_checkout ) {
						return $this->render_empty_cart_message_block( $thank_you_step_id );
					} else {
						return '<p class="woocommerce-notice">' . __( 'No completed or processing order found to show the order details form demo.', 'cartflows' ) . '</p>';
					}
				}
			} else {
				if ( ! isset( $_GET[ $id_param ] ) ) {
					if ( $is_instant_checkout ) {
						return $this->render_empty_cart_message_block( $thank_you_step_id );
					} else {
						return '<p class="woocommerce-notice">' . __( 'Order not found. You cannot access this page directly.', 'cartflows' ) . '</p>';
					}
				}

				// Get the order.
				$order_id  = apply_filters( 'woocommerce_thankyou_order_id', empty( $_GET[ $id_param ] ) ? 0 : intval( $_GET[ $id_param ] ) );
				$order_key = apply_filters( 'woocommerce_thankyou_order_key', empty( $_GET[ $key_param ] ) ? '' : wc_clean( wp_unslash( $_GET[ $key_param ] ) ) );
				//phpcs:enable WordPress.Security.NonceVerification.Recommended
				if ( $order_id > 0 ) {
					$order = wc_get_order( $order_id );

					if ( ! $order || $order->get_order_key() !== $order_key ) {
						$order = false;
					}
				}
			}

			// Empty awaiting payment session.
			unset( WC()->session->order_awaiting_payment );

			if ( null !== WC()->session ) {
				if ( ! isset( WC()->cart ) || '' === WC()->cart ) {
					WC()->cart = new WC_Cart();
				}

				if ( ! WC()->cart->is_empty() ) {
					// wc_empty_cart();
					// Empty current cart.
					WC()->cart->empty_cart( true );

					wc_clear_notices();
				}

				wc_print_notices();
			}

			do_action( 'cartflows_thankyou_details_before', $order );

			ob_start();

			$default_data = array(
				'order'         => array( 'order' => $order ),
				'template'      => 'checkout/thankyou.php',
				'template_path' => '',
				'flow_id'       => wcf()->utils->get_flow_id(),
				'load_custom'   => false,
			);

			$template_data = apply_filters( 'cartflows_thankyou_template_data', $default_data );

			if ( ! $template_data['load_custom'] ) {
				echo "<div class='wcf-thankyou-wrap' id='wcf-thankyou-wrap'>";
				wc_get_template( $template_data['template'], $template_data['order'], $template_data['template_path'] );
				echo '</div>';
			} else {
				include $template_data['template_path'] . $template_data['template'];
			}

			$output = ob_get_clean();
		}

		return $output;
	}

	/**
	 * Load Thank You scripts.
	 *
	 * @return void
	 */
	public function thank_you_scripts() {
		if ( _is_wcf_thankyou_type() ) {

			do_action( 'cartflows_thank_you_scripts' );

			$thank_you_dynamic_css = apply_filters( 'cartflows_thank_you_enable_dynamic_css', true );

			if ( $thank_you_dynamic_css ) {

				global $post;

				$thanku_page_id = $post->ID;

				$style = get_post_meta( $thanku_page_id, 'wcf-dynamic-css', true );

				$css_version = get_post_meta( $thanku_page_id, 'wcf-dynamic-css-version', true );
				if ( empty( $style ) || CARTFLOWS_ASSETS_VERSION !== $css_version ) {
					$style = $this->generate_thank_you_style();
					update_post_meta( $thanku_page_id, 'wcf-dynamic-css', wp_slash( $style ) );
					update_post_meta( $thanku_page_id, 'wcf-dynamic-css-version', CARTFLOWS_ASSETS_VERSION );
				}

				CartFlows_Font_Families::render_fonts( $thanku_page_id );
				wp_add_inline_style( 'wcf-frontend-global', $style );
			}
		}
	}

	/**
	 * Load DIVI compatibility Thank You style.
	 *
	 * @return void
	 */
	public function add_divi_compatibility_css() {
		global $post;

		$thank_you_id = $post->ID;

		if (
			Cartflows_Compatibility::get_instance()->is_divi_enabled() ||
			Cartflows_Compatibility::get_instance()->is_divi_builder_enabled( $thank_you_id )
		) {
			wp_enqueue_style( 'wcf-frontend-global-divi', wcf()->utils->get_css_url( 'frontend-divi' ), array(), CARTFLOWS_VER );
		}
	}

	/**
	 * Set thank you as a order received page.
	 *
	 * @param boolean $is_order_page order page.
	 * @return boolean
	 */
	public function set_order_received_page( $is_order_page ) {
		if ( _is_wcf_thankyou_type() ) {
			$is_order_page = true;
		}

		return $is_order_page;
	}

	/**
	 * Generate Thank You Styles.
	 *
	 * @return string
	 */
	public function generate_thank_you_style() {
		global $post;

		if ( _is_wcf_thankyou_type() ) {
			$thank_you_id = $post->ID;
		} else {
			$thank_you_id = _get_wcf_thankyou_id( $post->post_content );
		}

		$output              = '';
		$primary_color       = '';
		$text_color          = '';
		$text_font_family    = '';
		$text_font_size      = '';
		$heading_text_color  = '';
		$heading_font_family = '';
		$heading_font_weight = '';
		$container_width     = '';
		$section_bg_color    = '';
		$design_css          = array();

		$enable_design_settings = wcf()->options->get_thankyou_meta_value( $thank_you_id, 'wcf-enable-design-settings' );

		$hide_show_settings = array(
			'show_order_review'     => wcf()->options->get_thankyou_meta_value( $thank_you_id, 'wcf-show-overview-section' ),
			'show_order_details'    => wcf()->options->get_thankyou_meta_value( $thank_you_id, 'wcf-show-details-section' ),
			'show_billing_details'  => wcf()->options->get_thankyou_meta_value( $thank_you_id, 'wcf-show-billing-section' ),
			'show_shipping_details' => wcf()->options->get_thankyou_meta_value( $thank_you_id, 'wcf-show-shipping-section' ),
		);

		if ( 'yes' === $enable_design_settings ) {

			$primary_color       = wcf()->options->get_thankyou_meta_value( $thank_you_id, 'wcf-tq-primary-color' );
			$text_color          = wcf()->options->get_thankyou_meta_value( $thank_you_id, 'wcf-tq-text-color' );
			$text_font_family    = wcf()->options->get_thankyou_meta_value( $thank_you_id, 'wcf-tq-font-family' );
			$text_font_size      = wcf()->options->get_thankyou_meta_value( $thank_you_id, 'wcf-tq-font-size' );
			$heading_text_color  = wcf()->options->get_thankyou_meta_value( $thank_you_id, 'wcf-tq-heading-color' );
			$heading_font_family = wcf()->options->get_thankyou_meta_value( $thank_you_id, 'wcf-tq-heading-font-family' );
			$heading_font_weight = wcf()->options->get_thankyou_meta_value( $thank_you_id, 'wcf-tq-heading-font-wt' );
			$container_width     = wcf()->options->get_thankyou_meta_value( $thank_you_id, 'wcf-tq-container-width' );
			$section_bg_color    = wcf()->options->get_thankyou_meta_value( $thank_you_id, 'wcf-tq-section-bg-color' );

			$design_css = array(
				'primary_color'       => $primary_color,
				'text_color'          => $text_color,
				'text_font_family'    => $text_font_family,
				'text_font_size'      => $text_font_size,
				'heading_text_color'  => $heading_text_color,
				'heading_font_family' => $heading_font_family,
				'heading_font_weight' => $heading_font_weight,
				'container_width'     => $container_width,
				'section_bg_color'    => $section_bg_color,
			);

			if (
				Cartflows_Compatibility::get_instance()->is_divi_enabled() ||
				Cartflows_Compatibility::get_instance()->is_divi_builder_enabled( $thank_you_id )
			) {

				include CARTFLOWS_THANKYOU_DIR . 'includes/thankyou-dynamic-divi-css.php';
			} else {
				include CARTFLOWS_THANKYOU_DIR . 'includes/thankyou-dynamic-css.php';
			}
		}

		$output .= $this->get_section_hide_show_css( $output, $hide_show_settings );

		$output .= $this->get_instant_thankyou_css( $output, $thank_you_id, $design_css );

		return apply_filters( 'cartflows_thank_you_generated_styles', $output, $thank_you_id, $design_css );
	}

	/**
	 * Generate Instant Thank You CSS
	 *
	 * @since 2.1.0
	 *
	 * @param string $output Already generated CSS.
	 * @param int    $thank_you_id Thank you page ID.
	 * @param array  $design_css Design CSS.
	 * @return string $output Modified CSS
	 */
	public function get_instant_thankyou_css( $output, $thank_you_id, $design_css ) {

		// Don't generate the instant thank you page is not enabled.
		if ( ! Cartflows_Helper::is_instant_layout_enabled() ) {
			return $output;
		}

		// Getting the css values from free version options.
		$primary_color       = isset( $design_css['primary_color'] ) ? $design_css['primary_color'] : '';
		$text_color          = isset( $design_css['text_color'] ) ? $design_css['text_color'] : '';
		$text_font_family    = isset( $design_css['text_font_family'] ) ? $design_css['text_font_family'] : '';
		$text_font_size      = isset( $design_css['text_font_size'] ) ? $design_css['text_font_size'] : '';
		$heading_text_color  = isset( $design_css['heading_text_color'] ) ? $design_css['heading_text_color'] : '';
		$heading_font_family = isset( $design_css['heading_font_family'] ) ? $design_css['heading_font_family'] : '';
		$heading_font_weight = isset( $design_css['heading_font_weight'] ) ? $design_css['heading_font_weight'] : '';
		$container_width     = isset( $design_css['container_width'] ) ? $design_css['container_width'] : '';
		$section_bg_color    = isset( $design_css['section_bg_color'] ) ? $design_css['section_bg_color'] : '';

		// Prepare the CSS values for PRO options.
		$button_bg_color                        = wcf()->options->get_thankyou_meta_value( $thank_you_id, 'wcf-tq-button-background-color' );
		$button_text_color                      = wcf()->options->get_thankyou_meta_value( $thank_you_id, 'wcf-tq-button-text-color' );
		$button_font_family                     = wcf()->options->get_thankyou_meta_value( $thank_you_id, 'wcf-tq-button-font-family' );
		$instant_thankyou_left_column_bg_color  = wcf()->options->get_thankyou_meta_value( $thank_you_id, 'wcf-instant-thankyou-left-side-bg-color' );
		$instant_thankyou_right_column_bg_color = wcf()->options->get_thankyou_meta_value( $thank_you_id, 'wcf-instant-thankyou-right-side-bg-color' );

		$output     .= 'body .wcf-instant-thankyou { ';
			$output .= ! empty( $primary_color ) ? '--wcf-primary-color: ' . $primary_color . ';' : '';
			$output .= ! empty( $instant_thankyou_right_column_bg_color ) ? '--wcf-instant-thankyou-right-side-bg-color: ' . $instant_thankyou_right_column_bg_color . ';' : '';
			$output .= ! empty( $instant_thankyou_left_column_bg_color ) ? '--wcf-instant-thankyou-left-side-bg-color: ' . $instant_thankyou_left_column_bg_color . ';' : '';
			// setting the border color for the section same as background color.
			$output .= ! empty( $instant_thankyou_left_column_bg_color ) ? '--wcf-section-border-color: ' . $instant_thankyou_left_column_bg_color . ';' : '';

			$output .= ! empty( $button_bg_color ) ? '--wcf-btn-bg-color: ' . $button_bg_color . ';' : '';
			$output .= ! empty( $button_text_color ) ? '--wcf-button-tx-color: ' . $button_text_color . ';' : '';
			$output .= ! empty( $button_font_family ) ? '--wcf-button-font-family: ' . $button_font_family . ';' : '';

			$output .= ! empty( $text_color ) ? '--wcf-text-color: ' . $text_color . ';' : '';
			$output .= ! empty( $text_font_family ) ? '--wcf-text-font-family: ' . $text_font_family . ';' : '';
			$output .= ! empty( $text_font_size ) ? '--wcf-text-font-size: ' . $text_font_size . 'px;' : '';
			$output .= ! empty( $heading_text_color ) ? '--wcf-heading-text-color: ' . $heading_text_color . ';' : '';
			$output .= ! empty( $heading_font_weight ) ? '--wcf-heading-font-weight: ' . $heading_font_weight . ';' : '';
			$output .= ! empty( $heading_font_family ) ? '--wcf-heading-font-family: ' . $heading_font_family . ';' : '';
			$output .= ! empty( $container_width ) ? '--wcf-container-width: ' . $container_width . ';' : '';
			$output .= ! empty( $section_bg_color ) ? '--wcf-section-bg-color: ' . $section_bg_color . ';' : '';
		$output     .= '}';

		return $output;
	}

	/**
	 * Generate CSS for hide/show the order sections on the thank you page.
	 *
	 * @param string $output Already generated CSS.
	 * @param array  $hide_show_settings Enable/disable sections setting of thank page.
	 *
	 * @return string $output Modified CSS
	 */
	public function get_section_hide_show_css( $output, $hide_show_settings ) {

		if ( 'no' == $hide_show_settings['show_order_review'] ) {
			$output .= '
				.woocommerce-order ul.order_details{
					display: none;
				}
				';
		}

		if ( 'no' == $hide_show_settings['show_order_details'] ) {
			$output .= '
				.woocommerce-order .woocommerce-order-details{
					display: none;
				}
				';
		}

		if ( 'no' == $hide_show_settings['show_billing_details'] ) {
			$output .= '
				.woocommerce-order .woocommerce-customer-details .woocommerce-column--billing-address{
					display: none;
				}
				.woocommerce-order .woocommerce-customer-details .woocommerce-column--shipping-address{
					float:left;
				}
				';
		}

		if ( 'no' == $hide_show_settings['show_shipping_details'] ) {
			$output .= '
				.woocommerce-order .woocommerce-customer-details .woocommerce-column--shipping-address{
					display: none;
				}
				';
		}

		if ( 'no' == $hide_show_settings['show_billing_details'] && 'no' == $hide_show_settings['show_shipping_details'] ) {
			$output .= '
				.woocommerce-order .woocommerce-customer-details{
					display: none;
				}
				';
		}

		return $output;

	}

	/**
	 * Set as a checkout page if it is thank you page.
	 * Thank you page need to be set as a checkout page.
	 * Becauye ayment gateways will not load if it is not checkout.
	 *
	 * @param bool $is_checkout is checkout.
	 *
	 * @return bool
	 */
	public function woo_checkout_flag( $is_checkout ) {
		if ( ! is_admin() ) {
			if ( _is_wcf_thankyou_type() ) {
				$is_checkout = true;
			}
		}

		return $is_checkout;
	}

	/**
	 *  Add custom text on thank you page.
	 *
	 * @param string $woo_text Default text.
	 * @param int    $order order.
	 */
	public function custom_tq_text( $woo_text, $order ) {
		global $post;

		if ( $post ) {
			$thank_you_id = $post->ID;
		} else {
			if ( is_admin() && isset( $_POST['id'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing
				$thank_you_id = intval( $_POST['id'] ); //phpcs:ignore WordPress.Security.NonceVerification.Missing
			}
		}

		$new_text = wcf()->options->get_thankyou_meta_value( $thank_you_id, 'wcf-tq-text' );

		if ( ! empty( $new_text ) ) {
			$woo_text = do_shortcode( wp_kses_post( $new_text ) );
		}

		return $woo_text;
	}

	/**
	 * Add a designed block message for an empty cart.
	 *
	 * This method is used to add a custom message or block when the cart is empty.
	 * The message or block design is typically intended to be displayed in place of
	 * the cart contents, providing a more visually appealing notification or call-to-action
	 * for the user to take further steps (e.g., continue shopping or view products).
	 *
	 * @since 2.1.0
	 * @param int $thank_you_id The current step ID.
	 * @return string $output The HTML of empty cart block.
	 */
	public function render_empty_cart_message_block( $thank_you_id ) {

		$shop_url = wc_get_page_permalink( 'shop' );
		$message  = __( 'No completed or processing order found to show the order details form demo.', 'cartflows' );

		$output                      = '<div class="wcf-empty-cart-notice-block">';
			$output                 .= '<div class="wcf-empty-cart-message-container">';
				$output             .= '<div class="wcf-empty-cart-wrapper">';
					$output         .= '<div class="wcf-empty-cart-icon">';
						$output     .= '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon" class="">';
							$output .= '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />';
						$output     .= '</svg>';
					$output         .= '</div>';
					$output         .= '<div class="wcf-empty-cart-content">';
						$output     .= '<h2 class="wcf-empty-cart-heading">' . esc_html( apply_filters( 'cartflows_checkout_empty_cart_heading', __( 'Order Details Not Found.', 'cartflows' ) ) ) . '</h2>';
						$output     .= '<p class="wcf-empty-cart-message">' . esc_html( apply_filters( 'cartflows_checkout_empty_cart_message', $message ) ) . '</p>';
						$output     .= '<a href="' . esc_url( $shop_url ) . '" class="wcf-empty-cart-button">' . esc_html( apply_filters( 'cartflows_checkout_empty_cart_button_text', __( 'Return to Shopping', 'cartflows' ) ) ) . '</a>';
					$output         .= '</div>';
				$output             .= '</div>';
			$output                 .= '</div>';
		$output                     .= '</div>';

		return $output;

	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Thankyou_Markup::get_instance();
