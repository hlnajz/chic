<?php
/**
 * Alipay Payments support for WooCommerce Blocks checkout block payment method.
 *
 * @package checkout-plugins-stripe-woo
 * @since 1.7.0
 */

namespace CPSW\Gateway\BlockSupport;

use WC_HTTPS;

/**
 * Alipay Payments class.
 *
 * @extends Local_Payments
 * @since 1.7.0
 */
final class Alipay_Payments extends Local_Payments {

	/**
	 * Payment method name defined by payment methods extending this class.
	 *
	 * @var string
	 * @since 1.7.0
	 */
	protected $name = 'cpsw_alipay';

	/**
	 * Allowed countries based on currency codes.
	 *
	 * The keys represent currency codes, and the values are arrays of countries allowed for each currency.
	 * 
	 * Reference : https://stripe.com/docs/payments/alipay#supported-currencies
	 * 
	 * @var array
	 * @since 1.7.0
	 */
	public $supported_countries = [
		'EUR' => [ 'AT', 'BE', 'BG', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'NO', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE', 'CH' ],
	];

	/**
	 * Constructor
	 *
	 * @since 1.7.0
	 */
	public function __construct() {
		parent::__construct();
		$this->default_title = __( 'Alipay', 'checkout-plugins-stripe-woo' );
	}
}
