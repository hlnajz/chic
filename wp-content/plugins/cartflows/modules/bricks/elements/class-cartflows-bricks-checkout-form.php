<?php
/**
 * Bricks theme compatibility
 *
 * @package CartFlows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class for Bricks theme compatibility
 */
class Cartflows_Bricks_Checkout_Form extends \Bricks\Element {
	/**
	 * Category of the element.
	 *
	 * @var instance
	 */
	public $category = 'CartFlows';

	/**
	 * Name of the element.
	 *
	 * @var instance
	 */

	public $name = 'bricks-checkout-form';

	/**
	 * Icon of the element.
	 *
	 * @var instance
	 */
	public $icon = 'fa-solid fa-cart-shopping';

	/**
	 * Label of the element.
	 *
	 * @var instance
	 */
	public function get_label() {
		return esc_html__( 'Checkout Form', 'cartflows' );
	}

	/**
	 * Function to get layout types.
	 *
	 * @since 1.6.15
	 * @access protected
	 */
	protected function get_layout_types() {

		$layout_options = array();

		if ( ! _is_cartflows_pro() ) {
			$layout_options = array(
				'modern-checkout'    => __( 'Modern Checkout', 'cartflows' ),
				'modern-one-column'  => esc_html__( 'Modern One Column', 'cartflows' ),
				'one-column'         => esc_html__( 'One Column', 'cartflows' ),
				'two-column'         => esc_html__( 'Two Column', 'cartflows' ),
				'two-step'           => esc_html__( 'Two Step ( PRO )', 'cartflows' ),
				'multistep-checkout' => esc_html__( 'MultiStep Checkout ( PRO )', 'cartflows' ),
			);
		} else {
			$layout_options = array(
				'modern-checkout'    => esc_html__( 'Modern Checkout', 'cartflows' ),
				'modern-one-column'  => esc_html__( 'Modern One Column', 'cartflows' ),
				'one-column'         => esc_html__( 'One Column', 'cartflows' ),
				'two-column'         => esc_html__( 'Two Column', 'cartflows' ),
				'two-step'           => esc_html__( 'Two Step', 'cartflows' ),
				'multistep-checkout' => esc_html__( 'MultiStep Checkout', 'cartflows' ),
			);
		}

		return $layout_options;
	}
	
	/**
	 * Global GCP Primary Theme Color
	 *
	 * @access public
	 *
	 * @var string $gcp_primary_theme_color Primary Color.
	 * @return string $gcp_primary_theme_color Primary Color.
	 */
	public static $gcp_primary_theme_color = '';


	/**
	 * Global Primary Text Color
	 *
	 * @var string string $gcp_primary_text_color Primary Text Color.
	 * @return string $gcp_primary_text_color Primary Text Color.
	 */
	public static $gcp_primary_text_color = '';

	/**
	 * Global Secondary Color
	 *
	 * @var string $gcp_secondary_theme_color Secondary Color.
	 * @return string $gcp_secondary_theme_color Secondary Color.
	 */
	public static $gcp_secondary_theme_color = '';


	/**
	 * Register General Controls.
	 *
	 * @since 1.6.15
	 * @access protected
	 */
	protected function register_general_content_controls() {

		$this->controls['layout'] = array(
			'label'         => __( 'Layout', 'cartflows' ),
			'type'          => 'select',
			'default'       => 'modern-checkout',
			'options'       => $this->get_layout_types(),
			'tab'           => 'content',
			'reload'        => true,
			'reloadScripts' => true,
		);

		if ( ! _is_cartflows_pro() ) {

			$this->controls['layout_upgrade_pro'] = array(
			
				'type'     => 'raw',
				/* translators: %s admin link */
				'label'    => sprintf(
					/* translators: %s is the URL for upgrading */
					__( 'This feature is available in the CartFlows higher plan. <a href="%1$s" target="_blank" rel="noopener">%2$s</a>.', 'cartflows' ),
					esc_url( CARTFLOWS_DOMAIN_URL ),
					__( 'Upgrade Now!', 'cartflows' )
				),
				'required' => array( 'layout', '=', array( 'multistep-checkout', 'two-step' ) ),
				'tab'      => 'content',
				
			);
		}
	}

