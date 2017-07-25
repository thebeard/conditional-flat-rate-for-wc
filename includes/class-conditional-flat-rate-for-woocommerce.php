<?php

if ( !class_exists( 'Conditional_Flat_Rate_For_WooCommerce' ) ) :

	/**
	 * Plugin Class Container
	 */
	class Conditional_Flat_Rate_For_WooCommerce {

		/**
		 * The single instance of Conditional_Flat_Rate_For_WooCommerce.
		 * @var 	object
		 * @access  private
		 * @since 	1.0.0
		 */
		private static $_instance = null;

		/**
		 * Unique ID
		 * @var 	int
		 * @access  public
		 * @since 	1.0.0
		 */
		public $uid = null;

		/**
		 * Let's construct!
		 */
		public function __construct() {
			// Single init function after theme setup
			$this->set_uid( 'cfrfw' );
			add_action( 'after_setup_theme', array( $this, 'init' ) );			
		}

		/**
		 * Setting UID
		 * @param int $uid
		 */
		private function set_uid( $uid ) {
			$this->uid = $uid;
		}

		/**
		 * Return UID
		 * @return int
		 */
		private function get_uid() {
			return $this->uid;
		}

		/**
		 * Initialises the plugin
		 * @return void
		 */
		public function init() {
			if ( class_exists( 'WC_Shipping_Flat_Rate' ) && !class_exists( 'WC_Shipping_Conditional_Flat_Rate' ) ) {	
				$this->add_new_flat_rate_shipping_method();
				add_filter( 'woocommerce_shipping_methods', array( $this, 'add_shipping_method' ) );
			}
		}

		/**
		 * Add our shipping method
		 * @param array $methods Array of existing methods
		 */
		public function add_shipping_method( $methods ) {	
		    $methods[ 'conditional_flat_rate' ] = 'WC_Shipping_Conditional_Flat_Rate';
		    return $methods;
		}

		/**
		 * Include our Shipping method class
		 */
		private function add_new_flat_rate_shipping_method() {
			$text_domain = $this->get_uid();
			include "class-wc-shipping-conditional-flat-rate.php";
		}

		/**
		 * Conditional_Flat_Rate_For_WooCommerce
		 *
		 * Ensures only one instance of Conditional_Flat_Rate_For_WooCommerce is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @static
		 * @see Conditional_Flat_Rate_For_WooCommerce()
		 * @return Conditional_Flat_Rate_For_WooCommerce instance
		 */
		public static function instance ( $file = '', $version = '1.0.0' ) {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self( $file, $version );
			}
			return self::$_instance;
		} // End instance ()
	}

endif;





