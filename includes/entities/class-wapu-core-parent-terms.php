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

if ( ! class_exists( 'Wapu_Core_Parent_Terms' ) ) {

	/**
	 * Define Wapu_Core_Parent_Terms class
	 */
	class Wapu_Core_Parent_Terms {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Constructor for the class
		 */
		public function init() {

			if ( is_main_site() ) {
				return;
			}

			add_action( 'init', array( $this, 'init_meta_boxes' ), 99 );
			add_action( 'admin_enqueue_scripts', array( $this, 'hide_tax_boxes' ) );

		}

		public function hide_tax_boxes( $hook ) {

			if ( 'post.php' === $hook && 'post' === get_post_type() ) {
				echo '<style>#categorydiv, #tagsdiv-post_tag { display: none !important; }</style>';
			}

			if ( 'edit.php'=== $hook && 'post' === get_post_type() ) {
				echo '<style>.category-checklist, .inline-edit-tags, .inline-edit-col .inline-edit-categories-label:first-child { display: none !important; }</style>';
			}

		}

		/**
		 * Init category and tag metaboxes
		 *
		 * @return [type] [description]
		 */
		public function init_meta_boxes() {

			wapu_core()->get_core()->init_module( 'cherry-post-meta', array(
				'id'            => 'parent-categories',
				'title'         => esc_html__( 'Parent Taxonomies', 'wapu-core' ),
				'page'          => array( 'post' ),
				'context'       => 'normal',
				'priority'      => 'high',
				'callback_args' => false,
				'fields'        => array(
					'_wapu_cat'  => array(
						'type'             => 'select',
						'multiple'         => true,
						'title'            => esc_html__( 'Category', 'wapu-core' ),
						'options'          => false,
						'options_callback' => array( $this, 'get_parent_categories' ),
					),
					'_wapu_tag'  => array(
						'type'             => 'select',
						'multiple'         => true,
						'title'            => esc_html__( 'Tags', 'wapu-core' ),
						'options'          => false,
						'options_callback' => array( $this, 'get_parent_tags' ),
					),
				),
			) );

		}

		/**
		 * Get parent categories list
		 *
		 * @return array
		 */
		public function get_parent_categories() {

			switch_to_blog( wapu_core_posts_aggregator()->main_blog_id );

			$terms = get_categories( array(
				'hide_empty' => false,
			) );

			$result = array();

			if ( ! empty( $terms ) ) {
				$result = wp_list_pluck( $terms, 'name', 'term_id' );
			}

			restore_current_blog();

			return $result;
		}

		/**
		 * Get parent terms list
		 *
		 * @return array
		 */
		public function get_parent_tags() {

			switch_to_blog( wapu_core_posts_aggregator()->main_blog_id );

			$terms = get_tags( array(
				'hide_empty' => false,
			) );

			$result = array();

			if ( ! empty( $terms ) ) {
				$result = wp_list_pluck( $terms, 'name', 'term_id' );
			}

			restore_current_blog();

			return $result;
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
 * Returns instance of Wapu_Core_Parent_Terms
 *
 * @return object
 */
function wapu_core_parent_terms() {
	return Wapu_Core_Parent_Terms::get_instance();
}