	/**
	 * Register General Style Controls.
	 *
	 * @since 1.6.15
	 * @access protected
	 */
	protected function register_global_style_controls() {
		
		$this->controls['global_typography'] = array(
			'label' => esc_html__( ' Global Text Typography', 'cartflows' ),
			'type'  => 'typography',
			'tab'   => 'style',
			'group' => 'section_general_style_fields',
			'css'   => array(
				array(
					'property' => 'font',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce a',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-embed-checkout-form',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-embed-checkout-form p',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-embed-checkout-form .woocommerce-checkout .woocommerce-privacy-policy-text p',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-embed-checkout-form .woocommerce-checkout #payment .payment_methods a',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-embed-checkout-form span:not( .optional )',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-embed-checkout-form .woocommerce form .form-row input.input-text:focus',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-embed-checkout-form .woocommerce form .form-row textarea:focus',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-embed-checkout-form .woocommerce #order_review .wcf-custom-coupon-field input.input-text:focus',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-steps .step-one.wcf-current:before',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-steps .step-two.wcf-current:before',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-note:before',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-embed-checkout-form-two-step .woocommerce .wcf-embed-checkout-form-nav-btns .wcf-next-button',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-note',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-embed-checkout-form .wcf-custom-coupon-field button.wcf-submit-coupon',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-embed-checkout-form .woocommerce #order_review button',
				),
				array(
					'property' => 'font',
					'selector' => 'body .wcf-pre-checkout-offer-wrapper #wcf-pre-checkout-offer-content button.wcf-pre-checkout-offer-btn',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form label',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-embed-checkout-form .woocommerce #payment [type="radio"]:checked + label, .wcf-embed-checkout-form .woocommerce #payment [type="radio"]:not( :checked ) + label',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-embed-checkout-form .woocommerce #shipping_method [type="radio"]:checked + label, .wcf-embed-checkout-form .woocommerce #shipping_method [type="radio"]:not( :checked ) + label',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .wcf-logged-in-customer-info',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form h3',
				),
				
			),
		);

		$this->controls['global_primary_color'] = array(
			'label' => esc_html__( ' Global Primary Color', 'cartflows' ),
			'type'  => 'color',
			'tab'   => 'style',
			'group' => 'section_general_style_fields',
			'css'   => array(
				array(
					'property' => 'background-color',
					'selector' => '.wcf-embed-checkout-form-two-step .woocommerce .wcf-embed-checkout-form-nav-btns .wcf-next-button',
				),
				
				array(
					'property' => 'background-color',
					'selector' => '.wcf-embed-checkout-form .wcf-custom-coupon-field button.wcf-submit-coupon',
				),
				array(
					'property' => 'background-color',
					'selector' => '.wcf-embed-checkout-form .woocommerce #order_review button',
				),
				array(
					'property' => 'background-color',
					'selector' => 'body .wcf-pre-checkout-offer-wrapper #wcf-pre-checkout-offer-content button.wcf-pre-checkout-offer-btn',
				),
				array(
					'property' => 'background-color',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce #payment input[type="radio"]:checked::before',
				),
				array(
					'property' => 'background-color',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce #order_review input[type="radio"]:checked::before',
				),
				array(
					'property' => 'background-color',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce .wcf-col2-set input[type="radio"]:checked::before',
				),
				array(
					'property' => 'background-color',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce .woocommerce-checkout #payment button',
				),
				array(
					'property' => 'background-color',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce .wcf-customer-login-section__login-button',
				),
				array(
					'property' => 'background-color',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce #order_review button',
				),
				array(
					'property' => 'background-color',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .wcf-custom-coupon-field button.wcf-submit-coupon',
				),
				array(
					'property' => 'background-color',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce #order_review button.wcf-btn-small',
				),
				array(
					'property' => 'background-color',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce-checkout form.woocommerce-form-login .button',
				),
				array(
					'property' => 'background-color',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce-checkout form.checkout_coupon .button',
				),
				array(
					'property' => 'background-color',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form-two-step .woocommerce .wcf-embed-checkout-form-nav-btns .wcf-next-button',
				),
				array(
					'property' => 'background-color',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout.wcf-modern-skin-multistep .woocommerce form .wcf-multistep-nav-btn-group .wcf-multistep-nav-next-btn',
				),
				array(
					'property' => 'background-color',
					'selector' => 'body .wcf-pre-checkout-offer-wrapper #wcf-pre-checkout-offer-content button.wcf-pre-checkout-offer-btn',
				),
				array(
					'property' => 'color',
					'selector' => '.wcf-bricks-checkout-form  .wcf-embed-checkout-form .woocommerce form .form-row .required',
				),
				
			),
		);


	}

