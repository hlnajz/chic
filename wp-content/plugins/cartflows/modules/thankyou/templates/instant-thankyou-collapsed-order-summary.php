<?php
/**
 * CartFlows Mobile Order Review Table for Modern Checkout.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$is_coupon_enabled = Cartflows_Checkout_Markup::get_instance()->is_custom_coupon_field_enabled();
$visibility_class  = '';

// Add display class only if the filter is true. By defauled summary box should be closed.
if ( ! apply_filters( 'cartflows_show_mobile_order_summary_collapsed', true ) ) {
	$visibility_class = 'wcf-show';
}


?>

<!-- Mobile responsive order review template -->
<div class="wcf-collapsed-order-review-section <?php echo esc_attr( $visibility_class ); ?> <?php echo esc_attr( $position ); ?> ">
	<div class='wcf-order-review-toggle'>
		<div class='wcf-order-review-toggle-button-wrap'>
			<span class='wcf-order-review-toggle-text'><?php echo esc_html( Cartflows_Checkout_Markup::get_instance()->get_order_review_toggle_texts() ); ?></span>
			<span class='wcf-order-review-toggle-button cartflows-icon cartflows-cheveron-down'></span>
			<span class='wcf-order-review-toggle-button cartflows-icon cartflows-cheveron-up'></span>
		</div>
	</div>

	<div class="wcf-cartflows-review-order-wrapper">
		<?php require CARTFLOWS_THANKYOU_DIR . 'templates/instant-thankyou-your-product.php'; ?>
	</div>
</div>
<?php
