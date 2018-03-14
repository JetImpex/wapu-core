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

if ( ! class_exists( 'Wapu_Core_Meta_Cache' ) ) {

	/**
	 * Define Wapu_Core_Meta_Cache class
	 */
	class Wapu_Core_Meta_Cache {

		private $post_id = null;

		/**
		 * Constructor for the class
		 */
		public function __construct( $post_id ) {
			$this->post_id = $post_id;
		}

		/**
		 * Try to print meta cache
		 *
		 * @param  [type] $key [description]
		 * @return [type]      [description]
		 */
		public function get( $key = null ) {

			$cached = get_post_meta( $this->post_id, $key, true );

			if ( ! $cached ) {
				return false;
			} else {
				echo $cached;
				return true;
			}

		}

		/**
		 * Try to print meta cache
		 *
		 * @param  [type] $key [description]
		 * @return [type]      [description]
		 */
		public function update( $key = null, $value ) {

			update_post_meta( $this->post_id, $key, $value );

		}

		/**
		 * Try to print meta cache
		 *
		 * @param  [type] $key [description]
		 * @return [type]      [description]
		 */
		public function delete( $key = null ) {

			delete_post_meta( $this->post_id, $key );

		}

	}

}
