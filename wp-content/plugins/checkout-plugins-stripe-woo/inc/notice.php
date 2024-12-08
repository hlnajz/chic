<?php
/**
 * Notice helper.
 *
 * @package checkout-plugins-stripe-woo
 *
 * @since 1.2.0
 */

namespace CPSW\Inc;

use CPSW\Inc\Traits\Get_Instance;

/**
 * Class that represents admin notices.
 *
 * @since 1.2.0
 */
class Notice {

	use Get_Instance;

	/**
	 * Notices (array)
	 *
	 * @var array
	 */
	public static $notices = [];

	/**
	 * Constructor
	 *
	 * @since 1.2.0
	 */
	public function __construct() {
		add_action( 'admin_notices', [ $this, 'get_notices' ] );
		add_action( 'wp_loaded', [ $this, 'hide' ] );
		add_action( 'wp_ajax_dismiss_cpsw_notice', [ $this, 'dismiss_admin_notice' ] );
	}

	/**
	 * Allow this class and other classes to add slug keyed notices (to avoid duplication).
	 *
	 * @since 1.2.0
	 *
	 * @param string $slug Class slug.
	 * @param string $class CSS class name.
	 * @param string $message Notice message.
	 * @param string $dismissible Dismissible icon.
	 *
	 * @return void
	 */
	public static function add( $slug, $class, $message, $dismissible = false ) {
		self::$notices[ $slug ] = apply_filters(
			'cpsw_notices_add_args',
			[
				'class'       => $class,
				'message'     => $message,
				'dismissible' => $dismissible,
				'custom'      => false,
			]
		);
	}

	/**
	 * Display any notices we've collected thus far.
	 *
	 * @since 1.2.0
	 *
	 * @return void
	 */
	public function get_notices() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		foreach ( (array) self::$notices as $notice_key => $notice ) {
			// Check if the notice is custom.
			if ( $notice['custom'] ) {
				// Check if the notice should be shown based on the specific option added on db while dismissing notice.
				if ( 'no' === get_option( 'cpsw_show_' . $notice_key . '_notice' ) ) {
					// skip to the next iteration.
					continue;
				}

				$transient_status = get_transient( 'cpsw_notice_' . $notice['id'] . '_delay_timing' );

				// Check if the transient exists.
				if ( $transient_status ) {
					// Skip showing the notice since the transient exists (delay time not passed).
					continue;
				}

				$classes = '';

				// Check if the notice is dismissible.
				if ( $notice['dismissible'] ) {
					// Add dismissible classes.
					$classes = ' cpsw-dismissible-notice is-dismissible';
				}

				// Output the custom notice HTML.
				echo '<div id="' . esc_attr( $notice_key ) . '" class="' . esc_attr( $notice['class'] ) . esc_attr( $classes ) . ' cpsw-notice cpsw-custom-notice notice">' . wp_kses_post( $notice['html'] ) . '</div>';
			} else {
				echo '<div class="' . esc_attr( $notice['class'] ) . '" style="position:relative;">';

				if ( $notice['dismissible'] ) {
					?>
					<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'cpsw-stripe-hide-notice', $notice_key ), 'cpsw_stripe_hide_notices_nonce', '_cpsw_stripe_notice_nonce' ) ); ?>" class="woocommerce-message-close notice-dismiss" style="position:relative;float:right;padding:9px 0px 9px 9px 9px;text-decoration:none;"></a>
					<?php
				}

				echo '<p>';
				echo wp_kses(
					$notice['message'],
					[
						'a' => [
							'href'   => [],
							'target' => [],
						],
					]
				);
				echo '</p></div>';
			}
		}
	}

	/**
	 * Hides any notice.
	 *
	 * @since 1.2.0
	 *
	 * @return void
	 */
	public function hide() {
		if (
			! isset( $_GET['_cpsw_stripe_notice_nonce'] )
			|| ! wp_verify_nonce( sanitize_text_field( $_GET['_cpsw_stripe_notice_nonce'] ), 'cpsw_stripe_hide_notices_nonce' )
		) {
			return;
		}

		$notice = isset( $_GET['cpsw-stripe-hide-notice'] ) ? wc_clean( wp_unslash( $_GET['cpsw-stripe-hide-notice'] ) ) : '';

		update_option( 'cpsw_show_' . $notice . '_notice', 'no' );
	}

	/**
	 * Check current page is cpsw setting page.
	 *
	 * @since 1.2.0
	 *
	 * @param string $section gateway section.
	 *
	 * @return boolean
	 */
	public static function is_cpsw_section( $section ) {
		// This function just determines scope of admin notice, Nonce verification may not be required.
		if ( isset( $_GET['page'] ) && 'wc-settings' === sanitize_text_field( $_GET['page'] ) && isset( $_GET['tab'] ) && isset( $_GET['section'] ) && sanitize_text_field( $_GET['section'] ) === $section ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return true;
		}

		return false;
	}

	/**
	 * Function to add notices with custom html.
	 *
	 * @since 1.10.0
	 *
	 * @param string $id The unique ID of the notice.
	 * @param string $class The CSS class to apply to the notice.
	 * @param string $html The custom HTML content of the notice.
	 * @param bool   $dismissible Whether the notice can be dismissed by the user. Default is false.
	 * @param string $delay Notice delay time in seconds. Default is false.
	 *
	 * @return void
	 */
	public static function add_custom( $id, $class, $html, $dismissible = false, $delay = false ) {
		self::$notices[ $id ] = apply_filters(
			'cpsw_notices_add_args',
			[
				'class'       => $class,
				'id'          => $id,
				'html'        => $html,
				'dismissible' => $dismissible,
				'custom'      => true,
			]
		);

		// Set the transient for the notice delay time if not already set.
		if ( $delay ) {
			$delay_timing = get_transient( 'cpsw_notice_' . $id . '_delay_timing' );
			$is_dismissed = get_option( 'cpsw_show_' . $id . '_notice' );

			if ( false === $delay_timing && 'no' !== $is_dismissed && 'delayed' !== $is_dismissed ) {
				set_transient( 'cpsw_notice_' . $id . '_delay_timing', true, $delay );
				update_option( 'cpsw_show_' . $id . '_notice', 'delayed' );
			}
		}
	}

	/**
	 * Ajax callback function triggered when a notice is dismissed.
	 *
	 * @since 1.10.0
	 *
	 * @return void
	 */
	public function dismiss_admin_notice() {
		if ( ! is_admin() ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! isset( $_POST['_security'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['_security'] ), 'cpsw_notice_nonce' ) ) {
			wp_send_json_error( [ 'message' => __( 'Invalid Nonce', 'checkout-plugins-stripe-woo' ) ] );
			return;
		}

		// Get the notice ID.
		$notice_id = isset( $_POST['notice_id'] ) ? sanitize_key( $_POST['notice_id'] ) : '';

		// Get the notice duration to repeat after.
		$notice_duration = isset( $_POST['duration'] ) ? absint( $_POST['duration'] ) : '';

		if ( $notice_id && $notice_duration ) {
			// Update an option to set repeat time for a notice.
			set_transient( 'cpsw_notice_' . $notice_id . '_delay_timing', true, $notice_duration );
		} elseif ( $notice_id ) {
			// Update an option to mark notice as dismissed.
			update_option( 'cpsw_show_' . $notice_id . '_notice', 'no' );
		}

		// Success response.
		wp_send_json_success( null, 200 );
	}

}
