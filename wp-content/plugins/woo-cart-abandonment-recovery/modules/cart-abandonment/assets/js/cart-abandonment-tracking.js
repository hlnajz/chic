( function ( $ ) {
	let timer;
	const wcf_cart_abandonment = {
		init() {
			if (
				wcf_ca_vars._show_gdpr_message &&
				! $( '#wcf_cf_gdpr_message_block' ).length
			) {
				$( '#billing_email' ).after(
					"<span id='wcf_cf_gdpr_message_block'> <span style='font-size: xx-small'> " +
						wcf_ca_vars._gdpr_message +
						" <a style='cursor: pointer' id='wcf_ca_gdpr_no_thanks'> " +
						wcf_ca_vars._gdpr_nothanks_msg +
						' </a></span></span>'
				);
			}

			$( document ).on(
				'keyup keypress change',
				'#email, #billing-phone, #billing_email, #billing_phone, #shipping-phone, #shipping_phone,  input.input-text, textarea.input-text, select',
				this._getCheckoutData
			);

			$( '#wcf_ca_gdpr_no_thanks' ).on( 'click', function () {
				wcf_cart_abandonment._set_cookie();
			} );

			$( document.body ).on( 'updated_checkout', function () {
				wcf_cart_abandonment._getCheckoutData();
			} );

			$( function () {
				setTimeout( function () {
					wcf_cart_abandonment._getCheckoutData();
				}, 800 );
			} );
		},

		_set_cookie() {
			const data = {
				wcf_ca_skip_track_data: true,
				action: 'cartflows_skip_cart_tracking_gdpr',
				security: wcf_ca_vars._gdpr_nonce,
			};

			jQuery.post( wcf_ca_vars.ajaxurl, data, function ( response ) {
				if ( response.success ) {
					$( '#wcf_cf_gdpr_message_block' )
						.empty()
						.append(
							"<span style='font-size: xx-small'>" +
								wcf_ca_vars._gdpr_after_no_thanks_msg +
								'</span>'
						)
						.delay( 5000 )
						.fadeOut();
				}
			} );
		},

		_validate_email( value ) {
			let valid = true;
			if ( value.indexOf( '@' ) === -1 ) {
				valid = false;
			} else {
				const parts = value.split( '@' );
				const domain = parts[ 1 ];
				if ( domain.indexOf( '.' ) === -1 ) {
					valid = false;
				} else {
					const domainParts = domain.split( '.' );
					const ext = domainParts[ 1 ];
					if ( ext.length > 14 || ext.length < 2 ) {
						valid = false;
					}
				}
			}
			return valid;
		},

		_getCheckoutData() {
			const wcf_email = wcf_ca_vars._is_block_based_checkout
				? jQuery( '#email' ).val()
				: jQuery( '#billing_email' ).val();

			if ( typeof wcf_email === 'undefined' ) {
				return;
			}

			let wcf_phone = wcf_ca_vars._is_block_based_checkout
				? jQuery( '#billing-phone' ).val() ||
				jQuery( '#shipping-phone' ).val()
				: jQuery( '#billing_phone' ).val() ||
				jQuery( '#shipping_phone' ).val();

			const atposition = wcf_email.indexOf( '@' );
			const dotposition = wcf_email.lastIndexOf( '.' );

			if ( typeof wcf_phone === 'undefined' || wcf_phone === null ) {
				// If phone number field does not exist on the Checkout form
				wcf_phone = '';
			}

			clearTimeout( timer );

			if (
				! (
					atposition < 1 ||
					dotposition < atposition + 2 ||
					dotposition + 2 >= wcf_email.length
				) ||
				wcf_phone.length >= 1
			) {
				const fieldIds = {
					billing_first_name: 'billing-first_name',
					billing_last_name: 'billing-last_name',
					billing_city: 'billing-city',
					billing_company: 'billing-company',
					billing_country: 'components-form-token-input-2',
					billing_address_1: 'billing-address_1',
					billing_address_2: 'billing-address_2',
					billing_state: 'components-form-token-input-3',
					billing_postcode: 'billing-postcode',
					shipping_first_name: 'shipping-first_name',
					shipping_last_name: 'shipping-last_name',
					shipping_company: 'shipping-company',
					shipping_country: 'components-form-token-input-0',
					shipping_address_1: 'shipping-address_1',
					shipping_address_2: 'shipping-address_2',
					shipping_city: 'shipping-city',
					shipping_state: 'components-form-token-input-1',
					shipping_postcode: 'shipping-postcode',
					order_comments: 'checkbox-control-0',
				};

				const fieldMapping = {
					billing_first_name: 'wcf_name',
					billing_last_name: 'wcf_surname',
					billing_city: 'wcf_city',
					billing_country: 'wcf_country',
				};

				const data = {
					action: 'cartflows_save_cart_abandonment_data',
					wcf_email,
					wcf_phone,
					security: wcf_ca_vars._nonce,
					wcf_post_id: wcf_ca_vars._post_id,
				};

				Object.keys( fieldIds ).forEach( ( defaultKey ) => {
					const checkoutFieldId = wcf_ca_vars._is_block_based_checkout
						? fieldIds[ defaultKey ]
						: defaultKey;

					const fieldElement = jQuery( '#' + checkoutFieldId );
					const dataKey = fieldMapping[ defaultKey ]
						? fieldMapping[ defaultKey ]
						: `wcf_${ defaultKey }`;

					data[ dataKey ] = fieldElement.length
						? fieldElement.val()
						: null;
				} );
				// Check if the checkbox is checked for using the same address for billing
				if (
					$(
						'.wc-block-checkout__use-address-for-billing input[type="checkbox"]'
					).prop( 'checked' )
				) {
					const billingAndShippingFields = [
						[ 'wcf_name', 'wcf_shipping_first_name' ],
						[ 'wcf_surname', 'wcf_shipping_last_name' ],
						[ 'wcf_billing_state', 'wcf_shipping_state' ],
						[ 'wcf_billing_address_1', 'wcf_shipping_address_1' ],
						[ 'wcf_billing_address_2', 'wcf_shipping_address_2' ],
						[ 'wcf_city', 'wcf_shipping_city' ],
						[ 'wcf_country', 'wcf_shipping_country' ],
						[ 'wcf_billing_postcode', 'wcf_shipping_postcode' ],
					];

					billingAndShippingFields.forEach(
						( [ billingField, shippingField ] ) => {
							if ( ! data[ billingField ] ) {
								data[ billingField ] = data[ shippingField ];
							}
						}
					);
				}

				timer = setTimeout( function () {
					if (
						wcf_cart_abandonment._validate_email( data.wcf_email )
					) {
						jQuery.post(
							wcf_ca_vars.ajaxurl,
							data, //Ajaxurl coming from localized script and contains the link to wp-admin/admin-ajax.php file that handles AJAX requests on Wordpress
							function () {
								// success response
							}
						);
					}
				}, 500 );
			} else {
				//console.log("Not a valid e-mail or phone address");
			}
		},
	};

	wcf_cart_abandonment.init();
} )( jQuery );
