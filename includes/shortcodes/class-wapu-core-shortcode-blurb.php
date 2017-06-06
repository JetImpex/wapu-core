<?php
/**
 * Row shortcode
 */

class Wapu_Core_Blurb_Shortcode extends Wapu_Core_Shortcode {

	/**
	 * Init shortcode properties
	 */
	public function __construct() {

		$this->tag = 'blurb';

		$this->info = array(
			'group' => array(
				'name' => esc_html__( 'Content', 'wapu-core' ),
				'icon' => 'welcome-add-page',
				'slug' => 'wapu-content',
			),
			'shortcode' => array(
				'name'      => esc_html__( 'Blurb', 'wapu-core' ),
				'icon'      => 'format-image',
			),
		);

		$this->args = array(
			'font_icon' => array(
				'type'       => 'iconpicker',
				'title'      => esc_html__( 'Font Icon', 'cherry-services' ),
				'value'      => '',
				'auto_parse' => true,
				'icon_data'  => array(
					'icon_set'    => 'nucleoOutline',
					'icon_css'    => wapu_core()->plugin_url( 'assets/css/nucleo-outline.css' ),
					'icon_base'   => 'nc-icon-outline',
					'icon_prefix' => '',
					'icons'       => false,
				),
			),
			'image' => array(
				'type'         => 'media',
				'title'        => esc_html__( 'Image', 'cherry-services' ),
				'value'        => '',
				'multi_upload' => false,
				'library_type' => 'image',
			),
			'title' => array(
				'type'   => 'text',
				'title'  => esc_html__( 'Title', 'wapu-core' ),
				'value'  => '',
			),
			'text' => array(
				'type'   => 'text',
				'title'  => esc_html__( 'Text', 'wapu-core' ),
				'value'  => '',
			),
			'link_text' => array(
				'type'   => 'text',
				'title'  => esc_html__( 'Link Text', 'wapu-core' ),
				'value'  => '',
			),
			'link' => array(
				'type'   => 'text',
				'title'  => esc_html__( 'Link URL', 'wapu-core' ),
				'value'  => '',
			),
			'link_target' => array(
				'type'    => 'select',
				'title'   => esc_html__( 'Link Target', 'wapu-core' ),
				'value'   => 'none',
				'options' => array(
					'none'   => esc_html__( 'Self', 'wapu-core' ),
					'_blank' => esc_html__( 'Blank', 'wapu-core' ),
				),
			),
			'template' => array(
				'type'   => 'text',
				'title'  => esc_html__( 'Custom template filename (like example.php)', 'wapu-core' ),
				'value'  => '',
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

		$template = ! empty( $atts['template'] ) ? esc_attr( $atts['template'] ) : 'default.php';
		$tmplfile = $this->get_template( $template );

		if ( ! $tmplfile ) {
			$tmplfile = $this->get_template( 'default.php' );
		}

		$font_icon = ( ! empty( $atts['font_icon'] ) ) ? esc_attr( $atts['font_icon'] ) : false;
		$image     = ( ! empty( $atts['image'] ) ) ? intval( $atts['image'] ) : false;
		$img_tag   = ( $image ) ? wp_get_attachment_image( $image, 'full', '', array( 'class' => 'blurb__img' ) ) : '';
		$title     = ( ! empty( $atts['title'] ) ) ? wp_kses_post( $atts['title'] ) : false;
		$text      = ( ! empty( $atts['text'] ) ) ? wp_kses_post( $atts['text'] ) : false;
		$link_text = ( ! empty( $atts['link_text'] ) ) ? wp_kses_post( $atts['link_text'] ) : '';
		$link      = ( ! empty( $atts['link'] ) ) ? esc_attr( $atts['link'] ) : false;
		$target    = ( ! empty( $atts['link_target'] ) && 'none' !== $atts['link_target'] ) ? ' target="_blank"' : '';
		$class     = ( ! empty( $atts['class'] ) ) ? esc_attr( $atts['class'] ) : false;
		$id        = ( ! empty( $atts['id'] ) ) ? sprintf( 'id="%s"', esc_attr( $atts['id'] ) ) : false;

		include $tmplfile;

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

wapu_core()->init_shortcode( 'Wapu_Core_Blurb_Shortcode' );
