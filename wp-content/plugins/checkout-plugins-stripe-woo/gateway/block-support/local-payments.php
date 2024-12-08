<?php
/**
 * Local Payments support for WooCommerce checkout blocks.
 *
 * @package checkout-plugins-stripe-woo
 * @since 1.7.0
 */

namespace CPSW\Gateway\BlockSupport;

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use Automattic\WooCommerce\Blocks\Assets\AssetDataRegistry;
use CPSW\Inc\Helper;

/**
 * Local_Payments class.
 *
 * @extends AbstractPaymentMethodType
 * @since 1.7.0
 */
class Local_Payments extends AbstractPaymentMethodType {

	/**
	 * The Payment Request configuration class used for Shortcode PRBs. We use it here to retrieve
	 * the same configurations.
	 *
	 * @var WC_Stripe_Payment_Request
	 * @since 1.7.0
	 */
	private $payment_request_configuration;

	/**
	 * Assets registry.
	 *
	 * @var mixed
	 * @since 1.7.0
	 */
	public $assets_registry;

	/**
	 * WooCommerce container.
	 *
	 * @var mixed
	 * @since 1.7.0
	 */
	public $container;

	/**
	 * Extra descriptions like mandate description.
	 *
	 * @var mixed
	 * @since 1.8.0
	 */
	public $local_payment_description;

	/**
	 * Supported features.
	 *
	 * @var array
	 * @since 1.8.0
	 */
	public $features = array( 'products' );

	/**
	 * Allowed countries.
	 * 
	 * @var array
	 * @since 1.8.0
	 */
	public $supported_countries;

	/**
	 * Default title.
	 * 
	 * @var array
	 * @since 1.8.0
	 */
	public $default_title;

	/**
	 * Constructor
	 *
	 * @param mixed $payment_request_configuration  The Stripe Payment Request configuration used for Payment
	 *                                   Request buttons.
	 * @since 1.7.0
	 */
	public function __construct( $payment_request_configuration = null ) {
		$this->container       = \Automattic\WooCommerce\Blocks\Package::container();
		$this->assets_registry = $this->container->get( AssetDataRegistry::class );
		add_action( 'woocommerce_blocks_checkout_enqueue_data', array( $this, 'enqueue_checkout_data' ) );
	}

	/**
	 * Initializes the payment method type.
	 *
	 * @since 1.7.0
	 * @return void
	 */
	public function initialize() {
		$this->settings = Helper::get_gateway_settings( $this->name );
	}

	/**
	 * Enqueue data for checkout page.
	 *
	 * @since 1.7.0
	 * @return void
	 */
	public function enqueue_checkout_data() {
		$this->enqueue_data( 'checkout' );
	}

	/**
	 * Enqueue data for cart page.
	 *
	 * @param string $page Current page.
	 * @since 1.7.0
	 * @return void
	 */
	private function enqueue_data( $page ) {
		if ( ! $this->assets_registry->exists( "{$this->name}_data" ) ) {
			$public_key        = Helper::get_stripe_pub_key();
			$countries         = null;
			$allowed_countries = Helper::get_setting( 'allowed_countries', $this->name );

			if ( ! empty( $allowed_countries ) && ( 'all_except' === $allowed_countries || 'specific' === $allowed_countries ) ) {
				$setting_prefix = 'all_except' === $allowed_countries ? 'except' : 'specific';
				$countries      = Helper::get_setting( $setting_prefix . '_countries', $this->name );
			}

			$localize_data = [
				'mode'                      => Helper::get_payment_mode(),
				'test_mode_description'     => $this->get_test_mode_description(),
				'public_key'                => $public_key,
				'account_id'                => Helper::get_setting( 'cpsw_account_id' ),
				'icons'                     => $this->get_icon(),
				'error_messages'            => Helper::get_localized_messages(),
				'label'                     => $this->get_title(),
				'description'               => Helper::get_setting( 'description', $this->name ),
				'local_payment_description' => $this->local_payment_description,
				'countries'                 => $countries,
				'enable_saved_cards'        => Helper::get_setting( 'enable_saved_card', $this->name ),
				'allowed_countries'         => $allowed_countries,
				'supported_countries'       => wp_json_encode( $this->supported_countries ),
				'order_button_text'         => Helper::get_order_button_text( $this->name ),
				'features'                  => $this->features,
				'stripe_local_nonce'        => wp_create_nonce( 'stripe_local_nonce' ),
			];

			$localize_data = apply_filters( "{$this->name}_localize_data", $localize_data );

			$this->assets_registry->add( "{$this->name}_data", $localize_data );
		}

		// Enqueue the script.
		wp_enqueue_style( 'cpsw-block-payment-method', CPSW_URL . 'build/block/style-block.css', array(), CPSW_VERSION );
	}

	/**
	 * Returns if this payment method should be active. If false, the scripts will not be enqueued.
	 *
	 * @since 1.7.0
	 * @return boolean
	 */
	public function is_active() {
		return ! empty( $this->settings['enabled'] ) && 'yes' === $this->settings['enabled'];
	}

	/**
	 * Returns an array of scripts/handles to be registered for this payment method.
	 *
	 * @since 1.7.0
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
	 * @since 1.7.0
	 * @return string Title / label string
	 */
	private function get_title() {
		return isset( $this->settings['title'] ) && ! empty( trim( $this->settings['title'] ) ) ? $this->settings['title'] : $this->default_title;
	}

	/**
	 * Returns an array of icons to use in the UI.
	 *
	 * @since 1.7.0
	 * 
	 * @return array
	 */
	protected function get_icon() {
		$payment_icon = Helper::get_payment_icon( $this->name );

		if ( empty( $payment_icon ) ) {
			return [];
		}

		// Remove the 'width' attribute from the icon, as it is not needed in checkout blocks.
		unset( $payment_icon['width'] );

		// Return the payment icon in an array, since WooCommerce checkout block expects icons in array format.
		return [ $payment_icon ];
	}  

	/**
	 * Get test mode description
	 *
	 * @return string
	 * @since 1.8.0
	 */
	public function get_test_mode_description() {
		return Helper::get_local_test_mode_description();
	}
}
