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

if ( ! class_exists( 'Wapu_Core_Popup' ) ) {

	/**
	 * Define Wapu_Core_Popup class
	 */
	class Wapu_Core_Popup {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Popup added trigger
		 *
		 * @var boolean
		 */
		public $added = false;

		/**
		 * Initalize popup
		 */
		public function init() {

			if ( $this->added ) {
				return;
			}

			wp_enqueue_script( 'wapu-core' );

			add_filter( 'wp_footer', array( $this, 'add_popup' ) );
			$this->added = true;
		}

		/**
		 * Add popup int footer
		 */
		public function add_popup() {
			include wapu_core()->get_template( 'handlers/popup.php' );
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
 * Returns instance of Wapu_Core_Popup
 *
 * @return object
 */
function wapu_core_popup() {
	return Wapu_Core_Popup::get_instance();
}
