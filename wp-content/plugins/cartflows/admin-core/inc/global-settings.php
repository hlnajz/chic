<?php
/**
 * CartFlows Global Data.
 *
 * @package CartFlows
 */

namespace CartflowsAdmin\AdminCore\Inc;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class flowMeta.
 */
class GlobalSettings {


	/**
	 * Get flow meta options.
	 */
	public static function get_global_settings_fields() {
		global $wp_roles;

		$origin = get_site_url();

		// Get all user roles.
		$all_roles_tmp = $wp_roles->roles;
		$all_roles     = array();

		foreach ( $all_roles_tmp as $role_name => $dispaly_name ) {
			if ( 'administrator' !== $role_name ) {
				$all_roles[ $role_name ] = array(
					'role_name' => $dispaly_name['name'],
					'fields'    => array(
						'roles-structure' => array(
							'type'          => 'select_card',
							'display_radio' => false,
							'name'          => '_cartflows_roles[' . $role_name . ']',
							'options'       => array(
								array(
									'value' => 'no_access',
									'label' => __( 'No Access', 'cartflows' ),
								),
								array(
									'value'   => 'access_to_cartflows',
									'label'   => __( 'Full Access', 'cartflows' ),
									'tooltip' => __( 'A full access to all settings.', 'cartflows' ),
								),
								array(
									'value'   => 'access_to_flows_and_step',
									'label'   => __( 'Limited Access', 'cartflows' ),
									'tooltip' => __( 'Can create/edit/delete/import flows and steps only.', 'cartflows' ),
								),
							),
						),
					),
				);
			}
		}

		$settings = array(
			'general'                => array(
				'title'  => '',
				'fields' => array(
					'page_builder'              => array(
						'type'    => 'select_card',
						'name'    => '_cartflows_common[default_page_builder]',
						'label'   => __( 'Show Ready Templates for', 'cartflows' ),
						/* translators: %1$1s: link html start, %2$12: link html end*/
						'desc'    => sprintf( __( 'Please choose your preferred page builder from the list so you will only see templates that are made using that page builder. %1$sLearn More >>%2$s', 'cartflows' ), '<a href="https://cartflows.com/docs/import-cartflows-templates-for-flows-steps/?utm_source=dashboard&utm_medium=free-cartflows&utm_campaign=docs" target="_blank">', '</a>' ),
						'options' => array(
							array(
								'value' => 'gutenberg',
								'label' => __( 'Block Editor', 'cartflows' ),
								'image' => esc_url_raw( CARTFLOWS_URL . 'admin-core/assets/images/page-builders/block-editor.png' ),
							),
							array(
								'value' => 'elementor',
								'label' => __( 'Elementor', 'cartflows' ),
								'image' => esc_url_raw( CARTFLOWS_URL . 'admin-core/assets/images/page-builders/elementor.svg' ),
							),
							array(
								'value' => 'bricks-builder',
								'label' => __( 'Bricks', 'cartflows' ),
								'image' => esc_url_raw( CARTFLOWS_URL . 'admin-core/assets/images/page-builders/bricks.png' ),
							),
							array(
								'value' => 'beaver-builder',
								'label' => __( 'Beaver', 'cartflows' ),
								'image' => esc_url_raw( CARTFLOWS_URL . 'admin-core/assets/images/page-builders/beaver-builder.svg' ),
							),
							array(
								'value' => 'other',
								'label' => __( 'Other', 'cartflows' ),
								'image' => esc_url_raw( CARTFLOWS_URL . 'admin-core/assets/images/page-builders/other.svg' ),
							),
						),

					),
					'page_builder_seperator'    => array(
						'type' => 'separator',
					),
					'override_global_checkout'  => array(
						'type'     => 'toggle',
						'backComp' => true,
						'name'     => '_cartflows_common[override_global_checkout]',
						'label'    => __( 'Override Store Checkout', 'cartflows' ),
						/* translators: %1$1s: link html start, %2$12: link html end*/
						'desc'     => sprintf( __( 'For more information about the Store Checkout settings please %1$sClick here%2$s.', 'cartflows' ), '<a href="https://cartflows.com/docs/global-checkout/?utm_source=dashboard&utm_medium=free-cartflows&utm_campaign=docs" target="_blank">', '</a>' ),
					),
					'global_checkout_seperator' => array(
						'type' => 'separator',
					),
					'search_engine'             => array(
						'type'     => 'toggle',
						'name'     => '_cartflows_common[disallow_indexing]',
						'label'    => __( 'Disallow search engine from indexing funnels.', 'cartflows' ),
						'backComp' => true,
						'desc'     => __( 'Prevent search engines from including funnels in their search results.', 'cartflows' ),
					),
					'search_engine_seperator'   => array(
						'type' => 'separator',
					),
				),
			),
			'permalink'              => array(
				'title'  => '',
				'fields' => array(
					'perma-structure' => array(
						'type'    => 'select_card',
						'layout'  => 'vertical',
						'name'    => '_cartflows_permalink[permalink_structure]',
						'options' => array(
							array(
								'value' => '',
								'label' => __( 'Default Permalinks', 'cartflows' ),
								'desc'  => __( 'Default WordPress Permalink', 'cartflows' ),
							),
							array(
								'value' => '/cartflows_flow/%flowname%/cartflows_step',
								'label' => __( 'Funnel and Step Slug', 'cartflows' ),
								'desc'  => $origin . '/' . CARTFLOWS_FLOW_PERMALINK_SLUG . '/%flowname%/' . CARTFLOWS_STEP_PERMALINK_SLUG . '/%stepname%/',
							),
							array(
								'value' => '/cartflows_flow/%flowname%',
								'label' => __( 'Funnel Slug', 'cartflows' ),
								'desc'  => $origin . '/' . CARTFLOWS_FLOW_PERMALINK_SLUG . '/%flowname%/%stepname%/',
							),
							array(
								'value' => '/%flowname%/cartflows_step',
								'label' => __( 'Step Slug', 'cartflows' ),
								'desc'  => $origin . '/%flowname%/' . CARTFLOWS_STEP_PERMALINK_SLUG . '/%stepname%/',
							),
						),
					),
					'perma-separator' => array(
						'type' => 'separator',
					),
					'perma-heading'   => array(
						'type'  => 'heading',
						'label' => __( 'Post Type Permalink Base', 'cartflows' ),
					),
					'perma-step-base' => array(
						'type'  => 'text',
						'label' => __( 'Step Base', 'cartflows' ),
						'name'  => '_cartflows_permalink[permalink]',
						'class' => 'input-field',
					),
					'perma-flow-base' => array(
						'type'  => 'text',
						'label' => __( 'Funnel Base', 'cartflows' ),
						'name'  => '_cartflows_permalink[permalink_flow_base]',
						'class' => 'input-field',
					),
				),
			),
			'facebook-pixel'         => array(
				'title'  => '',
				'fields' => array(
					'enable-fb-pixel'               => array(
						'type'     => 'toggle',
						'label'    => __( 'Enable For CartFlows Pages', 'cartflows' ),
						'name'     => '_cartflows_facebook[facebook_pixel_tracking]',
						'backComp' => true,
					),
					'fb-pixel-separator'            => array(
						'type'       => 'separator',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_facebook[facebook_pixel_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
					),
					'enable-fb-pixel-for-site'      => array(
						'type'       => 'toggle',
						'label'      => __( 'Enable for the whole site', 'cartflows' ),
						'name'       => '_cartflows_facebook[facebook_pixel_tracking_for_site]',
						'desc'       => __( 'If checked, page view and view content event will also be triggered for other pages/posts of site.', 'cartflows' ),
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_facebook[facebook_pixel_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
						'backComp'   => true,
					),
					'fb-pixel-for-site-separator'   => array(
						'type'       => 'separator',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_facebook[facebook_pixel_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
					),
					'pixel-id'                      => array(
						'type'       => 'text',
						'label'      => __( 'Enter Facebook pixel ID', 'cartflows' ),
						'name'       => '_cartflows_facebook[facebook_pixel_id]',
						'class'      => 'input-field',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_facebook[facebook_pixel_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
					),
					'pixel-id-separator'            => array(
						'type'       => 'separator',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_facebook[facebook_pixel_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
					),
					'pixel-event-heading'           => array(
						'type'       => 'heading',
						'label'      => __( 'Facebook Pixel Events', 'cartflows' ),
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_facebook[facebook_pixel_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
					),

					'pixel-event-view-content'      => array(
						'type'       => 'checkbox',
						'label'      => __( 'View Content', 'cartflows' ),
						'name'       => '_cartflows_facebook[facebook_pixel_view_content]',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_facebook[facebook_pixel_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
						'backComp'   => true,
					),

					'pixel-event-ini-checkout'      => array(
						'type'       => 'checkbox',
						'label'      => __( 'Initiate Checkout', 'cartflows' ),
						'name'       => '_cartflows_facebook[facebook_pixel_initiate_checkout]',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_facebook[facebook_pixel_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
						'backComp'   => true,
					),

					'pixel-event-payment-info'      => array(
						'type'       => 'checkbox',
						'label'      => __( 'Add Payment Info', 'cartflows' ),
						'name'       => '_cartflows_facebook[facebook_pixel_add_payment_info]',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_facebook[facebook_pixel_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
						'backComp'   => true,
					),

					'pixel-event-purchase-complete' => array(
						'type'       => 'checkbox',
						'label'      => __( 'Purchase Complete', 'cartflows' ),
						'name'       => '_cartflows_facebook[facebook_pixel_purchase_complete]',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_facebook[facebook_pixel_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
						'backComp'   => true,
					),

					'pixel-event-lead-info'         => array(
						'type'       => 'checkbox',
						'label'      => __( 'Optin Lead', 'cartflows' ),
						'name'       => '_cartflows_facebook[facebook_pixel_optin_lead]',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_facebook[facebook_pixel_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
						'backComp'   => true,
						'tooltip'    => __( 'Optin Lead event will be trigger for optin page.', 'cartflows' ),
					),

					'pixel-not-work-doc'            => array(
						'type'       => 'doc',
						'label'      => '',
						'name'       => 'pixel-not-work-doc',
						/* translators: %1$1s: link html start, %2$12: link html end*/
						'content'    => sprintf( __( 'Facebook Pixel not working correctly? %1$1s Click here %2$2s to know more.', 'cartflows' ), '<a href="https://cartflows.com/docs/facebook-pixel-support/?utm_source=dashboard&utm_medium=free-cartflows&utm_campaign=docs" target="_blank">', '</a>' ),
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_facebook[facebook_pixel_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
					),
				),
			),
			'ga-analytics'           => array(
				'title'  => '',
				'fields' => array(
					'enable-ga-analytics'             => array(
						'type'     => 'toggle',
						'label'    => __( 'Enable For CartFlows Pages', 'cartflows' ),
						'name'     => '_cartflows_google_analytics[enable_google_analytics]',
						'backComp' => true,
					),
					'ga-analytics-separator'          => array(
						'type'       => 'separator',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_google_analytics[enable_google_analytics]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
					),
					'enable-ga-analytics-for-site'    => array(
						'type'       => 'toggle',
						'label'      => __( 'Enable for the whole site', 'cartflows' ),
						'name'       => '_cartflows_google_analytics[enable_google_analytics_for_site]',
						'desc'       => __( 'If checked, page view event will also be triggered for other pages/posts of site.', 'cartflows' ),
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_google_analytics[enable_google_analytics]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
						'backComp'   => true,
					),
					'ga-analytics-for-site-separator' => array(
						'type'       => 'separator',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_google_analytics[enable_google_analytics]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
					),
					'ga-id'                           => array(
						'type'       => 'text',
						'label'      => __( 'Enter Google Analytics ID', 'cartflows' ),
						'name'       => '_cartflows_google_analytics[google_analytics_id]',
						'class'      => 'input-field',
						/* translators: %1$1s: link html start, %2$12: link html end*/
						'desc'       => sprintf( __( 'Log into your %1$1s google analytics account %2$2s to find your ID. e.g. G-XXXXX or UA-XXXXX-X', 'cartflows' ), '<a href="https://analytics.google.com/" target="_blank">', '</a>' ),
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_google_analytics[enable_google_analytics]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
					),
					'ga-id-separator'                 => array(
						'type'       => 'separator',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_google_analytics[enable_google_analytics]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
					),
					'ga-event-heading'                => array(
						'type'       => 'heading',
						'label'      => __( 'Google Analytics Events', 'cartflows' ),
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_google_analytics[enable_google_analytics]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
					),
					'ga-event-ini-checkout'           => array(
						'type'       => 'checkbox',
						'label'      => __( 'Begin Checkout', 'cartflows' ),
						'name'       => '_cartflows_google_analytics[enable_begin_checkout]',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_google_analytics[enable_google_analytics]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
						'backComp'   => true,
					),

					'ga-event-add-to-cart'            => array(
						'type'       => 'checkbox',
						'label'      => __( 'Add To Cart', 'cartflows' ),
						'name'       => '_cartflows_google_analytics[enable_add_to_cart]',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_google_analytics[enable_google_analytics]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
						'backComp'   => true,
					),
					'ga-event-payment-info'           => array(
						'type'       => 'checkbox',
						'label'      => __( 'Add Payment Info', 'cartflows' ),
						'name'       => '_cartflows_google_analytics[enable_add_payment_info]',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_google_analytics[enable_google_analytics]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
						'backComp'   => true,
					),
					'ga-event-purchase-complete'      => array(
						'type'       => 'checkbox',
						'label'      => __( 'Purchase', 'cartflows' ),
						'name'       => '_cartflows_google_analytics[enable_purchase_event]',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_google_analytics[enable_google_analytics]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
						'backComp'   => true,
					),

					'ga-event-lead-info'              => array(
						'type'       => 'checkbox',
						'label'      => __( 'Optin Lead', 'cartflows' ),
						'name'       => '_cartflows_google_analytics[enable_optin_lead]',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_google_analytics[enable_google_analytics]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
						'backComp'   => true,
						'tooltip'    => __( 'Optin Lead event will be trigger for optin page.', 'cartflows' ),
					),

					'ga-not-work-doc'                 => array(
						'type'       => 'doc',
						'label'      => '',
						'name'       => 'ga-not-work-doc',
						/* translators: %1$1s: link html start, %2$12: link html end*/
						'content'    => sprintf( __( 'Google Analytics not working correctly? %1$1s Click here %2$2s to know more.', 'cartflows' ), '<a href="https://cartflows.com/docs/troubleshooting-google-analytics-tracking-issues/?utm_source=dashboard&utm_medium=free-cartflows&utm_campaign=docs" target="_blank">', '</a>' ),
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_google_analytics[enable_google_analytics]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
					),
				),
			),
			'g-address-autocomplete' => array(
				'title'  => '',
				'fields' => array(
					'g-api-key' => array(
						'type'      => 'password',
						'label'     => __( 'Enter Google Map API key', 'cartflows' ),
						'name'      => '_cartflows_google_auto_address[google_map_api_key]',
						'class'     => 'input-field',
						'icon'      => 'dashicons dashicons-visibility',
						'afterIcon' => 'dashicons dashicons-hidden',
						'iconclick' => 'show_field_value',
						/* translators: %1$1s: link html start, %2$12: link html end*/
						'desc'      => sprintf( __( 'Check this %1$1s article %2$2s to setup and find an API key.', 'cartflows' ), '<a href="https://cartflows.com/docs/enabling-google-address-autocompletes/?utm_source=dashboard&utm_medium=free-cartflows&utm_campaign=docs" target="_blank">', '</a>' ),
					),
				),
			),
			'tiktok-pixel'           => array(
				'title'  => '',
				'fields' => array(
					'enable-tiktok-pixel'             => array(
						'type'     => 'toggle',
						'label'    => __( 'Enable For CartFlows Pages', 'cartflows' ),
						'name'     => '_cartflows_tiktok[tiktok_pixel_tracking]',
						'backComp' => true,
					),
					'tiktok-pixel-separator'          => array(
						'type'       => 'separator',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_tiktok[tiktok_pixel_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
					),
					'enable-tiktok-pixel-for-site'    => array(
						'type'       => 'toggle',
						'label'      => __( 'Enable for the whole site', 'cartflows' ),
						'name'       => '_cartflows_tiktok[tiktok_pixel_tracking_for_site]',
						'desc'       => __( 'If checked, PageView event will also be triggered for other pages/posts of site.', 'cartflows' ),
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_tiktok[tiktok_pixel_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
						'backComp'   => true,
					),
					'tiktok-pixel-for-site-separator' => array(
						'type'       => 'separator',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_tiktok[tiktok_pixel_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
					),
					'tiktok-id'                       => array(
						'type'       => 'text',
						'label'      => __( 'Enter TikTok ID', 'cartflows' ),
						'name'       => '_cartflows_tiktok[tiktok_pixel_id]',
						'class'      => 'input-field',
						/* translators: %1$1s: link html start, %2$12: link html end*/
						'desc'       => sprintf( __( 'Log into your %1$1s TikTok business account %2$2s to find your ID.', 'cartflows' ), '<a href="https://ads.tiktok.com/" target="_blank">', '</a>' ),
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_tiktok[tiktok_pixel_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
					),
					'tiktok-id-separator'             => array(
						'type'       => 'separator',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_tiktok[tiktok_pixel_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
					),
					'tiktok-event-heading'            => array(
						'type'       => 'heading',
						'label'      => __( 'TikTok Events', 'cartflows' ),
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_tiktok[tiktok_pixel_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
					),
					'tiktok-event-begin-checkout'     => array(
						'type'       => 'checkbox',
						'label'      => __( 'Begin Checkout', 'cartflows' ),
						'name'       => '_cartflows_tiktok[enable_tiktok_begin_checkout]',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_tiktok[tiktok_pixel_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
						'backComp'   => true,
					),

					'tiktok-event-add-to-cart'        => array(
						'type'       => 'checkbox',
						'label'      => __( 'Add To Cart', 'cartflows' ),
						'name'       => '_cartflows_tiktok[enable_tiktok_add_to_cart]',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_tiktok[tiktok_pixel_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
						'backComp'   => true,
					),
					'tiktok-event-view-content'       => array(
						'type'       => 'checkbox',
						'label'      => __( 'View Content', 'cartflows' ),
						'name'       => '_cartflows_tiktok[enable_tiktok_view_content]',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_tiktok[tiktok_pixel_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
						'backComp'   => true,
					),
					'tiktok-event-payment-info'       => array(
						'type'       => 'checkbox',
						'label'      => __( 'Add Payment Info', 'cartflows' ),
						'name'       => '_cartflows_tiktok[enable_tiktok_add_payment_info]',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_tiktok[tiktok_pixel_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
						'backComp'   => true,
					),
					'tiktok-event-purchase-complete'  => array(
						'type'       => 'checkbox',
						'label'      => __( 'Purchase', 'cartflows' ),
						'name'       => '_cartflows_tiktok[enable_tiktok_purchase_event]',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_tiktok[tiktok_pixel_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
						'backComp'   => true,
					),

					'tiktok-event-lead-info'          => array(
						'type'       => 'checkbox',
						'label'      => __( 'Optin Lead', 'cartflows' ),
						'name'       => '_cartflows_tiktok[enable_tiktok_optin_lead]',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_tiktok[tiktok_pixel_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
						'backComp'   => true,
						'tooltip'    => __( 'Optin Lead event will be triggered for optin page.', 'cartflows' ),
					),
				),
			),
			'snapchat-pixel'         => array(
				'title'  => '',
				'fields' => array(
					'enable-snapchat-pixel'             => array(
						'type'     => 'toggle',
						'label'    => __( 'Enable for CartFlows pages', 'cartflows' ),
						'name'     => '_cartflows_snapchat[snapchat_pixel_tracking]',
						'backComp' => true,
					),
					'snapchat-pixel-separator'          => array(
						'type'       => 'separator',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_snapchat[snapchat_pixel_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
					),
					'enable-snapchat-pixel-for-site'    => array(
						'type'       => 'toggle',
						'label'      => __( 'Enable for the whole site', 'cartflows' ),
						'name'       => '_cartflows_snapchat[snapchat_pixel_tracking_for_site]',
						'desc'       => __( 'If checked, PageView event will also be triggered for other pages/posts of site.', 'cartflows' ),
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_snapchat[snapchat_pixel_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
						'backComp'   => true,
					),
					'snapchat-pixel-for-site-separator' => array(
						'type'       => 'separator',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_snapchat[snapchat_pixel_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
					),
					'snapchat-id'                       => array(
						'type'       => 'text',
						'label'      => __( 'Enter Snapchat pixel ID', 'cartflows' ),
						'name'       => '_cartflows_snapchat[snapchat_pixel_id]',
						'class'      => 'input-field',
						/* translators: %1$1s: link html start, %2$12: link html end*/
						'desc'       => sprintf( __( 'Log into your %1$1s Snapchat business account %2$2s to find your ID.', 'cartflows' ), '<a href="https://ads.snapchat.com/" target="_blank">', '</a>' ),
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_snapchat[snapchat_pixel_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
					),
					'snapchat-id-separator'             => array(
						'type'       => 'separator',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_snapchat[snapchat_pixel_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
					),
					'snapchat-event-heading'            => array(
						'type'       => 'heading',
						'label'      => __( 'Snapchat Events', 'cartflows' ),
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_snapchat[snapchat_pixel_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
					),
					'snapchat-event-begin-checkout'     => array(
						'type'       => 'checkbox',
						'label'      => __( 'Begin Checkout', 'cartflows' ),
						'name'       => '_cartflows_snapchat[enable_snapchat_begin_checkout]',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_snapchat[snapchat_pixel_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
						'backComp'   => true,
					),
					'snapchat-event-add-to-cart'        => array(
						'type'       => 'checkbox',
						'label'      => __( 'Add To Cart', 'cartflows' ),
						'name'       => '_cartflows_snapchat[enable_snapchat_add_to_cart]',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_snapchat[snapchat_pixel_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
						'backComp'   => true,
					),
					'snapchat-event-view-content'       => array(
						'type'       => 'checkbox',
						'label'      => __( 'View Content', 'cartflows' ),
						'name'       => '_cartflows_snapchat[enable_snapchat_view_content]',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_snapchat[snapchat_pixel_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
						'backComp'   => true,
					),
					'snapchat-event-purchase-complete'  => array(
						'type'       => 'checkbox',
						'label'      => __( 'Purchase', 'cartflows' ),
						'name'       => '_cartflows_snapchat[enable_snapchat_purchase_event]',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_snapchat[snapchat_pixel_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
						'backComp'   => true,
					),
					'snapchat-event-subscribe'          => array(
						'type'       => 'checkbox',
						'label'      => __( 'Subscribe', 'cartflows' ),
						'name'       => '_cartflows_snapchat[enable_snapchat_subscribe_event]',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_snapchat[snapchat_pixel_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
						'backComp'   => true,
						'tooltip'    => __( 'This option is only applicable for subscription products.', 'cartflows' ),
					),
					'snapchat-event-lead-info'          => array(
						'type'       => 'checkbox',
						'label'      => __( 'Optin Lead', 'cartflows' ),
						'name'       => '_cartflows_snapchat[enable_snapchat_optin_lead]',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_snapchat[snapchat_pixel_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
						'backComp'   => true,
						'tooltip'    => __( 'Optin Lead event will be triggered for optin page.', 'cartflows' ),
					),
				),
			),
			'google-ads'             => array(
				'title'  => '',
				'fields' => array(
					'enable-google-ads'                  => array(
						'type'     => 'toggle',
						'label'    => __( 'Enable For CartFlows Pages', 'cartflows' ),
						'name'     => '_cartflows_google_ads[google_ads_tracking]',
						'backComp' => true,
					),
					'google-ads-separator'               => array(
						'type'       => 'separator',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_google_ads[google_ads_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
					),
					'enable-google-ads-for-site'         => array(
						'type'       => 'toggle',
						'label'      => __( 'Enable for the whole site', 'cartflows' ),
						'name'       => '_cartflows_google_ads[google_ads_for_site]',
						'desc'       => __( 'If checked, PageView event will also be triggered for other pages/posts of site.', 'cartflows' ),
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_google_ads[google_ads_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
						'backComp'   => true,
					),
					'google-ads-for-site-separator'      => array(
						'type'       => 'separator',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_google_ads[google_ads_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
					),
					'google-ads-id'                      => array(
						'type'       => 'text',
						'label'      => __( 'Enter Google Ads Conversion ID', 'cartflows' ),
						'name'       => '_cartflows_google_ads[google_ads_id]',
						'class'      => 'input-field',
						/* translators: %1$1s: link html start, %2$12: link html end*/
						'desc'       => sprintf( __( 'Log into your %1$1s Google Ads account %2$2s to find your conversion ID.', 'cartflows' ), '<a href="https://ads.google.com/home/" target="_blank">', '</a>' ),
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_google_ads[google_ads_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
					),
					'google-ads-label'                   => array(
						'type'       => 'text',
						'label'      => __( 'Enter Google Ads Conversion Label', 'cartflows' ),
						'name'       => '_cartflows_google_ads[google_ads_label]',
						'class'      => 'input-field',
						/* translators: %1$1s: link html start, %2$12: link html end*/
						'desc'       => sprintf( __( 'Log into your %1$1s Google Ads account %2$2s to find your conversion label.', 'cartflows' ), '<a href="https://ads.google.com/home/" target="_blank">', '</a>' ),
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_google_ads[google_ads_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
					),
					'google-ads-id-separator'            => array(
						'type'       => 'separator',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_google_ads[google_ads_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
					),

					'google-ads-event-heading'           => array(
						'type'       => 'heading',
						'label'      => __( 'Google Ads Events', 'cartflows' ),
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_google_ads[google_ads_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
					),
					'google-ads-event-begin-checkout'    => array(
						'type'       => 'checkbox',
						'label'      => __( 'Begin Checkout', 'cartflows' ),
						'name'       => '_cartflows_google_ads[enable_google_ads_begin_checkout]',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_google_ads[google_ads_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
						'backComp'   => true,
					),

					'google-ads-event-add-to-cart'       => array(
						'type'       => 'checkbox',
						'label'      => __( 'Add To Cart', 'cartflows' ),
						'name'       => '_cartflows_google_ads[enable_google_ads_add_to_cart]',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_google_ads[google_ads_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
						'backComp'   => true,
					),
					'google-ads-event-view-content'      => array(
						'type'       => 'checkbox',
						'label'      => __( 'View Content', 'cartflows' ),
						'name'       => '_cartflows_google_ads[enable_google_ads_view_content]',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_google_ads[google_ads_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
						'backComp'   => true,
					),
					'google-ads-event-payment-info'      => array(
						'type'       => 'checkbox',
						'label'      => __( 'Add Payment Info', 'cartflows' ),
						'name'       => '_cartflows_google_ads[enable_google_ads_add_payment_info]',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_google_ads[google_ads_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
						'backComp'   => true,
					),
					'google-ads-event-purchase-complete' => array(
						'type'       => 'checkbox',
						'label'      => __( 'Purchase', 'cartflows' ),
						'name'       => '_cartflows_google_ads[enable_google_ads_purchase_event]',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_google_ads[google_ads_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
						'backComp'   => true,
					),

					'google-ads-event-lead-info'         => array(
						'type'       => 'checkbox',
						'label'      => __( 'Optin Lead', 'cartflows' ),
						'name'       => '_cartflows_google_ads[enable_google_ads_optin_lead]',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_google_ads[google_ads_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
						'backComp'   => true,
						'tooltip'    => __( 'Optin Lead event will be triggered for optin page.', 'cartflows' ),
					),
				),
			),
			'pinterest-tag'          => array(
				'title'  => '',
				'fields' => array(
					'enable-pinterest-tag'              => array(
						'type'     => 'toggle',
						'label'    => __( 'Enable for CartFlows pages', 'cartflows' ),
						'name'     => '_cartflows_pinterest[pinterest_tag_tracking]',
						'backComp' => true,
					),
					'pinterest-tag-separator'           => array(
						'type'       => 'separator',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_pinterest[pinterest_tag_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
					),
					'enable-pinterest-tag-for-site'     => array(
						'type'       => 'toggle',
						'label'      => __( 'Enable for the whole site', 'cartflows' ),
						'name'       => '_cartflows_pinterest[pinterest_tag_tracking_for_site]',
						'desc'       => __( 'If checked, PageVisit event will also be triggered for other pages/posts of site.', 'cartflows' ),
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_pinterest[pinterest_tag_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
						'backComp'   => true,
					),
					'pinterest-tag-for-site-separator'  => array(
						'type'       => 'separator',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_pinterest[pinterest_tag_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
					),
					'pinterest-id'                      => array(
						'type'       => 'text',
						'label'      => __( 'Enter Pinterest Tag ID', 'cartflows' ),
						'name'       => '_cartflows_pinterest[pinterest_tag_id]',
						'class'      => 'input-field',
						/* translators: %1$1s: link html start, %2$12: link html end*/
						'desc'       => sprintf( __( 'Log into your %1$1s Pinterest business account %2$2s to find your ID.', 'cartflows' ), '<a href="https://www.pinterest.com/business/hub/" target="_blank">', '</a>' ),
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_pinterest[pinterest_tag_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
					),
					'pinterest-id-separator'            => array(
						'type'       => 'separator',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_pinterest[pinterest_tag_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
					),
					'pinterest-consent'                 => array(
						'type'       => 'checkbox',
						'label'      => __( 'Enable Pinterest tag tracking consent notice', 'cartflows' ),
						'name'       => '_cartflows_pinterest[enable_pinterest_consent]',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_pinterest[pinterest_tag_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
						'backComp'   => true,
						/* translators: %1$1s: link html start, %2$2s: link html end */
						'desc'       => sprintf( __( 'This setting enables a consent notice for Pinterest Tag tracking on your website. For more information check %1$1sPinterest documentation%2$2s.', 'cartflows' ), '<a href="https://help.pinterest.com/en/business/article/install-the-base-code" target="_blank">', '</a>' ),
					),
					'pinterest-consent-separator'       => array(
						'type'       => 'separator',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_pinterest[pinterest_tag_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
					),
					'pinterest-event-heading'           => array(
						'type'       => 'heading',
						'label'      => __( 'Pinterest Events', 'cartflows' ),
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_pinterest[pinterest_tag_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
					),
					'pinterest-event-begin-checkout'    => array(
						'type'       => 'checkbox',
						'label'      => __( 'Begin Checkout', 'cartflows' ),
						'name'       => '_cartflows_pinterest[enable_pinterest_begin_checkout]',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_pinterest[pinterest_tag_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
						'backComp'   => true,
					),
					'pinterest-event-add-to-cart'       => array(
						'type'       => 'checkbox',
						'label'      => __( 'Add To Cart', 'cartflows' ),
						'name'       => '_cartflows_pinterest[enable_pinterest_add_to_cart]',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_pinterest[pinterest_tag_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
						'backComp'   => true,
					),
					'pinterest-event-payment-info'      => array(
						'type'       => 'checkbox',
						'label'      => __( 'Add Payment Info', 'cartflows' ),
						'name'       => '_cartflows_pinterest[enable_pinterest_add_payment_info]',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_pinterest[pinterest_tag_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
						'backComp'   => true,
					),
					'pinterest-event-purchase-complete' => array(
						'type'       => 'checkbox',
						'label'      => __( 'Purchase', 'cartflows' ),
						'name'       => '_cartflows_pinterest[enable_pinterest_purchase_event]',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_pinterest[pinterest_tag_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
						'backComp'   => true,
					),
					'pinterest-event-signup'            => array(
						'type'       => 'checkbox',
						'label'      => __( 'Signup', 'cartflows' ),
						'name'       => '_cartflows_pinterest[enable_pinterest_signup]',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_pinterest[pinterest_tag_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
						'backComp'   => true,
						'tooltip'    => __( 'Signup event will be triggered for optin page.', 'cartflows' ),
					),
					'pinterest-event-lead-info'         => array(
						'type'       => 'checkbox',
						'label'      => __( 'Optin Lead', 'cartflows' ),
						'name'       => '_cartflows_pinterest[enable_pinterest_optin_lead]',
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => '_cartflows_pinterest[pinterest_tag_tracking]',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
						'backComp'   => true,
						'tooltip'    => __( 'Optin Lead event will be triggered for optin page.', 'cartflows' ),
					),
				),
			),
			'other'                  => array(
				'title'  => '',
				'fields' => array(
					'weekly-report-separator'    => array(
						'type' => 'separator',
					),
					'weekly-report-heading'      => array(
						'type'  => 'heading',
						'label' => __( 'Store Revenue Report Emails', 'cartflows' ),
					),
					'enable_weekly_emails'       => array(
						'type'     => 'toggle',
						'name'     => 'cartflows_stats_report_emails',
						'label'    => __( 'Enable Store Report Email.', 'cartflows' ),
						'backComp' => true,
						/* translators: %1$1s: link html start, %2$12: link html end*/
						'desc'     => __( 'If enabled, you will receive the weekly report emails of your store for the revenue stats generated by CartFlows.', 'cartflows' ),
					),
					'email_id_for_weekly_emails' => array(
						'type'       => 'textarea',
						'rows'       => 2,
						'cols'       => 38,
						'name'       => 'cartflows_stats_report_email_ids',
						'label'      => __( 'Email Address', 'cartflows' ),
						'desc'       => __( 'Email address to receive the weekly sales report emails. For multiple emails, add each email address per line.', 'cartflows' ),
						'conditions' => array(
							'fields' => array(
								array(
									'name'     => 'cartflows_stats_report_emails',
									'operator' => '===',
									'value'    => 'enable',
								),
							),
						),
					),
					'delete-data-separator'      => array(
						'type' => 'separator',
					),
					'delete_data'                => array(
						'type'     => 'toggle',
						'name'     => 'cartflows_delete_plugin_data',
						'label'    => __( 'Delete plugin data on plugin deletion', 'cartflows' ),
						'backComp' => true,
						'notice'   => array(
							'type'    => 'prompt',
							'check'   => 'delete',
							'message' => __( 'Are you sure? Do you want to delete plugin data while deleting the plugin? Type "DELETE" to confirm!', 'cartflows' ),
						),
						/* translators: %1$1s: link html start, %2$12: link html end*/
						'desc'     => sprintf( __( 'This option will delete all the CartFlows options data on plugin deletion. If you enable this and deletes the plugin, you can\'t restore your saved data. To learn more, %1$1s Click here %2$2s.', 'cartflows' ), '<a href="https://cartflows.com/docs/delete-plugin-data-while-uninstalling-plugin/?utm_source=dashboard&utm_medium=free-cartflows&utm_campaign=docs" target="_blank">', '</a>' ),
					),
				),
			),
			'user_role_manager'      => array(
				'title' => '',
				'roles' => (
					$all_roles
				),
			),
			'automations'            => array(
				'title' => '',
			),
		);

		$settings = apply_filters( 'cartflows_admin_global_settings_data', $settings );

		return $settings;
	}
}
