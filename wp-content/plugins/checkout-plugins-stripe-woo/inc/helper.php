<?php
/**
 * Stripe Gateway webhook.
 *
 * @package checkout-plugins-stripe-woo
 * @since 0.0.1
 */

namespace CPSW\Inc;

use WC_HTTPS;
use CPSW\Gateway\Stripe\Stripe_Api;
use CPSW\Inc\Traits\Subscription_Helper;

/**
 * Stripe Webhook.
 */
class Helper {

	use Subscription_Helper;

	/**
	 * Default global values
	 *
	 * @var array
	 */
	private static $global_defaults = [
		'cpsw_test_pub_key'        => '',
		'cpsw_pub_key'             => '',
		'cpsw_test_secret_key'     => '',
		'cpsw_secret_key'          => '',
		'cpsw_test_con_status'     => '',
		'cpsw_con_status'          => '',
		'cpsw_mode'                => 'test',
		'cpsw_live_webhook_secret' => '',
		'cpsw_test_webhook_secret' => '',
		'cpsw_account_id'          => '',
		'cpsw_debug_log'           => 'yes',
		'cpsw_element_type'        => 'card',
	];

	/**
	 * Constructor
	 *
	 * @since 0.0.1
	 */
	public function __construct() {
	}

	/**
	 * Stripe get all settings
	 *
	 * @return $global_settings array It returns all stripe settings in an array.
	 */
	public static function get_settings() {
		$response = [];
		foreach ( self::$global_defaults as $key => $default_data ) {
			$response[ $key ] = self::get_global_setting( $key );
		}
		return apply_filters( 'cpsw_settings', $response );
	}

	/**
	 * Stripe get all settings
	 *
	 * @return $global_settings array It returns all stripe settings in an array.
	 */
	public static function get_gateway_defaults() {
		return apply_filters(
			'cpsw_stripe_gateway_defaults_settings',
			[
				'woocommerce_cpsw_stripe_settings'         => [
					'enabled'                             => 'no',
					'inline_cc'                           => 'yes',
					'order_status'                        => '',
					'allowed_cards'                       => [
						'mastercard',
						'visa',
						'diners',
						'discover',
						'amex',
						'jcb',
						'unionpay',
					],
					'express_checkout_location'           => [
						'product',
						'cart',
						'checkout',
					],
					'express_checkout_enabled'            => 'no',
					'express_checkout_button_text'        => __( 'Pay now', 'checkout-plugins-stripe-woo' ),
					'express_checkout_button_theme'       => 'dark',
					'express_checkout_button_height'      => '40',
					'express_checkout_title'              => __( 'Express Checkout', 'checkout-plugins-stripe-woo' ),
					'express_checkout_tagline'            => __( 'Checkout faster with one of our express checkout options.', 'checkout-plugins-stripe-woo' ),
					'express_checkout_product_page_position' => 'above',
					'express_checkout_product_sticky_footer' => 'yes',
					'express_checkout_separator_product'  => __( 'OR', 'checkout-plugins-stripe-woo' ),
					'express_checkout_button_width'       => '',
					'express_checkout_button_alignment'   => 'left',
					'express_checkout_separator_cart'     => __( 'OR', 'checkout-plugins-stripe-woo' ),
					'express_checkout_separator_checkout' => '',
					'express_checkout_checkout_page_position' => 'above-billing',
					'express_checkout_checkout_page_layout' => 'classic',
					'express_checkout_button_type'        => 'custom',
				],
				'woocommerce_cpsw_alipay_settings'         => [
					'enabled' => 'no',
				],
				'woocommerce_cpsw_stripe_element_settings' => [
					'layout' => 'tabs',
				],
			]
		);
	}

	/**
	 * Get all settings of a particular gateway
	 *
	 * @param string $gateway gateway id.
	 * @return array
	 */
	public static function get_gateway_settings( $gateway = 'cpsw_stripe' ) {
		$default_settings = [];
		$setting_name     = 'woocommerce_' . $gateway . '_settings';
		$saved_settings   = is_array( get_option( $setting_name, [] ) ) ? get_option( $setting_name, [] ) : [];
		$gateway_defaults = self::get_gateway_defaults();

		if ( isset( $gateway_defaults[ $setting_name ] ) ) {
			$default_settings = $gateway_defaults[ $setting_name ];
		}

		$settings = array_merge( $default_settings, $saved_settings );

		return apply_filters( 'cpsw_gateway_settings', $settings );
	}

	/**
	 * Get value of gateway option parameter
	 *
	 * @param string $key key name.
	 * @param string $gateway gateway id.
	 * @return mixed
	 */
	public static function get_gateway_setting( $key = '', $gateway = 'cpsw_stripe' ) {
		$settings = self::get_gateway_settings( $gateway );
		$value    = false;

		if ( isset( $settings[ $key ] ) ) {
			$value = $settings[ $key ];
		}

		return $value;
	}

	/**
	 * Get value of global option
	 *
	 * @param string $key value of global setting.
	 * @return mixed
	 */
	public static function get_global_setting( $key ) {
		$db_data                                    = get_option( $key );
		self::$global_defaults['cpsw_element_type'] = self::default_element_type();
		return $db_data ? $db_data : self::$global_defaults[ $key ];
	}

