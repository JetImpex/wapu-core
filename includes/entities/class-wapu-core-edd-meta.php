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

if ( ! class_exists( 'Wapu_Core_EDD_Meta' ) ) {

	/**
	 * Define Wapu_Core_EDD_Meta class
	 */
	class Wapu_Core_EDD_Meta {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * EDD post type
		 *
		 * @var string
		 */
		public $post_type = 'download';

		/**
		 * Constructor for the class
		 */
		public function __construct() {
			$this->register_metaboxes();
			$this->extend_default_metaboxes();
		}

		/**
		 * Register new metaboxes for EDD
		 * @return [type] [description]
		 */
		public function register_metaboxes() {

			wapu_core()->get_core()->init_module( 'cherry-post-meta', array(
				'id'            => 'wapu_gallery',
				'title'         => esc_html__( 'Gallery', 'wapu-core' ),
				'page'          => array( $this->post_type ),
				'context'       => 'normal',
				'priority'      => 'high',
				'callback_args' => false,
				'fields' => array(
					'_wapu_single_large' => array(
						'type'  => 'media',
						'title' => esc_html__( 'Single Large Image', 'wapu-core' ),
					),
				),
			) );

		}

		/**
		 * Extend default metaboxes for EDD
		 *
		 * @return [type] [description]
		 */
		public function extend_default_metaboxes() {

		}

	}

}
