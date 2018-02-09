<?php

class WC_Shipping_Conditional_Flat_Rate extends WC_Shipping_Flat_Rate {

	private $parent_title = null;
	private $text_domain;

	public function __construct( $instance_id = 0 ) {
		$this->text_domain           = 'wcscfr_custom';
		$this->id                    = 'conditional_flat_rate';
		$this->instance_id 			 = absint( $instance_id );
		$this->method_title          = __( 'Conditional Flat Rate', $this->text_domain );
		$this->method_description    = __( 'Lets you charge a fixed rate for shipping, conditionally ;)', $this->text_domain );
		$this->parent_title			 = __( 'Flat Rate', 'woocommerce' );
		$this->supports              = array(
			'shipping-zones',
			'instance-settings',
			'instance-settings-modal',
		);
		$this->init();
	}

	public function init() {

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables.
		$this->title      = $this->get_option( 'title' );
		$this->tax_status 	= $this->get_option( 'tax_status' );
		$this->min_amount = $this->get_option( 'min_amount', 0 );
		$this->max_amount = $this->get_option( 'max_amount', 0 );
		$this->requires   = $this->get_option( 'requires' );

		// Actions
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
		
	}

	public function init_form_fields() {
		$this->instance_form_fields = array(
			'title' => array(
				'title'       => __( 'Title', 'woocommerce' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
				'default'     => $this->parent_title,
				'desc_tip'    => true,
			),
			'tax_status' => array(
				'title' 		=> __( 'Tax status', 'woocommerce' ),
				'type' 			=> 'select',
				'class'         => 'wc-enhanced-select',
				'default' 		=> 'taxable',
				'options'		=> array(
					'taxable' 	=> __( 'Taxable', 'woocommerce' ),
					'none' 		=> _x( 'None', 'Tax status', 'woocommerce' ),
				),
			),
			'cost' => array(
				'title' 		=> __( 'Cost', 'woocommerce' ),
				'type' 			=> 'text',
				'placeholder'	=> '',
				'default'		=> '0',
				'desc_tip'		=> true,
			),
			'requires' => array(
				'title'   => __( 'Shipping method requires...', $this->text_domain ),
				'type'    => 'select',
				'class'   => 'wc-enhanced-select',
				'default' => '',
				'options' => array(
					''           => __( 'N/A', 'woocommerce' ),
					'coupon'     => __( 'A valid coupon', 'woocommerce' ),
					'min_amount' => __( 'A minimum order amount', 'woocommerce' ),
					'max_amount' => __( 'A maximum order amount', 'woocommerce' ),
					'either'     => __( 'A minimum/maximum order amount OR a coupon', $this->text_domain ),
					'both'       => __( 'A minimum/maximum order amount AND a coupon', $this->text_domain ),
				),
				'desc_tip'    => true,
				'description' => __( 'In the event that one of the last two options are selected, make sure that undesired order amount (min or max) is set to 0 and the other is set to required value.', $this->text_domain )
			),
			'min_amount' => array(
				'title'       => __( 'Minimum order amount', 'woocommerce' ),
				'type'        => 'price',
				'placeholder' => wc_format_localized_price( 0 ),
				'description' => __( 'Users will need to spend this amount to activate this shipping method (if enabled above).', $this->text_domain ),
				'default'     => '0',
				'desc_tip'    => true,
			),
			'max_amount' => array(
				'title'       => __( 'Maximum order amount', $this->text_domain ),
				'type'        => 'price',
				'placeholder' => wc_format_localized_price( 0 ),
				'description' => __( 'When a user has spent this much, this method will be available only (if enabled above).', $this->text_domain ),
				'default'     => '0',
				'desc_tip'    => true,
			),

		);
	}

	public function is_available( $package ) {		
		$has_coupon         = false;
		$has_met_min_amount = false;
		$has_met_max_amount = false;
		$identifier = null;

		if ( in_array( $this->requires, array( 'coupon', 'either', 'both' ) ) ) {
			if ( $coupons = WC()->cart->get_coupons() ) {
				foreach ( $coupons as $code => $coupon ) {
					if ( $coupon->is_valid() && $coupon->get_free_shipping() ) {
						$has_coupon = true;
						break;
					}
				}
			}			
		}		

		if ( in_array( $this->requires, array( 'min_amount', 'max_amount', 'either', 'both' ) ) && isset( WC()->cart->cart_contents_total ) ) {
			$total = WC()->cart->get_displayed_subtotal();

			if ( 'incl' === WC()->cart->tax_display_cart ) {
				$total = round( $total - ( WC()->cart->get_cart_discount_total() + WC()->cart->get_cart_discount_tax_total() ), wc_get_price_decimals() );
			} else {
				$total = round( $total - WC()->cart->get_cart_discount_total(), wc_get_price_decimals() );
			}
		}

		if ( in_array( $this->requires, array( 'min_amount' ) ) ) {
			$identifier = 'min';
			if ( $total >= $this->min_amount ) {
				$has_met_min_amount = true;
			}			
		}

		if ( in_array( $this->requires, array( 'max_amount' ) ) ) {
			$identifier = 'max';
			if ( $total <= $this->max_amount ) {
				$has_met_max_amount = true;
			}
		}

		if ( in_array( $this->requires, array( 'either', 'both' ) ) && isset( WC()->cart->cart_contents_total ) ) {			
			// Decide which to use			
			if ( $this->max_amount ) {
				$identifier = 'max';
				if ( $total <= $this->max_amount ) {
					$has_met_max_amount = true;
				}
			} else if ( $this->min_amount ) {
				$identifier = 'min';
				if ( $total >= $this->min_amount ) {
					$has_met_min_amount = true;
				}
			}
		}

		switch ( $this->requires ) {
			case 'max_amount' :
				$is_available = $has_met_max_amount;
				break;
			case 'min_amount' :				
				$is_available = $has_met_min_amount;
				break;
			case 'coupon' :
				$is_available = $has_coupon;
				break;
			case 'both' :
				$amount = ( $identifier == 'min' ) ? $has_met_min_amount : $has_met_max_amount;
				$is_available = $amount && $has_coupon;
				break;
			case 'either' :
				$amount = ( $identifier == 'min' ) ? $has_met_min_amount : $has_met_max_amount;
				$is_available = $amount || $has_coupon;
				break;
			default :
				$is_available = true;
				break;
		}

		return apply_filters( 'woocommerce_shipping_' . $this->id . '_is_available', $is_available, $package );
	}

	/**
	 * Evaluate a cost from a sum/string.
	 * @param  string $sum
	 * @param  array  $args
	 * @return string
	 */
	protected function evaluate_cost( $sum, $args = array() ) {
		include_once( WC()->plugin_path() . '/includes/libraries/class-wc-eval-math.php' );

		// Allow 3rd parties to process shipping cost arguments
		$args           = apply_filters( 'woocommerce_evaluate_shipping_cost_args', $args, $sum, $this );
		$locale         = localeconv();
		$decimals       = array( wc_get_price_decimal_separator(), $locale['decimal_point'], $locale['mon_decimal_point'], ',' );
		$this->fee_cost = $args['cost'];

		// Expand shortcodes
		add_shortcode( 'fee', array( $this, 'fee' ) );

		$sum = do_shortcode( str_replace(
			array(
				'[qty]',
				'[cost]',
			),
			array(
				$args['qty'],
				$args['cost'],
			),
			eval( 'return ' . str_replace( '{subtotal}', $this->fee_cost, $sum ) . ';' )
		) );

		remove_shortcode( 'fee', array( $this, 'fee' ) );

		// Remove whitespace from string
		$sum = preg_replace( '/\s+/', '', $sum );

		// Remove locale from string
		$sum = str_replace( $decimals, '.', $sum );

		// Trim invalid start/end characters
		$sum = rtrim( ltrim( $sum, "\t\n\r\0\x0B+*/" ), "\t\n\r\0\x0B+-*/" );

		// Do the math
		return $sum ? WC_Eval_Math::evaluate( $sum ) : 0;
	}
	
}