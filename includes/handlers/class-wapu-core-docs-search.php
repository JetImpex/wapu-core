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

if ( ! class_exists( 'Wapu_Core_Docs_Search' ) ) {

	/**
	 * Define Wapu_Core_Docs_Search class
	 */
	class Wapu_Core_Docs_Search {

		/**
		 * Name for database table
		 *
		 * @var string
		 */
		private $db_table = 'found_docs';

		/**
		 * Search handler arguments
		 *
		 * @var array
		 */
		public $args = array(
			'docs-base'        => 'https://documentation.jetimpex.com',
			'tm-base'          => 'https://www.templatemonster.com',
			'product-base'     => 'wordpress-themes',
			'doc-link-id'      => 'custom-documentation-link',
			'exceptions'       => array( 'monstroid2' => 'monstroid_2', 'monstroid'  => 'monstroid_2' ),
			'ignored-prefixes' => array( 'tm-', 'jx-' ),
			'name-hosts'       => array(
				'http://documentation.templatemonster.com/index.php?project=%s',
				'http://documentation.templatemonster.com/projects/%s/index.html',
			),
		);

		/**
		 * Sanitize callbacks
		 *
		 * @var array
		 */
		public $sanitize_cb = array();

		/**
		 * Templates API URL.
		 *
		 * @var string
		 */
		public $templates_api = 'http://api.templatemonster.com/products/v1/products/';

		/**
		 * Set arguments.
		 *
		 * @param array $args Arguments array.
		 */
		public function __construct( $args = array() ) {

			$this->args = wp_parse_args( $args, $this->args );

			$this->sanitize_cb = array(
				'http[s]?:\/\/documentation\.[a-z]*\.com\/[a-zA-Z-_\/]*index\.php\?project=' => array( $this, 'sanitize_old_wp' ),
			);
		}

		/**
		 * Perform serach by documentataion
		 *
		 * @param  string $query Search query.
		 * @return string|bool
		 */
		public function run( $query ) {

			$query  = trim( $query );
			$query  = $this->sanitize_query( $query );
			$stored = $this->try_get_stored_result( $query );

			if ( ! empty( $stored ) ) {
				return $stored;
			}

			if ( 0 < intval( $query ) ) {
				$result = $this->search_by_template_id( $query );
			} else {
				$result = $this->search_by_theme_name( $query );
			}

			if ( ! empty( $result ) ) {
				$this->store_result( $query, $result );
				return $result;
			} else {
				return false;
			}
		}

		/**
		 * Sanitize exceptions before starting processing.
		 *
		 * @param  string $query Search query
		 * @return [type]        [description]
		 */
		public function sanitize_query( $query ) {

			$query      = strtolower( $query );
			$exceptions = $this->args['exceptions'];

			if ( empty( $exceptions ) || ! array_key_exists( $query, $exceptions ) ) {
				return $query;
			}

			return $exceptions[ $query ];

		}

		/**
		 * Try to search by template ID.
		 *
		 * @param  int $id Template ID.
		 * @return string|bool
		 */
		public function search_by_template_id( $id ) {

			$api_result = $this->get_by_id_from_api( $id );

			if ( $api_result && ! empty( $this->args['docs-base'] ) ) {
				return trailingslashit( $this->args['docs-base'] ) . $api_result;
			}

			$request_url = sprintf( '%s/%s/%d.html', $this->args['tm-base'], $this->args['product-base'], $id );
			$regex       = '/<a id="' . $this->args['doc-link-id'] . '".+?href="(.+?)"/';

			$response = wp_remote_get(
				$request_url,
				array(
					'timeout' => 30,
				)
			);

			$body = wp_remote_retrieve_body( $response );

			preg_match( $regex, $body, $matches );

			if ( ! empty( $matches[1] ) ) {
				return esc_url( $matches[1] );
			} else {
				return false;
			}

		}

		/**
		 * Try to search by template ID in TM API.
		 *
		 * @param  int $id Template ID.
		 * @return string|bool
		 */
		public function get_by_id_from_api( $id = 0 ) {

			$response = wp_remote_get( $this->templates_api . $id );
			$body     = wp_remote_retrieve_body( $response );
			$data     = json_decode( $body, true );

			if ( ! isset( $data['properties'] ) ) {
				return false;
			}

			foreach ( $data['properties'] as $property ) {
				if ( 'documentation-link' === $property['propertyName'] && ! empty( $property['value'] ) ) {
					return $property['value'];
				}
			}

			return false;
		}

		/**
		 * Search documentation by name.
		 *
		 * @param  string $query Searc query.
		 * @return string|bool
		 */
		public function search_by_theme_name( $query ) {

			if ( ! empty( $this->args['ignored-prefixes'] ) ) {
				$query = str_replace( $this->args['ignored-prefixes'], '', $query );
			}

			foreach ( array( '_', '-', '' ) as $replace ) {

				$replaced = str_replace( ' ', $replace, $query );
				$result   = $this->request_by_name( $replaced );

				if ( $result ) {
					return $result;
				}

			}

		}

		/**
		 * Perforem requests by theme name
		 *
		 * @param  string $name Theme name to search.
		 * @return string|bool
		 */
		public function request_by_name( $name ) {

			$hosts = $this->args['name-hosts'];

			if ( ! is_array( $hosts ) ) {
				return false;
			}

			foreach ( $hosts as $host ) {

				$result = $this->single_request( $host, $name );

				if ( ! empty( $result ) ) {
					return $result;
				}

			}

			return false;
		}

		/**
		 * Perform request for single host.
		 *
		 * @param  string $host Host mask.
		 * @param  string $name Theme name.
		 * @return string|bool
		 */
		public function single_request( $host, $name ) {

			$request_url = sprintf( $host, $name );

			$response = wp_remote_get(
				$request_url,
				array(
					'timeout'   => 30,
					'sslverify' => false,
				)
			);

			$status = wp_remote_retrieve_response_code( $response );

			if ( 404 === $status ) {
				return false;
			}

			$body = wp_remote_retrieve_body( $response );

			if ( empty( $body ) ) {
				return false;
			}

			return $this->sanitize_request( $request_url, $body, $name );
		}

		/**
		 * Sanitize request.
		 *
		 * @param  string $request_url [description]
		 * @param  string $body        [description]
		 * @param  string $name        [description]
		 * @return string
		 */
		public function sanitize_request( $request_url, $body, $name ) {

			if ( empty( $this->sanitize_cb ) ) {
				return $request_url;
			}

			foreach ( $this->sanitize_cb as $regex => $cb ) {

				if ( ! preg_match( '/' . $regex . '/', $request_url ) ) {
					continue;
				}

				return call_user_func( $cb, $body, $request_url, $name );
			}

			return $request_url;
		}

		/**
		 * Sanitize old WordPress docs
		 *
		 * @return string|bool
		 */
		public function sanitize_old_wp( $response, $request_url, $name ) {

			preg_match( '/<body.+?data-project="(.+?)"/', $response, $matches );

			if ( ! isset( $matches[1] ) ) {
				return false;
			}

			if ( $matches[1] !== $name ) {
				return false;
			}

			return $request_url;
		}

		/**
		 * Save found result
		 *
		 * @param  string $result Found URL.
		 * @return void
		 */
		public function store_result( $query, $result ) {

			if ( ! $this->validate_db_table() ) {
				return false;
			}

			global $wpdb;

			$insert = array(
				'query_hash' => $this->query_hash( $query ),
				'url'        => $result,
			);

			$format = array( '%s', '%s' );

			$inserted = $wpdb->insert(
				$this->table_name(),
				array(
					'query_hash' => $this->query_hash( $query ),
					'url'        => $result,
				),
				array(
					'%s',
					'%s',
				)
			);

		}

		/**
		 * Try to get stored result for passed query.
		 *
		 * @param  string $query Search query.
		 * @return string|bool
		 */
		public function try_get_stored_result( $query ) {

			if ( ! $this->validate_db_table() ) {
				return false;
			}

			global $wpdb;

			$query = $wpdb->prepare(
				"SELECT url FROM {$this->table_name()} WHERE query_hash = %s",
				$this->query_hash( $query )
			);

			$result = $wpdb->get_var( $query );

			if ( ! $result ) {
				return false;
			} else {
				return $result;
			}

		}

		/**
		 * Check, is database table exists, create if not.
		 *
		 * @return bool
		 */
		public function validate_db_table() {

			global $wpdb;

			if ( $wpdb->get_var( "SHOW TABLES LIKE '{$this->table_name()}'" ) === $this->table_name() ) {
				return true;
			}

			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE {$this->table_name()} (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				query_hash text NOT NULL,
				url text NOT NULL,
				PRIMARY KEY (id)
			) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );

			return true;
		}

		/**
		 * Return hash for passed key
		 *
		 * @param  string $query Query.
		 * @return string
		 */
		public function query_hash( $query ) {
			return md5( sanitize_text_field( $query ) );
		}

		/**
		 * Return prefixed DB table name
		 * @return string
		 */
		public function table_name() {
			global $wpdb;
			return $wpdb->prefix . $this->db_table;
		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @return object
		 */
		public static function get_instance( $args = array() ) {
			return new self( $args );
		}
	}

}

/**
 * Returns instance of Wapu_Core_Docs_Search
 *
 * @return object
 */
function wapu_core_docs_search( $args = array() ) {
	return Wapu_Core_Docs_Search::get_instance( $args );
}
