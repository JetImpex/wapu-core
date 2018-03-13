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

if ( ! class_exists( 'Wapu_Core_API_Themes' ) ) {

	/**
	 * Define Wapu_Core_API_Themes class
	 */
	class Wapu_Core_API_Themes extends Wapu_Core_Base_Endpoint {

		public function methods() {
			return 'GET';
		}

		public function route() {
			return '/themes';
		}

		public function callback( $params ) {

			$args = $params->get_params();

			$query_args = array(
				'post_type'      => 'download',
				'post_status'    => 'publish',
				'posts_per_page' => isset( $args['per_page'] ) ? absint( $args['per_page'] ) : 6,
				'paged'          => isset( $args['page'] ) ? absint( $args['per_page'] ) : 1,
			);

			if ( ! empty( $args['category'] ) ) {
				$query_args['tax_query'] = array(
					array(
						'taxonomy' => 'download_category',
						'field'    => 'slug',
						'terms'    => esc_attr( $args['category'] ),
					),
				);
			}

			$query = new WP_Query( $query_args );

			$data = array(
				'total_pages' => 1,
				'page'        => $query_args['paged'],
				'themes'      => array(),
			);

			if ( ! $query->have_posts() ) {
				return array(
					'response' => $data,
					'code'     => 200,
				);
			}

			$thumb_size = isset( $args['thumb_size'] ) ? esc_attr( $args['thumb_size'] ) : 'full';

			$data['total_pages'] = $query->max_num_pages;
			$posts               = array();

			add_filter( 'edd_currency_decimal_count', '__return_zero' );

			foreach ( $query->posts as $post ) {

				$rating = '';

				if ( function_exists( 'edd_reviews' ) ) {
					$average = edd_reviews()->average_rating( false, $post->ID );
					if ( $average ) {
						$rating = wapu_core()->edd->single->get_rating_stars( $average );
					}
				}

				ob_start();
				wapu_core()->edd->single->sales( '%s', $post->ID );
				$sales = ob_get_clean();

				$sale_price = get_post_meta( $post->ID, '_sale_price', true );

				if ( ! empty( $sale_price ) ) {
					$sale_price = edd_currency_filter( edd_format_amount( $sale_price ) );
				}

				$terms       = wp_get_post_terms( $post->ID, 'topic' );
				$terms_names = wp_list_pluck( $terms, 'name' );

				$current_post = array(
					'id'         => $post->ID,
					'title'      => $post->post_title,
					'url'        => get_permalink( $post->ID ),
					'live_demo'  => wapu_core()->edd->single->get_live_demo_url( $post->ID ),
					'thumb'      => get_the_post_thumbnail_url( $post->ID, $thumb_size ),
					'price'      => edd_currency_filter( edd_format_amount( edd_get_download_price( $post->ID ) ) ),
					'sale_price' => $sale_price,
					'sales'      => $sales,
					'rating'     => $rating,
					'topics'     => $terms_names,
				);

				$posts[] = $current_post;
			}

			$data['themes'] = $posts;

			return array(
				'response' => $data,
				'code'     => 200,
			);

		}

	}

}