	/**
	 * Register Heading Style Controls.
	 *
	 * @since 1.6.15
	 * @access protected
	 */
	protected function register_heading_style_controls() {

		$this->controls['heading_typography'] = array(
			'label' => __( 'Typography', 'cartflows' ),
			'type'  => 'typography',
			'tab'   => 'style',
			'group' => 'section_heading_style_fields',
			'css'   => array(
				array(
					'property' => 'font',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce h3',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce h3 span',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce-checkout #order_review_heading',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-steps .step-name',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce .col2-set .col-1 h3',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce .col2-set .col-2 h3',
				),
				
			),
		);
		
	}

	/**
	 * Register Input Fields Style Controls.
	 *
	 * @since 1.6.15
	 * @access protected
	 */
	protected function register_input_style_controls() {
		
		$this->controls['input_skins'] = array(
			'label'   => __( 'Style', 'cartflows' ),
			'type'    => 'select',
			'tab'     => 'style',
			'default' => 'default',
			'group'   => 'input_section',
			'options' => $this->get_skin_types(),
		);
		
		$this->controls['input_typography'] = array(
			'label' => __( 'Typography', 'cartflows' ),
			'type'  => 'typography',
			'tab'   => 'style',
			'group' => 'input_section',
			'css'   => array(
				array(
					'property' => 'font',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce form .form-row input.input-text::placeholder',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce form .form-row input.input-text',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce form .form-row textarea',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .select2-container--default .select2-selection--single',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce form .form-row select.select',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce .col2-set .col-1',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce .col2-set .col-2',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce form p.form-row label',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce #payment [type="radio"]:checked + label',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce #payment [type="radio"]:not(:checked) + label',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce form .form-row select',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-embed-checkout-form .woocommerce #order_review .wcf-custom-coupon-field input[type="text"]',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form #order_review .wcf-custom-coupon-field input[type="text"]::placeholder',
				),
			),
		);

		$this->controls['label_color'] = array(
			'label' => __( 'Label Color', 'cartflows' ),
			'type'  => 'color',
			'tab'   => 'style',
			'group' => 'input_section',
			'css'   => array(
				array(
					'property' => 'color',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce-checkout label',
				),
				array(
					'property' => 'color',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form-modern-checkout .woocommerce-checkout label',
				),
			),
		);

		$this->controls['input_bgcolor'] = array(
			'label'   => __( 'Field Background Color', 'cartflows' ),
			'type'    => 'color',
			'tab'     => 'style',
			'default' => '',
			'group'   => 'input_section',
			'css'     => array(
				array(
					'property' => 'background-color',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce form .form-row input.input-text',
				),
				array(
					'property' => 'background-color',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce form .form-row textarea',
				),
				array(
					'property' => 'background-color',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce form .form-row select',
				),
				array(
					'property' => 'background-color',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce .wcf-custom-coupon-field .wcf-coupon-code-input',
				),
			),
		);

		$this->controls['input_color'] = array(
			'label' => __( 'Input Text / Placeholder Color', 'cartflows' ),
			'type'  => 'color',
			'tab'   => 'style',
			'group' => 'input_section',
			'css'   => array(
				array(
					'property' => 'color',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce form .form-row input.input-text::placeholder',
				),
				array(
					'property' => 'color',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce form .form-row input.input-text',
				),
				array(
					'property' => 'color',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce form .form-row textarea',
				),
				array(
					'property' => 'color',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce form .form-row select',
				),
				array(
					'property' => 'color',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form #order_review .wcf-custom-coupon-field input[type="text"]::placeholder',
				),
			),
		);

		
		$this->controls['input_border_style'] = array(
			'label'       => __( 'Border Style', 'cartflows' ),
			'type'        => 'select',
			'label_block' => false,
			'default'     => '',
			'options'     => array(
				''       => __( 'Inherit', 'cartflows' ),
				'solid'  => __( 'Solid', 'cartflows' ),
				'double' => __( 'Double', 'cartflows' ),
				'dotted' => __( 'Dotted', 'cartflows' ),
				'dashed' => __( 'Dashed', 'cartflows' ),
			),
			'group'       => 'input_section',
			'css'         => array( 
				array(
					'property' => 'border-style',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form #order_review .wcf-custom-coupon-field input[type="text"]',
				),
				array(
					'property' => 'border-style',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce form .form-row input.input-text',
				),
				array(
					'property' => 'border-style',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce form .form-row textarea',
				),
				array(
					'property' => 'border-style',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .select2-container--default .select2-selection--single',
				),
				array(
					'property' => 'border-style',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce form .form-row select.select',
				),
				array(
					'property' => 'border-style',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce form .form-row select',
				),

			),
		);

		$this->controls['input_border_size'] = array(
			'label'       => __( 'Border Width', 'cartflows' ),
			'type'        => 'dimensions',
			'label_block' => false,
			'default'     => '',
			'group'       => 'input_section',
			'css'         => array( 
				array(
					'property' => 'border-width',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form #order_review .wcf-custom-coupon-field input[type="text"]',
				),
				array(
					'property' => 'border-width',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce form .form-row input.input-text',
				),
				array(
					'property' => 'border-width',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce form .form-row textarea',
				),
				array(
					'property' => 'border-width',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .select2-container--default .select2-selection--single',
				),
				array(
					'property' => 'border-width',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce form .form-row select.select',
				),
				array(
					'property' => 'border-width',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce form .form-row select',
				),

			),
		);

		$this->controls['input_border_color'] = array(
			'label'       => __( 'Border Color', 'cartflows' ),
			'type'        => 'color',
			'label_block' => false,
			'default'     => '',
			'group'       => 'input_section',
			'css'         => array( 
				array(
					'property' => 'border-color',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form #order_review .wcf-custom-coupon-field input[type="text"]',
				),
				array(
					'property' => 'border-color',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce form .form-row input.input-text',
				),
				array(
					'property' => 'border-color',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce form .form-row textarea',
				),
				array(
					'property' => 'border-color',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .select2-container--default .select2-selection--single',
				),
				array(
					'property' => 'border-color',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce form .form-row select.select',
				),
				array(
					'property' => 'border-color',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce form .form-row select',
				),

			),
		);

		$this->controls['input_radius'] = array(
			'label'       => __( 'Rounded Corners', 'cartflows' ),
			'type'        => 'dimensions',
			'label_block' => false,
			'default'     => '',
			'group'       => 'input_section',
			'css'         => array( 
				array(
					'property' => 'border-radius',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form #order_review .wcf-custom-coupon-field input[type="text"]',
				),
				array(
					'property' => 'border-radius',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce form .form-row input.input-text',
				),
				array(
					'property' => 'border-radius',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce form .form-row textarea',
				),
				array(
					'property' => 'border-radius',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .select2-container--default .select2-selection--single',
				),
				array(
					'property' => 'border-radius',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce form .form-row select.select',
				),
				array(
					'property' => 'border-radius',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce form .form-row select',
				),

			),
		);
	}

