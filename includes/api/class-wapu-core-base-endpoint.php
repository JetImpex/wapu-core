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

if ( ! class_exists( 'Wapu_Core_Base_Endpoint' ) ) {

	/**
	 * Define Wapu_Core_Base_Endpoint class
	 */
	abstract class Wapu_Core_Base_Endpoint {

		/**
		 * Constructor for the class
		 */
		function __construct() {
			if ( ! $this->is_ajax() ) {
				add_action( 'rest_api_init', array( $this, 'add_endpoint' ) );
			} else {
				add_action( 'wp_ajax_' . $this->route(), array( $this, '_callback' ) );
				add_action( 'wp_ajax_nopriv_' . $this->route(), array( $this, '_callback' ) );
			}
		}

		/**
		 * Check if is AJAX endpoint
		 *
		 * @return boolean
		 */
		public function is_ajax() {
			return false;
		}

		/**
		 * Returns namespace
		 *
		 * @return [type] [description]
		 */
		public function api_namespace() {
			return 'wapu';
		}

		public function version() {
			return 'v1';
		}

		/**
		 * Should rturn API methods
		 * GET, POST, PUT etc.
		 *
		 * @return string|array
		 */
		abstract function methods();

		/**
		 * Should rturn API route inside name space
		 * /themes etc.
		 *
		 * @return string
		 */
		abstract function route();

		/**
		 * API callback function
		 *
		 * @return void
		 */
		abstract function callback( $params );

		public function _callback( $params ) {

			$result      = $this->callback( $params );
			$response    = isset( $result['response'] ) ? $result['response'] : '';
			$status_code = isset( $result['code'] ) ? $result['code'] : 400;

			if ( $this->is_ajax() ) {
				wp_send_json( $response, $status_code );
			} else {
				return new WP_REST_Response( $response, $status_code );
			}
		}

		/**
		 * Add API endpoint
		 */
		public function add_endpoint() {
			register_rest_route(
				$this->api_namespace() . '/' . $this->version(),
				$this->route(),
				array(
					'methods'  => $this->methods(),
					'callback' => array( $this, '_callback' ),
				)
			);
		}

	}

}
