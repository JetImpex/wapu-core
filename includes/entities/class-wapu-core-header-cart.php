<?php
/**
 * Class description
 *
 * @package   package_name
 * @author    Cherry Team
 * @license   GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Wapu_Core_Header_Cart' ) ) {

	/**
	 * Define Wapu_Core_Header_Cart class
	 */
	class Wapu_Core_Header_Cart {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Constructor for the class
		 */
		public function __construct() {
			add_action( 'wapu_header_cart', array( $this, 'add_header_cart' ) );
		}

		/**
		 * Add header cart
		 */
		public function add_header_cart() {
			wp_enqueue_script( 'wapu-core' );
			include wapu_core()->get_template( 'entities/header-cart/cart-link.php' );
			add_action( 'wp_footer', array( $this, 'add_cart_popups' ) );
		}

		/**
		 * Render cart popups
		 */
		public function add_cart_popups() {

			echo '<div class="cart-popups">';
				include wapu_core()->get_template( 'entities/header-cart/account-popup.php' );
				include wapu_core()->get_template( 'entities/header-cart/cart-popup.php' );
				echo '<div class="cart-overlay"></div>';
			echo '</div>';
		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @return object
		 */
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}
	}

}

/**
 * Returns instance of Wapu_Core_Header_Cart
 *
 * @return object
 */
function wapu_core_header_cart() {
	return Wapu_Core_Header_Cart::get_instance();
}
