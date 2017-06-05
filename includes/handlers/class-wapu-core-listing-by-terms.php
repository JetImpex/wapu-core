<?php
/**
 * Listing by terms handler
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Wapu_Core_Listing_By_Terms' ) ) {

	/**
	 * Define Wapu_Core_Listing_By_Terms class
	 */
	class Wapu_Core_Listing_By_Terms {

		/**
		 * Listing arguments array
		 *
		 * @var array
		 */
		public $args = array();

		/**
		 * Post type class instance
		 * @var null
		 */
		public $post_type = null;

		/**
		 * Constructor for the class
		 */
		function __construct( $post_type = null, $args = array() ) {

			if ( ! is_object( $post_type ) ) {
				return;
			}

			$this->post_type = $post_type;

			$this->args = wp_parse_args( $args, array(
				'taxonomy'    => false,
				'number'      => 5,
				'terms_order' => 'name',
				'terms_args'  => array(),
				'posts_order' => 'date',
				'posts_args'  => array(),
				'terms_start' => wapu_core()->get_template( 'handlers/listing-by-terms/terms-loop-start.php' ),
				'terms_loop'  => wapu_core()->get_template( 'handlers/listing-by-terms/terms-loop.php' ),
				'terms_end'   => wapu_core()->get_template( 'handlers/listing-by-terms/terms-loop-end.php' ),
				'posts_start' => wapu_core()->get_template( 'handlers/listing-by-terms/posts-loop-start.php' ),
				'posts_loop'  => wapu_core()->get_template( 'handlers/listing-by-terms/posts-loop.php' ),
				'posts_end'   => wapu_core()->get_template( 'handlers/listing-by-terms/posts-loop-end.php' ),
			) );

			$this->build_listing();

		}

		/**
		 * Build listing tree.
		 *
		 * @return void
		 */
		public function build_listing() {

			$taxonomy = $this->post_type->tax( $this->args['taxonomy'] );

			$args = array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => true,
			);

			$args = array_merge( $args, $this->get_terms_order_args() );

			if ( ! empty( $this->args['terms_args'] ) ) {
				$args = array_merge( $args, $this->args['terms_args'] );
			}

			$terms = get_terms( $args );

			if ( empty( $terms ) || is_wp_error( $terms ) ) {
				return;
			}

			if ( file_exists( $this->args['terms_start'] ) ) {
				include $this->args['terms_start'];
			}

			foreach ( $terms as $term ) {

				$posts = $this->get_posts_loop( $term );

				if ( file_exists( $this->args['terms_loop'] ) ) {
					include $this->args['terms_loop'];
				}

			}

			if ( file_exists( $this->args['terms_end'] ) ) {
				include $this->args['terms_end'];
			}

		}

		/**
		 * Get posts loop for passed term.
		 *
		 * @param  object $term Term to get loop for.
		 * @return string
		 */
		public function get_posts_loop( $term ) {

			$args = array(
				'post_type'   => $this->post_type->slug,
				'numberposts' => $this->args['number'],
				'tax_query'   => array(
					array(
						'taxonomy' => $this->post_type->tax( $this->args['taxonomy'] ),
						'field'    => 'term_id',
						'terms'    => $term->term_id,
					),
				),
			);

			$args = array_merge( $args, $this->get_posts_order_args() );

			if ( ! empty( $this->args['posts_args'] ) ) {
				$args = array_merge( $args, $this->args['posts_args'] );
			}

			$posts = get_posts( $args );

			if ( empty( $posts ) ) {
				return '';
			}

			ob_start();

			if ( file_exists( $this->args['posts_start'] ) ) {
				include $this->args['posts_start'];
			}

			global $post;

			foreach ( $posts as $post ) {

				setup_postdata( $post );

				if ( file_exists( $this->args['posts_loop'] ) ) {
					include $this->args['posts_loop'];
				}

			}

			wp_reset_postdata();

			if ( file_exists( $this->args['posts_end'] ) ) {
				include $this->args['posts_end'];
			}


			return ob_get_clean();

		}

		/**
		 * Returns terms order argumnets
		 *
		 * @return array
		 */
		public function get_terms_order_args() {

			if ( in_array( $this->args['terms_order'] , array( 'name', 'date' ) ) ) {
				return array( 'orderby' => $this->args['terms_order'] );
			}

			$args = array(
				'orderby' => 'meta_value_num',
			);

			$allowed = apply_filters(
				'wapu_core/listing_by_term/terms_order_fields',
				array(
					'order' => $this->post_type->order_meta,
				)
			);

			if ( isset( $allowed[ $this->args['terms_order'] ] ) ) {
				$args['meta_key'] = $allowed[ $this->args['terms_order'] ];
				return $args;
			} else {
				return array();
			}

		}

		/**
		 * Get posts order arguments.
		 *
		 * @return array
		 */
		public function get_posts_order_args() {

			if ( 'date' === $this->args['posts_order'] ) {
				return array();
			}

			$args = array(
				'orderby' => 'meta_value_num',
			);

			$allowed = apply_filters(
				'wapu_core/listing_by_term/posts_order_fields',
				array(
					'views' => $this->post_type->views_meta,
					'order' => $this->post_type->order_meta,
				)
			);

			if ( isset( $allowed[ $this->args['posts_order'] ] ) ) {
				$args['meta_key'] = $allowed[ $this->args['posts_order'] ];
				return $args;
			} else {
				return array();
			}

		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @return object
		 */
		public static function get_instance( $post_type = null, $args = array() ) {
			return new self( $post_type, $args );
		}
	}

}

/**
 * Returns instance of Wapu_Core_Listing_By_Terms
 *
 * @return object
 */
function wapu_core_listing_by_terms( $post_type = null, $args = array() ) {
	return Wapu_Core_Listing_By_Terms::get_instance( $post_type, $args );
}
