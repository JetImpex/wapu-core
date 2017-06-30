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

if ( ! class_exists( 'Wapu_Core_How_To' ) ) {

	/**
	 * Define Wapu_Core_How_To class
	 */
	class Wapu_Core_How_To extends Wapu_Core_Post_Type {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		public function init() {

			$this->slug = 'how-to';
			$this->single_name = esc_html__( 'How To', 'wapu-core' );
			$this->plural_name = esc_html__( 'How To', 'wapu-core' );
			$this->args = array(
				'menu_icon'          => 'dashicons-hammer',
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'query_var'          => true,
				'capability_type'    => 'post',
				'has_archive'        => true,
				'hierarchical'       => false,
				'menu_position'      => 26,
				'supports'           => array(
					'title',
					'editor',
					'thumbnail',
					'excerpt',
				),
			);
			$this->rewrite_options = true;
			$this->taxonomies  = array(
				'category' => array(
					'single_name'       => esc_html__( 'Category', 'wapu-core' ),
					'plural_name'       => esc_html__( 'Categories', 'wapu-core' ),
					'breadcrumbs_trail' => true,
					'args'              => array(
						'hierarchical'      => true,
						'show_admin_column' => true,
					),
				),
			);

			wapu_core_related_posts()->add( $this->slug );

			add_action( 'pre_get_posts', array( $this, 'per_page' ) );

			parent::init();
		}

		/**
		 * A
		 * @param  [type] $query [description]
		 * @return [type]        [description]
		 */
		public function per_page( $query ) {

			if ( ! $query->is_main_query() ) {
				return;
			}

			$object = $query->get_object_vars();

			if ( ! isset( $object->taxonomy ) ) {
				return;
			}

			if ( in_array( $object->taxonomy, $this->taxonomies() ) ) {
				$query->set( 'posts_per_page', '-1' );
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
 * Returns instance of Wapu_Core_How_To
 *
 * @return object
 */
function wapu_core_how_to() {
	return Wapu_Core_How_To::get_instance();
}
