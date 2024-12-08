<?php
/**
 * Klarna Payments support for WooCommerce Blocks checkout block payment method.
 *
 * @package checkout-plugins-stripe-woo
 * @since 1.7.0
 */

namespace CPSW\Gateway\BlockSupport;

use CPSW\Inc\Helper;
use WC_HTTPS;

/**
 * Klarna Payments class.
 *
 * @extends Local_Payments
 * @since 1.7.0
 */
final class Klarna_Payments extends Local_Payments {

	/**
	 * Payment method name defined by payment methods extending this class.
	 *
	 * @var string
	 * @since 1.7.0
	 */
	protected $name = 'cpsw_klarna';

	/**
	 * Allowed countries.
	 * 
	 * @var array
	 * @since 1.7.0
	 */
	public $supported_countries;

	/**
	 * Constructor
	 *
	 * @since 1.7.0
	 */
	public function __construct() {
		parent::__construct();
		$this->supported_countries = Helper::get_supported_currency_country_for_gateway( 'klarna' )['country'];
		$this->default_title       = __( 'Klarna', 'checkout-plugins-stripe-woo' );
	}
}