	/**
	 * Stripe get settings value by key.
	 *
	 * @param string $key Name of the key to get the value.
	 * @param mixed  $gateway Name of the payment gateway to get options from the database.
	 *
	 * @return array $global_settings It returns all stripe settings in an array.
	 */
	public static function get_setting( $key = '', $gateway = false ) {
		$result = false;
		if ( false !== $gateway ) {
			$result = self::get_gateway_setting( $key, $gateway );
		} else {
			$result = self::get_global_setting( $key );
		}
		return is_array( $result ) || $result ? apply_filters( $key, $result ) : false;
	}

	/**
	 * Stripe get current mode
	 *
	 * @return $mode string It returns current mode of the stripe payment gateway.
	 */
	public static function get_payment_mode() {
		return apply_filters( 'cpsw_payment_mode', self::get_setting( 'cpsw_mode' ) );
	}

	/**
	 * Get webhook secret key.
	 *
	 * @since 1.2.0
	 * @param string $mode payment mode.
	 * @return mixed
	 */
	public static function get_webhook_secret( $mode = '' ) {
		$mode = empty( $mode ) ? self::get_payment_mode() : $mode;
		if ( 'live' === $mode ) {
			$endpoint_secret = self::get_setting( 'cpsw_live_webhook_secret' );
		} elseif ( 'test' === $mode ) {
			$endpoint_secret = self::get_setting( 'cpsw_test_webhook_secret' );
		}

		if ( empty( trim( $endpoint_secret ) ) ) {
			return false;
		}

		return $endpoint_secret;
	}

