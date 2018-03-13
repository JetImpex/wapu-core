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

if ( ! class_exists( 'Wapu_Core_API_Wishlist_Add' ) ) {

	/**
	 * Define Wapu_Core_API_Wishlist_Add class
	 */
	class Wapu_Core_API_Wishlist_Add extends Wapu_Core_Base_Endpoint {

		public function methods() {
			return 'GET';
		}

		public function route() {
			return '/wishlist-add';
		}

		public function callback( $params ) {

			if ( ! function_exists( 'edd_wl_get_wish_lists' ) ) {
				return array(
					'response' => array(
						'message' => 'Wish Lists not enabled',
					),
					'code' => 400
				);
			}

			$download_id     = $params->get_param( 'download_id' );
			$price_ids       = $params->get_param( 'price_ids' );
			$new_or_existing = $params->get_param( 'new_or_existing' );
			$list_name       = $params->get_param( 'list_name' );
			$list_status     = $params->get_param( 'list_status' );
			$list_id         = $params->get_param( 'list_id' );

			if ( ! $download_id ) {
				return array(
					'response' => array(
						'message' => 'Theme ID not provided',
					),
					'code' => 400,
				);
			}

			global $post;

			$to_add = array();

			if ( ! empty( $price_ids ) ) {
				foreach ( $price_ids as $price ) {
					$to_add[] = array( 'price_id' => $price );
				}
			}

			// create a new list
			$create_list = 'new-list' === $new_or_existing ? true : false;

			// the new list name being created. Fallback for blank list names
			$list_name = ! empty( $list_name ) ? $list_name : __( 'My list', 'edd-wish-lists' );

			// create new list
			if ( true == $create_list ) {
				$args = array(
					'post_title'    => $list_name,
					'post_content'  => '',
					'post_status'   => $list_status,
					'post_type'     => 'edd_wish_list',
				);

				$list_id = wp_insert_post( $args );

				if ( $list_id ) {
					$return['list_created'] = true;
					$return['list_name']    = $list_name;
				}

			}

			// add each download to wish list
			foreach ( $to_add as $options ) {

				if ( $download_id == $options['price_id'] ) {
					$options = array();
				}

				edd_wl_add_to_wish_list( $download_id, $options, $list_id );
			}

			$title = get_the_title( $list_id );
			$url   = get_permalink( $list_id );

			$result = sprintf(
				__( 'Successfully added to <strong>%s</strong>', 'edd-wish-lists' ),
				'<a href="' . $url . '">' . $title . '</a>'
			);

			return array(
				'response' => array(
					'result' => $result,
				),
				'code' => 200,
			);

		}

	}

}
