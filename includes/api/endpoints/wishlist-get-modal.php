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

if ( ! class_exists( 'Wapu_Core_API_Wishlist_Get_Modal' ) ) {

	/**
	 * Define Wapu_Core_API_Wishlist_Get_Modal class
	 */
	class Wapu_Core_API_Wishlist_Get_Modal extends Wapu_Core_Base_Endpoint {

		public function methods() {
			return 'GET';
		}

		public function route() {
			return '/wishlist-get-modal';
		}

		public function callback( $params ) {

			if ( ! function_exists( 'edd_wl_get_wish_lists' ) ) {
				return array(
					'response' => array(
						'message' => 'Wish Lists not enabled',
					),
					'code' => 400,
				);
			}

			$theme = $params->get_param( 'theme' );

			if ( ! $theme ) {
				array(
					'response' => array(
						'message' => 'Theme ID not provided',
					),
					'code' => 400,
				);
			}

			ob_start();

			do_action( 'edd_wl_modal_content' );

			$modal = ob_get_clean();

			$download_id = intval( $theme );

			// get price IDs
			$price_ids = array( $theme );

			// single price option (shortcode)
			$price_option_single = isset( $_POST['price_option_single'] ) ? $_POST['price_option_single'] : '';

			$to_add = array();
			$items  = array();

			if ( ! empty( $price_ids ) ) {
				foreach ( $price_ids as $price ) {
					$to_add[] = array( 'price_id' => $price );
				}
			}

			foreach ( $to_add as $options ) {

				if ( $download_id == $options['price_id'] ) {
					$options = array();
				}

				$item = array(
					'id'      =>  $download_id,
					'options' => $options
				);

				// Add each item to array.
				$items[] = $item;
			}

			// get wish lists and send price IDs + items array
			$lists = edd_wl_get_wish_lists( $download_id, $price_ids, $items, $price_option_single );
			$lists = preg_replace( '/input type=\"radio\"/', 'input type="radio" checked', $lists, 1 );

			// count lists
			$list_count = edd_wl_get_query() ? count ( edd_wl_get_query() ) : 0;

			return array(
				'response' => array(
					'post_id'    => $download_id,
					'list_count' => $list_count,
					'lists'      => html_entity_decode( $lists, ENT_COMPAT, 'UTF-8' ),
					'modal'      => $modal,
				),
				'code' => 200,
			);

		}

	}

}