	/**
	 * Localize Stripe messages based on code
	 *
	 * @since 1.4.1
	 *
	 * @param string $code Stripe error code.
	 * @param string $message Stripe error message.
	 *
	 * @return string
	 */
	public static function get_localized_messages( $code = '', $message = '' ) {
		$localized_messages = apply_filters(
			'cpsw_stripe_localized_messages',
			[
				'account_country_invalid_address'        => __( 'The business address that you provided does not match the country set in your account. Please enter an address that falls within the same country.', 'checkout-plugins-stripe-woo' ),
				'account_invalid'                        => __( 'The account ID provided in the Stripe-Account header is invalid. Please check that your requests specify a valid account ID.', 'checkout-plugins-stripe-woo' ),
				'amount_too_large'                       => __( 'The specified amount is greater than the maximum amount allowed. Use a lower amount and try again.', 'checkout-plugins-stripe-woo' ),
				'amount_too_small'                       => __( 'The specified amount is less than the minimum amount allowed. Use a higher amount and try again.', 'checkout-plugins-stripe-woo' ),
				'api_key_expired'                        => __( 'Your API Key has expired. Please update your integration with the latest API key available in your Dashboard.', 'checkout-plugins-stripe-woo' ),
				'authentication_required'                => __( 'The payment requires authentication to proceed. If your customer is off session, notify your customer to return to your application and complete the payment. If you provided the error_on_requires_action parameter, then your customer should try another card that does not require authentication.', 'checkout-plugins-stripe-woo' ),
				'balance_insufficient'                   => __( 'The transfer or payout could not be completed because the associated account does not have a sufficient balance available. Create a new transfer or payout using an amount less than or equal to the account’s available balance.', 'checkout-plugins-stripe-woo' ),
				'bank_account_declined'                  => __( 'The bank account provided can not be used either because it is not verified yet or it is not supported.', 'checkout-plugins-stripe-woo' ),
				'bank_account_unusable'                  => __( 'The bank account provided cannot be used. Please try a different bank account.', 'checkout-plugins-stripe-woo' ),
				'setup_intent_unexpected_state'          => __( 'The SetupIntent\'s state was incompatible with the operation you were trying to perform.', 'checkout-plugins-stripe-woo' ),
				'payment_intent_action_required'         => __( 'The provided payment method requires customer action to complete. If you\'d like to add this payment method, please upgrade your integration to handle actions.', 'checkout-plugins-stripe-woo' ),
				'payment_intent_authentication_failure'  => __( 'The provided payment method failed authentication. Provide a new payment method to attempt this payment again.', 'checkout-plugins-stripe-woo' ),
				'payment_intent_incompatible_payment_method' => __( 'The Payment expected a payment method with different properties than what was provided.', 'checkout-plugins-stripe-woo' ),
				'payment_intent_invalid_parameter'       => __( 'One or more provided parameters was not allowed for the given operation on the Payment.', 'checkout-plugins-stripe-woo' ),
				'payment_intent_mandate_invalid'         => __( 'The provided mandate is invalid and can not be used for the payment intent.', 'checkout-plugins-stripe-woo' ),
				'payment_intent_payment_attempt_expired' => __( 'The latest attempt for this Payment has expired. Provide a new payment method to attempt this Payment again.', 'checkout-plugins-stripe-woo' ),
				'payment_intent_unexpected_state'        => __( 'The PaymentIntent\'s state was incompatible with the operation you were trying to perform.', 'checkout-plugins-stripe-woo' ),
				'payment_method_billing_details_address_missing' => __( 'The PaymentMethod\'s billing details is missing address details. Please update the missing fields and try again.', 'checkout-plugins-stripe-woo' ),
				'payment_method_currency_mismatch'       => __( 'The currency specified does not match the currency for the attached payment method. A payment can only be created for the same currency as the corresponding payment method.', 'checkout-plugins-stripe-woo' ),
				'processing_error'                       => __( 'An error occurred while processing the card. Use a different payment method or try again later.', 'checkout-plugins-stripe-woo' ),
				'token_already_used'                     => __( 'The token provided has already been used. You must create a new token before you can retry this request.', 'checkout-plugins-stripe-woo' ),
				'invalid_number'                         => __( 'The card number is invalid. Check the card details or use a different card.', 'checkout-plugins-stripe-woo' ),
				'invalid_card_type'                      => __( 'The card provided as an external account is not supported for payouts. Provide a non-prepaid debit card instead.', 'checkout-plugins-stripe-woo' ),
				'invalid_charge_amount'                  => __( 'The specified amount is invalid. The charge amount must be a positive integer in the smallest currency unit, and not exceed the minimum or maximum amount.', 'checkout-plugins-stripe-woo' ),
				'invalid_charge_amount_currency'         => __( 'The specified amount is too low after conversion. Please enter a higher amount and try again.', 'checkout-plugins-stripe-woo' ),
				'invalid_cvc'                            => __( 'The card\'s security code is invalid. Check the card\'s security code or use a different card.', 'checkout-plugins-stripe-woo' ),
				'invalid_expiry_year'                    => __( 'The card\'s expiration year is incorrect. Check the expiration date or use a different card.', 'checkout-plugins-stripe-woo' ),
				'invalid_source_usage'                   => __( 'The source cannot be used because it is not in the correct state.', 'checkout-plugins-stripe-woo' ),
				'incorrect_address'                      => __( 'The address entered for the card is invalid. Please check the address or try a different card.', 'checkout-plugins-stripe-woo' ),
				'incorrect_cvc'                          => __( 'The security code entered is invalid. Please try again.', 'checkout-plugins-stripe-woo' ),
				'incorrect_number'                       => __( 'The card number entered is invalid. Please try again with a valid card number or use a different card.', 'checkout-plugins-stripe-woo' ),
				'incorrect_zip'                          => __( 'The postal code entered for the card is invalid. Please try again.', 'checkout-plugins-stripe-woo' ),
				'missing'                                => __( 'Both a customer and source ID have been provided, but the source has not been saved to the customer. To create a charge for a customer with a specified source, you must first save the card details.', 'checkout-plugins-stripe-woo' ),
				'email_invalid'                          => __( 'The email address is invalid. Check that the email address is properly formatted and only includes allowed characters.', 'checkout-plugins-stripe-woo' ),
				// Card declined started here.
				'card_declined'                          => __( 'The card has been declined. When a card is declined, the error returned also includes the decline_code attribute with the reason why the card was declined.', 'checkout-plugins-stripe-woo' ),
				'insufficient_funds'                     => __( 'The card has insufficient funds to complete the purchase.', 'checkout-plugins-stripe-woo' ),
				'generic_decline'                        => __( 'The card has been declined. Please try again with another card.', 'checkout-plugins-stripe-woo' ),
				'lost_card'                              => __( 'The card has been declined (Lost card). Please try again with another card.', 'checkout-plugins-stripe-woo' ),
				'stolen_card'                            => __( 'The card has been declined (Stolen card). Please try again with another card.', 'checkout-plugins-stripe-woo' ),
				'try_again_later'                        => __( 'The card has been declined for an unknown reason. Please try again with another card.', 'checkout-plugins-stripe-woo' ),
				'transaction_not_allowed'                => __( 'The card has been declined for an unknown reason. Please try again with another card.', 'checkout-plugins-stripe-woo' ),
				'call_issuer'                            => __( 'The card has been declined for an unknown reason. Please try again with another card.', 'checkout-plugins-stripe-woo' ),
				'card_not_supported'                     => __( 'This card does not support this type of purchase. Please try again with another card.', 'checkout-plugins-stripe-woo' ),
				'card_velocity_exceeded'                 => __( 'This card has been declined for making repeated attempts too frequently or exceeding its amount limit. Please try again with another card.', 'checkout-plugins-stripe-woo' ),
				'currency_not_supported'                 => __( 'This card does not support the specified currency. Please try again with another card.', 'checkout-plugins-stripe-woo' ),
				'do_not_honor'                           => __( 'The card has been declined for an unknown reason. Please try again with another card.', 'checkout-plugins-stripe-woo' ),
				'do_not_try_again'                       => __( 'The card has been declined for an unknown reason. Please try again with another card.', 'checkout-plugins-stripe-woo' ),
				'duplicate_transaction'                  => __( 'A transaction with identical amount and credit card information was submitted very recently. Please try again with another card.', 'checkout-plugins-stripe-woo' ),
				'expired_card'                           => __( 'This card has been expired. Please try again with another card.', 'checkout-plugins-stripe-woo' ),
				'fraudulent'                             => __( 'This card has been declined because Stripe suspects that it\'s fraudulent. Please try again with another card.', 'checkout-plugins-stripe-woo' ),
				'incorrect_pin'                          => __( 'The PIN entered for the card is incorrect. Please try again.', 'checkout-plugins-stripe-woo' ),
				'invalid_account'                        => __( 'The card, or account the card is connected to, is invalid. Please try again with another card.', 'checkout-plugins-stripe-woo' ),
				'invalid_amount'                         => __( 'The payment amount is invalid, or exceeds the amount that\'s allowed by this card. Please try again with another card.', 'checkout-plugins-stripe-woo' ),
				'invalid_expiry_month'                   => __( 'Your card\'s expiration month is incorrect. Please check or try again with another card.', 'checkout-plugins-stripe-woo' ),
				'issuer_not_available'                   => __( 'The payment couldn\'t be authorized because the card issuer was unreachable. Please try again with another card.', 'checkout-plugins-stripe-woo' ),
				'invalid_pin'                            => __( 'The PIN entered for the card is incorrect. Please try again.', 'checkout-plugins-stripe-woo' ),
				'merchant_blacklist'                     => __( 'The card has been declined (Merchant blacklist). Please try again with another card.', 'checkout-plugins-stripe-woo' ),
				'new_account_information_available'      => __( 'This card, or account the card is connected to, is invalid. Please try again with another card.', 'checkout-plugins-stripe-woo' ),
				'no_action_taken'                        => __( 'The card has been declined for an unknown reason. Please try again with another card.', 'checkout-plugins-stripe-woo' ),
				'not_permitted'                          => __( 'The card has been declined because the payment isn\'t permitted. Please try again with another card.', 'checkout-plugins-stripe-woo' ),
				'offline_pin_required'                   => __( 'The card has been declined because it requires a offline PIN. Please try again with another card.', 'checkout-plugins-stripe-woo' ),
				'online_or_offline_pin_required'         => __( 'The card has been declined because it requires a PIN. Please try again with another card.', 'checkout-plugins-stripe-woo' ),
				'pickup_card'                            => __( 'You can\'t use this card to make this payment. Please try again with another card.', 'checkout-plugins-stripe-woo' ),
				'pin_try_exceeded'                       => __( 'The allowable number of PIN tries was exceeded. Please try again with another card.', 'checkout-plugins-stripe-woo' ),
				'reenter_transaction'                    => __( 'The payment couldn\'t be processed by the issuer for an unknown reason. Please try again.', 'checkout-plugins-stripe-woo' ),
				'restricted_card'                        => __( 'You can\'t use this card to make this payment. Please try again with another card.', 'checkout-plugins-stripe-woo' ),
				'revocation_of_all_authorizations'       => __( 'The card has been declined for an unknown reason. Please try again with another card.', 'checkout-plugins-stripe-woo' ),
				'revocation_of_authorization'            => __( 'The card has been declined for an unknown reason. Please try again with another card.', 'checkout-plugins-stripe-woo' ),
				'security_violation'                     => __( 'The card has been declined for an unknown reason. Please try again with another card.', 'checkout-plugins-stripe-woo' ),
				'service_not_allowed'                    => __( 'The card has been declined for an unknown reason. Please try again with another card.', 'checkout-plugins-stripe-woo' ),
				'stop_payment_order'                     => __( 'The card has been declined for an unknown reason. Please try again with another card.', 'checkout-plugins-stripe-woo' ),
				'testmode_decline'                       => __( 'The card was declined because a Stripe test card number was used. Please try again with another card.', 'checkout-plugins-stripe-woo' ),
				'withdrawal_count_limit_exceeded'        => __( 'This card has been declined because it exceeded the available balance or credit limit. Please try again with another card.', 'checkout-plugins-stripe-woo' ),
				// Card declined end here.
				'parameter_unknown'                      => __( 'The request contains one or more unexpected parameters. Remove these and try again.', 'checkout-plugins-stripe-woo' ),
				'incomplete_number'                      => __( 'Your card number is incomplete.', 'checkout-plugins-stripe-woo' ),
				'incomplete_expiry'                      => __( 'Your card\'s expiration date is incomplete.', 'checkout-plugins-stripe-woo' ),
				'incomplete_cvc'                         => __( 'Your card\'s security code is incomplete.', 'checkout-plugins-stripe-woo' ),
				'incomplete_zip'                         => __( 'Your card\'s zip code is incomplete.', 'checkout-plugins-stripe-woo' ),
				'stripe_cc_generic'                      => __( 'There was an error processing your credit card.', 'checkout-plugins-stripe-woo' ),
				'invalid_expiry_year_past'               => __( 'Your card\'s expiration year is in the past.', 'checkout-plugins-stripe-woo' ),
				'bank_account_verification_failed'       => __(
					'The bank account cannot be verified, either because the microdeposit amounts provided do not match the actual amounts, or because verification has failed too many times.',
					'checkout-plugins-stripe-woo'
				),
				'card_decline_rate_limit_exceeded'       => __(
					'This card has been declined too many times. You can try to charge this card again after 24 hours. We suggest reaching out to your customer to make sure they have entered all of their information correctly and that there are no issues with their card.',
					'checkout-plugins-stripe-woo'
				),
				'charge_already_captured'                => __( 'The charge you\'re attempting to capture has already been captured. Update the request with an uncaptured charge ID.', 'checkout-plugins-stripe-woo' ),
				'charge_already_refunded'                => __(
					'The charge you\'re attempting to refund has already been refunded. Update the request to use the ID of a charge that has not been refunded.',
					'checkout-plugins-stripe-woo'
				),
				'charge_disputed'                        => __(
					'The charge you\'re attempting to refund has been charged back. Check the disputes documentation to learn how to respond to the dispute.',
					'checkout-plugins-stripe-woo'
				),
				'charge_exceeds_source_limit'            => __(
					'This charge would cause you to exceed your rolling-window processing limit for this source type. Please retry the charge later, or contact us to request a higher processing limit.',
					'checkout-plugins-stripe-woo'
				),
				'charge_expired_for_capture'             => __(
					'The charge cannot be captured as the authorization has expired. Auth and capture charges must be captured within seven days.',
					'checkout-plugins-stripe-woo'
				),
				'charge_invalid_parameter'               => __(
					'One or more provided parameters was not allowed for the given operation on the Charge. Check our API reference or the returned error message to see which values were not correct for that Charge.',
					'checkout-plugins-stripe-woo'
				),
				'account_number_invalid'                 => __( 'The bank account number provided is invalid (e.g., missing digits). Bank account information varies from country to country. We recommend creating validations in your entry forms based on the bank account formats we provide.', 'checkout-plugins-stripe-woo' ),
				'processing_error_for_element'           => __( 'An error occurred while processing the payment. Use a different payment method or try again later.', 'checkout-plugins-stripe-woo' ),
				'cashapp_country_error'                  => __( 'Payments with Cash App Pay support only US country.', 'checkout-plugins-stripe-woo' ),
				'payment_element_loaderror'              => __( 'There seems to be a issue loading Payment Element: ', 'checkout-plugins-stripe-woo' ),
			]
		);

		// if need all messages.
		if ( empty( $code ) ) {
			return $localized_messages;
		}

		return isset( $localized_messages[ $code ] ) ? $localized_messages[ $code ] : $message;
	}

