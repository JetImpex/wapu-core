<?php
/**
 * Row shortcode
 */

class Wapu_Core_Button_Shortcode extends Wapu_Core_Shortcode {

	/**
	 * Init shortcode properties
	 */
	public function __construct() {

		$this->tag = 'button';

		$this->info = array(
			'group' => array(
				'name' => esc_html__( 'Content', 'wapu-core' ),
				'icon' => 'welcome-add-page',
				'slug' => 'wapu-content',
			),
			'shortcode' => array(
				'name'      => esc_html__( 'Button', 'wapu-core' ),
				'icon'      => 'admin-links',
			),
		);

		$this->args = array(
			'text' => array(
				'type'  => 'text',
				'title' => esc_html__( 'Text', 'wapu-core' ),
				'value' => '',
			),
			'link' => array(
				'type'  => 'text',
				'title' => esc_html__( 'URL', 'wapu-core' ),
				'value' => '',
			),
			'type' => array(
				'type'    => 'select',
				'title'   => esc_html__( 'Type', 'wapu-core' ),
				'value'   => 'default',
				'options' => array(
					'default'   => esc_html__( 'Default', 'wapu-core' ),
					'primary'   => esc_html__( 'Primary', 'wapu-core' ),
					'secondary' => esc_html__( 'Secondary', 'wapu-core' ),
					'success'   => esc_html__( 'Success', 'wapu-core' ),
					'warning'   => esc_html__( 'Warning', 'wapu-core' ),
				),
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
		$text  = ! empty( $atts['text'] ) ? wp_kses_post( $atts['text'] ) : '';
		$link  = ! empty( $atts['link'] ) ? esc_attr( $atts['link'] ) : '';
		$type  = ! empty( $atts['type'] ) ? esc_attr( $atts['type'] ) : 'default';

		include $this->get_template( 'content.php' );

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

wapu_core()->init_shortcode( 'Wapu_Core_Button_Shortcode' );
