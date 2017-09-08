<?php
/**
 * Row shortcode
 */

class Wapu_Core_Docs_Search_Shortcode extends Wapu_Core_Shortcode {

	/**
	 * Init shortcode properties
	 */
	public function __construct() {

		$this->tag = 'docs-search';

		$this->info = array(
			'group' => array(
				'name' => esc_html__( 'Content', 'wapu-core' ),
				'icon' => 'welcome-add-page',
				'slug' => 'wapu-content',
			),
			'shortcode' => array(
				'name' => esc_html__( 'Search For Documentation', 'wapu-core' ),
				'icon' => 'search',
			),
		);

		$this->args = array(
			'class' => array(
				'type'   => 'text',
				'title'  => esc_html__( 'Custom CSS class', 'wapu-core' ),
				'value'  => '',
			),
			'id' => array(
				'type'   => 'text',
				'title'  => esc_html__( 'Custom ID', 'wapu-core' ),
				'value'  => '',
			),
		);

		add_action( 'wp_ajax_wapu_core_search_docs',        array( $this, 'process_search' ) );
		add_action( 'wp_ajax_nopriv_wapu_core_search_docs', array( $this, 'process_search' ) );
		//add_action( 'init', array( $this, 'process_search' ) );

		add_filter( 'wapu_core/general_setting', array( $this, 'register_docs_settings' ) );

		$this->messages = apply_filters( 'wapu_core/shortcodes/docs_search/messages', array(
			'empty'        => esc_html__( 'Query can\'t be empty', 'wapu-core' ),
			'not-found'    => esc_html__( 'Results not found', 'wapu-core' ),
			'result-title' => esc_html__( 'Documentation Link:', 'wapu-core' ),
			'result-text'  => esc_html__( 'Documentation found. Redirecting...', 'wapu-core' ),
		) );

		add_action( 'admin_init', array( $this, 'reset_docs_cache' ) );

	}

	/**
	 * Reset documentation seearch cache
	 *
	 * @return void
	 */
	public function reset_docs_cache() {

		$password = wapu_core_global_settings()->get( 'reset-password' );

		if ( ! $password ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! isset( $_GET['reset_search_cache'] ) || $password !== $_GET['reset_search_cache'] ) {
			return;
		}

		global $wpdb;

		$table = $wpdb->prefix . 'found_docs';

		$res = $wpdb->query( "TRUNCATE TABLE $table" );
		wp_die( 'Cache cleared!' );

	}

	/**
	 * Register additional settings for documentation search.
	 *
	 * @param  array $settings Default settings array.
	 * @return array
	 */
	public function register_docs_settings( $settings ) {

		$settings['tabs']['docs']        = esc_html__( 'Docs Search', 'wapu-core' );
		$settings['controls']['docs-base'] = array(
			'type'        => 'text',
			'title'       => esc_html__( 'Documentation Server Base', 'wapu-core' ),
			'placeholder' => esc_html__( 'Documentation Server URL', 'wapu-core' ),
			'value'       => 'https://documentation.jetimpex.com',
			'parent'      => 'docs',
		);
		$settings['controls']['tm-base'] = array(
			'type'        => 'text',
			'title'       => esc_html__( 'TemplateMonster Base', 'wapu-core' ),
			'placeholder' => esc_html__( 'TemplateMonster home URL', 'wapu-core' ),
			'value'       => 'https://www.templatemonster.com',
			'parent'      => 'docs',
		);
		$settings['controls']['product-base'] = array(
			'type'        => 'text',
			'title'       => esc_html__( 'TemplateMonster product directory base', 'wapu-core' ),
			'placeholder' => esc_html__( 'Your product path insisde TemplateMonster website', 'wapu-core' ),
			'value'       => 'wordpress-themes',
			'parent'      => 'docs',
		);
		$settings['controls']['doc-link-id'] = array(
			'type'        => 'text',
			'title'       => esc_html__( 'Documentation link ID on TemplateMonster product page', 'wapu-core' ),
			'placeholder' => esc_html__( 'ID of HTML tag with documentation link', 'wapu-core' ),
			'value'       => 'custom-documentation-link',
			'parent'      => 'docs',
		);
		$settings['controls']['name-hosts'] = array(
			'type'        => 'textarea',
			'title'       => esc_html__( 'Hosts list, to search docs in', 'wapu-core' ),
			'placeholder' => esc_html__( 'Format: http://hostname.com?doc_id=%s, http://hostname.com/%s/index.html', 'wapu-core' ),
			'value'       => 'http://documentation.templatemonster.com/index.php?project=%s, http://documentation.templatemonster.com/projects/%s/index.html',
			'parent'      => 'docs',
		);
		$settings['controls']['exceptions'] = array(
			'type'        => 'textarea',
			'title'       => esc_html__( 'Aliases list', 'wapu-core' ),
			'placeholder' => esc_html__( 'Format: alias1:original, alias2:original', 'wapu-core' ),
			'value'       => 'monstroid2::monstroid_2, monstroid::monstroid_2',
			'parent'      => 'docs',
		);
		$settings['controls']['ignored-prefixes'] = array(
			'type'        => 'textarea',
			'title'       => esc_html__( 'Ignored prefixes list', 'wapu-core' ),
			'placeholder' => esc_html__( 'Format: tm-, jx-', 'wapu-core' ),
			'value'       => 'tm-, jx-',
			'parent'      => 'docs',
		);
		$settings['controls']['results-title'] = array(
			'type'   => 'text',
			'title'  => esc_html__( 'Search results title', 'wapu-core' ),
			'value'  => esc_html__( 'Your Search Results Here!', 'wapu-core' ),
			'parent' => 'docs',
		);
		$settings['controls']['results-text'] = array(
			'type'        => 'textarea',
			'title'       => esc_html__( 'Search results text', 'wapu-core' ),
			'value'       => esc_html__( 'You can copy link leading to the unique documentation of your template using "Copy" button. Otherwise, press on "Open" button to use the direct link to go to documentation page immediately!', 'wapu-core' ),
			'parent'      => 'docs',
			'sanitize_cb' => array( $this, 'sanitize_text' ),
		);
		$settings['controls']['reset-password'] = array(
			'type'        => 'text',
			'title'       => esc_html__( 'Add reset cache password', 'wapu-core' ),
			'value'       => '',
			'parent'      => 'docs',
			'sanitize_cb' => array( $this, 'sanitize_text' ),
		);

		return $settings;
	}

