<?php
/**
 * Instant Thankyou page
 *
 * @package CartFlows
 */

defined( 'ABSPATH' ) || exit;


?>
<div class="woocommerce-order">
	<?php
	if ( $order ) :
		$billing_address  = $order->get_formatted_billing_address();
		$shipping_address = $order->get_formatted_shipping_address();

		$show_shipping = ! wc_ship_to_billing_address_only() && $order->needs_shipping_address() && $billing_address !== $shipping_address;

		$shop_url       = apply_filters( 'woocommerce_continue_shopping_redirect', wc_get_raw_referer() ? wp_validate_redirect( wc_get_raw_referer(), '' ) : wc_get_page_permalink( 'shop' ) );
		$downloads      = $order->get_downloadable_items();
		$show_downloads = $order->has_downloadable_item();

		$place_address = is_array( $order->get_address( 'shipping' ) ) ? $order->get_address( 'shipping' ) : $order->get_address( 'billing' );
		$google_maps   = Cartflows_Helper::get_admin_settings_option( '_cartflows_google_auto_address', false, false );

		if ( ! empty( $google_maps['google_map_api_key'] ) ) {
			$google_place_key  = $google_maps['google_map_api_key'];
			$formatted_address = sprintf( '%s, %s, %s, %s, %s', $place_address['address_1'], $place_address['address_2'], $place_address['city'], $place_address['state'], $place_address['country'] );
		}
		?>
		<?php do_action( 'woocommerce_before_thankyou', $order->get_id() ); ?>

		<?php if ( $order->has_status( 'failed' ) ) : ?>

			<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed">
				<?php esc_html_e( 'Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.', 'cartflows' ); ?>
			</p>

			<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed-actions">
				<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="button pay"><?php esc_html_e( 'Pay', 'cartflows' ); ?></a>
				<?php if ( is_user_logged_in() ) : ?>
					<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="button pay"><?php esc_html_e( 'My account', 'cartflows' ); ?></a>
				<?php endif; ?>
			</p>

		<?php else : ?>
			<?php
			if ( $order->has_status( 'cancelled' ) ) :
				do_action( 'cartflows_woocommerce_order_overview_cancelled', $order );
			endif;
			?>

			<?php
			$flow_id      = wcf()->utils->get_flow_id();
			$thank_you_id = _get_wcf_thankyou_id();
			$position     = wcf()->options->get_thankyou_meta_value( $thank_you_id, 'wcf-instant-thankyou-order-review-summary-position' );

			if ( 'top' === $position ) {
				include CARTFLOWS_THANKYOU_DIR . 'templates/instant-thankyou-collapsed-order-summary.php';
			}

			echo '<div class="wcf-ic-layout-container">';
				include CARTFLOWS_THANKYOU_DIR . 'templates/instant-thankyou-order-details.php';
				include CARTFLOWS_THANKYOU_DIR . 'templates/instant-thankyou-your-product.php';
			echo '</div>';

			if ( 'bottom' === $position ) {
				include CARTFLOWS_THANKYOU_DIR . 'templates/instant-thankyou-collapsed-order-summary.php';
			}
		endif;
		?>

	<?php else : ?>
		<p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received">
			<?php echo apply_filters( 'woocommerce_thankyou_order_received_text', esc_html__( 'Thank you. Your order has been received.', 'cartflows' ), null ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</p>
	<?php endif; ?>
</div>
