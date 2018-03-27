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

if ( ! class_exists( 'Wapu_Core_EDD_Badges' ) ) {

	/**
	 * Define Wapu_Core_EDD_Badges class
	 */
	class Wapu_Core_EDD_Badges {

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
		public function __construct() {
			$this->register_badges();
		}

		/**
		 * Get registered badges data
		 * @return [type] [description]
		 */
		public function get_badges() {

			return array(
				'ecommerce' => array(
					'label'  => 'E-Commerce',
					'admin'  => array( $this, '_register_ecommerce_control' ),
					'public' => array( $this, 'is_ecommerce' ),
				),
				'featured' => array(
					'label'  => 'Featured',
					'admin'  => array( $this, '_register_featured_control' ),
					'public' => array( $this, 'is_featured' ),
				),
				'bestseller' => array(
					'label'  => 'Bestseller',
					'admin'  => false,
					'public' => array( $this, 'is_bestseller' ),
				),
				'new' => array(
					'label'  => 'New',
					'admin'  => false,
					'public' => array( $this, 'is_new' ),
				),
			);

		}

		/**
		 * Get badges list
		 *
		 * @param  object $post Current post object
		 * @return [type]       [description]
		 */
		public function get_post_badges( $post ) {

			$badges = $this->get_badges();
			$result = array();

			foreach ( $badges as $badge_id => $badge ) {

				if ( empty( $badge['public'] ) ) {
					continue;
				}

				if ( true === call_user_func( $badge['public'], $post ) ) {
					$result[ $badge_id ] = $badge['label'];
				}

			}

			return $result;

		}

		/**
		 * Check if is ecommerce
		 * @return boolean [description]
		 */
		public function is_ecommerce( $post ) {
			$value = get_post_meta( $post->ID, '_wapu_is_ecommerce', true );
			if ( 'yes' === $value ) {
				return true;
			}
			return false;
		}

		/**
		 * Check if is ecommerce
		 * @return boolean [description]
		 */
		public function is_featured( $post ) {
			$value = get_post_meta( $post->ID, '_wapu_is_featured', true );
			if ( 'yes' === $value ) {
				return true;
			}
			return false;
		}

		/**
		 * Check if is ecommerce
		 * @return boolean [description]
		 */
		public function is_bestseller( $post ) {

			global $edd_options;

			$min = isset( $edd_options['wapu_bestseller_min'] ) ? absint( $edd_options['wapu_bestseller_min'] ) : 30;
			$current = wapu_core()->edd->single->sales( '%s', $post->ID, false );
			$current = absint( $current );

			return $current >= $min;
		}

		/**
		 * Check if is ecommerce
		 * @return boolean [description]
		 */
		public function is_new( $post ) {

			global $edd_options;

			$post_date = strtotime( $post->post_date );
			$now       = time();
			$period    = isset( $edd_options['wapu_new_period'] ) ? absint( $edd_options['wapu_new_period'] ) : 12;
			$period    = absint( $period ) * HOUR_IN_SECONDS;
			$diff      = $now - $post_date;

			return $period >= $diff;
		}

		public function _register_ecommerce_control() {
			add_filter( 'wapu-core/edd/metabxes/misc', array( $this, 'ecommerce_control' ) );
		}

		public function _register_featured_control() {
			add_filter( 'wapu-core/edd/metabxes/misc', array( $this, 'featured_control' ) );
		}

		public function ecommerce_control( $fields ) {

			$fields['_wapu_is_ecommerce'] = array(
				'type'    => 'select',
				'title'   => esc_html__( 'Is E-commerce', 'wapu-core' ),
				'options' => array(
					'no'  => 'No',
					'yes' => 'Yes',
				),
			);

			return $fields;
		}

		public function featured_control( $fields ) {

			$fields['_wapu_is_featured'] = array(
				'type'    => 'select',
				'title'   => esc_html__( 'Is Featured', 'wapu-core' ),
				'options' => array(
					'no'  => 'No',
					'yes' => 'Yes',
				),
			);

			return $fields;
		}

		/**
		 * Register new bages
		 *
		 * @return [type] [description]
		 */
		public function register_badges() {

			$badges = $this->get_badges();

			foreach ( $badges as $badge_id => $badge ) {

				if ( empty( $badge['admin'] ) ) {
					continue;
				}

				call_user_func( $badge['admin'] );
			}

		}

	}

}
