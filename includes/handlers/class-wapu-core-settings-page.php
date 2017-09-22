<?php
/**
 * Settings manager
 *
 * @package   package_name
 * @author    Cherry Team
 * @license   GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Wapu_Core_Settings_Page' ) ) {

	/**
	 * Define Wapu_Core_Settings_Page class
	 */
	class Wapu_Core_Settings_Page {

		/**
		 * Popup added trigger
		 *
		 * @var boolean
		 */
		public $args = array();

		/**
		 * Instance of the class Cherry_Interface_Builder.
		 *
		 * @since 1.0.0
		 * @var object
		 */
		public $builder = null;

		/**
		 * Stored settings
		 *
		 * @var array
		 */
		public $settings = null;

		/**
		 * Sets up needed actions/filters for the admin to initialize.
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function __construct( $args = array() ) {

			$this->args = wp_parse_args( $args, array(
				'slug'     => false,
				'parent'   => false,
				'title'    => esc_html__( 'Settings', 'wapu-core' ),
				'cap'      => 'edit_theme_options',
				'icon'     => '',
				'position' => 65,
				'tabs'     => array(),
				'controls' => array(),
			) );

			if ( is_admin() ) {
				$this->builder = wapu_core()->get_core()->init_module( 'cherry-interface-builder', array() );
				add_action( 'admin_menu', array( $this, 'register_page' ) );
				add_action( 'admin_init', array( $this, 'save_settings' ) );
			}
		}

		/**
		 * Register settings page
		 *
		 * @return void
		 */
		public function register_page() {


			if ( ! empty( $this->args['parent'] ) ) {
				$_func = 'add_submenu_page';
				$args  = array( $this->args['parent'] );
			} else {
				$_func = 'add_menu_page';
				$args  = array();
			}

			$args = array_merge( $args, array(
				$this->args['title'],
				$this->args['title'],
				$this->args['cap'],
				$this->args['slug'],
				array( $this, 'render_page' ),
				$this->args['icon'],
				$this->args['position'],
			) );

			call_user_func_array( $_func, $args );
		}

		/**
		 * Register settings tabs.
		 *
		 * @param  array $tabs Registered tabs.
		 * @return void
		 */
		public function register_tabs( $tabs ) {

			foreach ( $tabs as $id => $title ) {
				$this->builder->register_settings(
					array(
						$id => array(
							'type'   => 'settings',
							'parent' => 'main_settings',
							'title'  => $title,
						),
					)
				);
			}

		}

		/**
		 * Register controls
		 *
		 * @param  srray $controls Controls set.
		 * @return void
		 */
		public function register_controls( $controls ) {

			foreach ( $controls as $id => $control ) {

				$control['value'] = $this->get( $id );
				$this->builder->register_control( array(
					$id => $control
				) );

			}

		}

		/**
		 * Get setting
		 *
		 * @return mixed
		 */
		public function get( $name = null ) {

			if ( null === $this->settings ) {
				$this->settings = get_option( $this->args['slug'], array() );
			}

			if ( isset( $this->settings[ $name ] ) ) {
				return $this->settings[ $name ];
			}

			$default = false;

			if ( isset( $this->args['controls'][ $name ]['value'] ) ) {
				$default = $this->args['controls'][ $name ]['value'];
			}

			return $default;
		}

		/**
		 * Render settings page
		 *
		 * @return void
		 */
		public function render_page() {

			$this->builder->register_section(
				array(
					'settings' => array(
						'type'   => 'section',
						'scroll' => false,
						'title'  => $this->args['title'],
					),
				)
			);

			$this->builder->register_form(
				array(
					'settings_form' => array(
						'type'   => 'form',
						'parent' => 'settings',
						'action' => add_query_arg(
							array( 'page' => $this->args['slug'], 'action' => 'save-settings' ),
							esc_url( admin_url( 'admin.php' ) )
						),
					),
				)
			);

			$this->builder->register_settings(
				array(
					'settings_content' => array(
						'type'   => 'settings',
						'parent' => 'settings_form',
					),
					'settings_footer' => array(
						'type'   => 'settings',
						'parent' => 'settings_form',
					),
				)
			);

			$this->builder->register_component(
				array(
					'main_settings' => array(
						'type'   => 'component-tab-vertical',
						'parent' => 'settings_content',
					),
				)
			);

			if ( ! empty( $this->args['tabs'] ) ) {
				$this->register_tabs( $this->args['tabs'] );
			}

			if ( ! empty( $this->args['controls'] ) ) {
				$this->register_controls( $this->args['controls'] );
			}

			$this->builder->register_component(
				array(
					'submit_button' => array(
						'type'        => 'button',
						'id'          => 'save-settings',
						'name'        => 'save-settings',
						'button_type' => 'submit',
						'style'       => 'primary',
						'content'     => esc_html__( 'Save Settings', 'wapu-core' ),
						'parent'      => 'settings_footer',
					),
				)
			);

			echo '<div class="settings-page ' . $this->args['slug'] . '-page">';
				$this->builder->render();
			echo '</div>';

		}

		/**
		 * Save settings
		 *
		 * @return void
		 */
		public function save_settings() {

			if ( ! isset( $_GET['page'] ) || $this->args['slug'] !== $_GET['page'] ) {
				return;
			}

			if ( ! isset( $_GET['action'] ) || 'save-settings' !== $_GET['action'] ) {
				return;
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			if ( empty( $this->args['controls'] ) ) {
				return;
			}

			$settings     = $this->args['controls'];
			$whitelisted  = array_keys( $settings );
			$new_settings = array();

			foreach ( $whitelisted as $key ) {

				if ( ! isset( $_POST[ $key ] ) ) {
					continue;
				}

				$value = $_POST[ $key ];

				$sanitize_cb = isset( $settings[ $key ]['sanitize_cb'] ) ? $settings[ $key ]['sanitize_cb'] : false;

				if ( ! $sanitize_cb && ! is_array( $value ) ) {
					$sanitize_cb = 'esc_attr';
				}

				if ( $sanitize_cb ) {
					$new_settings[ $key ] = call_user_func( $sanitize_cb, $value );
				} else {
					$new_settings[ $key ] = $value;
				}

			}

			do_action( 'wapu-core/settings-page/save', $new_settings, $this->args );

			update_option( $this->args['slug'], $new_settings );

			wp_redirect( add_query_arg(
				array( 'page' => $this->args['slug'] ),
				esc_url( admin_url( 'admin.php' ) )
			) );

			die();

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
 * Returns instance of Wapu_Core_Settings_Page
 *
 * @return object
 */
function wapu_core_settings_page( $args = array() ) {
	return Wapu_Core_Settings_Page::get_instance( $args );
}
