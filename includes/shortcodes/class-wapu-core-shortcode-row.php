<?php
/**
 * Row shortcode
 */

class Wapu_Core_Row_Shortcode extends Wapu_Core_Shortcode {

	/**
	 * Init shortcode properties
	 */
	public function __construct() {

		$this->tag = 'row';

		$this->info = array(
			'group' => array(
				'name' => esc_html__( 'Layout', 'wapu-core' ),
				'icon' => 'editor-table',
				'slug' => 'wapu-layout',
			),
			'shortcode' => array(
				'name'      => esc_html__( 'Row', 'wapu-core' ),
				'icon'      => 'editor-justify',
				'enclosing' => true,
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
	}

	/**
	 * Shortcode
	 *
	 * @return void
	 */
	public function _shortcode( $atts, $content ) {

		ob_start();

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

wapu_core()->init_shortcode( 'Wapu_Core_Row_Shortcode' );
