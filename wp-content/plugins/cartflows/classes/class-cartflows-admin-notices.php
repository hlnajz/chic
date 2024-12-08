<?php
/**
 * CartFlows Admin Notices.
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Class Cartflows_Admin_Notices.
 */
class Cartflows_Admin_Notices {

	/**
	 * Instance
	 *
	 * @access private
	 * @var object Class object.
	 * @since 1.0.0
	 */
	private static $instance;

	/**
	 * Initiator
	 *
	 * @since 1.0.0
	 * @return object initialized object of class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {

		add_action( 'admin_head', array( $this, 'show_admin_notices' ) );

		add_action( 'admin_footer', array( $this, 'show_nps_notice' ), 999 );

		add_action( 'admin_enqueue_scripts', array( $this, 'notices_scripts' ) );

		add_action( 'wp_ajax_cartflows_ignore_gutenberg_notice', array( $this, 'ignore_gb_notice' ) );

		add_action( 'wp_ajax_cartflows_disable_weekly_report_email_notice', array( $this, 'disable_weekly_report_email_notice' ) );

		add_filter( 'woo_ca_plugin_review_url', array( $this, 'update_review_link' ), 10, 1 );
	}

	/**
	 * Update review link for cart abandonment.
	 *
	 * @param string $review_link review link.
	 *
	 * @return string URL.
	 */
	public function update_review_link( $review_link ) {

		return 'https://wordpress.org/support/plugin/cartflows/reviews/?filter=5#new-post';
	}


	/**
	 * Show the weekly email Notice
	 *
	 * @return void
	 */
	public function show_weekly_report_email_settings_notice() {

		if ( ! $this->allowed_screen_for_notices() ) {
			return;
		}

		$is_show_notice = get_option( 'cartflows_show_weekly_report_email_notice', 'no' );

		if ( 'yes' === $is_show_notice && current_user_can( 'manage_options' ) ) {

			$setting_url = admin_url( 'admin.php?page=cartflows&path=settings#other_settings' );

			/* translators: %1$s Software Title, %2$s Plugin, %3$s Anchor opening tag, %4$s Anchor closing tag, %5$s Software Title. */
			$message = sprintf( __( '%1$sCartFlows:%2$s We just introduced an awesome new feature, weekly store revenue reports via email. Now you can see how many revenue we are generating for your store each week, without having to log into your website. You can set the email address for these email from %3$shere.%4$s', 'cartflows' ), '<strong>', '</strong>', '<a class="wcf-redirect-to-settings" target="_blank" href=" ' . esc_url( $setting_url ) . ' ">', '</a>' );
			$output  = '<div class="wcf-notice weekly-report-email-notice wcf-dismissible-notice notice notice-info is-dismissible">';
			$output .= '<p>' . $message . '</p>';
			$output .= '</div>';

			echo wp_kses_post( $output );
		}

	}

