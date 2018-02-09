<?php

/**
 * Plugin Name: Conditional Flat Rate for WooCommerce
 * Description: Adds conditional Flat Rate shipping method for WooCommerce
 * Version: 0.2
 * Author: Theunis Cilliers
 * Author URI: http://digitalreliance.co.za
 * Contributors: Hugh Lashbrooke
 * 
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Load plugin class files
require_once( 'includes/class-conditional-flat-rate-for-woocommerce.php' );

/**
 * Returns the main instance of Conditional_Flat_Rate_For_WooCommerce to prevent the need to use globals.
 *
 * @return object Conditional_Flat_Rate_For_WooCommerce
 */
if ( ! function_exists( 'Conditional_Flat_Rate_For_WooCommerce' ) ) {

	function Conditional_Flat_Rate_For_WooCommerce() {
		$instance = Conditional_Flat_Rate_For_WooCommerce::instance( __FILE__, '1.0.0' );
		return $instance;
	}	

	Conditional_Flat_Rate_For_WooCommerce();
}