	/**
	 * Get stripe key based on mode.
	 *
	 * @since 1.6.0
	 * @return string Stripe key.
	 */
	public static function get_stripe_pub_key() {
		return self::get_payment_mode() === 'live' ? self::get_setting( 'cpsw_pub_key' ) : self::get_setting( 'cpsw_test_pub_key' );
	}

	/**
	 * Get icon details of a particular gateway.
	 *
	 * @since 1.7.0
	 *
	 * @param string $gateway gateway unique id or name to fetch icon.
	 *
	 * @return array
	 */
	public static function get_payment_icon( $gateway ) {
		// Check if $gateway is a non-empty string.
		if ( empty( $gateway ) || ! is_string( $gateway ) ) {
			return [];
		}

		$icon_url = WC_HTTPS::force_https_url( CPSW_URL . 'assets/icon/' );

		$icons = [
			'cpsw_alipay'     => [
				'src'   => $icon_url . 'alipay.svg',
				'alt'   => __( 'Alipay', 'checkout-plugins-stripe-woo' ),
				'id'    => 'cpsw-alipay',
				'width' => '50px',
			],
			'cpsw_ideal'      => [
				'src'   => $icon_url . 'ideal.svg',
				'alt'   => __( 'iDEAL', 'checkout-plugins-stripe-woo' ),
				'id'    => 'cpsw-ideal',
				'width' => '32',
			],
			'cpsw_klarna'     => [
				'src'   => $icon_url . 'klarna.svg',
				'alt'   => __( 'Klarna', 'checkout-plugins-stripe-woo' ),
				'id'    => 'cpsw-klarna',
				'width' => '60',
			],
			'cpsw_p24'        => [
				'src'   => $icon_url . 'p24.svg',
				'alt'   => __( 'Przelewy24', 'checkout-plugins-stripe-woo' ),
				'id'    => 'cpsw-p24',
				'width' => '60',
			],
			'cpsw_bancontact' => [
				'src'   => $icon_url . 'bancontact.svg',
				'alt'   => __( 'Bancontact', 'checkout-plugins-stripe-woo' ),
				'id'    => 'cpsw-bancontact',
				'width' => '40',
			],
			'cpsw_wechat'     => [
				'src'   => $icon_url . 'wechat.svg',
				'alt'   => __( 'WeChat', 'checkout-plugins-stripe-woo' ),
				'id'    => 'cpsw-wechat',
				'width' => '80',
			],
			'cpsw_sepa'       => [
				'src'   => $icon_url . 'sepa.svg',
				'alt'   => __( 'SEPA', 'checkout-plugins-stripe-woo' ),
				'id'    => 'cpsw-sepa',
				'width' => '50px',
			],
		];

		return ! empty( $icons[ $gateway ] ) ? $icons[ $gateway ] : [];
	}

