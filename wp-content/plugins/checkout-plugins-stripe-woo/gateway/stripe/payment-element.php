<?php
/**
 * Stripe Payment element Gateway
 *
 * @package checkout-plugins-stripe-woo
 * @since 1.9.0
 */

namespace CPSW\Gateway\Stripe;

use CPSW\Inc\Helper;
use CPSW\Inc\Logger;
use CPSW\Inc\Traits\Get_Instance;
use CPSW\Inc\Traits\Subscriptions;
use CPSW\Gateway\Abstract_Payment_Gateway;
use CPSW\Gateway\Stripe\Stripe_Api;
use CPSW\Inc\Notice;
use WC_AJAX;
use WC_HTTPS;
use WC_Payment_Token_CC;
use CPSW\Inc\Token;
use Exception;
use WP_Error;

/**
 * Payment_Element
 *
 * @extends Abstract_Payment_Gateway
 * @since 1.9.0
 */
class Payment_Element extends Abstract_Payment_Gateway {

	use Get_Instance;
	use Subscriptions;

	/**
	 * Gateway id
	 *
	 * @var string
	 */
	public $id = 'cpsw_stripe_element';

	/**
	 * Payment method types
	 *
	 * @var string
	 */
	public $payment_method_types = [ 'card' ];

	/**
	 * Should enable the saving of card tokens.
	 *
	 * @var string
	 */
	public $enable_saved_cards = false;

	/**
	 * Statement descriptor of the payment method.
	 *
	 * @var string
	 */
	public $statement_descriptor;

	/**
	 * Constructor
	 *
	 * @since 1.9.0
	 */
	public function __construct() {
		parent::__construct();

		$this->method_title       = __( 'Stripe Options', 'checkout-plugins-stripe-woo' );
		$this->method_description = $this->method_description();
		$this->has_fields         = true;
		$this->init_supports();
		$this->maybe_init_subscriptions();

		$this->init_form_fields();
		$this->init_settings();

		// get_option should be called after init_form_fields().
		$this->title                = $this->get_option( 'title' );
		$this->description          = $this->get_option( 'description' );
		$this->order_button_text    = $this->get_option( 'order_button_text' );
		$this->enable_saved_cards   = $this->get_option( 'enable_saved_cards' );
		$this->statement_descriptor = $this->clean_statement_descriptor( $this->get_option( 'statement_descriptor' ) );

		add_action( 'admin_init', [ $this, 'add_notice' ] );
		add_filter( 'woocommerce_payment_successful_result', [ $this, 'modify_successful_payment_result' ], 999, 2 );
		add_action( 'wc_ajax_' . $this->id . '_verify_payment_intent', [ $this, 'verify_intent' ] );
	}

	/**
	 * Registers supported filters for payment gateway
	 *
	 * @return void
	 */
	public function init_supports() {
		$this->supports = apply_filters(
			'cpsw_stripe_element_supports',
			[
				'products',
				'refunds',
				'tokenization',
				'add_payment_method',
			]
		);
	}

	/**
	 * Description for the method
	 *
	 * @since 1.9.0
	 *
	 * @return string
	 */
	public function method_description() {
		$payment_description = sprintf(
			/* translators: %1$s: HTML entities, %2$s: HTML entities */
			__( 'Payment with stripe options support only %1$sCard, Alipay, iDeal, Giropay, Bancontact, EPS, Przelewy24, Klarna, SEPA, WeChat and CashApp%2$s payment methods. ', 'checkout-plugins-stripe-woo' ),
			'<b>',
			'</b>'
		);
		$extra_description = sprintf(
			/* translators: %1$s: HTML entities, %2$s: HTML entities, %3$s: Break */
			__( 'To utilize these payment methods, ensure that you have enabled the respective payment methods in your %1$s Stripe Dashboard settings%2$s. %3$sThe availability of payment methods on checkout is also determined by the currency and country.', 'checkout-plugins-stripe-woo' ),
			'<a href="https://dashboard.stripe.com/settings/payments">',
			'</a>',
			'<br />'
		);

		return sprintf(
			/* translators: %1$s: Break, %2$s: Gateway appear message, %3$s: Break, %4$s: Gateway appear message */
			__( 'Accepts payments via multiple payment methods. %1$s %2$s %3$s %4$s', 'checkout-plugins-stripe-woo' ),
			'<br/>',
			$payment_description,
			'<br/>',
			$extra_description
		);
	}

