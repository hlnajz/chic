<?php
/**
 * P24 Payments support for WooCommerce Blocks checkout block payment method.
 *
 * @package checkout-plugins-stripe-woo
 * @since 1.8.0
 */

namespace CPSW\Gateway\BlockSupport;

use WC_HTTPS;

/**
 * P24 Payments class.
 *
 * @extends Local_Payments
 * @since 1.8.0
 */
final class P24_Payments extends Local_Payments {

	/**
	 * Payment method name.
	 *
	 * @var string
	 * @since 1.8.0
	 */
	protected $name = 'cpsw_p24';

	/**
	 * Constructor
	 *
	 * @since 1.8.0
	 */
	public function __construct() {
		parent::__construct();
		$this->default_title = __( 'Przelewy24', 'checkout-plugins-stripe-woo' );
	}
}
