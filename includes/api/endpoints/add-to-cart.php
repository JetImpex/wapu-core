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

if ( ! class_exists( 'Wapu_Core_API_Add_To_Cart' ) ) {

	/**
	 * Define Wapu_Core_API_Add_To_Cart class
	 */
	class Wapu_Core_API_Add_To_Cart extends Wapu_Core_Base_Endpoint {

		public function methods() {
			return 'GET';
		}

		public function route() {
			return '/add-to-cart';
		}

		public function callback( $params ) {

			$theme = $params->get_param( 'theme' );

			if ( ! $theme ) {
				return new WP_REST_Response(
					array(
						'message' => 'Theme ID not provided',
					),
					400
				);
			}

			$theme = get_post( $theme );

			if ( ! $theme || 'download' !== $theme->post_type ) {
				return array(
					'response' => array(
						'message' => 'Theme ID not provided',
					),
					'code' => 400
				);
			}

			$count = edd_add_to_cart( $theme->ID );

			return array(
				'response' => array(
					'count'    => $count,
					'checkout' => edd_get_checkout_uri(),
				),
				'code' => 200,
			);

		}

	}

}
