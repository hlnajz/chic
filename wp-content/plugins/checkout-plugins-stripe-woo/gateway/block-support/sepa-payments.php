<?php
/**
 * SEPA Payments support for WooCommerce Blocks checkout block payment method.
 *
 * @package checkout-plugins-stripe-woo
 * @since 1.8.0
 */

namespace CPSW\Gateway\BlockSupport;

use WC_HTTPS;
use CPSW\Inc\Helper;

/**
 * SEPA Payments class.
 *
 * @extends Local_Payments
 * @since 1.8.0
 */
final class Sepa_Payments extends Local_Payments {

	/**
	 * Payment method name defined by payment methods extending this class.
	 *
	 * @var string
	 * @since 1.8.0
	 */
	protected $name = 'cpsw_sepa';

	/**
	 * Constructor
	 *
	 * @since 1.8.0
	 */
	public function __construct() {
		parent::__construct();
		// Extend feature support for the gateway.
		$this->features                  = array_merge(
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
		$this->default_title             = __( 'SEPA', 'checkout-plugins-stripe-woo' );
		$this->local_payment_description = Helper::get_sepa_mandate_description();
	}

	/**
	 * Get test mode description
	 *
	 * @return string
	 * @since 1.8.0
	 */
	public function get_test_mode_description() {
		return Helper::get_sepa_test_mode_description();
	}
}