	/**
	 * Disable the weekly email Notice
	 *
	 * @return void
	 */
	public function disable_weekly_report_email_notice() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		check_ajax_referer( 'cartflows-disable-weekly-report-email-notice', 'security' );
		delete_option( 'cartflows_show_weekly_report_email_notice' );
		wp_send_json_success();
	}

	/**
	 *  After save of permalinks.
	 */
	public function notices_scripts() {

		if ( ! $this->allowed_screen_for_notices() || ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			return;
		}

		wp_enqueue_style( 'cartflows-custom-notices', CARTFLOWS_URL . 'admin/assets/css/notices.css', array(), CARTFLOWS_VER );

		wp_enqueue_script( 'cartflows-notices', CARTFLOWS_URL . 'admin/assets/js/ui-notice.js', array( 'jquery' ), CARTFLOWS_VER, true );

		$localize_vars = array(
			'ignore_gb_notice'                   => wp_create_nonce( 'cartflows-ignore-gutenberg-notice' ),
			'dismiss_weekly_report_email_notice' => wp_create_nonce( 'cartflows-disable-weekly-report-email-notice' ),
		);

		wp_localize_script( 'cartflows-notices', 'cartflows_notices', $localize_vars );
	}

	/**
	 *  After save of permalinks.
	 */
	public function show_admin_notices() {

		if ( ! $this->allowed_screen_for_notices() || ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			return;
		}

		global  $wp_version;

		if ( version_compare( $wp_version, '5.0', '>=' ) && is_plugin_active( 'gutenberg/gutenberg.php' ) ) {
			add_action( 'admin_notices', array( $this, 'gutenberg_plugin_deactivate_notice' ) );
		}

		add_action( 'admin_notices', array( $this, 'show_weekly_report_email_settings_notice' ) );

	}

	/**
	 * Render CartFlows NPS Survey Notice.
	 *
	 * @since x.x.x
	 * @return void
	 */
	public function show_nps_notice() {

		Nps_Survey::show_nps_notice(
			'nps-survey-cartflows',
			array(
				'show_if'          => $this->should_display_nps_survey_notice(),
				'dismiss_timespan' => 2 * WEEK_IN_SECONDS,
				'display_after'    => 0,
				'plugin_slug'      => 'cartflows',
				'message'          => array(

					// Step 1 i.e rating input.
					'logo'                  => esc_url( CARTFLOWS_URL . 'admin-core/assets/images/cartflows-icon.svg' ),
					'plugin_name'           => __( 'CartFlows', 'cartflows' ),
					'nps_rating_message'    => __( 'How likely are you to recommend #pluginname to your friends or colleagues?', 'cartflows' ),

					// Step 2A i.e. positive.
					'feedback_content'      => __( 'Could you please do us a favor and give us a 5-star rating on WordPress? It would help others choose CartFlows with confidence. Thank you!', 'cartflows' ),
					'plugin_rating_link'    => esc_url( 'https://wordpress.org/support/plugin/cartflows/reviews/#new-post' ),

					// Step 2B i.e. negative.
					'plugin_rating_title'   => __( 'Thank you for your feedback', 'cartflows' ),
					'plugin_rating_content' => __( 'We value your input. How can we improve your experience?', 'cartflows' ),
				),
			)
		);
	}

	/**
	 * Show Deactivate gutenberg plugin notice.
	 *
	 * @since 1.1.19
	 *
	 * @return void
	 */
	public function gutenberg_plugin_deactivate_notice() {

		$ignore_notice = get_option( 'wcf_ignore_gutenberg_notice', false );

		if ( 'yes' !== $ignore_notice ) {
			printf(
				'<div class="notice notice-error wcf_notice_gutenberg_plugin is-dismissible"><p>%s</p>%s</div>',
				wp_kses_post(
					sprintf(
					/* translators: %1$s: HTML, %2$s: HTML */
						__( 'Heads up! The Gutenberg plugin is not recommended on production sites as it may contain non-final features that cause compatibility issues with CartFlows and other plugins. %1$s Please deactivate the Gutenberg plugin %2$s to ensure the proper functioning of your website.', 'cartflows' ),
						'<strong>',
						'</strong>'
					)
				),
				''
			);
		}
	}

	/**
	 * Ignore admin notice.
	 */
	public function ignore_gb_notice() {

		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			return;
		}

		check_ajax_referer( 'cartflows-ignore-gutenberg-notice', 'security' );

		update_option( 'wcf_ignore_gutenberg_notice', 'yes' );
	}

	/**
	 * Check allowed screen for notices.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function allowed_screen_for_notices() {

		$screen          = get_current_screen();
		$screen_id       = $screen ? $screen->id : '';
		$allowed_screens = array(
			'toplevel_page_cartflows',
			'dashboard',
			'plugins',
		);

		if ( in_array( $screen_id, $allowed_screens, true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if the user has completed the onboarding, skipped the onboarding on ready step, and the store checkout is imported.
	 *
	 * @since x.x.x
	 * @return bool
	 */
	public function should_display_nps_survey_notice() {

		$is_store_checkout_imported = (bool) get_option( '_cartflows_wizard_store_checkout_set', false );   // Must be true.
		$onboarding_completed       = (bool) get_option( 'wcf_setup_complete', false );                     // Must be true.
		$is_first_funnel_imported   = (bool) get_option( 'wcf_first_flow_imported', false );                // Must be true.
		$total_funnels              = intval( wp_count_posts( CARTFLOWS_FLOW_POST_TYPE )->publish );        // Must be greater than or equal to 1.

		/**
		 * Show the notice in two conditions.
		 * 1. If completed the onboarding steps/process of plugin and sets their first store checkout funnel successfully.
		 * 2. If sets up the first funnel manually and makes it live.
		 */

		return ( $is_store_checkout_imported && $onboarding_completed ) || ( $is_first_funnel_imported && 1 >= $total_funnels ) || ( 1 >= $total_funnels );
	}
}

Cartflows_Admin_Notices::get_instance();
