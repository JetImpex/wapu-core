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

if ( ! class_exists( 'Wapu_Core_API_Cart' ) ) {

	/**
	 * Define Wapu_Core_API_Cart class
	 */
	class Wapu_Core_API_Cart extends Wapu_Core_Base_Endpoint {

		public function methods() {
			return 'GET';
		}

		public function route() {
			return '/cart';
		}

		public function callback( $params ) {

			var_dump( $request->get_param( 'per_page' ) );

		}

	}

}
