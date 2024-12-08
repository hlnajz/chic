( function( $ ) {
	const pubKey = cpsw_global_settings.public_key;
	const inlineCC = cpsw_global_settings.inline_cc;
	const mode = cpsw_global_settings.mode;
	const ajaxUrl = cpsw_global_settings.ajax_url;
	const jsNonce = cpsw_global_settings.js_nonce;
	const allowedCards = cpsw_global_settings.allowed_cards;
	const notAllowedString = cpsw_global_settings.not_allowed_string;
	const defaultCards = cpsw_global_settings.default_cards;
	const homeURL = cpsw_global_settings.get_home_url;
	const stripeLocalized = cpsw_global_settings.stripe_localized;

	if ( '' === pubKey || ( 'live' === mode && ! cpsw_global_settings.is_ssl ) ) {
		return;
	}

	const stripe = Stripe( pubKey );
	const elements = stripe.elements();
	let savedCard = false;
	let savedPaymentElementMethod = false;
	let card = null;
	let cardNumber = null;
	let cardExpiry = null;
	let cardCvc = null;
	let paymentForm = null;
	let paymentMethod = '';
	let paymentMethodSepa = '';
	let paymentElementMethod = '';
	let paymentType = '';
	let isAllowedCard = '';
	let selectedGatewayId = '';
	let selectedIdealBank = '';
	let selectedP24Bank = '';
	let selectedPaymentElementType = '';
	let sepaIBAN = false;
	let emptySepaIBANMessage = cpsw_global_settings.empty_sepa_iban_message;
	const currentUserBilling = cpsw_global_settings.current_user_billing;

	const style = {
		base: {
			color: '#32325d',
		},
	};

	// Register stripe app info
	stripe.registerAppInfo( {
		name: 'Checkout Plugins - Stripe Woo',
		partner_id: 'pp_partner_KOjySVEy3ClX6G',
		version: cpsw_global_settings.cpsw_version,
		url: 'https://wordpress.org/plugins/checkout-plugins-stripe-woo/',
	} );

	// Create an instance of the iban Element
	const sepaOptions = Object.keys( cpsw_global_settings.sepa_options ).length ? cpsw_global_settings.sepa_options : {};
	const sepa = elements.create( 'iban', sepaOptions );

	/**
	 * display error messages for sepa
	 */
	sepa.on( 'change', ( { error } ) => {
		if ( isSepaSaveCardChosen() ) {
			return true;
		}

		if ( error ) {
			sepaIBAN = false;
			emptySepaIBANMessage = error.message;
			$( '.cpsw_stripe_sepa_error' ).html( error.message );
		} else {
			sepaIBAN = true;
			$( '.cpsw_stripe_sepa_error' ).html( '' );
		}
	} );

	if ( 'yes' === inlineCC ) {
		card = elements.create( 'card', {
			style,
			hidePostalCode: true,
			iconStyle: 'solid',
		} );

		/**
		 * display error messages
		 */
		card.on( 'change', ( { brand, error } ) => {
			if ( brand ) {
				isAllowedBrand( brand );
				if ( ! isAllowedCard ) {
					if ( 'unknown' === brand ) {
						$( '.cpsw-stripe-error' ).html( '' );
					} else {
						$( '.cpsw-stripe-error' ).html( defaultCards[ brand ] + ' ' + notAllowedString );
						return;
					}
				} else {
					$( '.cpsw-stripe-error' ).html( '' );
				}
			}
			if ( error ) {
				$( '.cpsw-stripe-error' ).html( getStripeLocalizedMessage( error.code, error.message ) );
			} else {
				$( '.cpsw-stripe-error' ).html( '' );
			}
		} );
	}

	if ( 'no' === inlineCC ) {
		cardNumber = elements.create( 'cardNumber', {
			style,
			iconStyle: 'solid',
		} );

		cardExpiry = elements.create( 'cardExpiry', {
			style,
		} );

		cardCvc = elements.create( 'cardCvc', {
			style,
		} );
		/**
		 * display error messages
		 */
		cardNumber.on( 'change', ( { brand, error } ) => {
			if ( brand ) {
				isAllowedBrand( brand );
				if ( ! isAllowedCard ) {
					if ( 'unknown' === brand ) {
						$( '.cpsw-number-error' ).html( '' );
					} else {
						$( '.cpsw-number-error' ).html( defaultCards[ brand ] + ' ' + notAllowedString );
						return;
					}
				} else {
					$( '.cpsw-number-error' ).html( '' );
				}
			}

			if ( error ) {
				$( '.cpsw-number-error' ).html( getStripeLocalizedMessage( error.code, error.message ) );
			} else {
				$( '.cpsw-number-error' ).html( '' );
			}
		} );
		cardExpiry.on( 'change', ( { error } ) => {
			if ( error ) {
				$( '.cpsw-expiry-error' ).html( getStripeLocalizedMessage( error.code, error.message ) );
			} else {
				$( '.cpsw-expiry-error' ).html( '' );
			}
		} );
		cardCvc.on( 'change', ( { error } ) => {
			if ( error ) {
				$( '.cpsw-cvc-error' ).html( getStripeLocalizedMessage( error.code, error.message ) );
			} else {
				$( '.cpsw-cvc-error' ).html( '' );
			}
		} );
	}

	const options = {
		style: {
			base: {
				padding: '10px 12px',
				color: '#32325d',
				fontSize: '16px',
				'::placeholder': {
					color: '#aab7c4',
					backgroundColor: '#fff',
				},
			},
		},
	};

	const paymentElementSettings = cpsw_global_settings.payment_element_settings;
	const theme = paymentElementSettings.appearance.theme;
	switch ( theme ) {
		case 'minimal':
			paymentElementSettings.appearance = {
				theme: 'flat',
				variables: {
					fontFamily: ' "Gill Sans", sans-serif',
					fontLineHeight: '1.5',
					borderRadius: '10px',
					colorBackground: '#F6F8FA',
					accessibleColorOnColorPrimary: '#262626',
				},
				rules: {
					'.Block': {
						backgroundColor: 'var(--colorBackground)',
						boxShadow: 'none',
						padding: '12px',
					},
					'.Input': {
						padding: '12px',
					},
					'.Input:disabled, .Input--invalid:disabled': {
						color: 'lightgray',
					},
					'.Tab': {
						padding: '10px 12px 8px 12px',
						border: 'none',
					},
					'.Tab:hover': {
						border: 'none',
						boxShadow: '0px 1px 1px rgba(0, 0, 0, 0.03), 0px 3px 7px rgba(18, 42, 66, 0.04)',
					},
					'.Tab--selected, .Tab--selected:focus, .Tab--selected:hover': {
						border: 'none',
						backgroundColor: '#fff',
						boxShadow: '0 0 0 1.5px var(--colorPrimaryText), 0px 1px 1px rgba(0, 0, 0, 0.03), 0px 3px 7px rgba(18, 42, 66, 0.04)',
					},
					'.Label': {
						fontWeight: '500',
					},
				},
			};
			break;
		case 'bubblegum':
			paymentElementSettings.appearance = {
				theme: 'stripe',
				variables: {
					fontWeightNormal: '500',
					borderRadius: '2px',
					colorPrimary: '#f360a6',
					tabIconSelectedColor: '#fff',
					gridRowSpacing: '16px',
				},
				rules: {
					'.Tab, .Input, .Block, .CheckboxInput, .CodeInput': {
						boxShadow: '0px 3px 10px rgba(18, 42, 66, 0.08)',
					},
					'.Block': {
						borderColor: 'transparent',
					},
					'.BlockDivider': {
						backgroundColor: '#ebebeb',
					},
					'.Tab, .Tab:hover, .Tab:focus': {
						border: '0',
					},
					'.Tab--selected, .Tab--selected:hover': {
						backgroundColor: '#f360a6',
						color: '#fff',
					},
				},
			};
			break;
		case 'ninety-five':
			paymentElementSettings.appearance = {
				theme: 'flat',
				variables: {
					fontFamily: 'Verdana',
					fontLineHeight: '1.5',
					borderRadius: '0',
					colorBackground: '#fff',
					focusBoxShadow: 'none',
					focusOutline: '-webkit-focus-ring-color auto 1px',
					tabIconSelectedColor: 'var(--colorText)',
				},
				rules: {
					'.Input, .CheckboxInput, .CodeInput': {
						transition: 'none',
						boxShadow: 'inset -1px -1px #ffffff, inset 1px 1px #0a0a0a, inset -2px -2px #dfdfdf, inset 2px 2px #808080',
					},
					'.Input': {
						padding: '12px',
					},
					'.Input--invalid': {
						color: '#DF1B41',
					},
					'.Tab, .Block, .PickerItem--selected': {
						backgroundColor: '#dfdfdf',
						boxShadow: 'inset -1px -1px #0a0a0a, inset 1px 1px #ffffff, inset -2px -2px #808080, inset 2px 2px #dfdfdf',
					},
					'.Tab': {
						transition: 'none',
					},
					'.Tab:hover': {
						backgroundColor: '#eee',
					},
					'.Tab--selected, .Tab--selected:focus, .Tab--selected:hover': {
						color: 'var(--colorText)',
						backgroundColor: '#ccc',
					},
					'.Tab:focus, .Tab--selected:focus': {
						boxShadow: 'inset -1px -1px #0a0a0a, inset 1px 1px #ffffff, inset -2px -2px #808080, inset 2px 2px #dfdfdf',
						outline: 'none',
					},
					'.Tab:focus-visible': {
						outline: 'var(--focusOutline)',
					},
					'.PickerItem': {
						backgroundColor: '#dfdfdf',
						boxShadow: 'inset -1px -1px #0a0a0a, inset 1px 1px #ffffff, inset -2px -2px #808080, inset 2px 2px #dfdfdf',
						transition: 'none',
					},
					'.PickerItem:hover': {
						backgroundColor: '#eee',
					},
					'.PickerItem--highlight': {
						outline: '1px solid blue',
					},
					'.PickerItem--selected:hover': {
						backgroundColor: '#dfdfdf',
					},
				},
			};
			break;
		case 'dark-blue':
			paymentElementSettings.appearance = {
				theme: 'night',
				variables: {
					fontFamily: 'Sohne, system-ui, sans-serif',
					fontWeightNormal: '500',
					borderRadius: '8px',
					colorBackground: '#0A2540',
					colorPrimary: '#EFC078',
					accessibleColorOnColorPrimary: '#1A1B25',
					colorText: 'white',
					colorTextPlaceholder: '#727F96',
					tabIconColor: 'white',
					logoColor: 'dark',
				},
				rules: {
					'.Input, .Block': {
						backgroundColor: '#0A2540',
						border: '1.5px solid var(--colorPrimary)',
					},
					'.Tab': {
						color: 'white',
					},
				},
			};
			break;
		default:
			paymentElementSettings.appearance = { theme: 'stripe' };
			break;
	}
	const paymentElementOptions = cpsw_global_settings.payment_element_options;

	paymentElementOptions.fields = {
		billingDetails: {
			name: getBillingDetails().name ? 'never' : 'auto',
			email: getBillingDetails().email ? 'never' : 'auto',
			phone: getBillingDetails().phone ? 'never' : 'auto',
			address: {
				country: getBillingDetails().address?.country ? 'never' : 'never',
				line1: getBillingDetails().address?.line1 ? 'never' : 'auto',
				line2: getBillingDetails().address?.line2 ? 'never' : 'auto',
				city: getBillingDetails().address?.city ? 'never' : 'auto',
				state: getBillingDetails().address?.state ? 'never' : 'auto',
				postalCode: getBillingDetails().address?.postal_code ? 'never' : 'never',
			},
		},
	};

	// Create an instance of the idealBank Element
	const ideal = elements.create( 'idealBank', options );
	// Create an instance of the p24 Element
	const p24 = elements.create( 'p24Bank', options );

	const elementsForPayment = stripe.elements( paymentElementSettings );
	// Create an instance of the Payment Element
	const paymentElement = elementsForPayment.create( 'payment', paymentElementOptions );

	// If Payment element is not loaded, show error message
	paymentElement.on( 'loaderror', ( event ) => {
		if ( event.error ) {
			const errorCode = event.error.code ? event.error.code : event.error?.message_code;
			$( '.cpsw_stripe_payment_element_error' ).html(
				getStripeLocalizedMessage( 'payment_element_loaderror', null ) + getStripeLocalizedMessage( errorCode, event?.error?.message ),
			);
		}
	} );

	const saveCardGateways = cpsw_global_settings.savecard_supported_gateways;

	paymentElement.on( 'change', ( event ) => {
		selectedPaymentElementType = event.value.type;
		if ( ! saveCardGateways.includes( selectedPaymentElementType ) ) {
			$( '.cpsw-payment-element-save-method' ).hide();
			$( 'input[name="wc-cpsw_stripe_element-new-payment-method"]' ).prop( 'checked', false );
			$( 'input[name="wc-cpsw_stripe_element-new-payment-method"]' ).trigger( 'change' );
		} else {
			$( '.cpsw-payment-element-save-method' ).show();
		}
	} );

	ideal.on( 'change', function( event ) {
		selectedIdealBank = event.value;
		$( '.cpsw_stripe_ideal_error' ).html( '' );
	} );

	function isSepaSaveCardChosen() {
		return (
			$( '#payment_method_cpsw_sepa' ).is( ':checked' ) &&
			$( 'input[name="wc-cpsw_sepa-payment-token"]' ).is( ':checked' ) &&
			'new' !== $( 'input[name="wc-cpsw_sepa-payment-token"]:checked' ).val()
		);
	}

	p24.on( 'change', function( event ) {
		selectedP24Bank = event.value;
		$( '.cpsw_stripe_p24_error' ).html( '' );
	} );

	function mountCard() {
		$( '.cpsw-stripe-elements-form' ).show();
		if ( 0 === $( '.cpsw-stripe-elements-form' ).length ) {
			return;
		}
		if ( 'yes' === inlineCC ) {
			if ( $( '.cpsw-stripe-elements-form .cpsw-cc' ).html() ) {
				card.unmount();
			}
			card.mount( '.cpsw-stripe-elements-form .cpsw-cc' );
			$( '.cpsw-stripe-elements-form div' ).css( { backgroundColor: '#fff', padding: '1em' } );
		} else if ( 'no' === inlineCC ) {
			cardNumber.mount( '.cpsw-stripe-elements-form .cpsw-number' );
			cardExpiry.mount( '.cpsw-stripe-elements-form .cpsw-expiry' );
			cardCvc.mount( '.cpsw-stripe-elements-form .cpsw-cvc' );
			$( '.cpsw-stripe-elements-form div' ).css( { backgroundColor: '#fff', padding: '1em', marginTop: '0.5em' } );
		}
	}

	function mountSepa() {
		if ( 0 === $( '.payment_method_cpsw_sepa' ).length ) {
			return false;
		}

		sepa.mount( '.cpsw_stripe_sepa_iban_element_field' );
		$( '.cpsw_stripe_sepa_payment_form .cpsw_stripe_sepa_iban_element_field' ).css( { backgroundColor: '#fff', borderRadius: '3px' } );
	}

	function mountIdeal() {
		$( '.cpsw_stripe_ideal_form' ).show();
		if ( 0 === $( '.cpsw_stripe_ideal_form' ).length ) {
			return;
		}

		ideal.mount( '.cpsw_stripe_ideal_form .cpsw_stripe_ideal_select' );
		$( '.cpsw_stripe_ideal_form .cpsw_stripe_ideal_select' ).css( { backgroundColor: '#fff' } );
	}

	function mountP24() {
		$( '.cpsw_stripe_p24_form' ).show();
		if ( 0 === $( '.cpsw_stripe_p24_form' ).length ) {
			return;
		}

		p24.mount( '.cpsw_stripe_p24_form .cpsw_stripe_p24_select' );
		$( '.cpsw_stripe_p24_form .cpsw_stripe_p24_select' ).css( { backgroundColor: '#fff' } );
	}

	function mountPaymentElement() {
		if ( 0 === $( '.cpsw_stripe_element_payment_form' ).length ) {
			return false;
		}

		paymentElement?.mount( '#cpsw_stripe_payment_element' );
	}

	function mountGateways() {
		mountCard();
		mountIdeal();
		mountP24();
		mountSepa();
		mountPaymentElement();
	}

	function createStripePaymentMethod() {
		let paymentObject = {};
		switch ( selectedGatewayId ) {
			case 'cpsw_stripe':
				if ( 'no' === inlineCC ) {
					card = cardNumber;
				}
				paymentObject = {
					type: 'card',
					card,
				};
				stripe.createPaymentMethod( paymentObject )
					.then( function( result ) {
						// Handle result.error or result.paymentMethod
						if ( result.paymentMethod ) {
							paymentMethod = result.paymentMethod.id;
							const brand = result.paymentMethod.card.brand;
							$( '.cpsw_payment_method' ).remove();
							$( '.cpsw_card_brand' ).remove();
							paymentForm.append(
								"<input type='hidden' class='cpsw_payment_method' name='payment_method_created' value='" +
								paymentMethod +
								"'/><input type='hidden' class='cpsw_card_brand' name='card_brand' value='" +
								brand +
								"'/>",
							);
							if ( $( 'form#order_review' ).length && cpsw_global_settings.changing_payment_method ) {
								confirmCardSetup( paymentMethod );
							} else if ( $( 'form#add_payment_method' ).length ) {
								confirmCardSetup( paymentMethod );
							} else {
								paymentForm.trigger( 'submit' );
							}
						} else if ( result.error ) {
							$( '.woocommerce-error' ).remove();
							$( 'form.woocommerce-checkout' ).unblock();
							$( 'form#order_review' ).unblock();
							$( 'form#add_payment_method' ).unblock();
							logError( result.error );
							$( '.woocommerce-notices-wrapper:first-child' ).html( '<div class="woocommerce-error cpsw-errors">' + getStripeLocalizedMessage( result.error.code, result.error.message ) + '</div>' ).show();
							window.scrollTo( { top: 0, behavior: 'smooth' } );
							return false;
						}
					} );
				break;
			case 'cpsw_sepa':
				if ( $( 'form.woocommerce-checkout' ).length && 'no' === cpsw_global_settings.is_cart_amount_zero ) {
					return true;
				}

				$( '.woocommerce-error' ).remove();
				stripe.createPaymentMethod( {
					type: 'sepa_debit',
					sepa_debit: sepa,
					billing_details: $( 'form.woocommerce-checkout' ).length ? getBillingDetails() : currentUserBilling,
				} ).then( function( result ) {
					$( 'input.cpsw_payment_method' ).remove();
					// Handle result.error or result.paymentMethod
					if ( result.paymentMethod ) {
						paymentMethodSepa = result.paymentMethod.id;
						paymentForm.append(
							"<input type='hidden' class='cpsw_payment_method' name='payment_method_created' value='" +
							paymentMethodSepa +
							"'/>",
						);
						paymentForm.trigger( 'submit' );
					} else if ( result.error ) {
						$( 'form.woocommerce-checkout' ).unblock();
						logError( result.error );
						$( '.woocommerce-notices-wrapper:first-child' ).html( '<div class="woocommerce-error cpsw-errors">' + getStripeLocalizedMessage( result.error.code, result.error.message ) + '</div>' ).show();
						window.scrollTo( { top: 0, behavior: 'smooth' } );
						$( 'form#order_review' ).unblock();
						$( 'form#add_payment_method' ).unblock();
						return false;
					}
				} );
				break;
			case 'cpsw_stripe_element':
				if ( selectedPaymentElementType === 'cashapp' && getBillingDetails().address?.country?.toLowerCase() !== 'us' ) {
					$( '.woocommerce-notices-wrapper:first-child' ).html( '<div class="woocommerce-error cpsw-errors">' + getStripeLocalizedMessage( 'cashapp_country_error', null ) + '</div>' ).show();
					window.scrollTo( { top: 0, behavior: 'smooth' } );
					return false;
				}
				elements.submit();
				stripe.createPaymentMethod( {
					elements: elementsForPayment,
					params: {
						billing_details: $( 'form.woocommerce-checkout' ).length ? getBillingDetails() : currentUserBilling,
					},
				} ).then( function( result ) {
					$( '.woocommerce-error' ).remove();
					if ( result.paymentMethod ) {
						paymentElementMethod = result.paymentMethod.id;
						paymentType = result.paymentMethod.type;
						$( '.cpsw_payment_method' ).remove();
						paymentForm.append(
							"<input type='hidden' class='cpsw_payment_method' name='payment_method_created' value='" +
							paymentElementMethod +
							"'/><input type='hidden' class='cpsw_payment_type' name='selected_payment_type' value='" +
							paymentType +
							"'/>",
						);
						paymentForm.trigger( 'submit' );
					} else if ( result.error ) {
						$( 'form.woocommerce-checkout' ).unblock();
						$( 'form#order_review' ).unblock();
						$( 'form#add_payment_method' ).unblock();
						logError( result.error );
						$( '.woocommerce-notices-wrapper:first-child' ).html( '<div class="woocommerce-error cpsw-errors">' + getStripeLocalizedMessage( result.error.code, result.error.message ) + '</div>' ).show();
						window.scrollTo( { top: 0, behavior: 'smooth' } );
						return false;
					}
				} );
				break;
			default:
				break;
		}
		return true;
	}

	function confirmStripePayment( clientSecret, redirectURL, authenticationAlready = false ) {
		const wcCheckoutForm = $( 'form.woocommerce-checkout' );

		switch ( selectedGatewayId ) {
			case 'cpsw_stripe':
				stripe.confirmCardPayment( clientSecret, {} ).then( function( result ) {
					if ( result.error ) {
						// Show error to your customer (e.g., insufficient funds)
						$( '.woocommerce-error' ).remove();
						wcCheckoutForm.unblock();
						wcCheckoutForm.removeClass( 'processing' );
						logError( result.error );
						let errorCode = result.error.code;

						if ( 'card_declined' === result.error.code ) {
							errorCode = result.error.decline_code;
						}

						$( '.woocommerce-notices-wrapper:first-child' ).html( '<div class="woocommerce-error cpsw-errors">' + getStripeLocalizedMessage( errorCode, result.error.message ) + '</div>' ).show();
						window.scrollTo( { top: 0, behavior: 'smooth' } );
					} else {
						// The payment has been processed!
						if ( result.paymentIntent.status === 'succeeded' || result.paymentIntent.status === 'requires_capture' ) {
							window.location = redirectURL;
						}
					}
				} );
				break;
			case 'cpsw_ideal':
				stripe.confirmIdealPayment( clientSecret, {
					payment_method: {
						ideal,
						billing_details: getBillingDetails(),
					},
					return_url: homeURL + redirectURL,
				} ).then( function( result ) {
					if ( result.error ) {
						// Show error to your customer (e.g., insufficient funds)
						$( '.woocommerce-error' ).remove();
						wcCheckoutForm.unblock();
						logError( result.error );
						$( '.woocommerce-notices-wrapper:first-child' ).html( '<div class="woocommerce-error cpsw-errors">' + getStripeLocalizedMessage( result.error.code, result.error.message ) + '</div>' ).show();
						window.scrollTo( { top: 0, behavior: 'smooth' } );
					}
				} );
				break;
			case 'cpsw_alipay':
				stripe.confirmAlipayPayment( clientSecret, {
					payment_method: {
						billing_details: getBillingDetails(),
					},
					return_url: homeURL + redirectURL,
				} ).then( function( result ) {
					if ( result.error ) {
						// Show error to your customer (e.g., insufficient funds)
						$( '.woocommerce-error' ).remove();
						wcCheckoutForm.unblock();
						logError( result.error );
						$( '.woocommerce-notices-wrapper:first-child' ).html( '<div class="woocommerce-error cpsw-errors">' + getStripeLocalizedMessage( result.error.code, result.error.message ) + '</div>' ).show();
						window.scrollTo( { top: 0, behavior: 'smooth' } );
						wcCheckoutForm.removeClass( 'processing' );
					}
				} );
				break;
			case 'cpsw_klarna':
				stripe.confirmKlarnaPayment( clientSecret, {
					payment_method: {
						billing_details: getBillingDetails(),
					},
					return_url: homeURL + redirectURL,
				} ).then( function( result ) {
					if ( result.error ) {
						// Show error to your customer (e.g., insufficient funds)
						$( '.woocommerce-error' ).remove();
						wcCheckoutForm.unblock();
						logError( result.error );
						$( '.woocommerce-notices-wrapper:first-child' ).html( '<div class="woocommerce-error cpsw-errors">' + getStripeLocalizedMessage( result.error.code, result.error.message ) + '</div>' ).show();
						window.scrollTo( { top: 0, behavior: 'smooth' } );
						wcCheckoutForm.removeClass( 'processing' );
					}
				} );
				break;
			case 'cpsw_bancontact':
				stripe.confirmBancontactPayment( clientSecret, {
					payment_method: {
						billing_details: getBillingDetails(),
					},
					return_url: homeURL + redirectURL,
				} ).then( function( result ) {
					if ( result.error ) {
						// Show error to your customer (e.g., insufficient funds)
						$( '.woocommerce-error' ).remove();
						wcCheckoutForm.unblock();
						logError( result.error );
						$( '.woocommerce-notices-wrapper:first-child' ).html( '<div class="woocommerce-error cpsw-errors">' + getStripeLocalizedMessage( result.error.code, result.error.message ) + '</div>' ).show();
						window.scrollTo( { top: 0, behavior: 'smooth' } );
						wcCheckoutForm.removeClass( 'processing' );
					}
				} );
				break;
			case 'cpsw_sepa':
				if ( isSepaSaveCardChosen() || authenticationAlready ) {
					stripe.confirmSepaDebitPayment( clientSecret, {} ).then( function( result ) {
						if ( result.error ) {
							// Show error to your customer (e.g., insufficient funds)
							$( '.woocommerce-error' ).remove();
							$( 'form.woocommerce-checkout' ).unblock();
							logError( result.error );
							$( '.woocommerce-notices-wrapper:first-child' ).html( '<div class="woocommerce-error cpsw-errors">' + getStripeLocalizedMessage( result.error.code, result.error.message ) + '</div>' ).show();
							window.scrollTo( { top: 0, behavior: 'smooth' } );
						} else {
							// The payment has been processed!
							if ( result.paymentIntent.status === 'succeeded' || result.paymentIntent.status === 'processing' ) {
								$( '.woocommerce-error' ).remove();
								window.location = redirectURL;
							}
						}
					} );
				} else {
					stripe.confirmSepaDebitPayment( clientSecret, {
						payment_method: {
							sepa_debit: sepa,
							billing_details: getBillingDetails(),
						},
					} ).then( function( result ) {
						if ( result.error ) {
							// Show error to your customer (e.g., insufficient funds)
							$( '.woocommerce-error' ).remove();
							$( 'form.woocommerce-checkout' ).unblock();
							logError( result.error );
							$( '.woocommerce-notices-wrapper:first-child' ).html( '<div class="woocommerce-error cpsw-errors">' + getStripeLocalizedMessage( result.error.code, result.error.message ) + '</div>' ).show();
							window.scrollTo( { top: 0, behavior: 'smooth' } );
						} else {
							// The payment has been processed!
							if ( result.paymentIntent.status === 'succeeded' || result.paymentIntent.status === 'processing' ) {
								$( '.woocommerce-error' ).remove();
								window.location = redirectURL;
							}
						}
					} );
				}

				break;
			case 'cpsw_p24':
				stripe.confirmP24Payment(
					clientSecret,
					{
						payment_method: {
							billing_details: getBillingDetails(),
						},
						return_url: homeURL + redirectURL,
					},
				);
				break;
			case 'cpsw_wechat':
				stripe.confirmWechatPayPayment( clientSecret, {
					payment_method_options: {
						wechat_pay: {
							client: 'web',
						},
					},
				} ).then( function( result ) {
					if ( result.error ) {
						// Show error to your customer (e.g., insufficient funds)
						$( '.woocommerce-error' ).remove();
						wcCheckoutForm.unblock();
						logError( result.error );
						$( '.woocommerce-notices-wrapper:first-child' ).html( '<div class="woocommerce-error cpsw-errors">' + getStripeLocalizedMessage( result.error.code, result.error.message ) + '</div>' ).show();
						window.scrollTo( { top: 0, behavior: 'smooth' } );
					} else {
						// The payment has been processed!
						if ( result.paymentIntent.status === 'succeeded' ) {
							window.location = redirectURL;
						} else if ( result.paymentIntent.status === 'requires_payment_method' ) {
							// Show error when requires payment method or some how failed.
							$( '.woocommerce-error' ).remove();
							wcCheckoutForm.unblock();
							logError( result.paymentIntent.last_payment_error );
							$( '.woocommerce-notices-wrapper:first-child' ).html( '<div class="woocommerce-error cpsw-errors">' + getStripeLocalizedMessage( result.paymentIntent.last_payment_error.code, result.paymentIntent.last_payment_error.message ) + '</div>' ).show();
							window.scrollTo( { top: 0, behavior: 'smooth' } );
							wcCheckoutForm.removeClass( 'processing' );
						} else {
							wcCheckoutForm.removeClass( 'processing' );
							wcCheckoutForm.unblock();
						}
					}
				} );
				break;
			case 'cpsw_stripe_element':
				const redirect = selectedPaymentElementType === 'card' ? 'always' : 'if_required';
				stripe.confirmPayment( {
					elements: elementsForPayment,
					clientSecret,
					confirmParams: {
						return_url: homeURL + redirectURL,
						payment_method_data: {
							billing_details: getBillingDetails(),
						},
						payment_method_options: {
							wechat_pay: {
								client: 'web',
							},
							card: {
								setup_future_usage: 'off_session',
							},
						},
					},
					redirect,
				} ).then( function( result ) {
					if ( result.error ) {
						// Show error to your customer (e.g., insufficient funds)
						$( '.woocommerce-error' ).remove();
						wcCheckoutForm.unblock();
						logError( result.error );
						let errorMessage = result.error?.message;
						let errorCode = result.error?.code;

						if ( 'card_declined' === errorCode ) {
							errorCode = result.error?.decline_code;
						}

						errorMessage = errorMessage?.replace( new RegExp( '`billing_details\\[address\\]\\[country\\]`', 'g' ), 'Billing country' );

						$( '.woocommerce-notices-wrapper:first-child' ).html( '<div class="woocommerce-error cpsw-errors">' + getStripeLocalizedMessage( errorCode, errorMessage ) + '</div>' ).show();
						window.scrollTo( { top: 0, behavior: 'smooth' } );
						wcCheckoutForm.removeClass( 'processing' );
					}
					wcCheckoutForm.removeClass( 'processing' );
					wcCheckoutForm.unblock();
					paymentElementMethod = '';
				} );
				break;
			default:
				break;
		}
	}

	function getStripeLocalizedMessage( type, message ) {
		return ( null !== stripeLocalized[ type ] && undefined !== stripeLocalized[ type ] ) ? stripeLocalized[ type ] : message;
	}

	function getBillingDetails() {
		if ( ! $( 'form.woocommerce-checkout' ).length ) {
			return currentUserBilling;
		}

		const billingFirstName = document.getElementById( 'billing_first_name' );
		const billingLastName = document.getElementById( 'billing_last_name' );
		const billingEmail = document.getElementById( 'billing_email' );
		const billingPhone = document.getElementById( 'billing_phone' );
		const billingCountry = document.getElementById( 'billing_country' );
		const billingPostcode = document.getElementById( 'billing_postcode' );
		const billingCity = document.getElementById( 'billing_city' );
		const billingState = document.getElementById( 'billing_state' );
		const billingAddress1 = document.getElementById( 'billing_address_1' );
		const billingAddress2 = document.getElementById( 'billing_address_2' );
		const firstName = null !== billingFirstName ? billingFirstName.value : '';
		const lastName = null !== billingLastName ? billingLastName.value : '';

		const billingDetails = {
			name: firstName + ' ' + lastName,
			email: null !== billingEmail ? billingEmail.value : '',
			phone: null !== billingPhone ? billingPhone.value : '',
			address: {
				country: null !== billingCountry ? billingCountry.value : '',
				city: null !== billingCity ? billingCity.value : '',
				postal_code: null !== billingPostcode ? billingPostcode.value : '',
				state: null !== billingState ? billingState.value : '',
				line1: null !== billingAddress1 ? billingAddress1.value : '',
				line2: null !== billingAddress2 ? billingAddress2.value : '',
			},
		};

		return billingDetails;
	}

	function confirmCardSetup() {
		$.ajax( {
			type: 'POST',
			dataType: 'json',
			url: ajaxUrl,
			data: { action: 'create_setup_intent', _security: jsNonce, paymentMethod },
			beforeSend: () => {
				$( 'body' ).css( 'cursor', 'progress' );
			},
			success( response ) {
				if ( response.success === true ) {
					const clientSecret = response.data.client_secret;
					stripe.confirmCardSetup( clientSecret, { payment_method: paymentMethod } ).then( function() {
						paymentForm.trigger( 'submit' );
					} );
				} else if ( response.success === false ) {
					return false;
				}
				$( 'body' ).css( 'cursor', 'default' );
			},
			error() {
				$( 'body' ).css( 'cursor', 'default' );
				alert( 'Something went wrong!' );
			},
		} );
	}

	function selectedGateway() {
		const allPaymentMethods = [ 'cpsw_stripe', 'cpsw_alipay', 'cpsw_ideal', 'cpsw_klarna', 'cpsw_p24', 'cpsw_bancontact', 'cpsw_wechat', 'cpsw_sepa', 'cpsw_stripe_element' ];

		if ( 0 < $( '.wc_payment_method' ).length ) {
			const selectedPaymentMethod = $( '.wc_payment_method input[name="payment_method"]:checked' ).val();
			if ( -1 === $.inArray( selectedPaymentMethod, allPaymentMethods ) ) {
				return false;
			}
			return selectedGatewayId = selectedPaymentMethod;
		} else if ( 0 < $( '#payment_method_cpsw_stripe' ).length && $( '#payment_method_cpsw_stripe' ).is( ':checked' ) && 'cpsw_stripe' === $( '#payment_method_cpsw_stripe' ).val() ) {
			const selectedPaymentMethod = $( '.payment_methods input[name="payment_method"]:checked' ).val();
			if ( -1 === $.inArray( selectedPaymentMethod, allPaymentMethods ) ) {
				return false;
			}
			return selectedGatewayId = selectedPaymentMethod;
		} else if ( 0 < $( '#payment_method_cpsw_sepa' ).length && $( '#payment_method_cpsw_sepa' ).is( ':checked' ) && 'cpsw_sepa' === $( '#payment_method_cpsw_sepa' ).val() ) {
			const selectedPaymentMethod = $( '.payment_methods input[name="payment_method"]:checked' ).val();
			if ( -1 === $.inArray( selectedPaymentMethod, allPaymentMethods ) ) {
				return false;
			}
			return selectedGatewayId = selectedPaymentMethod;
		}
		return false;
	}

	function isAllowedBrand( brand ) {
		if ( 0 === allowedCards.length ) {
			isAllowedCard = true;
			return;
		}

		isAllowedCard = ( -1 === $.inArray( brand, allowedCards ) ) ? false : true;
	}

	function hideShowCard() {
		const isSavedCard = ( 'new' === $( "input[name='wc-cpsw-payment-token']:checked" ).val() ) ? false : true;
		if ( ! isSavedCard ) {
			$( '.cpsw-stripe-elements-form' ).fadeIn();
		} else {
			$( '.cpsw-stripe-elements-form' ).fadeOut();
		}
	}

	function hideShowSepaIBAN() {
		const isSavedSepaIBAN = ( 'new' === $( "input[name='wc-cpsw_sepa-payment-token']:checked" ).val() ) ? false : true;
		if ( ! isSavedSepaIBAN ) {
			$( '.cpsw_stripe_sepa_payment_form' ).fadeIn();
		} else {
			$( '.cpsw_stripe_sepa_payment_form' ).fadeOut();
		}
	}

	function hideShowPaymentElement() {
		const isSavedPaymentElement = ( 'new' === $( "input[name='wc-cpsw_stripe_element-payment-token']:checked" ).val() );
		if ( isSavedPaymentElement ) {
			$( '.cpsw_stripe_element_payment_form' ).show();
			savedPaymentElementMethod = false;
		} else {
			$( '.cpsw_stripe_element_payment_form' ).hide();
			savedPaymentElementMethod = true;
		}
	}

	function onHashChange() {
		if ( ! selectedGateway() ) {
			return;
		}
		const partials = window.location.hash.match(
			/^#?confirm-(pi|si)-([^:]+):(.+):(.+)$/,
		);

		if ( ! partials || 4 > partials.length ) {
			return;
		}

		const intentClientSecret = partials[ 2 ];
		const redirectURL = decodeURIComponent( partials[ 3 ] );

		// Cleanup the URL
		history.pushState( {}, '', window.location.pathname );
		confirmStripePayment( intentClientSecret, redirectURL );
	}

	function showSavedCards() {
		if ( $( '.payment_method_cpsw_stripe .wc-saved-payment-methods' ).length ) {
			$( '.payment_method_cpsw_stripe .wc-saved-payment-methods' ).show();
		}
	}

	function hideShowElements() {
		if ( 'new' === $( 'input[type=radio][name="wc-cpsw_stripe-payment-token"]:checked' ).val() ) {
			$( '.cpsw-stripe-elements-form' ).show();
			savedCard = false;
		} else {
			$( '.cpsw-stripe-elements-form' ).hide();
			savedCard = true;
		}
	}

	function logError( error ) {
		$.ajax( {
			type: 'POST',
			dataType: 'json',
			url: ajaxUrl,
			data: { action: 'cpsw_js_errors', _security: jsNonce, error },
			beforeSend: () => {
				$( 'body' ).css( 'cursor', 'progress' );
			},
			success( response ) {
				if ( response.success === true ) {
				} else if ( response.success === false ) {
					return response.message;
				}
				$( 'body' ).css( 'cursor', 'default' );
			},
			error() {
				$( 'body' ).css( 'cursor', 'default' );
				alert( 'Something went wrong!' );
			},
		} );
	}

	const processingSubmit = function( e ) {
		if ( ( 'yes' === cpsw_global_settings.is_cart_amount_zero || $( 'form#order_review' ).length || $( 'form#add_payment_method' ).length ) && 'cpsw_sepa' === selectedGateway() && ! isSepaSaveCardChosen() && '' === paymentMethodSepa ) {
			e.preventDefault();
			createStripePaymentMethod();

			return false;
		}

		if ( 'cpsw_stripe' === selectedGateway() && ! savedCard && '' === paymentMethod ) {
			if ( false === isAllowedCard ) {
				return false;
			}

			e.preventDefault();
			createStripePaymentMethod();

			return false;
		}

		if ( 'cpsw_stripe_element' === selectedGateway() && ! savedPaymentElementMethod && '' === paymentElementMethod ) {
			e.preventDefault();
			createStripePaymentMethod();

			return false;
		}

		return true;
	};

	if ( $( 'form.woocommerce-checkout' ).length ) {
		paymentForm = $( 'form.woocommerce-checkout' );
	}

	$( 'form.woocommerce-checkout' ).on( 'submit checkout_place_order_cpsw_ideal', function() {
		// check for iDEAL.
		if ( 'cpsw_ideal' === selectedGateway() ) {
			if ( '' === selectedIdealBank ) {
				$( '.cpsw_stripe_ideal_error' ).html( cpsw_global_settings.empty_bank_message );
				$( '.woocommerce-error' ).remove();
				$( 'form.woocommerce-checkout' ).unblock();
				$( '.woocommerce-notices-wrapper:first-child' ).html( '<div class="woocommerce-error cpsw-errors">' + cpsw_global_settings.empty_bank_message + '</div>' ).show();
				window.scrollTo( { top: 0, behavior: 'smooth' } );
				return false;
			}
			$( '.cpsw_stripe_ideal_error' ).html( '' );
		}
	} );

	$( 'form.woocommerce-checkout' ).on( 'submit checkout_place_order_cpsw_sepa', function() {
		if ( isSepaSaveCardChosen() ) {
			$( '.cpsw_stripe_sepa_error' ).html( '' );
			return true;
		}

		// check for SEPA.
		if ( ! sepaIBAN && 'cpsw_sepa' === selectedGateway() ) {
			$( '.woocommerce-error' ).remove();
			$( '.woocommerce-notices-wrapper:first-child' ).html( '<div class="woocommerce-error cpsw-errors">' + emptySepaIBANMessage + '</div>' ).show();
			window.scrollTo( { top: 0, behavior: 'smooth' } );
			$( '.cpsw_stripe_sepa_error' ).html( emptySepaIBANMessage );
			return false;
		}
		$( '.cpsw_stripe_sepa_error' ).html( '' );
	} );

	$( 'form.woocommerce-checkout' ).on( 'submit checkout_place_order_cpsw_p24', function() {
		// check for P24.
		if ( 'cpsw_p24' === selectedGateway() ) {
			if ( '' === selectedP24Bank ) {
				$( '.cpsw_stripe_p24_error' ).html( cpsw_global_settings.empty_bank_message );
				$( '.woocommerce-error' ).remove();
				$( 'form.woocommerce-checkout' ).unblock();
				$( '.woocommerce-notices-wrapper:first-child' ).html( '<div class="woocommerce-error cpsw-errors">' + cpsw_global_settings.empty_bank_message + '</div>' ).show();
				window.scrollTo( { top: 0, behavior: 'smooth' } );
				return false;
			}
			$( '.cpsw_stripe_p24_error' ).html( '' );
		}
	} );

	$( 'form.woocommerce-checkout' ).on( 'submit checkout_place_order_cpsw_stripe checkout_place_order_cpsw_sepa checkout_place_order_cpsw_stripe_element', processingSubmit );

	if ( $( 'form#order_review' ).length ) {
		showSavedCards();
		paymentForm = $( 'form#order_review' );
	}

	if ( $( 'form#add_payment_method' ).length ) {
		isAllowedCard = true;
		paymentForm = $( 'form#add_payment_method' );
	}

	$( 'form#order_review, form#add_payment_method' ).on( 'submit', processingSubmit );

	/**
	 * mount card element after woocommerce ajax call
	 */
	$( document.body ).on( 'updated_checkout', function() {
		showSavedCards();
		$( 'input[type=radio][name="wc-cpsw_stripe-payment-token"]' ).change( function() {
			hideShowElements();
		} );
		$( 'input[type=radio][name="wc-cpsw_stripe_element-payment-token"]' ).change( function() {
			hideShowPaymentElement();
		} );
		mountGateways();
		$( "input[name='wc-cpsw-payment-token']" ).click( function() {
			hideShowCard();
		} );
	} );

	$( "input[name='wc-cpsw_sepa-payment-token']" ).click( function() {
		hideShowSepaIBAN();
	} );

	$( "input[type=radio][name='wc-cpsw_stripe_element-payment-token']" ).change( function() {
		hideShowPaymentElement();
	} );

	$( 'input[type=radio][name="wc-cpsw_stripe-payment-token"]' ).change( function() {
		hideShowElements();
	} );
	$( '.cpsw-stripe-elements-form' ).hide();

	if ( $( 'form#order_review' ).length && window.location.hash && typeof window.location.hash === 'string' ) {
		const partials = window.location.hash.match(
			/^#?confirm-(pi|si)-([^:]+):(.+):(.+)$/,
		);

		if ( partials ) {
			try {
				if ( ! partials || 4 > partials.length ) {
					return;
				}

				selectedGatewayId = partials[ 4 ];
				const intentClientSecret = partials[ 2 ];
				const redirectURL = decodeURIComponent( partials[ 3 ] );

				// Cleanup the URL
				history.pushState( {}, '', window.location.pathname + window.location.search );
				confirmStripePayment( intentClientSecret, redirectURL, true );
			} catch ( err ) {

			}
		}
	}

	// Check if the payment method is available on the checkout page
	if ( $( '.cpsw_stripe_element_payment_form' ).length ) {
		// Adding custom class to the body tag
		$( 'body' ).addClass( 'cpsw_stripe_option_enabled' );
	}

	window.addEventListener( 'hashchange', onHashChange );
	mountGateways();
	if ( $( 'form#order_review' ).length ) {
		hideShowCard();
		hideShowSepaIBAN();
		hideShowElements();
		hideShowPaymentElement();
	}
}( jQuery ) );
