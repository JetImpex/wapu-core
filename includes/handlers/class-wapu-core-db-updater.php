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

if ( ! class_exists( 'Wapu_Core_DB_Updater' ) ) {

	/**
	 * Define Wapu_Core_DB_Updater class
	 */
	class Wapu_Core_DB_Updater {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Update to version 1.0.1 callback
		 *
		 * @return void
		 */
		public function update101() {

			// update faq posts and terms
			$faqs = get_posts( array(
				'post_type'      => wapu_core_faq()->slug,
				'posts_per_page' => -1,
			) );

			if ( ! empty( $faqs ) ) {
				foreach ( $faqs as $faq ) {
					$order = get_post_meta( $faq->ID, wapu_core_faq()->order_meta, true );
					if ( '' === $order ) {
						update_post_meta( $faq->ID, wapu_core_faq()->order_meta, '1' );
					}
				}
			}

			unset( $order );

			$terms = get_terms( array(
				'taxonomy'   => wapu_core_faq()->tax( 'category' ),
				'hide_empty' => false,
			) );

			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					$order = get_term_meta( $term->term_id, wapu_core_faq()->order_meta, true );
					if ( '' === $order ) {
						update_term_meta( $term->term_id, wapu_core_faq()->order_meta, '1' );
					}
				}
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
 * Returns instance of Wapu_Core_DB_Updater
 *
 * @return object
 */
function wapu_core_db_updater() {
	return Wapu_Core_DB_Updater::get_instance();
}
