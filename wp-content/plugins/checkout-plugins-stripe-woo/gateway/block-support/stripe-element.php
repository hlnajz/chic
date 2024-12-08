<?php
/**
 * Payments using Payment element support for WooCommerce checkout block.
 *
 * @package checkout-plugins-stripe-woo
 * @since 1.9.0
 */

namespace CPSW\Gateway\BlockSupport;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use Automattic\WooCommerce\Blocks\Assets\AssetDataRegistry;
use CPSW\Inc\Helper;
use CPSW\Inc\Traits\Subscriptions;
use WC_HTTPS;

/**
 * Stripe_Element class.
 *
 * @extends AbstractPaymentMethodType
 * @since 1.9.0
 */
final class Stripe_Element extends AbstractPaymentMethodType {

	use Subscriptions;

	/**
	 * Payment method name defined by payment methods extending this class.
	 *
	 * @var string
	 * @since 1.9.0
	 */
	protected $name = 'cpsw_stripe_element';

	/**
	 * The Payment Request configuration class used for Shortcode PRBs. We use it here to retrieve
	 * the same configurations.
	 *
	 * @var WC_Stripe_Payment_Request
	 * @since 1.9.0
	 */
	private $payment_request_configuration;

	/**
	 * Assets registry.
	 *
	 * @var mixed
	 * @since 1.9.0
	 */
	public $assets_registry;

	/**
	 * WooCommerce container.
	 *
	 * @var mixed
	 * @since 1.9.0
	 */
	public $container;

	/**
	 * Supported features.
	 *
	 * @var array
	 * @since 1.9.0
	 */
	public $features = array( 'products' );

	/**
	 * Constructor
	 *
	 * @param mixed $payment_request_configuration  The Stripe Payment Request configuration used for Payment
	 *                                   Request buttons.
	 * @since 1.9.0
	 */
	public function __construct( $payment_request_configuration = null ) {
		$this->container       = \Automattic\WooCommerce\Blocks\Package::container();
		$this->assets_registry = $this->container->get( AssetDataRegistry::class );
		add_action( 'woocommerce_blocks_checkout_enqueue_data', array( $this, 'enqueue_checkout_data' ) );
	}

	/**
	 * Initializes the payment method type.
	 *
	 * @since 1.9.0
	 * @return void
	 */
	public function initialize() {
		$this->settings = Helper::get_gateway_settings( $this->name );
	}

	/**
	 * Enqueue data for checkout page.
	 *
	 * @since 1.9.0
	 * @return void
	 */
	public function enqueue_checkout_data() {
		$this->enqueue_data( 'checkout' );
	}

	/**
	 * Extended feature support for subscription product.
	 *
	 * @since 1.9.0
	 * @return void
	 */
	public function subscription_product_supports() {
		// Extend feature support for the gateway.
		$this->features = array_merge(
			$this->features,
			array(
				'subscriptions',
				'subscription_cancellation', 
				'subscription_suspension', 
				'subscription_reactivation',
				'subscription_amount_changes',
				'subscription_date_changes',
				'subscription_payment_method_change',
				'subscription_payment_method_change_customer',
				'subscription_payment_method_change_admin',
				'multiple_subscriptions',
			)
		);
	}

	/**
	 * Enqueue data for cart page.
	 *
	 * @param string $page Current page.
	 * @since 1.9.0
	 * @return void
	 */
	private function enqueue_data( $page ) {
		if ( ! $this->assets_registry->exists( 'cpsw_stripe_element_data' ) ) {
			// Extend feature support if cart contains subscription product.
			if ( $this->is_subscription_item_in_cart() ) {
				$this->subscription_product_supports();
			}

			$public_key = Helper::get_stripe_pub_key();

			$localize_data = [
				'mode'                 => Helper::get_payment_mode(),
				'public_key'           => $public_key,
				'account_id'           => Helper::get_setting( 'cpsw_account_id' ),
				'gateways'             => Helper::get_available_gateways(),
				'icons'                => $this->get_icon(),
				'error_messages'       => Helper::get_localized_messages(),
				'label'                => $this->get_title(),
				'description'          => Helper::get_setting( 'description', 'cpsw_stripe_element' ),
				'enable_saved_cards'   => Helper::get_setting( 'enable_saved_cards', 'cpsw_stripe_element' ),
				'savecard_gateways'    => Helper::$savecard_supported_gateways,
				'appearance'           => Helper::get_setting( 'appearance', 'cpsw_stripe_element' ),
				'layout'               => Helper::get_setting( 'layout', 'cpsw_stripe_element' ),
				'stripe_payment_nonce' => wp_create_nonce( 'stripe_payment_nonce' ),
				'order_button_text'    => Helper::get_order_button_text( 'cpsw_stripe_element' ),
				'features'             => $this->features,
				'get_home_url'         => get_home_url(),
			];

			$localize_data = apply_filters( 'cpsw_stripe_element_localize_data', $localize_data );

			$this->assets_registry->add( 'cpsw_stripe_element_data', $localize_data );
		}

		// Enqueue the script.
		wp_enqueue_style( 'cpsw-block-payment-method', CPSW_URL . 'build/block/style-block.css', array(), CPSW_VERSION );
	}

	/**
	 * Get stripe activated payment cards icon.
	 *
	 * @since 1.9.0
	 */
	public function get_icon() {
		$icon = [];
		return $icon;
	}

	/**
	 * Returns if this payment method should be active. If false, the scripts will not be enqueued.
	 *
	 * @since 1.9.0
	 * @return boolean
	 */
	public function is_active() {
		return ! empty( $this->settings['enabled'] ) && 'yes' === $this->settings['enabled'];
	}

	/**
	 * Returns an array of scripts/handles to be registered for this payment method.
	 *
	 * @since 1.9.0
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
	 * Returns the title string to use in the UI (customisable via admin settings screen).
	 *
	 * @since 1.9.0
	 * @return string Title / label string
	 */
	private function get_title() {
		return isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'Flexible Payment Options', 'checkout-plugins-stripe-woo' );
	}

}
