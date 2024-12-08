<?php
/**
 * Instant Checkout.
 *
 * @package CartFlows Checkout
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Instant Checkout
 */
class Cartflows_Instant_Checkout {


	/**
	 * Member Variable
	 *
	 * @var object instance
	 */
	private static $instance;


	/**
	 *  Initiator
	 *
	 * @return object Cartflows_Instant_Checkout.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 *  Constructor
	 */
	public function __construct() {
		// Register Instant Checkout actions & hooks.
		add_action( 'wp', array( $this, 'instant_checkout_actions' ), 999 );

		// Store Checkout related actions.
		add_filter( 'cartflows_checkout_show_empty_cart_notice', array( $this, 'should_show_empty_cart_notice' ), 10, 1 );

		// Change the checkout skin value.
		add_filter( 'cartflows_checkout_meta_wcf-checkout-layout', array( $this, 'stop_other_checkout_layout_implementations' ), 10, 1 );

		// Set the page template to Instant Checkout.
		add_filter( 'cartflows_page_template_body_classes', array( $this, 'add_body_class_for_instant_checkout' ) );

		// Instant checkout template file.
		add_filter( 'cartflows_page_template_file', array( $this, 'cartflows_instant_checkout_page_template_file' ), 10, 1 );

		// Add admin checkout editor settings for instant checkout.
		add_filter( 'cartflows_admin_checkout_editor_settings', array( $this, 'add_editor_settings' ), 10, 1 );

		// Product quantity badge.
		add_filter( 'cartflows_checkout_order_review_item_image', array( $this, 'add_product_quantity_badge' ), 10, 5 );

	}

	/**
	 * Hide the display of WC notice of empty cart.
	 *
	 * @since 2.1.0
	 * @param bool $show_message Should show the error message or not.
	 * @return bool $show_message Updated should show the error message or not.
	 */
	public function should_show_empty_cart_notice( $show_message ) {

		$flow_id = wcf()->utils->is_step_post_type() ? wcf()->utils->get_flow_id() : 0;

		if ( empty( $flow_id ) || ! class_exists( 'Cartflows_Helper' ) || ! Cartflows_Helper::is_instant_layout_enabled( (int) $flow_id ) ) {
			return $show_message;
		}

		$show_message = false;

		return $show_message;

	}

	/**
	 * Instant checkout actions register.
	 *
	 * @since 2.1.0
	 * @return void
	 */
	public function instant_checkout_actions() {

		// Return if not checkout type.
		if ( ! _is_wcf_checkout_type() ) {
			return;
		}

		$flow_id = wcf()->utils->is_step_post_type() ? wcf()->utils->get_flow_id() : 0;

		if ( empty( $flow_id ) || ! class_exists( 'Cartflows_Helper' ) || ! Cartflows_Helper::is_instant_layout_enabled( (int) $flow_id ) ) {
			return;
		}

		add_filter( 'cartflows_checkout_cart_empty_message', array( $this, 'remove_default_empty_cart_message' ), 9, 1 );

		add_action( 'cartflows_checkout_cart_empty', array( $this, 'add_empty_cart_designed_block' ), 10, 1 );

		add_filter( 'cartflows_checkout_form_layout', array( $this, 'add_class_for_instant_checkout' ), 10, 1 );
		add_filter( 'cartflows_page_template', array( $this, 'set_instant_checkout_template_type' ), 10, 1 );

		// Change the location of coupon field.
		remove_action( 'woocommerce_checkout_order_review', array( Cartflows_Checkout_Markup::get_instance(), 'display_custom_coupon_field' ), 10 );

		// Add customer billing email field.
		add_action( 'woocommerce_checkout_billing', array( $this, 'add_custom_billing_email_field' ), 9, 1 );

		// Unset the extra email field.
		add_filter( 'woocommerce_checkout_fields', array( $this, 'unset_fields_for_instant_checkout' ), 10, 1 );

		// Change heading strings for instant checkout.
		add_filter( 'cartflows_woo_shipping_options_text', array( $this, 'update_shipping_options_text' ), 10, 1 );

		remove_action( 'woocommerce_before_checkout_form_cart_notices', 'woocommerce_output_all_notices', 10 );
		remove_action( 'woocommerce_before_checkout_form', 'woocommerce_output_all_notices', 10 );

		// Change the structure of the checkout form.
		add_action( 'woocommerce_checkout_before_customer_details', array( $this, 'instant_checkout_wrapper_start' ), 10, 1 );
		add_action( 'woocommerce_checkout_after_customer_details', array( $this, 'order_review_wrapper_start' ), 15, 1 );

		// Rarrange the payment section.
		remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );
		add_action( 'cartflows_checkout_after_instant_shipping', 'woocommerce_checkout_payment', 21 );

