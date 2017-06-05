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

if ( ! class_exists( 'Wapu_Core_Faq' ) ) {

	/**
	 * Define Wapu_Core_Faq class
	 */
	class Wapu_Core_Faq extends Wapu_Core_Post_Type {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		public function init() {

			$this->slug = 'faq';
			$this->single_name = esc_html__( 'Question', 'wapu-core' );
			$this->plural_name = esc_html__( 'FAQ', 'wapu-core' );
			$this->args = array(
				'menu_icon'          => 'dashicons-editor-help',
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
					'single_name' => esc_html__( 'Category', 'wapu-core' ),
					'plural_name' => esc_html__( 'Categories', 'wapu-core' ),
					'args'        => array(
						'hierarchical'      => true,
						'show_admin_column' => true,
					),
				),
			);

			wapu_core_related_posts()->add( $this->slug );

			add_action( 'pre_get_posts', array( $this, 'per_page' ) );

			parent::init();

			wapu_core()->get_core()->init_module( 'cherry-post-meta', array(
				'id'            => 'faq-order',
				'title'         => esc_html__( 'Post Order', 'wapu-core' ),
				'page'          => array( $this->slug ),
				'context'       => 'normal',
				'priority'      => 'high',
				'callback_args' => false,
				'fields' => array(
					$this->order_meta => array(
						'type'  => 'text',
						'value' => '1',
						'title' => esc_html__( 'Post order (-1, -10, 1, 99 etc.) WARNING - 0 is not allowed as order value', 'wapu-core' ),
					),
				),
			) );

			wapu_core()->get_core()->init_module( 'cherry-term-meta', array(
				'tax'      => $this->tax( 'category' ),
				'priority' => 10,
				'fields'   => array(
					$this->order_meta => array(
						'type'  => 'text',
						'value' => '1',
						'label' => esc_html__( 'Category order (-1, -10, 1, 99 etc.) WARNING - 0 is not allowed as order value', 'wapu-core' ),
					),
				),
			) );
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

			if ( ! is_singular() && $this->slug === get_query_var( 'post_type' ) ) {
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
 * Returns instance of Wapu_Core_Faq
 *
 * @return object
 */
function wapu_core_faq() {
	return Wapu_Core_Faq::get_instance();
}
