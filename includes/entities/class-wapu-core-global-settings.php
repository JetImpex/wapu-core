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

if ( ! class_exists( 'Wapu_Core_Global_Settings' ) ) {

	/**
	 * Define Wapu_Core_Global_Settings class
	 */
	class Wapu_Core_Global_Settings {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Settings object instance
		 *
		 * @var object
		 */
		private $settings;

		/**
		 * Initialize class
		 *
		 * @return void
		 */
		public function init() {

			$this->settings = wapu_core_settings_page( apply_filters( 'wapu_core/general_setting', array(
				'slug'     => 'wapu_core',
				'parent'   => 'options-general.php',
				'title'    => esc_html__( 'JetImpex', 'wapu-core' ),
				'tabs'     => array(
					'general' => esc_html__( 'General', 'wapu-core' ),
				),
				'controls' => array(
					'department' => array(
						'type'       => 'text',
						'id'         => 'department',
						'name'       => 'department',
						'value'      => 'JetImpex',
						'label'      => esc_html__( 'Department Name (WordPress, Joomla etc)', 'wapu-core' ),
						'parent'     => 'general',
					),
				),
			) ) );

			add_filter( 'wapu_tagline', array( $this, 'set_tagline' ) );

		}

		/**
		 * Set tagline
		 */
		public function set_tagline( $tagline ) {

			if ( ! is_multisite() || is_main_site() ) {
				return $tagline;
			}

			return $this->get( 'department' );
		}

		/**
		 * Get value from global settings by name
		 *
		 * @param  [type] $name [description]
		 * @return [type]       [description]
		 */
		public function get( $name ) {
			return $this->settings->get( $name );
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
 * Returns instance of Wapu_Core_Global_Settings
 *
 * @return object
 */
function wapu_core_global_settings() {
	return Wapu_Core_Global_Settings::get_instance();
}
