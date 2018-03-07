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

if ( ! class_exists( 'Wapu_Core_API_Manager' ) ) {

	/**
	 * Define Wapu_Core_API_Manager class
	 */
	class Wapu_Core_API_Manager {

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
		public function init() {
			require wapu_core()->plugin_path( 'includes/api/class-wapu-core-base-endpoint.php' );
			$this->register_endpoints();
		}

		public function register_endpoints() {

			$endpoints = array(
				'Wapu_Core_API_Themes' => wapu_core()->plugin_path( 'includes/api/endpoints/themes.php' ),
				'Wapu_Core_API_Cart'   => wapu_core()->plugin_path( 'includes/api/endpoints/cart.php' ),
			);

			foreach ( $endpoints as $class => $file ) {
				require $file;
				new $class();
			}

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
 * Returns instance of Wapu_Core_API_Manager
 *
 * @return object
 */
function wapu_core_api_manager() {
	return Wapu_Core_API_Manager::get_instance();
}
