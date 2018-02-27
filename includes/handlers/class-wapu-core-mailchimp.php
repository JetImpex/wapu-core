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

if ( ! class_exists( 'Wapu_Core_MailChimp' ) ) {

	/**
	 * Define Wapu_Core_MailChimp class
	 */
	class Wapu_Core_MailChimp {

		private $api_key;
		private $list_id;

		/**
		 * MailChimp API server
		 *
		 * @var string
		 */
		private $api_server = 'https://%s.api.mailchimp.com/3.0/';

		/**
		 * Constructor for the class
		 */
		public function __construct( $api_key = null, $list_id = null ) {
			$this->api_key = $api_key;
			$this->list_id = $list_id;
		}

		/**
		 * Subscribe user to list
		 *
		 * @return [type] [description]
		 */
		public function subscribe( $args = array() ) {

			$list_id = $this->list_id;

			if ( ! $list_id ) {
				return;
			}

			$args = array_merge( array(
				'email' => '',
				'fname' => '',
				'lname' => '',
			), $args );

			if ( empty( $args['email'] ) ) {
				return;
			}

			$request_args = array(
				'email_address' => $args['email'],
				'status'        => 'subscribed',
			);

			if ( ! empty( $args['fname'] ) ) {
				$request_args['merge_fields'] = array();
				$request_args['merge_fields']['FNAME'] = $args['fname'];
			}

			if ( ! empty( $args['lname'] ) ) {
				$request_args['merge_fields'] = ! empty( $args['merge_fields'] ) ? $args['merge_fields'] : array();
				$request_args['merge_fields']['LNAME'] = $args['fname'];
			}

			$path = 'lists/' . $list_id . '/members/';

			return $this->api_call( $path, $request_args );
		}

		/**
		 * Remove email from subscribers list
		 *
		 * @param  [type] $email [description]
		 * @return [type]        [description]
		 */
		public function change_status( $email, $status = 'subscribed' ) {

			$list_id = $this->list_id;

			if ( ! $list_id ) {
				return;
			}

			$path = 'lists/' . $list_id . '/members/' . md5( $email );

			return $this->api_call(
				$path,
				array(
					'status' => esc_attr( $status ),
				),
				'PATCH'
			);

		}

		/**
		 * Make remote request to mailchimp API
		 *
		 * @param  string $path  API path to call.
		 * @param  array  $args  API call arguments.
		 * @return array|bool
		 */
		public function api_call( $path, $args = array(), $method = 'post' ) {

			if ( ! $path ) {
				return false;
			}

			$api_key = $this->api_key;

			if ( ! $api_key ) {
				return false;
			}

			$key_data = explode( '-', $api_key );

			if ( empty( $key_data ) || ! isset( $key_data[1] ) ) {
				return false;
			}

			$api_server   = sprintf( $this->api_server, $key_data[1] );
			$url          = esc_url( $api_server . $path );
			$request_args = array(
				'headers' => array(
					'Authorization' => 'Basic ' . base64_encode( 'user:'. $api_key ),
				),
				'body' => json_encode( $args ),
			);

			switch ( $method ) {

				case 'post':
					$request = wp_remote_post( $url, $request_args );
					break;

				default:
					$request_args['method'] = $method;
					$request                = wp_remote_request( $url, $request_args );
					break;
			}

			return json_decode( wp_remote_retrieve_body( $request ), true );

		}

	}

}
