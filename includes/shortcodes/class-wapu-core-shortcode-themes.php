<?php
/**
 * Row shortcode
 */

class Wapu_Core_Tuts_Shortcode extends Wapu_Core_Shortcode {

	/**
	 * Init shortcode properties
	 */
	public function __construct() {

		$this->tag = 'themes-list';

		$this->info = array(
			'group' => array(
				'name' => esc_html__( 'Content', 'wapu-core' ),
				'icon' => 'welcome-add-page',
				'slug' => 'wapu-content',
			),
			'shortcode' => array(
				'name'      => esc_html__( 'Themes List', 'wapu-core' ),
				'icon'      => 'admin-links',
			),
		);

		$this->args = array(
			'category' => array(
				'type'  => 'text',
				'title' => esc_html__( 'Category', 'wapu-core' ),
				'value' => '',
			),
			'class' => array(
				'type'  => 'text',
				'title' => esc_html__( 'Custom CSS class', 'wapu-core' ),
				'value' => '',
			),
			'id' => array(
				'type'  => 'text',
				'title' => esc_html__( 'Custom ID', 'wapu-core' ),
				'value' => '',
			),
		);
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

		// TODO: handle themes list

		return ob_get_clean();
	}

	/**
	 * Returns class instance
	 *
	 * @return [type] [description]
	 */
	public static function get_instance() {
		return new self();
	}

}

wapu_core()->init_shortcode( 'Wapu_Core_Tuts_Shortcode' );
