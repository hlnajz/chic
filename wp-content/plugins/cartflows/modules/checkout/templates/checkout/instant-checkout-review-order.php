<?php
/**
 * Instant Checkout review order
 *
 * @package Cartflows-Pro
 */

defined( 'ABSPATH' ) || exit;

$is_coupon_enabled = Cartflows_Checkout_Markup::get_instance()->is_custom_coupon_field_enabled();

$checkout_id = _get_wcf_checkout_id();
if ( ! $checkout_id ) {
	$checkout_id = isset( $_GET['wcf_checkout_id'] ) && ! empty( $_GET['wcf_checkout_id'] ) ? intval( wp_unslash( $_GET['wcf_checkout_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
}

$optimize_coupon_field = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-optimize-coupon-field' );
$is_coupons            = WC()->cart->get_coupons();
$is_coupon_applied     = ! empty( $is_coupons ) && is_array( $is_coupons ) ? reset( $is_coupons ) : false;

?>
<table class="instant-checkout-order-review-table shop_table woocommerce-checkout-review-order-table ">
	<tbody>
		<?php
			do_action( 'woocommerce_review_order_before_cart_contents' );

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

			if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
				?>
						<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
							<td class="product-name">
						<?php
						echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) ) . '&nbsp;';



						$show_product_image = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-review-show-product-images' );

						if ( 'yes' !== $show_product_image ) {
							echo apply_filters( 'woocommerce_checkout_cart_item_quantity', ' <strong class="product-quantity">' . sprintf( '&times;&nbsp;%s', $cart_item['quantity'] ) . '</strong>', $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						}
						?>
						<?php echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</td>
							<td class="product-total">
							<?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</td>
						</tr>
						<?php
			}
		}

			do_action( 'woocommerce_review_order_after_cart_contents' );

		?>
	</tbody>
	<tfoot>
		<?php if ( $is_coupon_enabled ) : ?>
			<tr class="coupon-field">
				<td colspan="2">
					<!-- Order review coupon field -->

					<div class="wcf-custom-coupon-field <?php echo 'yes' === $optimize_coupon_field ? 'wcf-hide-field' : ''; ?> <?php echo ! empty( $is_coupon_applied ) ? 'wcf-coupon-applied' : ''; ?>" id="wcf_custom_coupon_field">

						<?php if ( ! empty( $is_coupon_applied ) ) { ?>
							<div class="woocommerce-message" role="alert">
								<?php esc_html_e( 'Coupon code applied successfully.', 'cartflows' ); ?>
							</div>
						<?php } ?>

						<?php
						if ( 'yes' === $optimize_coupon_field ) {
							?>
							<a href="#" id="wcf_optimized_wcf_custom_coupon_field" class="wcf-optimized-coupon-field"><?php esc_html_e( 'Have a coupon?', 'cartflows' ); ?></a>
							<?php
						}
						?>
						<div class="wcf-coupon-col-1">
							<span>
								<input type="text" name="coupon_code" class="input-text wcf-coupon-code-input" placeholder="<?php esc_attr_e( 'Coupon Code', 'cartflows' ); ?>" id="order_review_coupon_code" value="">
							</span>
						</div>
						<div class="wcf-coupon-col-2">
							<span>
								<button type="button" class="button wcf-submit-coupon wcf-btn-small" name="apply_coupon" value="Apply"><?php esc_html_e( 'Apply', 'cartflows' ); ?></button>
							</span>
						</div>
					</div>
				</td>
			</tr>
		<?php endif; ?>
		<tr class="cart-subtotal">
			<th><?php esc_html_e( 'Subtotal', 'cartflows' ); ?></th>
			<td><?php wc_cart_totals_subtotal_html(); ?></td>
		</tr>

		<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
			<tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
				<th><?php wc_cart_totals_coupon_label( $coupon ); ?></th>
				<td><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
			</tr>
		<?php endforeach; ?>

		<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
			<?php
			$i = 0;

			$package_name = apply_filters( 'wcf_woocommerce_shipping_package_name', esc_html__( 'Shipping', 'cartflows' ) );

			?>
			<tr class="cart-shipping">
				<th><?php echo wp_kses_post( $package_name ); ?></th>
				<td><?php Cartflows_Instant_Checkout::selected_shipping_method(); ?></td>
			</tr>

		<?php endif; ?>

		<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
			<tr class="fee">
				<th><?php echo esc_html( $fee->name ); ?></th>
				<td><?php wc_cart_totals_fee_html( $fee ); ?></td>
			</tr>
		<?php endforeach; ?>

		<?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
			<?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
				<?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited ?>
					<tr class="tax-rate tax-rate-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
						<th><?php echo esc_html( $tax->label ); ?></th>
						<td><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr class="tax-total">
					<th><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></th>
					<td><?php wc_cart_totals_taxes_total_html(); ?></td>
				</tr>
			<?php endif; ?>
		<?php endif; ?>


		<?php do_action( 'woocommerce_review_order_before_order_total' ); ?>

		<tr class="order-total">
			<th><?php esc_html_e( 'Total', 'cartflows' ); ?></th>
			<td><?php wc_cart_totals_order_total_html(); ?></td>
		</tr>

		<?php do_action( 'woocommerce_review_order_after_order_total' ); ?>

	</tfoot>
</table>





