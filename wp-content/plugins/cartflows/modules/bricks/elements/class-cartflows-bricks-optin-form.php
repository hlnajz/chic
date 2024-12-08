<?php
/**
 * Bricks Classes.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;   // Exit if accessed directly.
}

/**
 * Optin Form Widget
 *
 * @since 1.6.15
 */
class CartFlows_Bricks_Optin_Form extends \Bricks\Element {
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

	public $name = 'bricks-optin-form';

	/**
	 * Icon of the element.
	 *
	 * @var instance
	 */
	public $icon = 'fa-solid fa-envelope-open-text';

	/**
	 * Label of the element.
	 *
	 * @var instance
	 */
	public function get_label() {
		return esc_html__( 'Optin Form', 'cartflows' );
	}

	/**
	 * Retrieve Widget Keywords.
	 *
	 * @since 1.6.15
	 * @access public
	 *
	 * @return string Widget keywords.
	 */
	public function get_keywords() {
		return array( 'cartflows', 'optin', 'form' );
	}
	/**
	 * Set builder control groups
	 * 
	 * @since 3.1.0
	 * @access public
	 */
	public function set_control_groups() {
		$this->control_groups['general_fields'] = array(
			'title' => esc_html__( 'General', 'cartflows' ),
			'tab'   => 'style',
		);
		$this->control_groups['input_fields']   = array(
			'title' => esc_html__( 'Input Fields', 'cartflows' ),
			'tab'   => 'style',
		);
		$this->control_groups['button_fields']  = array(
			'title' => esc_html__( 'Button', 'cartflows' ),
			'tab'   => 'style',
		);
	}
	/**
	 * Register cart controls controls.
	 *
	 * @since 1.6.15
	 * @access protected
	 */
	public function set_controls() {

		// Style Tab.
		$this->register_general_style_controls();
		$this->register_input_style_controls();
		$this->register_button_style_controls();
	}

	/**
	 * Function to get skin types.
	 *
	 * @since 1.6.15
	 * @access protected
	 */
	public function get_skin_types() {

		$skin_options = array(
			'default'         => __( 'Default', 'cartflows' ),
			'floating-labels' => __( 'Floating Labels', 'cartflows' ),
		);

		return $skin_options;
	}