	/**
	 * Gateway form fields
	 *
	 * @return void
	 */
	public function init_form_fields() {
		$this->form_fields = apply_filters(
			'cpsw_stripe_element_form_fields',
			[
				'enabled'              => [
					'label'   => ' ',
					'type'    => 'hidden',
					'default' => 'yes',
				],
				'title'                => [
					'title'       => __( 'Title', 'checkout-plugins-stripe-woo' ),
					'type'        => 'text',
					'description' => __( 'Title for this payment method', 'checkout-plugins-stripe-woo' ),
					'default'     => __( 'Flexible Payment Options', 'checkout-plugins-stripe-woo' ),
					'desc_tip'    => true,
				],
				'description'          => [
					'title'       => __( 'Description', 'checkout-plugins-stripe-woo' ),
					'type'        => 'textarea',
					'css'         => 'width:25em',
					'description' => __( 'Description for this payment method', 'checkout-plugins-stripe-woo' ),
					'desc_tip'    => true,
				],
				'additional_methods'   => [
					'title'       => __( 'Additional payment methods', 'checkout-plugins-stripe-woo' ),
					'type'        => 'multiselect',
					'class'       => 'cpsw_select_woo',
					'description' => __( 'Giropay and EPS support only EUR currency, while Cashapp supports USD currency exclusively.', 'checkout-plugins-stripe-woo' ),
					'desc_tip'    => __( 'Choose additional payment methods that you want to display on checkout page.', 'checkout-plugins-stripe-woo' ),
					'options'     => Helper::$additional_gateways,
				],
				'enable_saved_cards'   => [
					'label'       => __( 'Enable Payments via Saved Cards', 'checkout-plugins-stripe-woo' ),
					'title'       => __( 'Saved Cards', 'checkout-plugins-stripe-woo' ),
					'type'        => 'checkbox',
					'description' => __( 'Save card details for future orders. Currently it is available only for payments with Card and SEPA.', 'checkout-plugins-stripe-woo' ),
					'default'     => 'no',
					'desc_tip'    => true,
				],
				'statement_descriptor' => [
					'title'       => __( 'Statement Descriptor', 'checkout-plugins-stripe-woo' ),
					'type'        => 'text',
					'description' => __( 'Statement descriptors should be 22 characters or less. Avoid special characters  >, <, ", \, *, /, (, ), {, }, and and ensure your descriptor isn\'t solely numeric. This will appear on your customer\'s statement in capital letters.', 'checkout-plugins-stripe-woo' ),
					'default'     => get_bloginfo( 'name' ),
					'desc_tip'    => true,
				],
				'order_button_text'    => [
					'title'       => __( 'Order Button Label', 'checkout-plugins-stripe-woo' ),
					'type'        => 'text',
					'description' => __( 'Customize label for Order button', 'checkout-plugins-stripe-woo' ),
					'desc_tip'    => true,
					'placeholder' => 'Place order',
				],
				'layout'               => [
					'type'        => 'select',
					'title'       => __( 'Layout', 'checkout-plugins-stripe-woo' ),
					'options'     => [
						'tabs'      => __( 'Tabs', 'checkout-plugins-stripe-woo' ),
						'accordion' => __( 'Accordion', 'checkout-plugins-stripe-woo' ),
					],
					'default'     => 'tabs',
					'desc_tip'    => true,
					'description' => __( 'Choose a option to customize the Payment Element’s layout to fit your checkout flow.', 'checkout-plugins-stripe-woo' ),
				],
				'appearance'           => [
					'type'        => 'select',
					'title'       => __( 'Theme', 'checkout-plugins-stripe-woo' ),
					'options'     => [
						'stripe'      => __( 'Stripe', 'checkout-plugins-stripe-woo' ),
						'night'       => __( 'Night', 'checkout-plugins-stripe-woo' ),
						'flat'        => __( 'Flat', 'checkout-plugins-stripe-woo' ),
						'minimal'     => __( 'Minimal', 'checkout-plugins-stripe-woo' ),
						'bubblegum'   => __( 'Bubblegum', 'checkout-plugins-stripe-woo' ),
						'dark-blue'   => __( 'Dark Blue', 'checkout-plugins-stripe-woo' ),
						'ninety-five' => __( 'Ninety Five', 'checkout-plugins-stripe-woo' ),
					],
					'default'     => 'stripe',
					'desc_tip'    => true,
					'description' => __( 'This option helps to change theme of the payment method.', 'checkout-plugins-stripe-woo' ),
				],
			]
		);
	}