	/**
	 * Register Button Style Controls.
	 *
	 * @since 1.6.15
	 * @access protected
	 */
	protected function register_button_style_controls() {
		
		$this->controls['buttons_typography'] = array(
			'label' => __( 'Typography', 'cartflows' ),
			'type'  => 'typography',
			'tab'   => 'style',
			'group' => 'button_section',
			'css'   => array(
				array(
					'property' => 'font',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce .woocommerce-checkout #payment button.button',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce .wcf-customer-login-section__login-button',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce #order_review button',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce #payment #place_order:before',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .wcf-custom-coupon-field button.wcf-submit-coupon',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce #order_review button.wcf-btn-small',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce-checkout form.woocommerce-form-login .button',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce-checkout form.checkout_coupon .button',
				),

				array(
					'property' => 'font',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form-two-step .woocommerce .wcf-embed-checkout-form-nav-btns .wcf-next-button',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout.wcf-modern-skin-multistep .woocommerce form .wcf-multistep-nav-btn-group .wcf-multistep-nav-next-btn',
				),
				array(
					'property' => 'font',
					'selector' => 'body .wcf-pre-checkout-offer-wrapper #wcf-pre-checkout-offer-content button.wcf-pre-checkout-offer-btn',
				),
			),
		);
	 
		$this->controls['btn_background_color'] = array(
			'label' => __( 'Background Color', 'cartflows' ),
			'type'  => 'color',
			'tab'   => 'style',
			'group' => 'button_section',
			'css'   => array(
				array(
					'property' => 'background-color',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce .woocommerce-checkout #payment button',
				),
				array(
					'property' => 'background-color',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce .wcf-customer-login-section__login-button',
				),
				array(
					'property' => 'background-color',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce #order_review button',
				),
				array(
					'property' => 'background-color',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .wcf-custom-coupon-field button.wcf-submit-coupon',
				),
				array(
					'property' => 'background-color',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce #order_review button.wcf-btn-small',
				),
				array(
					'property' => 'background-color',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce-checkout form.woocommerce-form-login .button',
				),
				array(
					'property' => 'background-color',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce-checkout form.checkout_coupon .button',
				),
				array(
					'property' => 'background-color',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form-two-step .woocommerce .wcf-embed-checkout-form-nav-btns .wcf-next-button',
				),
				array(
					'property' => 'background-color',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout.wcf-modern-skin-multistep .woocommerce form .wcf-multistep-nav-btn-group .wcf-multistep-nav-next-btn',
				),
				array(
					'property' => 'background-color',
					'selector' => 'body .wcf-pre-checkout-offer-wrapper #wcf-pre-checkout-offer-content button.wcf-pre-checkout-offer-btn',
				),
			),
		);
		
		$this->controls['btn_border'] = array(
			'label' => __( 'Border', 'cartflows' ),
			'type'  => 'border',
			'tab'   => 'style',
			'group' => 'button_section',
			'css'   => array(
				array(
					'property' => 'border',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce .woocommerce-checkout #payment button',
				),
				array(
					'property' => 'border',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce .wcf-customer-login-section__login-button',
				),
				array(
					'property' => 'border',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce #order_review button',
				),
				array(
					'property' => 'border',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .wcf-custom-coupon-field button.wcf-submit-coupon',
				),
				array(
					'property' => 'border',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce #order_review button.wcf-btn-small',
				),
				array(
					'property' => 'border',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce-checkout form.woocommerce-form-login .button',
				),
				array(
					'property' => 'border',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce-checkout form.checkout_coupon .button',
				),
				array(
					'property' => 'border',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form-two-step .woocommerce .wcf-embed-checkout-form-nav-btns .wcf-next-button',
				),
				array(
					'property' => 'border',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout.wcf-modern-skin-multistep .woocommerce form .wcf-multistep-nav-btn-group .wcf-multistep-nav-next-btn',
				),
				array(
					'property' => 'border',
					'selector' => 'body .wcf-pre-checkout-offer-wrapper #wcf-pre-checkout-offer-content button.wcf-pre-checkout-offer-btn',
				),
			),
		);

		$this->controls['button_box_shadow'] = array(
			'label' => __( 'Box Shadow', 'cartflows' ),
			'type'  => 'box-shadow',
			'tab'   => 'style',
			'group' => 'button_section',
			'css'   => array(
				array(
					'property' => 'box-shadow',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce .woocommerce-checkout #payment button',
				),
				array(
					'property' => 'box-shadow',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce .wcf-customer-login-section__login-button',
				),
				array(
					'property' => 'box-shadow',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce #order_review button',
				),
				array(
					'property' => 'box-shadow',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .wcf-custom-coupon-field button.wcf-submit-coupon',
				),
				array(
					'property' => 'box-shadow',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce #order_review button.wcf-btn-small',
				),
				array(
					'property' => 'box-shadow',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce-checkout form.woocommerce-form-login .button',
				),
				array(
					'property' => 'box-shadow',
					'selector' => '.wcf-embed-checkout-form .woocommerce-checkout form.checkout_coupon .button',
				),
				array(
					'property' => 'box-shadow',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form-two-step .woocommerce .wcf-embed-checkout-form-nav-btns .wcf-next-button',
				),
				array(
					'property' => 'box-shadow',
					'selector' => '.wcf-bricks-checkout-form .wcf-bricks-checkout-form .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout.wcf-modern-skin-multistep .woocommerce form .wcf-multistep-nav-btn-group .wcf-multistep-nav-next-btn',
				),
				array(
					'property' => 'box-shadow',
					'selector' => 'body .wcf-pre-checkout-offer-wrapper #wcf-pre-checkout-offer-content button.wcf-pre-checkout-offer-btn',
				),
				array(
					'property' => 'box-shadow',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form.wcf-embed-checkout-form-modern-checkout.wcf-modern-skin-multistep .woocommerce form .wcf-multistep-nav-btn-group .wcf-multistep-nav-next-btn',
				),
			),
		);

		
	}

