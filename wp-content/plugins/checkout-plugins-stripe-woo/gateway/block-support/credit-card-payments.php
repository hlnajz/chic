<?php
/**
 * Basic Credit Card Payments support for WooCommerce Blocks checkout block payment method.
 *
 * @package checkout-plugins-stripe-woo
 * @since 1.6.0
 */

namespace CPSW\Gateway\BlockSupport;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use Automattic\WooCommerce\Blocks\Assets\AssetDataRegistry;
use CPSW\Inc\Traits\Subscription_Helper as SH;
use CPSW\Inc\Helper;
use WC_HTTPS;

/**
 * CreditCardPayments class.
 *
 * @extends AbstractPaymentMethodType
 * @since 1.6.0
 */
final class Credit_Card_Payments extends AbstractPaymentMethodType {

	use SH;

	/**
	 * Payment method name defined by payment methods extending this class.
	 *
	 * @var string
	 * @since 1.6.0
	 */
	protected $name = 'cpsw_stripe';

	/**
	 * The Payment Request configuration class used for Shortcode PRBs. We use it here to retrieve
	 * the same configurations.
	 *
	 * @var WC_Stripe_Payment_Request
	 * @since 1.6.0
	 */
	private $payment_request_configuration;

	/**
	 * Assets registry.
	 *
	 * @var mixed
	 * @since 1.6.0
	 */
	public $assets_registry;


	/**
	 * WooCommerce container.
	 *
	 * @var mixed
	 * @since 1.6.0
	 */
	public $container;

	/**
	 * Supported Features.
	 *
	 * @var array
	 * @since 1.9.0
	 */
	public $features;

	/**
	 * Constructor
	 *
	 * @param mixed $payment_request_configuration  The Stripe Payment Request configuration used for Payment
	 *                                   Request buttons.
	 * @since 1.6.0
	 */
	public function __construct( $payment_request_configuration = null ) {
		$this->container       = \Automattic\WooCommerce\Blocks\Package::container();
		$this->assets_registry = $this->container->get( AssetDataRegistry::class );
		add_action( 'woocommerce_blocks_checkout_enqueue_data', array( $this, 'enqueue_checkout_data' ) );

		// Add supported features of for the blocks checkout.
		$this->features = $this->add_subscription_filters(
			[
				'products',
				'refunds',
				'tokenization',
				'add_payment_method',
				'pre-orders',
			]
		);
	}

	/**
	 * Initializes the payment method type.
	 *
	 * @since 1.6.0
	 * @return void
	 */
	public function initialize() {
		$this->settings = Helper::get_gateway_settings();
	}

	/**
	 * Enqueue data for checkout page.
	 *
	 * @since 1.6.0
	 * @return void
	 */
	public function enqueue_checkout_data() {
		$this->enqueue_data( 'checkout' );
	}

	/**
	 * Enqueue data for cart page.
	 *
	 * @param string $page Current page.
	 * @since 1.6.0
	 * @return void
	 */
	private function enqueue_data( $page ) {
		if ( ! $this->assets_registry->exists( 'cpsw_stripe_data' ) ) {
			$public_key = Helper::get_stripe_pub_key();

			$localize_data = [
				'mode'               => Helper::get_payment_mode(),
				'public_key'         => $public_key,
				'account_id'         => Helper::get_setting( 'cpsw_account_id' ),
				'icons'              => $this->get_icon(),
				'error_messages'     => Helper::get_localized_messages(),
				'label'              => $this->get_title(),
				'description'        => Helper::get_setting( 'description', 'cpsw_stripe' ),
				'enable_saved_cards' => Helper::get_setting( 'enable_saved_cards', 'cpsw_stripe' ),
				'stripe_cc_nonce'    => wp_create_nonce( 'stripe_cc_nonce' ),
				'inline_cc'          => Helper::get_setting( 'inline_cc', 'cpsw_stripe' ),
				'allowed_cards'      => Helper::get_setting( 'allowed_cards', 'cpsw_stripe' ),
				'order_button_text'  => Helper::get_order_button_text( 'cpsw_stripe' ),
				'features'           => $this->features,
			];

			$localize_data = apply_filters( 'cpsw_stripe_localize_data', $localize_data );

			$this->assets_registry->add( 'cpsw_stripe_data', $localize_data );
		}

		// Enqueue the script.
		wp_enqueue_style( 'cpsw-block-payment-method', CPSW_URL . 'build/block/style-block.css', array(), CPSW_VERSION );
	}

