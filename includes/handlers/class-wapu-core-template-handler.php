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

if ( ! class_exists( 'Wapu_Core_Template_Handler' ) ) {

	/**
	 * Define Wapu_Core_Template_Handler class
	 */
	class Wapu_Core_Template_Handler {

		public $slug       = null;
		public $taxonomies = array();

		/**
		 * Constructor for the class
		 */
		function __construct( $slug = null, $taxonomies = array() ) {

			$this->slug       = $slug;
			$this->taxonomies = $taxonomies;

			add_filter( 'template_include', array( $this, 'rewrite_templates' ) );
		}

		/**
		 * Rewrite templates callback
		 *
		 * @param  string $template Default template path
		 * @return void
		 */
		function rewrite_templates( $template ) {

			$base = 'post-types/' . $this->slug . '/';

			if ( is_singular( $this->slug ) ) {
				wapu_core()->the_core_page();
				return wapu_core()->get_template( $base . 'single.php' );
			}

			if ( ! empty( $this->taxonomies ) && is_tax( $this->taxonomies ) ) {
				wapu_core()->the_core_page();
				return wapu_core()->get_template( $base . 'taxonomy.php' );
			}

			if ( is_post_type_archive( $this->slug ) ) {
				wapu_core()->the_core_page();
				return wapu_core()->get_template( $base . 'archive.php' );
			}

			return $template;
		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @return object
		 */
		public static function get_instance( $slug = null, $taxonomies = array() ) {
			return new self( $slug, $taxonomies );
		}
	}

}

/**
 * Returns instance of Wapu_Core_Template_Handler
 *
 * @return object
 */
function wapu_core_template_handler( $slug = null, $taxonomies = array() ) {
	return Wapu_Core_Template_Handler::get_instance( $slug, $taxonomies );
}
