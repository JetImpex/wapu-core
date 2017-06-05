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

if ( ! class_exists( 'Wapu_Core_Search_Tax' ) ) {

	/**
	 * Define Wapu_Core_Search_Tax class
	 */
	class Wapu_Core_Search_Tax {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Allowed post types array
		 *
		 * @var array
		 */
		private $post_types = array( 'post' );

		/**
		 * Search category
		 *
		 * @var string
		 */
		public $tax = 'search-category';

		/**
		 * Constructor for the class
		 */
		public function init() {
			add_filter( 'init', array( $this, 'register_tax' ), 99 );
			add_filter( 'cherry_search-category_list_type', array( $this, 'search_tax' ) );
		}

		/**
		 * Register taxonomy from Cherry search
		 *
		 * @return void
		 */
		public function register_tax() {

			$args = array(
				'label'        => esc_html__( 'Search Category', 'wapu-core' ),
				'hierarchical' => true
			);

			register_taxonomy( $this->tax, $this->get_post_types(), $args );

		}

		/**
		 * Pass taxonomy into search plugins
		 *
		 * @return array
		 */
		public function search_tax( $default = array() ) {
			return array( $this->tax );
		}

		/**
		 * Return post types supports Search  Category
		 *
		 * @return array
		 */
		public function get_post_types() {

			return $this->post_types;

		}

		/**
		 * Add passed post type into supported post tyes list
		 *
		 * @param array $post_type
		 */
		public function add_post_type( $post_type ) {
			$this->post_types[] = $post_type;
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
 * Returns instance of Wapu_Core_Search_Tax
 *
 * @return object
 */
function wapu_core_search_tax() {
	return Wapu_Core_Search_Tax::get_instance();
}
