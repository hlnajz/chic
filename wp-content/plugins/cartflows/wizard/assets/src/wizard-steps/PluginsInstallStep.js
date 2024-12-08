import React, { useState, useEffect, useCallback } from 'react';
import { __ } from '@wordpress/i18n';
import { useHistory } from 'react-router-dom';
import { useStateValue } from '../utils/StateProvider';

function PluginsInstallStep() {
	const [ processing, setProcessing ] = useState( {
		isProcessing: false,
		buttonText: __( 'Install & Activate', 'cartflows' ),
	} );

	const { buttonText } = processing;

	const [ { action_button, selected_page_builder }, dispatch ] =
		useStateValue();
	const history = useHistory();

	const required_plugins = cartflows_wizard.plugins;
	let installed_plugins_count = 0;

	/**
	 * Dispatcher function to change the button text on wizard footer.
	 */
	const dispatchChangeButtonText = useCallback( ( data ) => {
		dispatch( {
			status: 'SET_NEXT_STEP',
			action_button: data,
		} );
	}, [] );

	/**
	 * Function used to change the footer button text and the primary button text while processing the request.
	 */
	const handleOnClickProcessing = function () {
		const processing_buttonText = __(
			'Installing Required Plugins',
			'cartflows'
		);

		setProcessing( {
			isProcessing: true,
			buttonText: processing_buttonText,
		} );

		dispatchChangeButtonText( {
			button_text: processing_buttonText,
			button_class: 'is-loading',
		} );

		dispatch( {
			status: 'PROCESSING',
		} );
	};

	useEffect( () => {
		dispatchChangeButtonText( {
			button_text: __( 'Install & Activate', 'cartflows' ),
			button_class: '',
		} );

		const installPluginsSuccess = document.addEventListener(
			'wcf-plugins-install-success',
			function () {
				setProcessing( false );
				if ( 'bricks-builder' === selected_page_builder ) {
					history.push( {
						pathname: 'index.php',
						search: `?page=cartflow-setup&step=optin`,
					} );
				} else {
					history.push( {
						pathname: 'index.php',
						search: `?page=cartflow-setup&step=store-checkout`,
					} );
				}

				dispatch( {
					status: 'RESET',
				} );
			},
			false
		);

		const installPluginsProcess = document.addEventListener(
			'wcf-install-require-plugins-processing',
			function () {
				handleOnClickProcessing();
			},
			false
		);

		return () => {
			document.removeEventListener(
				'wcf-plugins-install-success',
				installPluginsSuccess
			);
			document.removeEventListener(
				'wcf-install-require-plugins-processing',
				installPluginsProcess
			);
		};
	}, [ dispatchChangeButtonText ] );

	const handleRedirection = function ( e ) {
		e.preventDefault();
		setProcessing( {
			isProcessing: true,
			buttonText: __( 'Continuing…', 'cartflows' ),
		} );

		history.push( {
			pathname: 'index.php',
			search: `?page=cartflow-setup&step=store-checkout`,
		} );

		setProcessing( false );
	};

	return (
		<div className="wcf-container wcf-wizard--plugin-install">
			<div className="wcf-row mt-12">
				<div className="bg-white rounded mx-auto px-11 text-center">
					<span className="text-sm font-medium text-primary-600 mb-10 text-center block tracking-[.24em] uppercase">
						{ __( 'Step 3 of 6', 'cartflows' ) }
					</span>
					<h1 className="wcf-step-heading mb-4">
						<span className="flex items-center justify-center gap-3">
							{ __( 'Great job!', 'cartflows' ) }
						</span>
						{ __(
							"Now let's install some required plugins.",
							'cartflows'
						) }
					</h1>
					<p className="text-center overflow-hidden max-w-2xl mb-10 mx-auto text-lg font-normal text-slate-500 block">
						{ __(
							"Since CartFlows uses WooCommerce, we'll set it up for you along with Cart Abandonment and Stripe Payments so you can recover abandoned orders and easily accept payments.",
							'cartflows'
						) }
					</p>
					<p className="text-center overflow-hidden max-w-2xl mb-6 mx-auto text-lg font-normal text-slate-500 block">
						{ __(
							'The following plugins will be installed and activated for you:',
							'cartflows'
						) }
					</p>

					<div className="flex justify-center w-11/12 text-left text-base text-[#1F2937] mx-auto">
						<fieldset className="">
							<form
								method="post"
								className="wcf-install-plugin-form flex gap-3 space-y-1 text-gray-500 list-inside dark:text-gray-400"
							>
								{ required_plugins.map( ( plugin, index ) => {
									const plugin_name = plugin.name,
										plugin_stat = plugin.status;

									// Skip the display of Spectra plugin if default page builder is set other than gutenberg.
									if (
										'gutenberg' !== selected_page_builder &&
										'ultimate-addons-for-gutenberg' ===
											plugin.slug
									) {
										return '';
									}

									if ( 'active' === plugin_stat ) {
										installed_plugins_count++;
									}

									return (
										<div
											className="relative !m-0"
											key={ index }
										>
											<div className="!m-0 text-sm leading-6 py-2 px-3 border border-gray-200 rounded-md">
												<input
													id={ plugin.slug }
													aria-describedby={
														plugin.slug
													}
													name="required_plugins[]"
													type="checkbox"
													data-status={ plugin_stat }
													data-slug={ plugin.slug }
													className="!hidden !m-0 !h-5 !w-5 !rounded !border-gray-300 !text-[#f06434] focus:!ring-offset-2 focus:!ring-2 focus:!ring-[#f06434] checked:bg-[#f06434]"
													defaultChecked={ true }
													disabled={
														'active' === plugin_stat
													}
												/>

												<label
													htmlFor={ plugin.slug }
													className="font-medium text-slate-800 capitalize"
												>
													{ 'ultimate-addons-for-gutenberg' ===
													plugin
														? __(
																'Spectra',
																'cartflows'
														  )
														: plugin_name }{ ' ' }
													<span className="capitalize text-xs italic sr-only">
														({ plugin_stat })
													</span>
												</label>
											</div>
										</div>
									);
								} ) }
							</form>
						</fieldset>
					</div>

					<div className="wcf-action-buttons mt-[40px] flex justify-center">
						{ Object.keys( required_plugins ).length ===
						installed_plugins_count ? (
							<button
								className={ `installed-required-plugins wcf-wizard--button ${
									action_button.button_class
										? action_button.button_class
										: ''
								}` }
								onClick={ handleRedirection }
							>
								{ __( 'Continue', 'cartflows' ) }
								<svg
									xmlns="http://www.w3.org/2000/svg"
									className="w-5 mt-0.5 ml-1.5 fill-[#243c5a]"
									fill="none"
									viewBox="0 0 24 24"
									stroke="currentColor"
									strokeWidth={ 2 }
								>
									<path
										strokeLinecap="round"
										strokeLinejoin="round"
										d="M17 8l4 4m0 0l-4 4m4-4H3"
									/>
								</svg>
							</button>
						) : (
							<button
								className={ `install-required-plugins wcf-wizard--button ${
									action_button.button_class
										? action_button.button_class
										: ''
								}` }
							>
								{ buttonText }
							</button>
						) }
					</div>
				</div>
			</div>
		</div>
	);
}

export default PluginsInstallStep;