	/**
	 * Sanitize input.
	 *
	 * @param  [type] $input [description]
	 * @return [type]        [description]
	 */
	public function sanitize_text( $input ) {
		return wp_kses_post( stripcslashes( $input ) );
	}

	/**
	 * Settings string
	 *
	 * @param  [type] $settings_string [description]
	 * @return [type]                  [description]
	 */
	public function parse_settings( $settings_string = null ) {

		if ( empty( $settings_string ) ) {
			return false;
		}

		$settings_string = str_replace( ', ', ',', $settings_string );

		return explode( ',', $settings_string );
	}

	/**
	 * Settings string
	 *
	 * @param  [type] $settings_string [description]
	 * @return [type]                  [description]
	 */
	public function parse_assoc_settings( $settings_string = null ) {

		if ( empty( $settings_string ) ) {
			return false;
		}

		$settings_string = str_replace( ', ', ',', $settings_string );
		$settins_array   = explode( ',', $settings_string );
		$result          = array();

		foreach ( $settins_array as $row ) {

			$data = explode( '::', $row );

			if ( 1 < count( $data ) ) {
				$result[ $data[0] ] = $data[1];
			} else {
				$result[] = $data[0];
			}

		}

		return $result;
	}

	/**
	 * Perform search.
	 *
	 * @return void
	 */
	public function process_search() {

		$query = ( ! empty( $_REQUEST['query'] ) ) ? sanitize_text_field( $_REQUEST['query'] ) : false;

		if ( ! $query ) {
			wp_send_json_error( array(
				'message' => $this->messages['empty'],
			) );
		}

		$options = array(
			'docs-base'        => wapu_core_global_settings()->get( 'docs-base' ),
			'tm-base'          => wapu_core_global_settings()->get( 'tm-base' ),
			'product-base'     => wapu_core_global_settings()->get( 'product-base' ),
			'doc-link-id'      => wapu_core_global_settings()->get( 'doc-link-id' ),
			'name-hosts'       => $this->parse_settings( wapu_core_global_settings()->get( 'name-hosts' ) ),
			'ignored-prefixes' => $this->parse_settings( wapu_core_global_settings()->get( 'ignored-prefixes' ) ),
			'exceptions'       => $this->parse_assoc_settings( wapu_core_global_settings()->get( 'exceptions' ) ),
		);

		$options = array_filter( $options );
		$search  = wapu_core_docs_search( $options );
		$result  = $search->run( $query );

		if ( ! $result ) {
			wp_send_json_error( array(
				'message' => $this->messages['not-found'],
			) );
		} else {

			$title    = wapu_core_global_settings()->get( 'results-title' );
			$text     = wapu_core_global_settings()->get( 'results-text' );

			ob_start();

			include $this->get_template( 'search-results.php' );

			wp_send_json_success( array(
				'message' => ob_get_clean(),
			) );
		}

	}

	/**
	 * Shortcode
	 *
	 * @return void
	 */
	public function _shortcode( $atts, $content ) {

		ob_start();

		$class = ! empty( $atts['class'] ) ? esc_attr( $atts['class'] ) : false;
		$id    = ! empty( $atts['id'] ) ? sprintf( ' id="%s"', esc_attr( $atts['id'] ) ) : '';

		wp_enqueue_script( 'clipboard' );
		wp_enqueue_script( 'wapu-core' );

		include $this->get_template( 'content.php' );
		include $this->get_template( 'search-results-wrap.php' );

		wapu_core_popup()->init();

		return ob_get_clean();
	}

	/**
	 * Returns class instance
	 *
	 * @return object
	 */
	public static function get_instance() {
		return new self();
	}

}

wapu_core()->init_shortcode( 'Wapu_Core_Docs_Search_Shortcode' );