		// Change payment section heading.
		add_action( 'woocommerce_review_order_before_payment', array( $this, 'display_custom_payment_heading' ), 12 );

		// Change the shipping default package name.
		add_filter( 'woocommerce_shipping_package_name', array( $this, 'add_span_tag_in_shipping_package_name' ), 10, 1 );

		// Update the cart total price to display on button and on the mobile order view section.
		add_filter( 'woocommerce_update_order_review_fragments', array( $this, 'instant_checkout_order_review' ), 99, 1 );

		// Order Review template.
		add_action( 'woocommerce_checkout_after_order_review', array( $this, 'show_collapsed_order_summary' ), 11, 1 );
		add_filter( 'cartflows_get_order_review_template_path', array( $this, 'update_path_for_order_review_template' ), 10, 2 );



	}

	/**
	 * Include custom class for Instant checkout.
	 *
	 * @param string $checkout_layout layout type.
	 */
	public function add_class_for_instant_checkout( $checkout_layout ) {

		$checkout_layout = 'instant-checkout';

		return $checkout_layout;

	}

	/**
	 * Add instant checkout coupon wrapper.
	 *
	 * @since 2.1.0
	 * @param string $coupon_data Sent Coupon data.
	 * @return void
	 */
	public function add_instant_checkout_coupon_wrapper( $coupon_data ) {
		$is_coupon_enabled = Cartflows_Checkout_Markup::get_instance()->is_custom_coupon_field_enabled();
		// Return if the coupon field is not enabled.
		if ( ! $is_coupon_enabled ) {
			return;
		}

		echo '<tr class="coupon-field"><td colspan="2">';
	}

	/**
	 * Close instant checkout coupon wrapper.
	 *
	 * @since 2.1.0
	 * @param string $coupon_data SentCoupon data.
	 * @return void
	 */
	public function close_instant_checkout_coupon_wrapper( $coupon_data ) {

		$is_coupon_enabled = Cartflows_Checkout_Markup::get_instance()->is_custom_coupon_field_enabled();
		// Return if the coupon field is not enabled.
		if ( ! $is_coupon_enabled ) {
			return;
		}

		echo '</td></tr>';
	}

	/**
	 * Include order review template for instant checkout.
	 *
	 * @return void
	 */
	public function custom_order_review_template() {
		include CARTFLOWS_CHECKOUT_DIR . 'templates/checkout/instant-checkout-review-order.php';
	}

	/**
	 * Cartflows instant checkout page template file.
	 *
	 * @param string $file File path.
	 * @return string
	 */
	public function cartflows_instant_checkout_page_template_file( $file ) {
		$flow_id = wcf()->utils->is_step_post_type() ? wcf()->utils->get_flow_id() : 0;

		if ( empty( $flow_id ) || ! class_exists( 'Cartflows_Helper' ) || ! Cartflows_Helper::is_instant_layout_enabled( (int) $flow_id ) ) {
			return $file;
		}
		if ( _is_wcf_checkout_type() ) {
			return CARTFLOWS_CHECKOUT_DIR . 'templates/checkout/page-template/wcf-ic-template.php';
		} elseif ( _is_wcf_thankyou_type() ) {
			return CARTFLOWS_THANKYOU_DIR . 'templates/page-template/wcf-ic-thankyou-template.php';
		} else {
			return $file;
		}

	}

	/**
	 * Add body classes for instant checkout layout.
	 *
	 * @param array $body_classes The body classes.
	 * @return array $body_classes Modified body added classes/
	 */
	public function add_body_class_for_instant_checkout( $body_classes ) {
		$flow_id = wcf()->utils->is_step_post_type() ? wcf()->utils->get_flow_id() : 0;

		if ( empty( $flow_id ) ) {
			return $body_classes;
		}

		if ( _is_wcf_checkout_type() && Cartflows_Helper::is_instant_layout_enabled( intval( $flow_id ) ) ) {
			$body_classes[] = 'cartflows-instant-checkout';
		}

		return $body_classes;
	}

	/**
	 * Instant checkout template breadcrumbs.
	 *
	 * @return void
	 */
	public function instant_checkout_template_breadcrumbs() {
		ob_start();
		$separator  = ' &gt; ';
		$home_title = esc_html__( 'Home', 'cartflows' ); ?>

		<ul id="wcf-ic-breadcrumbs" class="wcf-ic-breadcrumbs">
		<?php
		if ( isset( $_SERVER['HTTP_REFERER'] ) && ! empty( $_SERVER['HTTP_REFERER'] ) ) {
			$url     = wp_strip_all_tags( $_SERVER['HTTP_REFERER'] );
			$post_id = url_to_postid( $url ); //phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.url_to_postid_url_to_postid
			if ( get_post_meta( $post_id, 'wcf-step-type', true ) === 'landing' ) {
				?>
				<li class="wcf-ic-breadcrumb-item"><a href="<?php echo esc_url( $url ); ?>"> <?php echo esc_html( get_the_title( $post_id ) ); ?></a></li> <?php echo esc_html( $separator ); ?>
				<li class="wcf-ic-breadcrumb-item"><?php echo esc_html( get_the_title() ); ?></li>
				<?php
			}
		} else {
			?>
			<li class="wcf-ic-breadcrumb-item"><a href="<?php echo esc_url( get_home_url() ); ?>"> <?php echo esc_html( $home_title ); ?></a></li>  <?php echo esc_html( $separator ); ?>
			<li class="wcf-ic-breadcrumb-item"> <?php echo esc_html( get_the_title() ); ?></li>
		<?php } ?>
		</ul>
		<?php
		$breadcrumbs = ob_get_contents();
		ob_end_clean();
		echo $breadcrumbs; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Update cart total on button and order review mobile sction.
	 *
	 * @param string $fragments shipping message.
	 *
	 * @return array|string $fragments updated Woo fragments.
	 */
	public function instant_checkout_order_review( $fragments ) {

		$checkout_id = _get_wcf_checkout_id();

		if ( ! $checkout_id ) {

			$checkout_id = isset( $_GET['wcf_checkout_id'] ) && ! empty( $_GET['wcf_checkout_id'] ) ? intval( wp_unslash( $_GET['wcf_checkout_id'] ) ) : 0; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		if ( empty( $checkout_id ) ) {
			return $fragments;
		}

		ob_start();

		$this->custom_order_review_template();
		$wcf_instant_checkout_order_review = ob_get_clean();

		$fragments['.wcf-instant-checkout-wrapper .woocommerce-checkout-review-order-table'] = $wcf_instant_checkout_order_review;
		$fragments['.wcf-order-review-total'] = "<div class='wcf-order-review-total'>" . WC()->cart->get_total() . '</div>';
		return $fragments;
	}

	/**
	 * Add order review template path for multistep checkout.
	 *
	 * @param string $path template path.
	 * @param string $checkout_layout layout type.
	 * @return string
	 */
	public function update_path_for_order_review_template( $path, $checkout_layout ) {

		return CARTFLOWS_CHECKOUT_DIR . 'templates/checkout/instant-checkout-review-order.php';

	}

	/**
	 * Add product quantity count badge in image.
	 *
	 * @param string $image Image.
	 * @param string $thumbnail Thumbnail.
	 * @param string $remove_label Removal label.
	 * @param array  $cart_item Cart array.
	 * @param int    $checkout_id  checkout id.
	 * @return string
	 */
	public function add_product_quantity_badge( $image, $thumbnail, $remove_label, $cart_item, $checkout_id ) {

		$flow_id = wcf()->utils->is_step_post_type() ? wcf()->utils->get_flow_id() : 0;

		if ( ! empty( $checkout_id ) ) {

			$flow_id = empty( $flow_id ) ? get_post_meta( $checkout_id, 'wcf-flow-id', true ) : $flow_id;

			// Return if no flow ID is found.
			if ( empty( $flow_id ) ) {
				return $image;
			}

			if ( class_exists( 'Cartflows_Helper' ) && ! Cartflows_Helper::is_instant_layout_enabled( (int) $flow_id ) ) {
				return $image;
			}

			$product_qty = isset( $cart_item['quantity'] ) ? $cart_item['quantity'] : 0;
			$image       = '<div class="wcf-product-thumbnail cf-ic-product-thumbnail-batch" >' . $thumbnail . '<span class="instant-checkout-batch">' . $product_qty . '</span>' . $remove_label . ' </div>';
		}
		return $image;
	}

	/**
	 * Update shipping options text.
	 *
	 * @param string $text text.
	 * @return string
	 */
	public function update_shipping_options_text( $text ) {
		$checkout_id = _get_wcf_checkout_id();
		if ( $checkout_id ) {

			$wcf_billing_details_text = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-instant-checkout-shipping-options-text' );

			if ( ! empty( $wcf_billing_details_text ) ) {
				$text = $wcf_billing_details_text;
			}
		}

		return $text;

	}

	/**
	 * Adding editor settings.
	 *
	 * @param array $custom_fields Custom fields.
	 * @return array
	 */
	public function add_editor_settings( $custom_fields ) {

		$custom_fields['checkout_settings']['fields']['wcf-instant-checkout-shipping-options'] = array(
			'type'          => 'text',
			'label'         => __( 'Shipping', 'cartflows' ),
			'name'          => 'wcf-instant-checkout-shipping-options-text',
			'placeholder'   => __( 'Shipping', 'cartflows' ),
			'tooltip'       => __( 'This heading will be displayed only on Instant checkout.', 'cartflows' ),
			'display_align' => 'vertical',
		);

		return $custom_fields;
	}

	/**
	 * Off other checkout skin values if cartflows_checkout_layout is enabled.
	 *
	 * @param string $value String.
	 * @return string|bool
	 */
	public function stop_other_checkout_layout_implementations( $value ) {

		$flow_id = wcf()->utils->is_step_post_type() ? wcf()->utils->get_flow_id() : 0;

		if ( empty( $flow_id ) || ! class_exists( 'Cartflows_Helper' ) || ! Cartflows_Helper::is_instant_layout_enabled( (int) $flow_id ) ) {
			return $value;
		}

		return false;
	}

	/**
	 * Set instant checkout template type.
	 *
	 * @since 2.1.0
	 * @param string $template_type The current page template type.
	 * @return string $template_type The updated page template type.
	 */
	public function set_instant_checkout_template_type( $template_type ) {

		$template_type = 'instant-checkout';

		return $template_type;
	}

	/**
	 * Get selected shipping method.
	 *
	 * @return void
	 */
	public static function selected_shipping_method() {

		$packages = WC()->shipping()->get_packages();

		foreach ( $packages as $i => $package ) {
			$chosen_method = isset( WC()->session->chosen_shipping_methods[ $i ] ) ? WC()->session->chosen_shipping_methods[ $i ] : '';
			$product_names = array();

			if ( count( $packages ) > 1 ) {
				foreach ( $package['contents'] as $item_id => $values ) {
					$product_names[ $item_id ] = $values['data']->get_name() . ' &times;' . $values['quantity'];
				}
				$product_names = apply_filters( 'woocommerce_shipping_package_details_array', $product_names, $package );
			}
			$available_methods    = $package['rates'];
			$show_package_details = count( $packages ) > 1;
			$package_details      = implode( ', ', $product_names );
			/* translators: %d: shipping package number */
			$package_name            = apply_filters( 'woocommerce_shipping_package_name', ( ( $i + 1 ) > 1 ) ? sprintf( _x( 'Shipping %d', 'shipping packages', 'cartflows' ), ( $i + 1 ) ) : _x( 'Shipping', 'shipping packages', 'cartflows' ), $i, $package );
			$index                   = $i;
			$formatted_destination   = WC()->countries->get_formatted_address( $package['destination'], ', ' );
			$has_calculated_shipping = WC()->customer->has_calculated_shipping();



			$formatted_destination    = ! empty( $formatted_destination ) ? $formatted_destination : WC()->countries->get_formatted_address( $package['destination'], ', ' );
			$has_calculated_shipping  = ! empty( $has_calculated_shipping );
			$show_shipping_calculator = ! empty( $show_shipping_calculator );
			$calculator_text          = '';
			if ( ! empty( $available_methods ) && is_array( $available_methods ) ) :
				foreach ( $available_methods as $method ) :
					if ( 1 < count( $available_methods ) ) {
						if ( ! empty( checked( $method->id, $chosen_method, false ) ) ) {
							printf( '<label for="shipping_method_%1$s_%2$s">%3$s</label>', $index, esc_attr( sanitize_title( $method->id ) ), wc_cart_totals_shipping_method_label( $method ) ); //phpcs:ignore
						}
					} else {
						printf( '<label for="shipping_method_%1$s_%2$s">%3$s</label>', $index, esc_attr( sanitize_title( $method->id ) ), wc_cart_totals_shipping_method_label( $method ) ); //phpcs:ignore
					}

						do_action( 'woocommerce_after_shipping_rate', $method, $index );

				endforeach;

			elseif ( ! $has_calculated_shipping || ! $formatted_destination ) :
				if ( is_cart() && 'no' === get_option( 'woocommerce_enable_shipping_calc' ) ) {
					echo wp_kses_post( apply_filters( 'woocommerce_shipping_not_enabled_on_cart_html', __( 'Shipping costs are calculated during checkout.', 'cartflows' ) ) );
				} else {
					echo wp_kses_post( apply_filters( 'woocommerce_shipping_may_be_available_html', __( 'Enter your address to view shipping options.', 'cartflows' ) ) );
				}
			elseif ( ! is_cart() ) :
				echo wp_kses_post( apply_filters( 'woocommerce_no_shipping_available_html', __( 'There are no shipping options available. Please ensure that your address has been entered correctly, or contact us if you need any help.', 'cartflows' ) ) );
			else :
				echo wp_kses_post(
					/**
					 * Provides a means of overriding the default 'no shipping available' HTML string.
					 *
					 * @since 3.0.0
					 *
					 * @param string $html                  HTML message.
					 * @param string $formatted_destination The formatted shipping destination.
					 */
					apply_filters(
						'woocommerce_cart_no_shipping_available_html',
						// Translators: $s shipping destination.
						sprintf( esc_html__( 'No shipping options were found for %s.', 'cartflows' ) . ' ', '<strong>' . esc_html( $formatted_destination ) . '</strong>' ),
						$formatted_destination
					)
				);
				$calculator_text = esc_html__( 'Enter a different address', 'cartflows' );
			endif;
			?>
			<?php if ( $show_package_details ) : ?>
				<?php echo '<p class="woocommerce-shipping-contents"><small>' . esc_html( $package_details ) . '</small></p>'; ?>
			<?php endif; ?>

			<?php if ( $show_shipping_calculator ) : ?>
				<?php woocommerce_shipping_calculator( $calculator_text ); ?>
				<?php
			endif;
		}

	}

	/**
	 * Add custom billing email field.
	 *
	 * @return void
	 */
	public function add_custom_billing_email_field() {
		$flow_id = wcf()->utils->is_step_post_type() ? wcf()->utils->get_flow_id() : 0;

		if ( empty( $flow_id ) || ! class_exists( 'Cartflows_Helper' ) || ! Cartflows_Helper::is_instant_layout_enabled( (int) $flow_id ) ) {
			return;
		}

		$default = '';

		if ( function_exists( 'is_auto_prefill_checkout_fields_enabled' ) && is_auto_prefill_checkout_fields_enabled() && ( isset( $_GET['billing_email'] ) && ! empty( $_GET['billing_email'] ) ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$default = sanitize_email( wp_unslash( $_GET['billing_email'] ) ); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		$lost_password_url  = esc_url( wp_lostpassword_url() );
		$current_user_name  = wp_get_current_user()->display_name;
		$current_user_email = wp_get_current_user()->user_email;
		$is_allow_login     = 'yes' === get_option( 'woocommerce_enable_checkout_login_reminder' );
		$required_mark      = '&#42;';

		?>
		<div class="wcf-customer-info" id="customer_info">
			<div class="wcf-customer-info__notice"></div>
			<div class="woocommerce-billing-fields-custom">
				<h3 id="customer_information_heading"><?php echo esc_html( apply_filters( 'cartflows_woo_customer_info_text', __( 'Contact', 'cartflows' ) ) ); ?>
					<?php if ( ! is_user_logged_in() && $is_allow_login ) { ?>
						<div class="woocommerce-billing-fields__customer-login-label"><?php /* translators: %1$s: Link HTML start, %2$s Link HTML End */ echo wp_kses_post( sprintf( __( '%1$1s Log in%2$2s', 'cartflows' ), '<a href="#!" class="wcf-customer-login-url">', '</a>' ) ); ?></div>
					<?php } ?>
				</h3>
				<div class="woocommerce-billing-fields__customer-info-wrapper">
				<?php
				if ( ! is_user_logged_in() ) {

						woocommerce_form_field(
							'billing_email',
							array(
								'type'         => 'email',
								'class'        => array( 'form-row-fill' ),
								'required'     => true,
								'label'        => __( 'Email Address', 'cartflows' ),
								'default'      => ! empty( $default ) ? $default : $current_user_email, // Default is false. Show the email ID received via URL parameter OR if show the current user email if he is logged in.
								'placeholder'  => __( 'Email Address', 'cartflows' ),
								'autocomplete' => 'email username',
							)
						);

					if ( 'yes' === get_option( 'woocommerce_enable_checkout_login_reminder' ) ) {
						?>
							<div class="wcf-customer-login-section">
							<?php
								woocommerce_form_field(
									'billing_password',
									array(
										'type'        => 'password',
										'class'       => array( 'form-row-fill', 'wcf-password-field' ),
										'required'    => true,
										'label'       => __( 'Password', 'cartflows' ),
										/* translators: %s: asterisk mark */
										'placeholder' => sprintf( __( 'Password %s', 'cartflows' ), $required_mark ),
									)
								);
							?>
							<div class="wcf-customer-login-actions">
						<?php
								echo "<input type='button' name='wcf-customer-login-btn' class='button wcf-customer-login-section__login-button' id='wcf-customer-login-section__login-button' value='" . esc_attr( __( 'Login', 'cartflows' ) ) . "'>";
								echo '<a href=' . esc_url( $lost_password_url ) . " class='wcf-customer-login-lost-password-url'>" . esc_html( __( 'Lost your password?', 'cartflows' ) ) . '</a>';
						?>
							</div>
						<?php
						if ( 'yes' === get_option( 'woocommerce_enable_guest_checkout', false ) ) {
							echo "<p class='wcf-login-section-message'>" . esc_html( __( 'Login is optional, you can continue with your order below.', 'cartflows' ) ) . '</p>';
						}
						?>
							</div>
					<?php } ?>
					<?php
					if ( 'yes' === get_option( 'woocommerce_enable_signup_and_login_from_checkout' ) ) {
						?>
							<div class="wcf-create-account-section" hidden>
							<?php if ( 'yes' === get_option( 'woocommerce_enable_guest_checkout' ) ) { ?>
									<p class="form-row form-row-wide create-account">
										<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
											<input class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" id="createaccount" type="checkbox" name="createaccount" value="1" /> <span><?php esc_html_e( 'Create an account?', 'cartflows' ); ?></span>
										</label>
									</p>
								<?php } ?>
								<div class="create-account">
								<?php

								if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) {
									woocommerce_form_field(
										'account_username',
										array(
											'type'        => 'text',
											'class'       => array( 'form-row-fill' ),
											'id'          => 'account_username',
											'required'    => true,
											'label'       => __( 'Account username', 'cartflows' ),
											/* translators: %s: asterisk mark */
											'placeholder' => sprintf( __( 'Account username %s', 'cartflows' ), $required_mark ),
										)
									);
								}
								if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) {
									woocommerce_form_field(
										'account_password',
										array(
											'type'        => 'password',
											'id'          => 'account_password',
											'class'       => array( 'form-row-fill' ),
											'required'    => true,
											'label'       => __( 'Create account password', 'cartflows' ),
											/* translators: %s: asterisk mark */
											'placeholder' => sprintf( __( 'Create account password %s', 'cartflows' ), $required_mark ),
										)
									);
								}
								?>
								</div>
							</div>
					<?php } ?>
				<?php } else { ?>
							<div class="wcf-logged-in-customer-info"> <?php /* translators: %1$s: username, %2$s emailid */ echo esc_html( apply_filters( 'cartflows_logged_in_customer_info_text', sprintf( __( ' Welcome Back %1$s ( %2$s )', 'cartflows' ), $current_user_name, $current_user_email ) ) ); ?>
								<div><input type="hidden" class="wcf-email-address" id="billing_email" name="billing_email" value="<?php echo esc_attr( $current_user_email ); ?>"/></div>
							</div>
				<?php } ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Prefill the checkout fields if available in url.
	 *
	 * @param array $checkout_fields checkout fields array.
	 */
	public function unset_fields_for_instant_checkout( $checkout_fields ) {


		$checkout_id = _get_wcf_checkout_id();

		if ( ! $checkout_id ) {
			$checkout_id = wcf()->utils->get_checkout_id_from_post_data();
		}

		$flow_id = (int) wcf()->utils->get_flow_id_from_step_id( $checkout_id );

		if ( ! _is_wcf_checkout_type() || empty( $flow_id ) || ! Cartflows_Helper::is_instant_layout_enabled( (int) $flow_id ) ) {
			return $checkout_fields;
		}

		// No nonce verification required as it is called on woocommerce action.
		if ( wp_doing_ajax() || ( isset( $_GET['wc-ajax'] ) && 'checkout' === $_GET['wc-ajax'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return $checkout_fields;
		}

		// Unset defalut billing email from Billing Details.
		unset( $checkout_fields['billing']['billing_company'] );
		unset( $checkout_fields['billing']['billing_email'] );
		unset( $checkout_fields['account']['account_username'] );
		unset( $checkout_fields['account']['account_password'] );

		return $checkout_fields;
	}

	/**
	 * Add Customer Information Section.
	 *
	 * @param int $checkout_id checkout ID.
	 *
	 * @return void
	 */
	public function instant_checkout_wrapper_start( $checkout_id ) {

		$position_class = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-review-summary-position' );

		if ( 'top' === $position_class ) {
			include CARTFLOWS_CHECKOUT_DIR . 'templates/checkout/collapsed-order-summary.php';
		}

		if ( ! $checkout_id ) {
			$checkout_id = _get_wcf_checkout_id();
		}

		do_action( 'cartflows_checkout_before_instant_checkout_layout', $checkout_id );

		echo '<div class="wcf-instant-checkout-wrapper">';

		do_action( 'cartflows_instant_checkout_before_main_wrapper' );

		echo '<div class="wcf-customer-info-main-wrapper">';

		do_action( 'cartflows_instant_checkout_main_wrapper' );

		echo '<div class="wcf-ic-layout-left-column">';

		// Print all the notices as we have changed the display location.
		if ( null != WC()->session && function_exists( 'woocommerce_output_all_notices' ) ) {
			woocommerce_output_all_notices();
		}

	}

	/**
	 * Add Customer Information Section.
	 *
	 * @param int $checkout_id checkout ID.
	 *
	 * @return void
	 */
	public function order_review_wrapper_start( $checkout_id ) {
		do_action( 'cartflows_checkout_after_instant_shipping' );

		echo '</div></div> <div class="wcf-ic-layout-right-column">';
	}

	/**
	 * Add custom shipping method secition.
	 *
	 * @return void
	 */
	public function add_shipping_section() {
		ob_start();
		if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) :
			?>
			<div class="wcf-shipping-option-heading">
				<h3 id="wcf_shipping_options_heading"><?php echo esc_html( apply_filters( 'cartflows_woo_shipping_options_text', __( 'Shipping', 'cartflows' ) ) ); ?></h3>
			</div>
		<?php endif; ?>
		<div id="wcf-ic-shipping" class="wcf-instant-checkout-shipping-method woocommerce-checkout-shipping">
			<?php
				do_action( 'woocommerce_review_order_before_shipping' );
				wc_cart_totals_shipping_html();
				do_action( 'woocommerce_review_order_after_shipping' );
				do_action( 'woocommerce_after_shipping_rate' );
			?>
			</div>
		<?php
		$custom_shipping_section = ob_get_clean();
		echo $custom_shipping_section; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		ob_end_flush();
	}

	/**
	 * Display Payment heading field after coupon code fields.
	 *
	 * @return void
	 */
	public function display_custom_payment_heading() {

		ob_start();
		?>
		<div class="wcf-payment-option-heading">
			<h3 id="payment_options_heading"><?php echo wp_kses_post( apply_filters( 'cartflows_woo_payment_text', esc_html__( 'Payment', 'cartflows' ) ) ); ?></h3>
		</div>
		<?php
		$custom_payment_heading = ob_get_clean();
		echo $custom_payment_heading; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		ob_end_flush();
	}

	/**
	 * Change HTML for shipping packcge name .
	 *
	 * @param string $text package name.
	 * @return string
	 */
	public function add_span_tag_in_shipping_package_name( $text ) {
		$text = '<span class="wcf-ic-shipping-package-name">' . $text . '</span>';
		return $text;
	}

	/**
	 * Order summary.
	 *
	 * @param int $checkout_id checkout ID.
	 *
	 * @return void
	 */
	public function show_collapsed_order_summary( $checkout_id ) {
		ob_start();
		?>
		</div>
		<?php
		$position_class = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-review-summary-position' );
		if ( 'bottom' === $position_class ) {
			include CARTFLOWS_CHECKOUT_DIR . 'templates/checkout/collapsed-order-summary.php';
		}
		?>
		<?php
		$order_summary = ob_get_contents();
		ob_end_clean();
		echo $order_summary; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Get WooCommerce Cart URL.
	 *
	 * @since 2.1.0
	 * @return string
	 */
	public function get_instant_checkout_cart_url() {
		return apply_filters( 'cartflows_instant_checkout_cart_url', wc_get_cart_url() );
	}

	/**
	 * Prepare Instant Checkout Header Template.
	 *
	 * @since 2.1.0
	 * @return void
	 */
	public function instant_checkout_header_template() {
		ob_start();
		include CARTFLOWS_CHECKOUT_DIR . 'templates/checkout/page-template/wcf-ic-header-template.php';
		$header = ob_get_clean();
		echo wp_kses_post( $header );
	}

	/**
	 * Remove the default empty cart message only for Instant Checkout Layout.
	 *
	 * This method is used to remove the default message displayed by WooCommerce
	 * or the theme when the cart is empty. It ensures that the default message
	 * is replaced by a custom-designed message or block, typically added via
	 * another method like `add_empty_cart_designed_block()`.

	 * @since 2.1.0
	 * @param string $message The Default Cart Empty Message.
	 * @return string $message Updated empty cart message.
	 */
	public function remove_default_empty_cart_message( $message ) {
		$message = '';

		return $message;
	}

	/**
	 * Add empty block HTML for empty cart.
	 *
	 * @since 2.1.0
	 * @param int $checkout_id The current step ID.
	 * @return void
	 */
	public function add_empty_cart_designed_block( $checkout_id ) {

		$message = __( 'Looks like you haven\'t added any items to cart yet — start shopping to fill it up!', 'cartflows' );

		$shop_url = wc_get_page_permalink( 'shop' );

		?>
		<div class="wcf-empty-cart-notice-block">
			<div class="wcf-empty-cart-message-container">
				<div class="wcf-empty-cart-wrapper">
					<div class="wcf-empty-cart-icon">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon" class=""><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"></path></svg>
					</div>
					<div class="wcf-empty-cart-content">
						<h2 class="wcf-empty-cart-heading"><?php echo esc_html( apply_filters( 'cartflows_checkout_empty_cart_heading', __( 'Your Cart is Currently Empty.', 'cartflows' ) ) ); ?></h2>
						<p class="wcf-empty-cart-message"><?php echo esc_html( apply_filters( 'cartflows_checkout_empty_cart_message', $message ) ); ?></p>
						<a href="<?php echo esc_url( $shop_url ); ?>" class="wcf-empty-cart-button"><?php echo esc_html( apply_filters( 'cartflows_checkout_empty_cart_button_text', esc_html_e( 'Start Shopping', 'cartflows' ) ) ); ?></a>
					</div>
				</div>
			</div>
		</div>
		<?php

	}

}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Instant_Checkout::get_instance();

