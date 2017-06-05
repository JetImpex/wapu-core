<?php
/**
 * Related posts handler
 *
 * @package   package_name
 * @author    Cherry Team
 * @license   GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Wapu_Core_Related_Posts' ) ) {

	/**
	 * Define Wapu_Core_Related_Posts class
	 */
	class Wapu_Core_Related_Posts {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Related taxonomy name.
		 *
		 * @var string
		 */
		public $tax = 'related-tags';

		/**
		 * Allowed post types
		 * @var array
		 */
		private $objects = array();

		/**
		 * Register relate posts taxonomy.
		 *
		 * @return void
		 */
		public function init() {

			$objects = $this->get_registered_post_types();

			if ( ! $objects ) {
				return;
			}

			$labels = array(
				'name'                       => esc_html__( 'Related Tags', 'wapu-core' ),
				'singular_name'              => esc_html__( 'Tag', 'wapu-core' ),
				'search_items'               => esc_html__( 'Search Tags', 'wapu-core' ),
				'popular_items'              => esc_html__( 'Popular Tags', 'wapu-core' ),
				'all_items'                  => esc_html__( 'All Tags', 'wapu-core' ),
				'parent_item'                => null,
				'parent_item_colon'          => null,
				'edit_item'                  => esc_html__( 'Edit Tag', 'wapu-core' ),
				'update_item'                => esc_html__( 'Update Tag', 'wapu-core' ),
				'add_new_item'               => esc_html__( 'Add New Tag', 'wapu-core' ),
				'new_item_name'              => esc_html__( 'New Tag Name', 'wapu-core' ),
				'separate_items_with_commas' => esc_html__( 'Separate tags with commas', 'wapu-core' ),
				'add_or_remove_items'        => esc_html__( 'Add or remove tags', 'wapu-core' ),
				'choose_from_most_used'      => esc_html__( 'Choose from the most used tags', 'wapu-core' ),
				'not_found'                  => esc_html__( 'No tags found.', 'wapu-core' ),
				'menu_name'                  => esc_html__( 'Related Tags', 'wapu-core' ),
			);

			$args = array(
				'hierarchical'          => false,
				'labels'                => $labels,
				'show_ui'               => true,
				'show_admin_column'     => false,
				'update_count_callback' => '_update_post_term_count',
				'query_var'             => true,
				'rewrite'               => false,
			);

			register_taxonomy( $this->tax, $objects, $args );
		}

		/**
		 * Returns related posts query for passed post ID, or for current post
		 *
		 * @param  int   $post_id (optional) Post Id to get related postss for.
		 * @param  array $args    (optional) Custom arguments array.
		 * @return array
		 */
		public function query( $post_id = null, $args = array() ) {

			$post_types = $this->get_registered_post_types();

			if ( empty( $post_types ) ) {
				return array();
			}

			if ( ! $post_id ) {
				$post_id = get_the_id();
			}

			$terms = get_the_terms( $post_id, $this->tax );

			if ( ! $terms ) {
				return array();
			}

			$post_terms  = array();
			$post_terms = wp_list_pluck( $terms, 'term_id' );

			$args = wp_parse_args( $args, array(
				'post_type'    => $post_types,
				'numberposts'  => 5,
				'post__not_in' => array( $post_id ),
			) );

			$args['tax_query'] = array(
				array(
					'taxonomy' => $this->tax,
					'field'    => 'term_id',
					'terms'    => $post_terms,
				),
			);

			return get_posts( $args );
		}

		/**
		 * Add new object into objects array
		 *
		 * @param string $object Object to add
		 */
		public function add( $object = null ) {

			if ( ! empty( $object ) ) {
				$this->objects[] = $object;
			}

		}

		/**
		 * Returns registered post types array
		 *
		 * @return array
		 */
		public function get_registered_post_types() {

			return array_unique( $this->objects );

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
 * Returns instance of Wapu_Core_Related_Posts
 *
 * @return object
 */
function wapu_core_related_posts() {
	return Wapu_Core_Related_Posts::get_instance();
}