	/**
	 * Register Sections error Controls.
	 *
	 * @since 1.6.15
	 * @access protected
	 */
	protected function register_error_style_controls() {

		
		$this->controls['error_fields_heading'] = array(
			'label'       => __( 'Field Validation', 'cartflows' ),
			'type'        => 'heading',
			'label_block' => true,
			'group'       => 'section_error_style_fields',
		);

		$this->controls['field_label_color'] = array(
			'label'       => __( 'Label Color', 'cartflows' ),
			'type'        => 'color',
			'label_block' => true,
			'group'       => 'section_error_style_fields',
			'css'         => array(
				array(
					'property' => 'color',
					'selector' => '.woocommerce-checkout .woocommerce-invalid label',
				),
				array(
					'property' => 'color',
					'selector' => '.wcf-embed-checkout-form .woocommerce form p.form-row.woocommerce-invalid label',
				),
				array(
					'property' => 'color',
					'selector' => '.woocommerce form .form-row.woocommerce-invalid label',
				),
			),

		);
		
		$this->controls['error_field_border_color'] = array(
			'selector'    => '.select2-container--default.field-required',
			'label'       => __( 'Field Border Color', 'cartflows' ),
			'type'        => 'color',
			'label_block' => true,
			'group'       => 'section_error_style_fields',
			'css'         => array(
				array(
					'property' => 'border-color',
					'selector' => '.select2-container--default.field-required .select2-selection--single',
				),
				array(
					'property' => 'border-color',
					'selector' => '.woocommerce form .form-row input.input-text.field-required',
				),
				array(
					'property' => 'border-color',
					'selector' => '.woocommerce form .form-row textarea.input-text.field-required',
				),
				array(
					'property' => 'border-color',
					'selector' => '.woocommerce #order_review .input-text.field-required',
				),
				array(
					'property' => 'border-color',
					'selector' => '.woocommerce form .form-row.woocommerce-invalid .select2-container',
				),
				array(
					'property' => 'border-color',
					'selector' => '.woocommerce form .form-row.woocommerce-invalid input.input-text',
				),
				array(
					'property' => 'border-color',
					'selector' => '.woocommerce form .form-row.woocommerce-invalid select',
				),
			),
		);

		$this->controls['error_fields_section'] = array(
			'label'       => __( 'Error Messages', 'cartflows' ),
			'type'        => 'heading',
			'label_block' => true,
			'group'       => 'section_error_style_fields',
		);

		$this->controls['error_text_color'] = array(
			'label'       => __( 'Error Message Color', 'cartflows' ),
			'type'        => 'color',
			'label_block' => true,
			'group'       => 'section_error_style_fields',
			'css'         => array(
				array(
					'property' => 'color',
					'selector' => '.wcf-embed-checkout-form .woocommerce .woocommerce-error',
				),
				array(
					'property' => 'color',
					'selector' => '.wcf-embed-checkout-form .woocommerce .woocommerce-NoticeGroup .woocommerce-error',
				),
				array(
					'property' => 'color',
					'selector' => '.wcf-embed-checkout-form .woocommerce .woocommerce-notices-wrapper .woocommerce-error',
				),
				
			),
		);
		
		$this->controls['error_bg_color'] = array(
			'label'       => __( 'Background Color', 'cartflows' ),
			'type'        => 'color',
			'label_block' => true,
			'group'       => 'section_error_style_fields',
			'css'         => array(
				array(
					'property' => 'background-color',
					'selector' => '.wcf-embed-checkout-form .woocommerce .woocommerce-error',
				),
				array(
					'property' => 'background-color',
					'selector' => '.wcf-embed-checkout-form .woocommerce .woocommerce-NoticeGroup .woocommerce-error',
				),
				array(
					'property' => 'background-color',
					'selector' => '.wcf-embed-checkout-form .woocommerce .woocommerce-notices-wrapper .woocommerce-error',
				),
				
			),
		);

		$this->controls['error_border_color'] = array(
			'label'       => __( 'Border Color', 'cartflows' ),
			'type'        => 'color',
			'label_block' => true,
			'group'       => 'section_error_style_fields',
			'css'         => array(
				array(
					'property' => 'border-color',
					'selector' => '.wcf-embed-checkout-form .woocommerce .woocommerce-error',
				),
				array(
					'property' => 'border-color',
					'selector' => '.wcf-embed-checkout-form .woocommerce .woocommerce-NoticeGroup .woocommerce-error',
				),
				array(
					'property' => 'border-color',
					'selector' => '.wcf-embed-checkout-form .woocommerce .woocommerce-notices-wrapper .woocommerce-error',
				),
				
			),
		);

	}