	/**
	 * Get test mode description for all local gateways
	 *
	 * @return string
	 * @since 1.7.0
	 */
	public static function get_local_test_mode_description() {
		/* translators: HTML Entities. */
		return apply_filters( 'cpsw_local_gateway_test_description', sprintf( esc_html__( '%1$1sTest Mode Enabled :%2$2s You will be redirected to an authorization page hosted by Stripe.', 'checkout-plugins-stripe-woo' ), '<strong>', '</strong>' ) );
	}

	/**
	 * Checks the current page to see if it contains checkout block.
	 *
	 * @return bool
	 * @since 1.7.0
	 */
	public static function is_block_checkout() {
		return has_block( 'woocommerce/checkout' );
	}

	/**
	 * Returns amount as per currency type
	 *
	 * @since 1.9.0
	 *
	 * @param string $total amount to be processed.
	 *
	 * @return int
	 */
	public static function get_the_formatted_amount( $total ) {
		return absint( wc_format_decimal( ( (float) $total * 100 ), wc_get_price_decimals() ) ); // In cents.
	}

	/**
	 * Array containing the Stripe ID of supported payment gateways for Payment element.
	 *
	 * @var array
	 * @since 1.9.0
	 */
	public static $supported_gateways = [
		'card'       => 'Card',
		'ideal'      => 'iDEAL',
		'bancontact' => 'Bancontact',
		'p24'        => 'Przelewy24',
		'alipay'     => 'Alipay',
		'klarna'     => 'Klarna',
		'sepa_debit' => 'SEPA',
		'wechat_pay' => 'WeChat',
	];

