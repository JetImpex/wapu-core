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

if ( ! class_exists( 'Wapu_Core_Multisite_Set_Data' ) ) {

	/**
	 * Define Wapu_Core_Multisite_Set_Data class
	 */
	class Wapu_Core_Multisite_Set_Data {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Network slug
		 *
		 * @var string
		 */
		public $slug = 'wapu-set-for-network';

		/**
		 * AJAX handler instance
		 *
		 * @var object
		 */
		public $handler = null;

		/**
		 * Builder instance
		 *
		 * @var object
		 */
		public $builder = null;

		/**
		 * Constructor for the class
		 */
		public function init() {

			if ( ! is_admin() ) {
				return;
			}

			if ( ! current_user_can( 'manage_network' ) ) {
				return;
			}

			add_action( 'network_admin_menu', array( $this, 'register_page' ) );

			if ( isset( $_GET['page'] ) && $this->slug !== $_GET['page'] ) {
				return;
			}

			$this->builder = wapu_core()->get_core()->init_module( 'cherry-interface-builder', array() );

			if ( isset( $_GET['action'] ) && 'process-data' === $_GET['action'] ) {
				add_action( 'init', array( $this, 'process_data' ), 99 );
			}

		}

		/**
		 * Register network set page
		 *
		 * @return void
		 */
		public function register_page() {

			add_menu_page(
				esc_html__( 'Set Data For Network', 'wapu-core' ),
				esc_html__( 'Set For Network', 'wapu-core' ),
				'manage_network',
				$this->slug,
				array( $this, 'render_page' )
			);

		}

		/**
		 * Process passed data
		 *
		 * @return void
		 */
		public function process_data() {

			$sites = get_sites();

			foreach( $sites as $site ) {

				switch_to_blog( $site->blog_id );

				$this->process_blog_options();
				$this->process_theme_mods();

				restore_current_blog();
			}

			wp_redirect( add_query_arg(
				array( 'page' => $this->slug ),
				esc_url( network_admin_url( 'admin.php' ) )
			) );

		}

		/**
		 * Process blog options
		 *
		 * @return [type] [description]
		 */
		public function process_blog_options() {

			if ( empty( $_POST['data_options'] ) ) {
				return;
			}

			foreach ( $_POST['data_options'] as $option ) {

				if ( empty( $option['key'] ) ) {
					continue;
				}

				$value = maybe_unserialize( stripslashes( $option['value'] ) );
				update_option( $option['key'], $value );
			}

		}

		/**
		 * Process theme mods
		 *
		 * @return [type] [description]
		 */
		public function process_theme_mods() {

			if ( empty( $_POST['data_mods'] ) ) {
				return;
			}

			foreach ( $_POST['data_mods'] as $option ) {

				if ( empty( $option['key'] ) ) {
					continue;
				}

				$value = maybe_unserialize( stripslashes( $option['value'] ) );
				set_theme_mod( $option['key'], $value );
			}
		}

		/**
		 * Render admin page
		 *
		 * @return [type] [description]
		 */
		public function render_page() {

			$this->builder->register_section(
				array(
					'data' => array(
						'type'   => 'section',
						'scroll' => false,
						'title'  => esc_html__( 'Set Data for Whole Network', 'wapu-core' ),
					),
				)
			);

			$this->builder->register_form(
				array(
					'data_form' => array(
						'type'   => 'form',
						'parent' => 'data',
						'action' => add_query_arg(
							array( 'page' => $this->slug, 'action' => 'process-data' ),
							esc_url( network_admin_url( 'admin.php' ) )
						),
					),
				)
			);

			$this->builder->register_settings(
				array(
					'data_content' => array(
						'type'   => 'settings',
						'parent' => 'data_form',
					),
					'data_footer' => array(
						'type'   => 'settings',
						'parent' => 'data_form',
					),
				)
			);

			$this->builder->register_control( array(
				'data_options' => array(
					'id'          => 'data_options',
					'name'        => 'data_options',
					'type'        => 'repeater',
					'parent'      => 'data_content',
					'label'       => esc_html__( 'Set Options', 'wapu-core' ),
					'add_label'   => esc_html__( 'Add Option', 'wapu-core' ),
					'title_field' => 'key',
					'fields'      => array(
						'key' => array(
							'type'        => 'text',
							'id'          => 'key',
							'name'        => 'key',
							'placeholder' => esc_html__( 'Option Key', 'wapu-core' ),
							'label'       => esc_html__( 'Option Key', 'wapu-core'  ),
						),
						'value' => array(
							'type'        => 'textarea',
							'id'          => 'value',
							'name'        => 'value',
							'placeholder' => esc_html__( 'Option Value', 'wapu-core' ),
							'label'       => esc_html__( 'Option Value', 'wapu-core'  ),
						),
					),
				),
				'data_mods' => array(
					'id'          => 'data_mods',
					'name'        => 'data_mods',
					'type'        => 'repeater',
					'parent'      => 'data_content',
					'label'       => esc_html__( 'Set Theme Mods', 'wapu-core' ),
					'add_label'   => esc_html__( 'Add Mod', 'wapu-core' ),
					'title_field' => 'key',
					'fields'      => array(
						'key' => array(
							'type'        => 'text',
							'id'          => 'key',
							'name'        => 'key',
							'placeholder' => esc_html__( 'Mod Key', 'wapu-core' ),
							'label'       => esc_html__( 'Mod Key', 'wapu-core'  ),
						),
						'value' => array(
							'type'        => 'textarea',
							'id'          => 'value',
							'name'        => 'value',
							'placeholder' => esc_html__( 'Mod Value', 'wapu-core' ),
							'label'       => esc_html__( 'Mod Value', 'wapu-core'  ),
						),
					),
				),
			) );

			$this->builder->register_component(
				array(
					'submit_button' => array(
						'type'        => 'button',
						'id'          => $this->slug,
						'name'        => $this->slug,
						'button_type' => 'submit',
						'style'       => 'primary',
						'content'     => esc_html__( 'Set Data', 'wapu-core' ),
						'parent'      => 'data_footer',
					),
				)
			);

			echo '<div class="network-set">';
				$this->builder->render();
			echo '</div>';

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
 * Returns instance of Wapu_Core_Multisite_Set_Data
 *
 * @return object
 */
function wapu_core_mutisite_set_data() {
	return Wapu_Core_Multisite_Set_Data::get_instance();
}
