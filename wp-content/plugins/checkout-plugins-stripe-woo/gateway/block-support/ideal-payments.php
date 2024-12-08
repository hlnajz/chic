<?php
/**
 * IDEAL Payments support for WooCommerce Blocks checkout block payment method.
 *
 * @package checkout-plugins-stripe-woo
 * @since 1.7.0
 */

namespace CPSW\Gateway\BlockSupport;

use WC_HTTPS;

/**
 * Ideal Payments class.
 *
 * @extends Local_Payments
 * @since 1.7.0
 */
final class Ideal_Payments extends Local_Payments {

	/**
	 * Payment method name defined by payment methods extending this class.
	 *
	 * @var string
	 * @since 1.7.0
	 */
	protected $name = 'cpsw_ideal';

	/**
	 * Constructor
	 *
	 * @since 1.7.0
	 */
	public function __construct() {
		parent::__construct();
		$this->default_title = __( 'iDEAL', 'checkout-plugins-stripe-woo' );
	}
}