	/**
	 * Register order review column style controls
	 */
	protected function register_order_review_style_controls() {
		$this->controls['order_review_section_text_color'] = array(
			'label'       => __( 'Text Color', 'cartflows' ),
			'type'        => 'color',
			'label_block' => true,
			'group'       => 'section_order_review_column',
			'css'         => array(
				array(
					'property' => 'color',
					'selector' => '.wcf-embed-checkout-form table.shop_table th',
				),
				array(
					'property' => 'color',
					'selector' => '.wcf-embed-checkout-form table.shop_table td',
				),
			),
		);


		$this->controls['order_review_section_bg_color'] = array(
			'label'       => __( 'Background Color', 'cartflows' ),
			'type'        => 'color',
			'label_block' => true,
			'group'       => 'section_order_review_column',
			'css'         => array(
				array(
					'property' => 'background-color',
					'selector' => '.wcf-embed-checkout-form table.shop_table',
				),
				array(
					'property' => 'background-color',
					'selector' => '.wcf-embed-checkout-form table.shop_table tbody',
				),
				array(
					'property' => 'background-color',
					'selector' => '.wcf-embed-checkout-form table.shop_table tfoot tr.cart-discount',
				),
				array(
					'property' => 'background-color',
					'selector' => '.wcf-embed-checkout-form. table.shop_table tfoot tr.cart-subtotal',
				),
				array(
					'property' => 'background-color',
					'selector' => '.wcf-embed-checkout-form table.shop_table tfoot tr.order-total:not( .recurring-total ) th',
				),
				array(
					'property' => 'background-color',
					'selector' => '.wcf-embed-checkout-form table.shop_table tfoot tr.order-total:not( .recurring-total ) td',
				),
			),
		);


	}

