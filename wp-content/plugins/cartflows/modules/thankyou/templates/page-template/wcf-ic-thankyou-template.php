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

$site_logo = get_custom_logo();

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
		<meta name="robots" content="noindex">
		<title><?php wp_title( '-', true, 'right' ); ?><?php bloginfo( 'name' ); ?></title>
		<link rel="profile" href="http://gmpg.org/xfn/11" />
		<?php wp_head(); ?>
	</head>

	<body <?php body_class(); ?>>
		<div class="wrapper">
			<?php echo wp_kses_post( Cartflows_Thankyou_Markup::get_instance()->instant_checkout_header_template() ); ?>

			<div class="main-container">
				<div class='wcf-instant-thankyou' id='wcf-instant-thankyou'>
					<!-- INSTANT THANK YOU STYLE TEMPLATE -->
					<?php echo do_shortcode( '[cartflows_order_details]' ); ?>
					<!-- INSTANT THANK YOU STYLE TEMPLATE -->
				</div>
			</div>
		</div>
		<div class="wcf-hidefb">
			<?php wp_footer(); ?>
		</div>
	</body>
</html>