	/**
	 * Get stripe activated payment cards icon.
	 *
	 * @since 1.6.0
	 */
	public function get_icon() {
		$get_allowed_cards = Helper::get_setting( 'allowed_cards', 'cpsw_stripe' );
		$allowed_cards     = empty( $get_allowed_cards ) ? [ 'mastercard', 'visa', 'diners', 'discover', 'amex', 'jcb', 'unionpay' ] : $get_allowed_cards;

		$ext  = version_compare( WC()->version, '2.6', '>=' ) ? '.svg' : '.png';
		$icon = [];

		if ( ( in_array( 'visa', $allowed_cards, true ) ) || ( in_array( 'Visa', $allowed_cards, true ) ) ) {
			$icon[] = [
				'src' => WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/visa' . $ext ),
				'alt' => __( 'Visa', 'checkout-plugins-stripe-woo' ),
				'id'  => 'visa',
			];
		}

		if ( ( in_array( 'mastercard', $allowed_cards, true ) ) || ( in_array( 'MasterCard', $allowed_cards, true ) ) ) {
			$icon[] = [
				'src' => WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/mastercard' . $ext ),
				'alt' => __( 'Mastercard', 'checkout-plugins-stripe-woo' ),
				'id'  => 'mastercard',
			];
		}

		if ( ( in_array( 'amex', $allowed_cards, true ) ) || ( in_array( 'American Express', $allowed_cards, true ) ) ) {
			$icon[] = [
				'src' => WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/amex' . $ext ),
				'alt' => __( 'American Express', 'checkout-plugins-stripe-woo' ),
				'id'  => 'amex',
			];
		}

		if ( 'USD' === get_woocommerce_currency() ) {
			if ( ( in_array( 'discover', $allowed_cards, true ) ) || ( in_array( 'Discover', $allowed_cards, true ) ) ) {
				$icon[] = [
					'src' => WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/discover' . $ext ),
					'alt' => __( 'Discover', 'checkout-plugins-stripe-woo' ),
					'id'  => 'discover',
				];
			}

			if ( ( in_array( 'jcb', $allowed_cards, true ) ) || ( in_array( 'JCB', $allowed_cards, true ) ) ) {
				$icon[] = [
					'src' => WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/jcb' . $ext ),
					'alt' => __( 'JCB', 'checkout-plugins-stripe-woo' ),
					'id'  => 'jcb',
				];
			}

			if ( ( in_array( 'diners', $allowed_cards, true ) ) || ( in_array( 'Diners Club', $allowed_cards, true ) ) ) {
				$icon[] = [
					'src' => WC_HTTPS::force_https_url( WC()->plugin_url() . '/assets/images/icons/credit-cards/diners' . $ext ),
					'alt' => __( 'Diners Club', 'checkout-plugins-stripe-woo' ),
					'id'  => 'diners',
				];
			}
		}

		return $icon;
	}

	/**
	 * Returns if this payment method should be active. If false, the scripts will not be enqueued.
	 *
	 * @since 1.6.0
	 * @return boolean
	 */
	public function is_active() {
		return ! empty( $this->settings['enabled'] ) && 'yes' === $this->settings['enabled'];
	}

	/**
	 * Returns an array of scripts/handles to be registered for this payment method.
	 *
	 * @since 1.6.0
	 * @return array
	 */
	public function get_payment_method_script_handles() {
		$script_asset_path = CPSW_DIR . 'build/block/block.asset.php';
		$script_info       = file_exists( $script_asset_path )
			? include $script_asset_path
			: [
				'dependencies' => [],
				'version'      => CPSW_VERSION,
			];

		wp_register_script( 'cpsw-block-payment-method', CPSW_URL . 'build/block/block.js', $script_info['dependencies'], CPSW_VERSION, true );

		wp_set_script_translations(
			'cpsw-block-payment-method',
			'woocommerce-cpsw-gateway-stripe'
		);

		return [ 'cpsw-block-payment-method' ];
	}

	/**
	 * Returns the title string to use in the UI (customizable via admin settings screen).
	 *
	 * @since 1.6.0
	 * @return string Title / label string
	 */
	private function get_title() {
		return isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'Credit Card (Stripe)', 'checkout-plugins-stripe-woo' );
	}
}