	/**
	 * Checks whether saved card settings is enabled or not.
	 *
	 * @return bool
	 */
	public function enable_saved_cards() {
		return ( $this->supports( 'tokenization' ) && 'yes' === $this->enable_saved_cards && is_user_logged_in() ) ? true : false;
	}

	/**
	 * Check current section is cpsw section.
	 *
	 * @since 1.9.0
	 *
	 * @return boolean
	 */
	public function is_current_section() {
		return Notice::is_cpsw_section( $this->id );
	}

	/**
	 * Add notices
	 *
	 * @since 1.9.0
	 *
	 * @return void
	 */
	public function add_notice() {
		if ( 'yes' !== $this->enabled ) {
			return;
		}

		if ( ! $this->is_current_section() ) {
			return;
		}
	}

	/**
	 * Checks whether this gateway is available.
	 *
	 * @return boolean
	 */
	public function is_available() {
		if ( 'payment' !== Helper::get_setting( 'cpsw_element_type' ) ) {
			return false;
		}

		// Check if any payment method available for payment element.
		if ( empty( Helper::get_available_gateways() ) ) {
			return false;
		}

		return parent::is_available();
	}

	/**
	 * Creates markup for payment form for Payment element
	 *
	 * @return void
	 */
	public function payment_fields() {
		$display_tokenization = $this->supports( 'tokenization' ) && is_checkout();

		/**
		 * Action before payment field.
		 *
		 * @since 1.9.0
		 */
		do_action( $this->id . '_before_payment_field_checkout' );

		if ( $display_tokenization ) {
			$this->tokenization_script();
			$this->saved_payment_methods();
		}
		echo '<div class="cpsw_stripe_element_payment_form">';
		if ( $this->description ) {
			echo wp_kses_post( $this->description );
		}
		echo '<div id="cpsw_stripe_payment_element" class="cpsw_stripe_payment_element_field"></div>';
		echo '<div class="cpsw_stripe_payment_element_error"></div>';
		echo ( apply_filters( 'cpsw_display_save_payment_method_checkbox', $display_tokenization ) && $this->enable_saved_cards() ) ? '<span class="cpsw-save-cards cpsw-payment-element-save-method"><label><input type="checkbox" name="wc-cpsw_stripe_element-new-payment-method" value="on"/>' . wp_kses_post( apply_filters( 'cpsw_saved_cards_label', __( 'Save Card for Future Payments', 'checkout-plugins-stripe-woo' ) ) ) . '</label></span>' : '';

		echo '</div>';

		// Action after payment field.
		do_action( $this->id . '_after_payment_field_checkout' );
	}

