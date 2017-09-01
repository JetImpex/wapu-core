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

if ( ! class_exists( 'Wapu_Core_Custom_Breadcrumbs' ) ) {

	/**
	 * Define Wapu_Core_Custom_Breadcrumbs class
	 */
	class Wapu_Core_Custom_Breadcrumbs {

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
			add_filter( 'cherry_breadcrumbs_items', array( $this, 'modify_breadcrumbs' ), 10, 3 );
		}

		/**
		 * Modify breadcrumbs callback
		 *
		 * @return array
		 */
		public function modify_breadcrumbs( $items, $args, $instance ) {

			if ( ! is_multisite() || is_main_site() ) {
				return $items;
			}

			$url   = esc_url( network_home_url() );
			$label = esc_html__( 'Home', 'wapu-core' );

			$instance->_add_item( 'target_format', $label, $url, '', true );

			return $this->maybe_fix_duplicate_links( $instance->items );
		}

		/**
		 * Remove duplicating links (currently may appears in custom post types categories)
		 *
		 * @param  [type] $items [description]
		 * @return [type]        [description]
		 */
		public function maybe_fix_duplicate_links( $items ) {

			$maybe_duplicated_1 = isset( $items[1] ) ? $items[1] : false;
			$maybe_duplicated_2 = isset( $items[2] ) ? $items[2] : false;

			if ( ! $maybe_duplicated_1 || ! $maybe_duplicated_2 ) {
				return $items;
			}

			$pattern = '/href="(.*?)"/';

			preg_match( $pattern, $maybe_duplicated_1, $url_1 );
			preg_match( $pattern, $maybe_duplicated_2, $url_2 );

			if ( empty( $url_1 ) || empty( $url_2 ) ) {
				return $items;
			}

			if ( $url_1[1] !== $url_2[1] ) {
				return $items;
			}

			unset( $items[1] );

			return $items;
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
 * Returns instance of Wapu_Core_Custom_Breadcrumbs
 *
 * @return object
 */
function wapu_core_custom_breadcrumbs() {
	return Wapu_Core_Custom_Breadcrumbs::get_instance();
}
