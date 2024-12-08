<?php
/**
 * Bancontact Payments support for WooCommerce Blocks checkout block payment method.
 *
 * @package checkout-plugins-stripe-woo
 * @since 1.8.0
 */

namespace CPSW\Gateway\BlockSupport;

use WC_HTTPS;

/**
 * Bancontact Payments class.
 *
 * @extends Local_Payments
 * @since 1.8.0
 */
final class Bancontact_Payments extends Local_Payments {

	/**
	 * Payment method name defined by payment methods extending this class.
	 *
	 * @var string
	 * @since 1.8.0
	 */
	protected $name = 'cpsw_bancontact';

	/**
	 * Constructor
	 *
	 * @since 1.8.0
	 */
	public function __construct() {
		parent::__construct();
		$this->default_title = __( 'Bancontact', 'checkout-plugins-stripe-woo' );
	}
}