	/**
	 * Register Sections Style Controls.
	 *
	 * @since 1.6.15
	 * @access protected
	 */
	protected function register_payment_section_style_controls() {

		$this->controls['payment_section_text_color'] = array(
			'label'       => __( 'Label Color', 'cartflows' ),
			'type'        => 'color',
			'label_block' => true,
			'group'       => 'section_payment_style_fields',
			'css'         => array(
				array(
					'property' => 'color',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce #payment [type="radio"]:checked + label',
				),
				array(
					'property' => 'color',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce #payment [type="radio"]:not( :checked ) + label',
				),

			),
		);
		$this->controls['payment_section_desc_color'] = array(
			'label'       => __( 'Description Color', 'cartflows' ),
			'type'        => 'color',
			'label_block' => true,
			'group'       => 'section_payment_style_fields',
			'css'         => array(
				array(
					'property' => 'color',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce-checkout #payment div.payment_box',
				),
			),
		);
		$this->controls['payment_section_bg_color']   = array(
			'label'       => __( 'Section Background Color', 'cartflows' ),
			'type'        => 'color',
			'label_block' => true,
			'group'       => 'section_payment_style_fields',
			'css'         => array(
				array(
					'property' => 'background-color',
					'selector' => '.woocommerce-checkout #payment div.payment_box',
				),
				
				array(
					'property' => 'border-bottom-color',
					'selector' => '.wcf-bricks-checkout-form .wcf-embed-checkout-form .woocommerce-checkout #payment div.payment_box::before',
				),
			),
		);

		$this->controls['payment_section_padding_dimension'] = array(
			'label'       => __( 'Section Padding', 'cartflows' ),
			'type'        => 'dimensions',
			'label_block' => true,
			'group'       => 'section_payment_style_fields',
			'css'         => array(
				array(
					'property' => 'padding',
					'selector' => '.wcf-embed-checkout-form .woocommerce-checkout #payment ul.payment_methods',
				),
			),
		);

		$this->controls['payment_section_margin_dimension'] = array(
			'label'       => __( 'Section Margin', 'cartflows' ),
			'type'        => 'dimensions',
			'label_block' => true,
			'group'       => 'section_payment_style_fields',
			'css'         => array(
				array(
					'property' => 'margin',
					'selector' => '.wcf-embed-checkout-form .woocommerce-checkout #payment ul.payment_methods',
				),
			),
		);

		$this->controls['payment_section_border_radius'] = array(
			'label'       => __( 'Border Radius', 'cartflows' ),
			'type'        => 'unit',
			'label_block' => true,
			'group'       => 'section_payment_style_fields',
			'css'         => array(
				array(
					'property' => 'border-radius',
					'selector' => '.woocommerce-checkout #payment ul.payment_methods',
				),
			),
		);
			
	}

   
	/**
	 * Function to get skin types.
	 *
	 * @since 1.6.15
	 * @access protected
	 */
	protected function get_skin_types() {

		$skin_options = array(
			'default'      => __( 'Default', 'cartflows' ),
			'modern-label' => __( 'Modern Labels', 'cartflows' ),
		);

		return $skin_options;
	}

