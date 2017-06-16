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

if ( ! class_exists( 'Wapu_Core_Post_Type_Switcher' ) ) {

	/**
	 * Define Wapu_Core_Post_Type_Switcher class
	 */
	class Wapu_Core_Post_Type_Switcher {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Initialize entetie
		 *
		 * @return [type] [description]
		 */
		public function init() {
			add_action( 'init', array( $this, 'init_metabox' ) );
		}

		/**
		 * Initialize metabox
		 *
		 * @return [type] [description]
		 */
		public function init_metabox() {

			$allowed_post_types = $this->get_allowed_post_types();

			wapu_core()->get_core()->init_module( 'cherry-post-meta', array(
				'id'            => 'move-post',
				'title'         => esc_html__( 'Video URL', 'wapu-core' ),
				'page'          => array_keys( $allowed_post_types ),
				'context'       => 'normal',
				'priority'      => 'high',
				'callback_args' => false,
				'fields' => array(
					'_wapu_move_post' => array(
						'type'   => 'switcher',
						'label'  => esc_html__( 'Move This Post?', 'wapu-core' ),
						'toggle' => array(
							'true_toggle'  => 'Yes',
							'false_toggle' => 'No',
						),
					),
					'_wapu_move_destination' => array(
						'type'             => 'select',
						'label'            => esc_html__( 'Move To:', 'wapu-core' ),
						'options_callback' => array( $this, 'prepare_destinations' ),
					),
				),
			) );

		}

		public function prepare_destinations() {

			$result  = array( 0 => esc_html__( 'Select Post Type', 'wapu-core' ) );
			$current = get_post_type();
			$allowed = $this->get_allowed_post_types();

			if ( isset( $allowed[ $current ] ) ) {
				unset( $allowed[ $current ] );
			}

			return array_merge( $result, $allowed );
		}

		/**
		 * Returns allowed post types slugs list
		 *
		 * @return array
		 */
		public function get_allowed_post_types() {
			return apply_filters( 'wapu_core/post_type_switcher/allowed_post_types', array() );
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
 * Returns instance of Wapu_Core_Post_Type_Switcher
 *
 * @return object
 */
function wapu_core_post_type_switcher() {
	return Wapu_Core_Post_Type_Switcher::get_instance();
}