	/**
	 * Array containing the Stripe ID of Additional payment gateways for Payment element.
	 *
	 * @var array
	 * @since 1.9.0
	 */
	public static $additional_gateways = [
		'giropay' => 'Giropay',
		'eps'     => 'EPS',
		'cashapp' => 'CashApp',
	];

	/**
	 * Array defining supported currencies for each Payment element.
	 *
	 * Leave empty if all are supported, otherwise provide an array with keys matching the `['id']` value from `$this->supported_gateways`.
	 *
	 * @var array
	 * @since 1.9.0
	 */
	public static function gateway_supported_currency() {
		return [
			'card'       => [],
			'ideal'      => [ 'EUR' ],
			'giropay'    => [ 'EUR' ],
			'bancontact' => [ 'EUR' ],
			'eps'        => [ 'EUR' ],
			'p24'        => [ 'EUR', 'PLN' ],
			'alipay'     => self::get_supported_currency_country_for_gateway( 'alipay' )['currency'],
			'klarna'     => self::get_supported_currency_country_for_gateway( 'klarna' )['currency'],
			'sepa_debit' => [ 'EUR' ],
			'wechat_pay' => self::get_supported_currency_country_for_gateway( 'wechat_pay' )['currency'],
			'cashapp'    => [ 'USD' ],
		];
	}

	/**
	 * Array containing the Stripe ID of supported payment gateways which support subscription for Payment element.
	 *
	 * @var array
	 * @since 1.9.0
	 */
	public static $subscription_supported_gateways = [
		'sepa_debit',
		'card',
	];

	/**
	 * Array containing the Stripe ID of payment gateways which support save card for Payment element.
	 *
	 * @var array
	 * @since 1.9.0
	 */
	public static $savecard_supported_gateways = [
		'sepa_debit',
		'card',
	];

	/**
	 * Get test mode description for SEPA
	 *
	 * @return string
	 * @since 1.8.0
	 */
	public static function get_sepa_test_mode_description() {
		/* translators: HTML Entities. */
		return apply_filters( 'cpsw_sepa_gateway_test_description', sprintf( esc_html__( '%1$1s Test Mode Enabled %2$2s : Use demo IBAN number DE89370400440532013000 for test payment. %3$3s Check more %4$4sDemo IBAN Number%5$5s', 'checkout-plugins-stripe-woo' ), '<b>', '</b>', '</br>', "<a href='https://stripe.com/docs/testing#sepa-direct-debit' referrer='noopener' target='_blank'>", '</a>' ) );
	}

	/**
	 * Get  SEPA Direct Debit mandate description for SEPA gateway
	 *
	 * Reference : https://stripe.com/docs/payments/sepa-debit/accept-a-payment?platform=web&ui=element#add-and-configure-an-component
	 *
	 * @return string
	 * @since 1.8.0
	 */
	public static function get_sepa_mandate_description() {
		/* translators: HTML Entities. */
		return apply_filters( 'cpsw_sepa_mandate_description', sprintf( __( 'By providing your IBAN and confirming this payment, you are authorizing %s and Stripe, our payment service provider, to send instructions to your bank to debit your account and your bank to debit your account in accordance with those instructions. You are entitled to a refund from your bank under the terms and conditions of your agreement with your bank. A refund must be claimed within 8 weeks starting from the date on which your account was debited.', 'checkout-plugins-stripe-woo' ), self::get_setting( 'company_name', 'cpsw_sepa' ) ) );
	}

	/**
	 * Get order button text
	 *
	 * @param string $gateway Gateway id.
	 *
	 * @return string
	 * @since 1.9.0
	 */
	public static function get_order_button_text( $gateway ) {
		return self::get_setting( 'order_button_text', $gateway ) ? self::get_setting( 'order_button_text', $gateway ) : __( 'Place order', 'checkout-plugins-stripe-woo' );

	}

	/**
	 * Retrieves the default country and currency associated with the Stripe account.
	 *
	 * @since 1.9.0
	 * @return array|false
	 */
	public static function get_stripe_default_country() {
		if ( empty( self::get_setting( 'cpsw_account_id' ) ) ) {
			return false;
		}

		$account_default_country_currency = get_transient( 'cpsw_stripe_account_default_country_currency' );

		if ( false === $account_default_country_currency ) {
			$stripe_api   = new Stripe_Api();
			$response     = $stripe_api->accounts( 'retrieve', [ self::get_setting( 'cpsw_account_id' ) ] );
			$account_info = $response['success'] ? $response['data'] : false;

			if ( ! $account_info ) {
				return false;
			}

			$account_default_country_currency = [
				'country'  => strtoupper( $account_info->country ),
				'currency' => strtoupper( $account_info->default_currency ),
			];
			delete_transient( 'cpsw_stripe_account_default_country_currency' );
			set_transient( 'cpsw_stripe_account_default_country_currency', $account_default_country_currency, 60 * MINUTE_IN_SECONDS );
		}

		return $account_default_country_currency;
	}

