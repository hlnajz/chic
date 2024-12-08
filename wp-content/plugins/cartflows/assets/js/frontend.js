( function ( $ ) {
	const wcf_order_review_toggler = function () {
		const mobile_order_review_section = $(
				'.wcf-collapsed-order-review-section'
			),
			mobile_order_review_wrap = $(
				'.wcf-cartflows-review-order-wrapper'
			),
			desktop_order_review_wrap = $( '.wcf-order-wrap' );

		let timeout = false;
		const resizeEvent =
			'onorientationchange' in window ? 'orientationchange' : 'resize';

		$( '.wcf-order-review-toggle' ).on(
			'click',
			function wcf_show_order_summary( e ) {
				e.preventDefault();

				if ( mobile_order_review_section.hasClass( 'wcf-show' ) ) {
					mobile_order_review_wrap.slideUp( 400 );
					mobile_order_review_section.removeClass( 'wcf-show' );
					$( '.wcf-order-review-toggle-text' ).text(
						cartflows.order_review_toggle_texts.toggle_show_text
					);
				} else {
					mobile_order_review_wrap.slideDown( 400 );
					mobile_order_review_section.addClass( 'wcf-show' );
					$( '.wcf-order-review-toggle-text' ).text(
						cartflows.order_review_toggle_texts.toggle_hide_text
					);
				}
			}
		);

		$( window ).on( resizeEvent, function () {
			clearTimeout( timeout );

			timeout = setTimeout( function () {
				const width = window.innerWidth || $( window ).width();

				if ( width >= 769 ) {
					mobile_order_review_wrap.css( { display: 'none' } );
					mobile_order_review_wrap.removeClass( 'wcf-show' );
					$( '.wcf-order-review-toggle' ).removeClass( 'wcf-show' );
					$( '.wcf-order-review-toggle-text' ).text(
						cartflows.order_review_toggle_texts.toggle_show_text
					);
				}
			}, 200 );
		} );

		// Update checkout when shipping methods changes.
		mobile_order_review_wrap.on(
			'change',
			'select.shipping_method, input[name^="shipping_method"]',
			function () {
				/**
				 * Uncheck all shipping radio buttons of desktop. Those will be auto updated by update_checkout action.
				 * While performing the update checkout, it searches for the selected shipping method in whole page.
				 */
				desktop_order_review_wrap
					.find(
						'input[name^="shipping_method"][type="radio"]:checked'
					)
					.each( function () {
						$( this ).removeAttr( 'checked' );
					} );

				$( document.body ).trigger( 'update_checkout', {
					update_shipping_method: true,
				} );
			}
		);
	};

	function setCookie( cName, cValue, expDays ) {
		const date = new Date();
		date.setTime( date.getTime() + expDays * 24 * 60 * 60 * 1000 );
		const expires = 'expires=' + date.toUTCString();
		document.cookie = cName + '=' + cValue + '; ' + expires + '; path=/';
	}

	/* It will redirect if anyone clicked on link before ready */
	$( document ).on( 'click', 'a[href*="wcf-next-step"]', function ( e ) {
		e.preventDefault();

		if (
			'undefined' !== typeof cartflows.is_pb_preview &&
			'1' === cartflows.is_pb_preview
		) {
			e.stopPropagation();
			return;
		}

		window.location.href = cartflows.next_step;

		return false;
	} );

	/* Once the link is ready this will work to stop conditional propogation*/
	$( document ).on( 'click', '.wcf-next-step-link', function ( e ) {
		if (
			'undefined' !== typeof cartflows.is_pb_preview &&
			'1' === cartflows.is_pb_preview
		) {
			e.preventDefault();
			e.stopPropagation();
			return false;
		}
	} );

	// Remove css when oceanwp theme is enabled.
	const remove_oceanwp_custom_style = function () {
		if (
			'OceanWP' === cartflows.current_theme &&
			'default' !== cartflows.page_template
		) {
			const style = document.getElementById( 'oceanwp-style-css' );
			if ( null !== style ) {
				style.remove();
			}
		}
	};

	const trigger_facebook_events = function () {
		if (
			'enable' === cartflows.fb_setting.facebook_pixel_tracking &&
			cartflows.fb_setting.facebook_pixel_id !== ''
		) {
			const add_payment_info_event =
				cartflows.fb_setting.facebook_pixel_add_payment_info;
			if (
				'enable' === add_payment_info_event &&
				cartflows.is_checkout_page
			) {
				jQuery( 'form.woocommerce-checkout' ).on(
					'submit',
					function () {
						fbq(
							'track',
							'AddPaymentInfo',
							JSON.parse( cartflows.fb_add_payment_info_data )
						);
					}
				);
			} else if (
				cartflows.is_optin &&
				'enable' === cartflows.fb_setting.facebook_pixel_optin_lead
			) {
				jQuery( 'form.woocommerce-checkout' ).on(
					'submit',
					function () {
						fbq( 'track', 'Lead', { plugin: 'CartFlows' } );
					}
				);
			}
		}
	};

	const trigger_google_events = function () {
		if ( cartflows.ga_setting.enable_google_analytics === 'enable' ) {
			if (
				cartflows.is_checkout_page &&
				'enable' === cartflows.ga_setting.enable_add_payment_info
			) {
				jQuery( 'form.woocommerce-checkout' ).on(
					'submit',
					function () {
						gtag(
							'event',
							'add_payment_info',
							JSON.parse( cartflows.add_payment_info_data )
						);
					}
				);
			} else if (
				cartflows.is_optin &&
				'enable' === cartflows.ga_setting.enable_optin_lead
			) {
				jQuery( 'form.woocommerce-checkout' ).on(
					'submit',
					function () {
						gtag( 'event', 'Lead', { plugin: 'CartFlows' } );
					}
				);
			}
		}
	};

	// Trigger TikTok events on form submit.
	const trigger_tiktok_events = function () {
		if (
			'enable' === cartflows.tik_setting.tiktok_pixel_tracking &&
			cartflows.tik_setting.tiktok_pixel_id !== ''
		) {
			/* global ttq */
			const add_payment_info_event =
				cartflows.tik_setting.enable_tiktok_add_payment_info;
			if (
				'enable' === add_payment_info_event &&
				cartflows.is_checkout_page
			) {
				jQuery( 'form.woocommerce-checkout' ).on(
					'submit',
					function () {
						ttq.track(
							'AddPaymentInfo',
							JSON.parse( cartflows.tiktok_add_payment_info_data )
						);
					}
				);
			} else if (
				cartflows.is_optin &&
				'enable' === cartflows.tik_setting.enable_tiktok_optin_lead
			) {
				jQuery( 'form.woocommerce-checkout' ).on(
					'submit',
					function () {
						ttq.track( 'Lead', { plugin: 'CartFlows' } );
					}
				);
			}
		}
	};

	// Trigger google ads events on form submit.
	const trigger_google_ads_events = function () {
		if ( cartflows.gads_setting.google_ads_tracking === 'enable' ) {
			if (
				cartflows.is_checkout_page &&
				'enable' ===
					cartflows.gads_setting.enable_google_ads_add_payment_info
			) {
				jQuery( 'form.woocommerce-checkout' ).on(
					'submit',
					function () {
						gtag(
							'event',
							'add_payment_info',
							JSON.parse( cartflows.add_payment_info_data )
						);
					}
				);
			} else if (
				cartflows.is_optin &&
				'enable' === cartflows.gads_setting.enable_google_ads_optin_lead
			) {
				jQuery( 'form.woocommerce-checkout' ).on(
					'submit',
					function () {
						gtag( 'event', 'Lead', { plugin: 'CartFlows' } );
					}
				);
			}
		}
	};

	// Trigger Snapchat events on form submit.
	const trigger_snapchat_events = function () {
		if (
			'enable' === cartflows.snap_settings.snapchat_pixel_tracking &&
			cartflows.snap_settings.snapchat_pixel_id !== ''
		) {
			/* global snaptr */
			if (
				cartflows.is_optin &&
				'enable' === cartflows.snap_settings.enable_snapchat_optin_lead
			) {
				jQuery( 'form.woocommerce-checkout' ).on(
					'submit',
					function () {
						snaptr( 'track', 'SIGN_UP', {
							sign_up_method: 'CartFlows Optin Lead',
						} );
					}
				);
			}
		}
	};

	// Trigger pinterest events on form submit.
	const trigger_pinterest_events = function () {
		if (
			'enable' === cartflows.pin_settings.pinterest_tag_tracking &&
			cartflows.pin_settings.pinterest_tag_id !== '' &&
			typeof pintrk !== 'undefined'
		) {
			/* global pintrk */
			const add_payment_info_event =
				cartflows.pin_settings.enable_pinterest_add_payment_info;
			if (
				'enable' === add_payment_info_event &&
				cartflows.is_checkout_page
			) {
				jQuery( 'form.woocommerce-checkout' ).on(
					'submit',
					function () {
						pintrk(
							'track',
							'AddPaymentInfo',
							JSON.parse(
								cartflows.pinterest_add_payment_info_data
							)
						);
					}
				);
			} else if ( cartflows.is_optin ) {
				if (
					'enable' ===
					cartflows.pin_settings.enable_pinterest_optin_lead
				) {
					jQuery( 'form.woocommerce-checkout' ).on(
						'submit',
						function () {
							pintrk( 'track', 'Lead', {
								lead_type: 'CartFlows Optin',
							} );
						}
					);
				}
				if (
					'enable' === cartflows.pin_settings.enable_pinterest_signup
				) {
					jQuery( 'form.woocommerce-checkout' ).on(
						'submit',
						function () {
							pintrk(
								'track',
								'Signup',
								JSON.parse(
									cartflows.pinterest_signup_info_data
								)
							);
						}
					);
				}
			}
		}
	};

	/**
	 *
	 * @param {string} next_step_url
	 * @return {string} next_step_url Modified string if query string is present.
	 */
	const may_be_append_query_string = function ( next_step_url ) {
		// Get the URL parameters.
		const urlParams = new URLSearchParams( window.location.search );

		// Return the URL if no query string is present.
		if ( urlParams.length <= 0 ) {
			return next_step_url;
		}

		// Get all URL parameter keys.
		const keys = urlParams.keys();

		// Store all parameters temporary.
		const params = {};

		// Loop through all parameter as keys to make key-value pair array/object.
		for ( const key of keys ) {
			params[ key ] = urlParams.get( key );
		}

		// Convert the object to a query string.
		const queryString = new URLSearchParams( params ).toString();

		// Return if the querystring is empty.
		if ( '' === queryString ) {
			return next_step_url;
		}

		/**
		 * Append the query string to the URL
		 * If: No question mark is in the URL then: add the question mark before query string.
		 * Else: Question mark is found then: add '&' before query string..
		 */
		if ( next_step_url.indexOf( '?' ) === -1 ) {
			next_step_url = next_step_url + '?' + queryString;
		} else {
			next_step_url = next_step_url + '&' + queryString;
		}

		return next_step_url;
	};

	/**
	 * Assign the class & link to specific button
	 */
	const setup_next_step_url = function () {
		const next_links = $( 'a[href*="wcf-next-step"]' );

		if (
			next_links.length > 0 &&
			'undefined' !== typeof cartflows.next_step
		) {
			next_links.addClass( 'wcf-next-step-link' );
			next_links.attr(
				'href',
				may_be_append_query_string( cartflows.next_step )
			);
		}
	};

	// Event listener for accepting or declining Pinterest consent.
	$( document ).on( 'click', '.wcf-pinterest-consent-button', function () {
		const consentButton = $( this );
		const consentAction = consentButton.data( 'action' );

		setCookie(
			cartflows?.pinterest_consent_cookie,
			'accept' === consentAction ? 'true' : 'false',
			30
		);

		// Hide the consent popup.
		$( '#cartflows-pinterest-consent-wrapper' ).hide();

		// Dispatch a custom event to notify that consent has changed.
		if ( 'accept' === consentAction ) {
			document.dispatchEvent(
				new CustomEvent( 'cartflows_pinterest_consent_changed', {
					detail: 'true',
				} )
			);
		}
	} );

	$( function () {
		setup_next_step_url();
		remove_oceanwp_custom_style();
		if ( '1' !== cartflows.is_pb_preview ) {
			trigger_facebook_events();
			trigger_google_events();
			trigger_tiktok_events();
			trigger_google_ads_events();
			trigger_snapchat_events();
			trigger_pinterest_events();
		}
		wcf_order_review_toggler();
	} );
} )( jQuery );
