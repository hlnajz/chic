<?php
/**
 * CartFlows Instant Checkout Product Details.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( ! $order ) {
	return;
}
?>
<div class="wcf-ic-layout-right-column">
	<div class="wcf-ic-ty-product-details">
		<h2 class="wcf-ic-heading wcf-ic-heading--cart-icon wcf-ic-order-review-heading--ty">
			<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="wcf-ic-cart-icon">
				<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
			</svg>
			<?php esc_html_e( 'Your Order', 'cartflows' ); ?>
		</h2>
		<div class="wcf-ic-order-item-wrap">
			<div class="wcf-ic-cart-order-item-wrap">
				<?php
					$order_items = $order->get_items( 'line_item' );

					$child_orders_ids = $order->get_meta( '_cartflows_offer_child_orders' );
				if ( ! empty( $child_orders_ids ) ) {
					foreach ( $child_orders_ids as $child_order_id => $child_data ) {
						$child_order = wc_get_order( $child_order_id );
						if ( ! $child_order ) {
							continue;
						}
						$child_order_items = $child_order->get_items( 'line_item' );
						$order_items       = array_merge( $order_items, $child_order_items );
					}
				}

					$total_price = 0;
				foreach ( $order_items as $item_id => $item ) {
					$product      = $item->get_product();
					$qty          = $item->get_quantity();
					$refunded_qty = $order->get_qty_refunded_for_item( $item_id );
					$total_price  = $total_price + $item->get_total();

					if ( empty( $product ) ) {
						continue;
					}
					?>
						<div class="wcf-ic-cart-order-item wcf-ic-cart-order-item--ty">
							<div class="wcf-ic-cart-order-item__content-wrapper">
								<div class="wcf-product-image">
									<div class="wcf-ic-cart-image wcf-ic-cart-image--ty">
										<?php
											echo wp_kses_post( $product->get_image() );
										?>
										<span class="wcf-ic-cart-order-item__info-qty">
											<?php
											/**
											 * Order item quantity HTML.
											 *
											 * @since 2.1.0
											 */
											echo wp_kses_post( apply_filters( 'woocommerce_order_item_quantity_html', ' <strong class="product-quantity">' . sprintf( '%s', esc_html( $qty ) ) . '</strong>', $item ) );
											?>
										</span>
									</div>

									<div class="wcf-ic-cart-order-item__info-name">
										<?php
											echo esc_html( $product->get_name() );
										?>
									</div>
								</div>
								<div class="wcf-ic-cart-order-item__info-varient">
									<?php
									/**
									 * Thank you page: Order item meta start.
									 *
									 * @since 2.1.0
									 */
									do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order, false );

									$meta = wc_display_item_meta( $item );

									if ( $meta ) {
										echo wp_kses_post( $meta );
									}

									/**
									 * Thank you page: Order item meta end.
									 *
									 * @since 2.1.0
									 */
									do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order, false );
									?>
								</div>

							</div>
							<div class="wcf-ic-cart-order-item__price">
								<?php
								echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) );
								?>
							</div>
						</div>
					<?php
				} // For loop closed.
				?>
			</div>
			<div class="wcf-ic-cart-totals wcf-ic-cart-totals--cart_subtotal">
				<div class="wcf-ic-cart-totals__label"><span><?php esc_html_e( 'Subtotal', 'cartflows' ); ?></span></div>
				<div class="wcf-ic-cart-totals__value">
					<?php
						echo wp_kses_post( wc_price( $total_price, array( 'currency' => $order->get_currency() ) ) );
					?>
				</div>
			</div>
			<?php
				$order_totals = $order->get_order_item_totals();
			foreach ( $order_totals as $key => $total ) {
				if ( 'payment_method' === $key || 'cart_subtotal' === $key || 'order_total' === $key ) {
					continue;
				}
				?>
						<div class="wcf-ic-cart-totals <?php echo 'wcf-ic-cart-totals--' . esc_html( $key ); ?>">
							<div class="wcf-ic-cart-totals__label"><span><?php echo esc_html( trim( $total['label'], ':' ) ); ?></span></div>
							<div class="wcf-ic-cart-totals__value">
							<?php
							if ( 'order_total' == $key ) {
								echo sprintf( '<div class="wcf-ic-cart-totals__currency-badge">%s</div>', esc_html( $order->get_currency() ) );
							}
							?>
								<span><?php echo wp_kses_post( $total['value'] ); ?></span>
							</div>
						</div>
				<?php
			}
			?>
			<div class="wcf-ic-cart-totals total">
				<div class="wcf-ic-cart-totals__label">
					<span><?php esc_html_e( 'Total', 'cartflows' ); ?></span>
				</div>
				<div class="wcf-ic-cart-totals__value">
					<?php
						$shipping_total = $order->get_shipping_total();
						$total_price    = $total_price + $shipping_total;
						echo sprintf( '<div class="wcf-ic-cart-totals__currency-badge">%s</div>', esc_html( $order->get_currency() ) );
					?>
					<span><?php echo wp_kses_post( wc_price( $total_price, array( 'currency' => $order->get_currency() ) ) ); ?></span>
				</div>
			</div>
		</div>
	</div>
</div>
