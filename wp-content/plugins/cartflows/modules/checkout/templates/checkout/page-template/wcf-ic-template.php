<?php
/**
 * Template Name: Instant checkout
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;
?>

<html <?php language_attributes(); ?> class="no-js">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

	<body <?php body_class(); ?>>
<?php

if ( empty( $post->ID ) ) {
	return;
}

	$checkout_html = do_shortcode( '[cartflows_checkout]' );
?>
		<div class="cartflows-checkout-main-wrapper">
			<?php echo wp_kses_post( Cartflows_Instant_Checkout::get_instance()->instant_checkout_header_template() ); ?>

			<div class="main-container--wrapper">
				<div class="checkout-form--wrapper">
					<!-- INSTANT CHECKOUT STYLE TEMPLATE -->
					<?php
					if (
							empty( $checkout_html ) ||
							trim( $checkout_html ) == '<div class="woocommerce"></div>'
						) {
						do_action( 'cartflows_checkout_cart_empty', $checkout_id );
					} else {
						// Ignoring the escaping rule as we are echoing shortcode.
						echo $checkout_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
					?>
					<!-- INSTANT CHECKOUT STYLE TEMPLATE -->
				</div>
			</div>
		</div>
		<div class="wcf-hidefb">
			<?php wp_footer(); ?>
		</div>
	</body>
</html>
