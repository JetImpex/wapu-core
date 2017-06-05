<?php
/**
 * Row shortcode
 */

class Wapu_Core_Notice_Shortcode extends Wapu_Core_Shortcode {

	/**
	 * Init shortcode properties
	 */
	public function __construct() {

		$this->tag = 'notice';

		$this->info = array(
			'group' => array(
				'name' => esc_html__( 'Content', 'wapu-core' ),
				'icon' => 'welcome-add-page',
				'slug' => 'wapu-content',
			),
			'shortcode' => array(
				'name'      => esc_html__( 'Notice', 'wapu-core' ),
				'icon'      => 'editor-help',
				'enclosing' => true,
			),
		);

		$this->args = array(
			'type' => array(
				'type'       => 'select',
				'title'      => esc_html__( 'Type', 'cherry-services' ),
				'value'      => 'success',
				'options'    => array(
					'success' => esc_html__( 'Success', 'wapu-core' ),
					'warning' => esc_html__( 'Warning', 'wapu-core' ),
					'error'   => esc_html__( 'Error', 'wapu-core' ),
					'info'    => esc_html__( 'Info', 'wapu-core' ),
				),
			),
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
	}

	/**
	 * Shortcode
	 *
	 * @return void
	 */
	public function _shortcode( $atts, $content ) {

		ob_start();

		$class   = ! empty( $atts['class'] ) ? esc_attr( $atts['class'] ) : false;
		$id      = ! empty( $atts['id'] ) ? sprintf( ' id="%s"', esc_attr( $atts['id'] ) ) : '';
		$type    = ! empty( $atts['type'] ) ? esc_attr( $atts['type'] ) : 'success';
		$content = do_shortcode( $content );

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

wapu_core()->init_shortcode( 'Wapu_Core_Notice_Shortcode' );