	/**
	 * Register General Style Controls.
	 *
	 * @since 1.6.15
	 * @access protected
	 */
	public function register_general_style_controls() {
	
		$this->controls['general_primary_color'] = array(
			'label'   => __( 'Primary Color', 'cartflows' ),
			'type'    => 'color',
			'default' => '',
			'group'   => 'general_fields',
			'css'     => array(
				array(
					'property' => 'background-color',
					'selector' => '.wcf-bricks-optin-form.cartflows-bricks__optin-form .wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review .woocommerce-checkout-payment button#place_order',
				),
				array(
					'property' => 'border-color',
					'selector' => '.wcf-bricks-optin-form.cartflows-bricks__optin-form .wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review .woocommerce-checkout-payment button#place_order',
				),
			),
		);
			
		$this->controls['general_typography'] = array(
			'label' => 'Typography',
			'type'  => 'typography',
			'group' => 'general_fields',
			'css'   => array(
				array(
					'property' => 'font',
					'selector' => '.wcf-bricks-optin-form.cartflows-bricks__optin-form .wcf-optin-form .checkout.woocommerce-checkout label',
				),
				array(
					'property' => 'font',
					'selector' => ' .wcf-bricks-optin-form.cartflows-bricks__optin-form .wcf-optin-form .checkout.woocommerce-checkout input',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-bricks-optin-form.cartflows-bricks__optin-form .wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review .woocommerce-checkout-payment button#place_order',
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
	public function register_input_style_controls() {
		$this->controls['input_skins'] = array(
			'label'   => __( 'Style', 'cartflows' ),
			'type'    => 'select',
			'default' => 'default',
			'options' => $this->get_skin_types(),
			'group'   => 'input_fields',
		);
		
		$this->controls['input_text_color'] = array(
			'label' => __( 'Text Color', 'cartflows' ),
			'type'  => 'typography',
			'group' => 'input_fields',
			'css'   => array(
				array(
					'property' => 'font',
					'selector' => '.wcf-bricks-optin-form.cartflows-bricks__optin-form .wcf-optin-form .checkout.woocommerce-checkout label',
				),
				array(
					'property' => 'font',
					'selector' => '.wcf-bricks-optin-form.cartflows-bricks__optin-form .wcf-optin-form .checkout.woocommerce-checkout input',
				),
			),
		);

		$this->controls['input_bgcolor'] = array(
			'label'   => __( 'Field Background Color', 'cartflows' ),
			'type'    => 'color',
			'default' => '',
			'group'   => 'input_fields',
			'css'     => array(
				array(
					'property' => 'background-color',
					'selector' => '.wcf-bricks-optin-form.cartflows-bricks__optin-form .wcf-optin-form .checkout.woocommerce-checkout input',
				),
			),
		);

		$this->controls['input_color'] = array(
			'label'   => __( 'Input Text / Placeholder Color', 'cartflows' ),
			'type'    => 'color',
			'default' => '',
			'group'   => 'input_fields',
			'css'     => array(
				array(
					'property' => 'color',
					'selector' => '.wcf-bricks-optin-form.cartflows-bricks__optin-form .wcf-optin-form .checkout.woocommerce-checkout input',
				),
				
			),
		);

		$this->controls['input_border'] = array(
			'label'   => __( 'Border', 'cartflows' ),
			'type'    => 'border',
			'default' => array(),
			'group'   => 'input_fields',
			'css'     => array(
				array(
					'property' => 'border',
					'selector' => '.wcf-bricks-optin-form.cartflows-bricks__optin-form .wcf-optin-form .checkout.woocommerce-checkout input',
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
	public function register_button_style_controls() {

		$this->controls['buttons_typography'] = array(
			'label' => 'Typography',
			'type'  => 'typography',
			'group' => 'button_fields',
			'css'   => array(
				array(
					'property' => 'font',
					'selector' => '.wcf-bricks-optin-form.cartflows-bricks__optin-form .wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review .woocommerce-checkout-payment button#place_order',
				),
			),
		);

		$this->controls['button_text_color'] = array(
			'label'   => __( 'Text Color', 'cartflows' ),
			'type'    => 'color',
			'default' => '',
			'group'   => 'button_fields',
			'css'     => array(
				array(
					'property' => 'color',
					'selector' => '.wcf-bricks-optin-form.cartflows-bricks__optin-form .wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review .woocommerce-checkout-payment button#place_order',
				),
			),
		);

		$this->controls['btn_background_color'] = array(
			'label'   => __( 'Background Color', 'cartflows' ),
			'type'    => 'color',
			'default' => '',
			'group'   => 'button_fields',
			'css'     => array(
				array(
					'property' => 'background-color',
					'selector' => '.wcf-bricks-optin-form.cartflows-bricks__optin-form .wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review .woocommerce-checkout-payment button#place_order',
				),
			),
		);

		$this->controls['btn_border'] = array(
			'label'   => __( 'Border', 'cartflows' ),
			'type'    => 'border',
			'default' => array(),
			'group'   => 'button_fields',
			'css'     => array(
				array(
					'property' => 'border',
					'selector' => '.wcf-bricks-optin-form.cartflows-bricks__optin-form .wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review .woocommerce-checkout-payment button#place_order',
				),
			),
		);

		$this->controls['button_box_shadow'] = array(
			'label'   => __( 'Box Shadow', 'cartflows' ),
			'type'    => 'box-shadow',
			'default' => array(),
			'group'   => 'button_fields',
			'css'     => array(
				array(
					'property' => 'box-shadow',
					'selector' => '.wcf-bricks-optin-form.cartflows-bricks__optin-form .wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review .woocommerce-checkout-payment button#place_order',
				),
			),
		);

		$this->controls['button_hover_color'] = array(
			'label'   => __( 'Text Color', 'cartflows' ),
			'type'    => 'color',
			'default' => '',
			'group'   => 'button_fields',
			'css'     => array(
				array(
					'property' => 'color',
					'selector' => '.wcf-bricks-optin-form.cartflows-bricks__optin-form .wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review .woocommerce-checkout-payment button#place_order:hover',
				),
			),
		);

		$this->controls['button_hover_border_color'] = array(
			'label'   => __( 'Border Hover Color', 'cartflows' ),
			'type'    => 'color',
			'default' => '',
			'group'   => 'button_fields',
			'css'     => array(
				array(
					'property' => 'border-color',
					'selector' => '.wcf-bricks-optin-form.cartflows-bricks__optin-form .wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review .woocommerce-checkout-payment button#place_order:hover',
				),
			),
		);

		$this->controls['button_background_hover_color'] = array(
			'label'   => __( 'Background Color', 'cartflows' ),
			'type'    => 'color',
			'default' => '',
			'group'   => 'button_fields',
			'css'     => array(
				array(
					'property' => 'background-color',
					'selector' => '.wcf-bricks-optin-form.cartflows-bricks__optin-form .wcf-optin-form .checkout.woocommerce-checkout .wcf-order-wrap #order_review .woocommerce-checkout-payment button#place_order:hover',
				),
			),
		);
	}


	/** 
	 * Render element HTML on frontend
	 * 
	 * If no 'render_builder' function is defined then this code is used to render element HTML in builder, too.
	 */
	public function render() {
		$this->set_attribute( '_root', 'data-element-id', $this->id );
		$checkout_id = $this->post_id;

		$this->dynamic_option_filters();

		do_action( 'cartflows_bricks_before_optin_shortcode', $checkout_id );

		?>
		<div <?php echo $this->render_attributes( '_root' ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> > 
			<div class = "wcf-bricks-optin-form cartflows-bricks__optin-form">
				<?php
					echo do_shortcode( '[cartflows_optin]' );
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Dynamic options of elementor and add filters.
	 *
	 * @since 1.6.15
	 */
	public function dynamic_option_filters() {

		$optin_fields = array(

			// Input Fields.
			array(
				'filter_slug'  => 'wcf-input-fields-skins',
				'setting_name' => 'input_skins',
			),
		);

		if ( isset( $optin_fields ) && is_array( $optin_fields ) ) {

			foreach ( $optin_fields as $key => $field ) {

				$setting_name = $field['setting_name'];

				add_filter(
					'cartflows_optin_meta_' . $field['filter_slug'],
					function ( $value ) use ( $setting_name ) {
						if ( ! empty( $this->settings[ $setting_name ] ) ) {
							$value = $this->settings[ $setting_name ];

							return $value;
						}
						
					},
					10,
					1
				);
			}
		}

		do_action( 'cartflows_bricks_optin_options_filters', $this->settings );
	}
}
