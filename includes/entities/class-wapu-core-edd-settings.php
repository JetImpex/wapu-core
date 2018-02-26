<?php
/**
 * Manage EDD-related settings
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Wapu_Core_EDD_Settings' ) ) {

	/**
	 * Define Wapu_Core_EDD_Settings class
	 */
	class Wapu_Core_EDD_Settings {

		/**
		 * Constructor for the class
		 */
		public function __construct() {
			add_filter( 'edd_registered_settings', array( $this, 'register_settings' ) );
			add_filter( 'edd_settings_tabs', array( $this, 'register_settings_tab' ) );
			add_filter( 'edd_settings_sections', array( $this, 'register_settings_section' ) );
		}


		public function register_settings_tab( $tabs ) {
			$tabs['wapu_core_settings'] = esc_html__( 'Zemez Settings', 'wapu-core' );
			return $tabs;
		}

		public function register_settings_section( $sections ) {
			$sections['wapu_core_settings'] = array(
				'main' => esc_html__( 'Zemez Settings', 'wapu-core' ),
			);
			return $sections;
		}

		/**
		 * Register Crocoblock core settings
		 *
		 * @return array
		 */
		public function register_settings( $settings ) {
			$settings['wapu_core_settings'] = array(
				'main' => array(
					'wapu_core_account_page' => array(
						'id'          => 'wapu_core_account_page',
						'name'        => __( 'Account Page', 'wapu-core' ),
						'desc'        => '',
						'type'        => 'select',
						'options'     => edd_get_pages(),
						'chosen'      => true,
						'placeholder' => __( 'Select a page', 'wapu-core' ),
					),
					'wapu_core_login_page' => array(
						'id'          => 'wapu_core_login_page',
						'name'        => __( 'Login Page', 'wapu-core' ),
						'desc'        => '',
						'type'        => 'select',
						'options'     => edd_get_pages(),
						'chosen'      => true,
						'placeholder' => __( 'Select a page', 'wapu-core' ),
					),
				),
			);
			return $settings;
		}

	}

}
