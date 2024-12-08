<?php

$link = 'https://xootix.com/plugins/easy-login-for-woocommerce#sp-addons';

$addons = array(

	'social_login' => array(
		'title' => 'Social Login',
		'icon' 	=> 'dashicons-facebook',
		'desc' 	=> 'Allow users to login & register using their social accounts ( Facebook, Google, Apple & X ) with a single click.',
		'link' 	=> $link,
		'demo' 	=> 'https://demo.xootix.com/easy-login-for-woocommerce'
	),

	'security' => array(
		'title' => 'Security',
		'icon' 	=> 'dashicons-shield-alt',
		'desc' 	=> 'Protect your form from bots using (Google Recaptcha(v2/v3) / Turnstile / Friendly Captcha ) + Password strength meter + Limit login attempts',
		'link' 	=> $link,
		'demo' 	=> 'https://demo.xootix.com/easy-login-for-woocommerce'
	),

	'fields' => array(
		'title' 	=> 'Custom Registration fields',
		'icon' 		=> 'dashicons-plus',
		'desc' 		=> 'Add extra fields to registration form , display them on user profile & myaccount page. (See <a href="'.admin_url('admin.php?page=xoo-el-fields').'" target="__blank">Fields page</a> to know supported field types )',
		'link' 	=> $link,
		'demo' 	=> 'https://demo.xootix.com/easy-login-for-woocommerce'
	),


	'email_verify' => array(
		'title' => 'Email Verification/User approval',
		'icon' 	=> 'dashicons-email',
		'desc' 	=> 'Disable user access to the account. You have two options: either ask users to verify their email address by clicking on the verification link sent to them, or manually approve them from the admin panel.',
		'link' 	=> $link,
		'demo' 	=> 'https://demo.xootix.com/user-verification-for-woocommerce'
	),


	'otp_login' => array(
		'title' => 'One time password(OTP) Login',
		'icon' 	=> 'dashicons-phone',
		'desc' 	=> 'Allow users to login with OTP ( sent on their phone or email ) therefore removing the need to remember a password.',
		'link' 	=> $link,
		'demo' 	=> 'https://demo.xootix.com/mobile-login-for-woocommerce'
	),

	'profile_builder' => array(
		'title' => 'Profile Builder',
		'icon' 	=> 'dashicons-admin-users',
		'desc' 	=> 'Replace the old WooCommerce/WordPress interface for updating fields with a new, modern design similar to the signup form. Use a shortcode to display and allow users to update their profile fields.',
		'link' 	=> $link,
		'demo' 	=> 'https://demo.xootix.com/easy-login-for-woocommerce'
	),

);

?>

<div class="xoo-addon-container">
	<?php foreach ( $addons as $id => $data ): ?>
		<div class="xoo-addon">
			<span class="dashicons <?php echo esc_attr( $data['icon'] ); ?>"></span>
			<span class="xoo-ao-title"><?php echo $data['title'] ?></span>
			<div class="xoo-ao-desc"><?php echo $data['desc']; ?></div>
			<div class="xoo-ao-btns">
				<a target="_blank" href="<?php echo esc_url( $data['link'] ) ?>">BUY</a>
				<?php if( isset( $data['demo'] ) ): ?>
					<a target="_blank" href="<?php echo esc_url( $data['demo'] ) ?>">DEMO</a>
				<?php endif; ?>
			</div>
		</div>
	<?php endforeach; ?>
</div>