	/**
	 * Process payment method functionality
	 *
	 * @return mixed
	 */
	public function add_payment_method() {
		$source_id = '';

		if ( empty( $_POST['payment_method_created'] ) && empty( $_POST['stripe_token'] ) || ! is_user_logged_in() ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing
			$error_msg = __( 'There was a problem adding the payment method.', 'checkout-plugins-stripe-woo' );
			/* translators: error msg */
			Logger::error( sprintf( __( 'Add payment method Error: %1$1s', 'checkout-plugins-stripe-woo' ), $error_msg ) );
			return;
		}

		$customer_id = $this->get_customer_id();

		$source        = ! empty( $_POST['payment_method_created'] ) ? wc_clean( wp_unslash( $_POST['payment_method_created'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Missing
		$stripe_api    = new Stripe_Api();
		$response      = $stripe_api->payment_methods( 'retrieve', [ $source ] );
		$source_object = $response['success'] ? $response['data'] : false;

		if ( isset( $source_object ) ) {
			if ( ! empty( $source_object->error ) ) {
				$error_msg = __( 'Invalid stripe source', 'checkout-plugins-stripe-woo' );
				wc_add_notice( $error_msg, 'error' );
				/* translators: error msg */
				Logger::error( sprintf( __( 'Add payment method Error: %1$1s', 'checkout-plugins-stripe-woo' ), $error_msg ) );
				return;
			}

			$source_id = $source_object->id;
		}
		$stripe_api = new Stripe_Api();
		$response   = $stripe_api->payment_methods( 'attach', [ $source_id, [ 'customer' => $customer_id ] ] );
		$response   = $response['success'] ? $response['data'] : false;
		$user       = wp_get_current_user();
		$user_id    = ( $user->ID && $user->ID > 0 ) ? $user->ID : false;
		$this->create_payment_token_for_user( $user_id, $source_object );

		if ( ! $response || is_wp_error( $response ) || ! empty( $response->error ) ) {
			$error_msg = __( 'Unable to attach payment method to customer', 'checkout-plugins-stripe-woo' );
			wc_add_notice( $error_msg, 'error' );
			/* translators: error msg */
			Logger::error( sprintf( __( 'Add payment method Error: %1$1s', 'checkout-plugins-stripe-woo' ), $error_msg ) );
			return;
		}

		do_action( 'cpsw_add_payment_method_' . ( isset( $_POST['payment_method'] ) ? wc_clean( wp_unslash( $_POST['payment_method'] ) ) : '' ) . '_success', $source_id, $source_object ); //phpcs:ignore WordPress.Security.NonceVerification.Missing

		Logger::info( __( 'New payment method added successfully', 'checkout-plugins-stripe-woo' ) );
		return [
			'result'   => 'success',
			'redirect' => wc_get_endpoint_url( 'payment-methods' ),
		];
	}

	/**
	 * Process woocommerce orders after payment is done
	 *
	 * @param int $order_id wooCommerce order id.
	 * @return mixed data to redirect after payment processing.
	 */
	public function process_payment( $order_id ) {
		if ( $this->maybe_change_subscription_payment_method( $order_id ) ) {
			return $this->process_change_subscription_payment_method( $order_id );
		}

		$order = wc_get_order( $order_id );

		if ( isset( $_POST['selected_payment_type'] ) ) {
			$this->payment_method_types = [ sanitize_text_field( $_POST['selected_payment_type'] ) ];
		}

		if ( $this->is_using_saved_payment_method() ) {
			return $this->process_payment_with_saved_payment_method( $order_id );
		}

		try {
			// Nonce verification already done in parent woocommerce function.
			if ( ! isset( $_POST['payment_method_created'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Missing
				return;
			}
			// Nonce verification already done in parent woocommerce function.
			$payment_method  = sanitize_text_field( $_POST['payment_method_created'] ); //phpcs:ignore WordPress.Security.NonceVerification.Missing
			$idempotency_key = $payment_method . '_' . $order->get_order_key();

			$data = [
				'amount'               => $this->get_formatted_amount( $order->get_total() ),
				'currency'             => $this->get_currency(),
				'description'          => $this->get_order_description( $order ),
				'metadata'             => $this->get_metadata( $order->get_id() ),
				'payment_method'       => $payment_method,
				'payment_method_types' => [ $this->payment_method_types ],
				'customer'             => $this->get_customer_id( $order ),
			];

			if ( $this->should_save_card( $order_id ) && in_array( $this->payment_method_types, Helper::$savecard_supported_gateways ) ) {
				$data['setup_future_usage'] = 'off_session';
			}

			if ( ! empty( trim( $this->statement_descriptor ) ) ) {
				$data['statement_descriptor'] = $this->statement_descriptor;
			}

			/* translators: %1$1s order id, %2$2s order total amount. */
			Logger::info( sprintf( __( 'Begin processing payment with new payment method for order %1$1s for the amount of %2$2s', 'checkout-plugins-stripe-woo' ), $order_id, $order->get_total() ) );
			$intent_data = $this->get_payment_intent( $order_id, $idempotency_key, apply_filters( 'cpsw_stripe_payment_intent_post_data', $data ), true );
			if ( $intent_data ) {
				if ( isset( $intent_data['success'] ) && false === $intent_data['success'] ) {
					$error = '';
					if ( 'currency' === $intent_data['type'] ) {
						$error = __( 'Contact seller. ', 'checkout-plugins-stripe-woo' );

						if ( 'test' === Helper::get_payment_mode() ) {
							$error = __( 'Store currency doesn\'t match stripe currency. ', 'checkout-plugins-stripe-woo' );
						}
					}

					if ( $error ) {
						/* translators: %1$1s error message from stripe. */
						Logger::info( sprintf( __( 'Stripe error:  %1$1s', 'checkout-plugins-stripe-woo' ), $error . $intent_data['message'] ) );
						wc_add_notice( $error . $intent_data['message'], 'error' );
					}

					return [
						'result'      => 'fail',
						'redirect'    => '',
						'stripeError' => $error . $intent_data['message'],
					];
				}

				$response_data = [
					'result'         => 'success',
					'redirect'       => $this->get_return_url( $order ),
					'intent_secret'  => $intent_data['client_secret'],
					'payment_method' => $payment_method,
					'save_card'      => $this->should_save_card( $order_id ),
				];

				/**
				 * This none is used to verify nonce for payment method checkout perform by block checkout.
				 */
				if ( isset( $_POST['payment_cc_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['payment_cc_nonce'] ), 'stripe_payment_nonce' ) ) {
					$response_data['verification_url'] = $this->get_verification_url( $order_id, $this->get_return_url( $order ), $this->should_save_card( $order_id ) );
				}

				return apply_filters( 'cpsw_stripe_element_return_intent_data', $response_data );
			} else {
				return [
					'result'   => 'fail',
					'redirect' => '',
				];
			}
		} catch ( Exception $e ) {
			Logger::error( $e->getMessage(), true );
			return new WP_Error( 'order-error', '<div class="woocommerce-error">' . $e->getMessage() . '</div>', [ 'status' => 200 ] );
		}
	}

	/**
	 * Modify redirect url
	 *
	 * @since 1.9.0
	 *
	 * @param array $result redirect url array.
	 * @param int   $order_id woocommerce order id.
	 *
	 * @return array modified redirect url array.
	 */
	public function modify_successful_payment_result( $result, $order_id ) {
		if ( empty( $order_id ) ) {
			return $result;
		}

		$order = wc_get_order( $order_id );

		if ( $this->id !== $order->get_payment_method() ) {
			return $result;
		}

		if ( ! isset( $result['intent_secret'] ) ) {
			return $result;
		}

		// Put the final thank you page redirect into the verification URL.
		$verification_url = $this->get_verification_url( $order_id, $result['redirect'], $result['save_card'] );

		// Combine into a hash.
		$redirect = sprintf( '#confirm-pi-%s:%s:%s', $result['intent_secret'], rawurlencode( $verification_url ), $this->id );

		return [
			'result'   => 'success',
			'redirect' => $redirect,
		];
	}

	/**
	 * Verify intent state and redirect.
	 *
	 * @since 1.9.0
	 *
	 * @return void
	 */
	public function verify_intent() {
		$checkout_url = wc_get_checkout_url();

		$order_id = isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 0; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$redirect = isset( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Recommended

		// Check for empty order id.
		if ( empty( $order_id ) ) {
			wc_add_notice( __( 'No orders are found for provided order ID.', 'checkout-plugins-stripe-woo' ), 'error' );
			Logger::error( __( 'Invalid order Id.', 'checkout-plugins-stripe-woo' ) );
			wp_safe_redirect( $checkout_url );
			exit();
		}

		$order = wc_get_order( $order_id );

		// Check for empty order object.
		if ( empty( $order ) ) {
			wc_add_notice( __( 'No valid order found.', 'checkout-plugins-stripe-woo' ), 'error' );
			Logger::error( __( 'Invalid order. No order found for provided order ID.', 'checkout-plugins-stripe-woo' ) );
			wp_safe_redirect( $checkout_url );
			exit();
		}

		if ( ! isset( $_GET['order_key'] ) || ! $order->key_is_valid( wc_clean( $_GET['order_key'] ) ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wc_add_notice( __( 'Invalid order key received.', 'checkout-plugins-stripe-woo' ), 'error' );
			Logger::error( __( 'Invalid order key.', 'checkout-plugins-stripe-woo' ) );
			wp_safe_redirect( $checkout_url );
			exit();
		}

		$intent_secret = $order->get_meta( '_cpsw_intent_secret' );
		$stripe_api    = new Stripe_Api();
		$response      = $stripe_api->payment_intents( 'retrieve', [ $intent_secret['id'] ] );
		$intent        = $response['success'] ? $response['data'] : false;

		if ( 'succeeded' === $intent->status || 'requires_capture' === $intent->status || 'processing' === $intent->status ) {
			if ( ( isset( $_GET['save_card'] ) && '1' === $_GET['save_card'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$user           = $order->get_id() ? $order->get_user() : wp_get_current_user();
				$user_id        = isset( $user->ID ) ? $user->ID : 0;
				$payment_method = $intent->payment_method;
				$response       = $stripe_api->payment_methods( 'retrieve', [ $payment_method ] );
				$payment_method = $response['success'] ? $response['data'] : false;
				$token          = $this->create_payment_token_for_user( $user_id, $payment_method );
				/* translators: %1$1s order id, %2$2s token id  */
				Logger::info( sprintf( __( 'Payment method tokenized for Order id - %1$1s with token id - %2$2s', 'checkout-plugins-stripe-woo' ), $order_id, $token->get_id() ) );
				$prepared_payment_method = $this->prepare_payment_method( $payment_method, $token );
				$this->save_payment_method_to_order( $order, $prepared_payment_method );
			}
			$redirect_to  = $this->process_order( end( $intent->charges->data ), $order_id );
			$redirect_url = apply_filters( 'cpsw_redirect_order_url', ! empty( $redirect ) ? $redirect : $redirect_to, $order );
		} elseif ( isset( $response['data']->last_payment_error ) ) {
			$message = isset( $response['data']->last_payment_error->message ) ? $response['data']->last_payment_error->message : '';
			$code    = isset( $response['data']->last_payment_error->code ) ? $response['data']->last_payment_error->code : '';
			$order->update_status( 'wc-failed' );

			/* translators: %1$1s order id, %2$2s payment fail message.  */
			Logger::error( sprintf( __( 'Payment failed for Order id - %1$1s. %2$2s', 'checkout-plugins-stripe-woo' ), $order_id, Helper::get_localized_messages( $code, $message ) ) );
			// translators: %s: payment fail message.
			wc_add_notice( sprintf( __( 'Payment failed. %s', 'checkout-plugins-stripe-woo' ), Helper::get_localized_messages( $code, $message ) ), 'error' );
			$redirect_url = $checkout_url;
		}
		wp_safe_redirect( $redirect_url );
		exit();
	}

	/**
	 * Tokenize card payment
	 *
	 * @param int    $user_id id of current user placing .
	 * @param object $payment_method payment method object.
	 * @return object token object.
	 */
	public function create_payment_token_for_user( $user_id, $payment_method ) {
		$type = $payment_method->type;
		if ( 'card' === $type ) {
			$token = new WC_Payment_Token_CC();
			$token->set_expiry_month( $payment_method->card->exp_month );
			$token->set_expiry_year( $payment_method->card->exp_year );
			$token->set_card_type( strtolower( $payment_method->card->brand ) );
		} else {
			$token = new Token();
			$token->set_payment_method_type( $type );
		}
		$token->set_last4( $payment_method->$type->last4 );
		$token->set_gateway_id( $this->id );
		$token->set_token( $payment_method->id );
		$token->set_user_id( $user_id );
		$token->save();

		return $token;
	}

	/**
	 * Get verification url for payment intent
	 *
	 * @param int    $order_id woocommerce order id.
	 * @param string $redirect_url redirect url.
	 * @param bool   $save_card save card.
	 * @since 1.9.0
	 * @return string verification url.
	 */
	public function get_verification_url( $order_id, $redirect_url, $save_card = false ) {
		$order = wc_get_order( $order_id );

		// Put the final thank you page redirect into the verification URL.
		return add_query_arg(
			[
				'order'                 => $order_id,
				'confirm_payment_nonce' => wp_create_nonce( 'cpsw_confirm_payment_intent' ),
				'redirect_to'           => rawurlencode( $redirect_url ),
				'save_card'             => $save_card,
				'order_key'             => $order->get_order_key(),
			],
			WC_AJAX::get_endpoint( $this->id . '_verify_payment_intent' )
		);
	}
}