	/**
	 * Set builder control groups
	 * 
	 * @since 3.1.0
	 * @access public
	 */
	public function set_control_groups() {
		$this->control_groups['section_general_style_fields'] = array(
			'title' => esc_html__( 'Global', 'cartflows' ),
			'tab'   => 'style',
		);
		$this->control_groups['input_section']                = array(
			'title' => esc_html__( 'Input Fields', 'cartflows' ),
			'tab'   => 'style',
		);
		$this->control_groups['section_heading_style_fields'] = array(
			'title' => esc_html__( 'Heading', 'cartflows' ),
			'tab'   => 'style',
		);
		$this->control_groups['button_section']               = array(
			'title' => esc_html__( 'Buttons (Normal)', 'cartflows' ),
			'tab'   => 'style',
		);
		$this->control_groups['section_order_review_column']  = array(
			'title' => esc_html__( 'Order Review', 'cartflows' ),
			'tab'   => 'style',
		);
		$this->control_groups['section_error_style_fields']   = array(
			'title' => esc_html__( 'Field Validation & Error Messages', 'cartflows' ),
			'tab'   => 'style',
		);
		$this->control_groups['section_payment_style_fields'] = array(
			'title' => esc_html__( 'Payment Section', 'cartflows' ),
			'tab'   => 'style',
		);
		
	}
	/**
	 * Constructor function.
	 */
	public function set_controls() {
		$this->register_general_content_controls();
		$this->register_global_style_controls();
		$this->register_heading_style_controls();
		$this->register_input_style_controls();
		$this->register_button_style_controls();
		$this->register_payment_section_style_controls();
		$this->register_error_style_controls();
		$this->register_order_review_style_controls();

	}
	
	/**
	 * Render Checkout Form.
	 *
	 * @since 2.1.0
	 * @access public
	 */
	public function render() {
		$checkout_id = $this->post_id;
		$this->set_attribute( '_root', 'data-element-id', $checkout_id );
		$this->dynamic_option_filters();

		do_action( 'cartflows_bricks_before_checkout_shortcode', $checkout_id );

		?>
		<div <?php echo $this->render_attributes( '_root' ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> > 
			<div class = "wcf-bricks-checkout-form cartflows-bricks__checkout-form">
				<?php
					echo do_shortcode( '[cartflows_checkout]' );
				?>
			</div>
		</div>
		<?php

	}

	/**
	 * Function to apply dynamic option filters.
	 *
	 * @access protected
	 */
	public function dynamic_option_filters() {

		$checkout_fields = array(
			array(
				'filter_slug'  => 'wcf-fields-skins',
				'setting_name' => 'input_skins',
			),
			array(
				'filter_slug'  => 'wcf-checkout-layout',
				'setting_name' => 'layout',
			),
		);

		if ( isset( $checkout_fields ) && is_array( $checkout_fields ) ) {

			foreach ( $checkout_fields as $key => $field ) {

				$setting_name = $field['setting_name'];

				if ( '' !== $this->settings[ $setting_name ] ) {

					add_filter(
						'cartflows_checkout_meta_' . $field['filter_slug'],
						function ( $value ) use ( $setting_name ) {

							$value = $this->settings[ $setting_name ];

							return $value;
						},
						10,
						1
					);
				}
			}
		}

		do_action( 'cartflows_bricks_checkout_options_filters', $this->settings );
	}
	
}
