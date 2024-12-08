<?php
/**
 * CartFlows Instant Checkout Order Details.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( ! $order ) {
	return;
}
// Fetch Billing email.
$billing_email = $order->get_billing_email();

?>
<div class="wcf-ic-layout-left-column">

	<div class="col2-set wcf-col2-set" id="order-address">
		<div class="wcf-ic-status">
			<div class="wcf-ic-status__left">
				<div class="wcf-ic-status__left-icon">
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="wcf-ic-status__icon-svg">
						<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
					</svg>
				</div>
				<div>
					<p>
						<?php
						// Translators: Order ID.
						echo sprintf( esc_html__( 'Order #%d', 'cartflows' ), esc_html( $order->get_id() ) );
						?>
					</p>
					<h2>
						<?php
						// Translators: First name.
						echo sprintf( esc_html__( 'Thank you, %s!', 'cartflows' ), esc_html( $order->get_billing_first_name() ) );
						?>
					</h2>
				</div>
			</div>
		</div>

		<?php

		if ( isset( $formatted_address ) && ! empty( $google_maps['google_map_api_key'] ) ) {
			?>
			<div class="wcf-ic-ty-map">
				<iframe width="640" height="300" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.it/maps?q=<?php echo esc_attr( $formatted_address ); ?>&output=embed"></iframe>
			</div>
		<?php } ?>
		<div class="wcf-ic-updates wcf-ic-box">
			<h2 class="woocommerce-order-status"><?php esc_html_e( 'Order Updates', 'cartflows' ); ?></h2>
			<p><?php echo esc_html( apply_filters( 'cartflows_pro_instant_thankyou_order_update_message', __( 'You will receive order and shipping updates via email.', 'cartflows' ) ) ); ?> </p>
		</div>
		<div class="wcf-ic-review-customer wcf-ic-review-customer--ty wcf-ic-box">

			<?php
			if ( ! empty( $billing_email ) ) :
				?>
				<div class="wcf-ic-review-customer__row wcf-ic-review-customer__row--contact">
					<div class="wcf-ic-review-customer__label"><label><?php esc_html_e( 'Contact', 'cartflows' ); ?></label></div>
					<div class="wcf-ic-review-customer__content"><p><?php echo esc_html( $order->get_billing_email() ); ?></p></div>
				</div>
			<?php endif; ?>

			<div class="wcf-ic-review-customer__row wcf-ic-review-customer__row--address">
				<div class="wcf-ic-review-customer__label">
					<label>
						<?php
						if ( ! $show_shipping ) {
							esc_html_e( 'Address', 'cartflows' );

						} else {
							esc_html_e( 'Billing', 'cartflows' );
						}
						?>
					</label>
				</div>
				<div class="wcf-ic-review-customer__content">
					<address>
						<?php echo wp_kses_post( $order->get_formatted_billing_address() ); ?>
						<?php if ( $order->get_billing_phone() ) : ?>
							<p class="woocommerce-customer-details--phone"><?php echo esc_html( $order->get_billing_phone() ); ?></p>
						<?php endif; ?>
						<?php if ( $order->get_billing_email() ) : ?>
							<p class="woocommerce-customer-details--email"><?php echo esc_html( $order->get_billing_email() ); ?></p>
						<?php endif; ?>
						<?php
							/**
							 * Action hook fired after an address in the order customer details.
							 *
							 * @since 8.7.0
							 * @param string $address_type Type of address (billing or shipping).
							 * @param WC_Order $order Order object.
							 */
							do_action( 'woocommerce_order_details_after_customer_address', 'billing', $order );
						?>
					</address>
				</div>
			</div>
			<?php if ( $show_shipping ) : ?>
				<div class="wcf-ic-review-customer__row wcf-ic-review-customer__row--shipping-address">
					<div class="wcf-ic-review-customer__label"><label><?php esc_html_e( 'Shipping', 'cartflows' ); ?></label></div>
					<div class="wcf-ic-review-customer__content">
						<address>
							<?php echo wp_kses_post( $order->get_formatted_shipping_address() ); ?>
							<?php
							/**
							 * Action hook fired after an address in the order customer details.
							 *
							 * @since 8.7.0
							 * @param string $address_type Type of address (billing or shipping).
							 * @param WC_Order $order Order object.
							 */
							do_action( 'woocommerce_order_details_after_customer_address', 'shipping', $order );
							?>
						</address>
					</div>
				</div>
			<?php endif; ?>
			<div class="wcf-ic-review-customer__row">
				<div class="wcf-ic-review-customer__label"><label><?php esc_html_e( 'Payment', 'cartflows' ); ?></label></div>
				<div class="wcf-ic-review-customer__content">
					<p>
						<?php echo esc_html( $order->get_payment_method_title() ); ?>
					</p>
				</div>
			</div>
			<?php do_action( 'woocommerce_receipt_' . $order->get_payment_method(), $order->get_id() ); ?>
		</div>
		<?php if ( $show_downloads ) : ?>
			<div class="wcf-ic-ty-downloads wcf-ic-ty-box wcf-ic-ty-box--downloads wcf-ic-box">
				<?php
				wc_get_template(
					'order/order-downloads.php',
					array(
						'downloads'  => $downloads,
						'show_title' => true,
					)
				);
				?>
			</div>
		<?php endif; ?>
		<div class="wcf-ic-footer">
			<span class="wcf-ic-footer__continue-shipping">
				<a class="wcf-ic-button wcf-ic-button--ty" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Continue Shopping', 'cartflows' ); ?></a>
			</span>
		</div>
	</div>
</div>