	/**
	 * Retrieves the supported currency for the specified payment gateway.
	 *
	 * @since 1.9.0
	 * @param string $gateway_name The name of the payment gateway.
	 * @return array Returns supported currency/currencies.
	 */
	public static function get_supported_currency_country_for_gateway( $gateway_name ) {
		$country_info    = self::get_stripe_default_country();
		$country         = $country_info['country'] ?? null;
		$currency        = $country_info['currency'] ?? null;
		$gateway_support = [
			'currency' => [],
			'country'  => [],
		];

		switch ( $gateway_name ) {
			case 'alipay':
				$alipay_currency = [ 'CNY' ];
				if ( null !== $country ) {
					switch ( $country ) {
						case 'AU':
							$alipay_currency = [ 'AUD', 'CNY' ];
							break;
						case 'CA':
							$alipay_currency = [ 'CAD', 'CNY' ];
							break;
						case 'UK':
							$alipay_currency = [ 'GBP', 'CNY' ];
							break;
						case 'HK':
							$alipay_currency = [ 'HKD', 'CNY' ];
							break;
						case 'JP':
							$alipay_currency = [ 'JPY', 'CNY' ];
							break;
						case 'MY':
							$alipay_currency = [ 'MYR', 'CNY' ];
							break;
						case 'NZ':
							$alipay_currency = [ 'NZD', 'CNY' ];
							break;
						case 'SG':
							$alipay_currency = [ 'SGD', 'CNY' ];
							break;
						case 'US':
							$alipay_currency = [ 'USD', 'CNY' ];
							break;
					}

					$alipay_euro_countries = [ 'AT', 'BE', 'BG', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'NO', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE', 'CH' ];
					if ( in_array( $country, $alipay_euro_countries, true ) ) {
						$alipay_currency = [ 'EUR', 'CNY' ];
					}
				}
				$gateway_support['currency'] = $alipay_currency;
				return $gateway_support;

			case 'klarna':
				// List of supported countries and their presentment currencies for Klarna.
				$klarna_supported_countries = [
					'AU' => 'AUD',
					'AT' => 'EUR',
					'BE' => 'EUR',
					'CA' => 'CAD',
					'CZ' => 'CZK', 
					'DK' => 'DKK',
					'FI' => 'EUR',
					'FR' => 'EUR',
					'DE' => 'EUR',
					'GR' => 'EUR', 
					'IE' => 'EUR',
					'IT' => 'EUR',
					'NL' => 'EUR',
					'NZ' => 'NZD',
					'NO' => 'NOK', 
					'PL' => 'PLN',
					'PT' => 'EUR',
					'ES' => 'EUR',
					'SE' => 'SEK',
					'CH' => 'CHF', 
					'GB' => 'GBP',
					'US' => 'USD',
				];
				$klarna_currency            = array_values( $klarna_supported_countries );
				$klarna_country             = array_keys( $klarna_supported_countries );

				if ( null !== $country ) {
					// EEA, UK, and Switzerland country codes.
					$klarna_eea_uk_switzerland_codes = [ 'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GB', 'GR', 'HR', 'HU', 'IE', 'IS', 'IT', 'LI', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK', 'CH' ];

					// Checking the stripe account is based in an EEA, UK, or Switzerland country.
					if ( in_array( $country, $klarna_eea_uk_switzerland_codes, true ) ) {
						$klarna_supported_currency = [ 'AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'NOK', 'NZD', 'PLN', 'SEK', 'USD' ];
						$klarna_country            = $klarna_eea_uk_switzerland_codes;

						// Extract the supported currencies for the given country codes.
						$filtered_currencies = array_unique( array_intersect_key( $klarna_supported_countries, array_flip( $klarna_eea_uk_switzerland_codes ) ) );

						// Filter the klarna_currency array.
						$klarna_currency = array_values( array_intersect( $klarna_supported_currency, $filtered_currencies ) );

					} else {
						// Ensure only the business's currency is used outside EEA, UK, or Switzerland.
						$klarna_currency = [ $klarna_supported_countries[ $country ] ];
						$klarna_country  = [ $country ];
					}
				}

				$gateway_support['currency'] = $klarna_currency;
				$gateway_support['country']  = $klarna_country;
				return $gateway_support;

			case 'wechat_pay':
				$wechat_currency = [ 'CNY' ];

				if ( null !== $country ) {
					switch ( $country ) {
						case 'AU':
							$wechat_currency = [ 'AUD', 'CNY' ];
							break;
						case 'CA':
							$wechat_currency = [ 'CAD', 'CNY' ];
							break;
						case 'AT':
						case 'BE':
						case 'DK':
						case 'FI':
						case 'FR':
						case 'DE':
						case 'IE':
						case 'IT':
						case 'LU':
						case 'NL':
						case 'NO':
						case 'PT':
						case 'ES':
						case 'SE':
							$wechat_currency = [ 'EUR', 'CNY' ];
							break;
						case 'UK':
							$wechat_currency = [ 'GBP', 'CNY' ];
							break;
						case 'HK':
							$wechat_currency = [ 'HKD', 'CNY' ];
							break;
						case 'JP':
							$wechat_currency = [ 'JPY', 'CNY' ];
							break;
						case 'SG':
							$wechat_currency = [ 'SGD', 'CNY' ];
							break;
						case 'US':
							$wechat_currency = [ 'USD', 'CNY' ];
							break;
						case 'DK':
							$wechat_currency = [ 'DKK', 'CNY' ];
							break;
						case 'NO':
							$wechat_currency = [ 'NOK', 'CNY' ];
							break;
						case 'SE':
							$wechat_currency = [ 'SEK', 'CNY' ];
							break;
						case 'CH':
							$wechat_currency = [ 'CHF', 'CNY' ];
							break;
					}
				}

				$gateway_support['currency'] = $wechat_currency;
				return $gateway_support;

			// Add cases for other gateways as needed...

			default:
				return $gateway_support;
		}
	}

	/**
	 * Get supported gateways based on current currency
	 *
	 * @since 1.9.0
	 * @return array
	 */
	public static function get_available_gateways() {
		$gateways           = [];
		$supported_gateways = [];

		foreach ( array_keys( self::$supported_gateways ) as $key ) {
			$settings_key = $key;
			if ( 'sepa_debit' === $key ) {
				$settings_key = 'sepa';
			} elseif ( 'wechat_pay' === $key ) {
				$settings_key = 'wechat';
			} elseif ( 'card' === $key ) {
				$settings_key = 'stripe';
			}

			$settings = self::get_gateway_settings( 'cpsw_' . $settings_key );
			// Check if the payment method is enabled.
			if ( isset( $settings['enabled'] ) && 'yes' === $settings['enabled'] ) {
				// Add the payment method key to the enabled payment methods array.
				$supported_gateways[] = $key;
			}
		}

		$additional_gateways = self::get_setting( 'additional_methods', 'cpsw_stripe_element' );

		// If there are some additional gateways then merge it in the supported gateways.
		if ( ! empty( $additional_gateways ) && is_array( $additional_gateways ) ) {
			$supported_gateways = array_merge( $additional_gateways, $supported_gateways );
		}

		// If there are no supported gateways then return.
		if ( empty( $supported_gateways ) && ! is_array( $supported_gateways ) ) {
			return;
		}

		// Check if cart has subscription product.
		// If so, show gateways for recurring payments only.
		$subscription_helper = new self();
		if ( $subscription_helper->is_subscription_item_in_cart() ) {
			$supported_gateways = array_intersect( self::$subscription_supported_gateways, $supported_gateways );
		}

		$gateway_supported_currency = self::gateway_supported_currency();
		// Loop through supported gateways.
		foreach ( $supported_gateways as $gateway ) {
			// Check if the gateway supports the current currency.
			if ( empty( $gateway_supported_currency[ $gateway ] ) || in_array( get_woocommerce_currency(), $gateway_supported_currency[ $gateway ] ) ) {
				$gateways[] = $gateway;
			}
		}

		return $gateways;
	}

	/**
	 * Default value for cpsw_element_type based on condition.
	 *
	 * @since 1.9.1
	 * @return string
	 */
	public static function default_element_type() {
		$all_gateways = [
			'cpsw_stripe',
			'cpsw_alipay',
			'cpsw_ideal',
			'cpsw_klarna',
			'cpsw_sepa',
			'cpsw_bancontact',
			'cpsw_p24',
			'cpsw_wechat',
		];

		foreach ( $all_gateways as $payment_method ) {
			$enabled = self::get_setting( 'enabled', $payment_method );
			if ( 'yes' === $enabled ) {
				return 'card';
			}
		}

		return 'payment';
	}

	/**
	 * Checks if the current page is a CPSW settings page for a specific payment method.
	 *
	 * @since 1.9.1
	 * @return bool
	 */
	public static function is_cpsw_settings_page() {
		$allowed_sections = apply_filters(
			'cpsw_allow_admin_scripts_methods',
			array(
				'cpsw_stripe',
				'cpsw_alipay',
				'cpsw_ideal',
				'cpsw_klarna',
				'cpsw_sepa',
				'cpsw_bancontact',
				'cpsw_p24',
				'cpsw_wechat',
				'cpsw_stripe_element',
			)
		);

		if ( ! is_admin() ) {
			return false;
		}

		if ( ! isset( $_GET['page'] ) || 'wc-settings' !== $_GET['page'] || ! isset( $_GET['tab'] ) ) {//phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return false; // Basic checks for settings page.
		}

		if ( 'cpsw_api_settings' === $_GET['tab'] ) {//phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return true; // Matches the "cpsw_api_settings" tab.
		}

		if ( isset( $_GET['section'] ) && in_array( sanitize_text_field( $_GET['section'] ), $allowed_sections, true ) ) {//phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return true; // Matches a section starting with "cpsw_" and in the allowed list.
		}

		return false;
	}

	/**
	 * Check weather the WooCommerce plugin is active.
	 *
	 * @return boolean Returns true or false if WooCommerce is active or inactive.
	 */
	public static function is_woo_active() {
		return class_exists( 'woocommerce' ) ? true : false;
	}
}
