<?php
/**
 * Row shortcode
 */

class Wapu_Core_Col_Shortcode extends Wapu_Core_Shortcode {

	/**
	 * Init shortcode properties
	 */
	public function __construct() {

		$this->tag = 'column';

		$this->info = array(
			'group' => array(
				'name' => esc_html__( 'Layout', 'wapu-core' ),
				'icon' => 'editor-table',
				'slug' => 'wapu-layout',
			),
			'shortcode' => array(
				'name'      => esc_html__( 'Column', 'wapu-core' ),
				'icon'      => 'align-left',
				'enclosing' => true,
			),
		);

		$cols_options = array(
			'12' => '100%',
			'9'  => '75%',
			'8'  => '66.6%',
			'6'  => '50%',
			'4'  => '33.3%',
			'3'  => '25%',
		);

		$this->args = array(
			'xl' => array(
				'type'       => 'select',
				'title'      => esc_html__( 'Column Width XL', 'cherry-services' ),
				'value'      => '6',
				'options'    => $cols_options,
			),
			'lg' => array(
				'type'       => 'select',
				'title'      => esc_html__( 'Column Width LG', 'cherry-services' ),
				'value'      => '6',
				'options'    => $cols_options,
			),
			'md' => array(
				'type'       => 'select',
				'title'      => esc_html__( 'Column Width MD', 'cherry-services' ),
				'value'      => '6',
				'options'    => $cols_options,
			),
			'sm' => array(
				'type'       => 'select',
				'title'      => esc_html__( 'Column Width SM', 'cherry-services' ),
				'value'      => '12',
				'options'    => $cols_options,
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

		$class = ! empty( $atts['class'] ) ? esc_attr( $atts['class'] ) : false;
		$id    = ! empty( $atts['id'] ) ? sprintf( ' id="%s"', esc_attr( $atts['id'] ) ) : '';
		$xl    = ! empty( $atts['xl'] ) ? intval( $atts['xl'] ) : 6;
		$lg    = ! empty( $atts['lg'] ) ? intval( $atts['lg'] ) : 6;
		$md    = ! empty( $atts['md'] ) ? intval( $atts['md'] ) : 6;
		$sm    = ! empty( $atts['sm'] ) ? intval( $atts['sm'] ) : 12;

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

wapu_core()->init_shortcode( 'Wapu_Core_Col_Shortcode' );
