<?php
/**
 * Row shortcode
 */

class Wapu_Core_Themes_Shortcode extends Wapu_Core_Shortcode {

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
				'title' => esc_html__( 'Category Slug', 'wapu-core' ),
				'value' => '',
			),
			'per_page' => array(
				'type'  => 'text',
				'title' => esc_html__( 'Per Page', 'wapu-core' ),
				'value' => 6,
			),
			'thumb_size' => array(
				'type'  => 'text',
				'title' => esc_html__( 'Thumb Size', 'wapu-core' ),
				'value' => 'theme-thumbnail',
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

		$class      = ! empty( $atts['class'] ) ? esc_attr( $atts['class'] ) : false;
		$id         = ! empty( $atts['id'] ) ? sprintf( ' id="%s"', esc_attr( $atts['id'] ) ) : '';
		$per_page   = ! empty( $atts['per_page'] ) ? absint( $atts['per_page'] ) : 6;
		$thumb_size = ! empty( $atts['thumb_size'] ) ? esc_attr( $atts['thumb_size'] ) : 'theme-thumbnail';
		$category   = ! empty( $atts['category'] ) ? esc_attr( $atts['category'] ) : '';

		$query_args = array(
			'category'   => $category,
			'per_page'   => $per_page,
			'thumb_size' => $thumb_size
		);

		wp_enqueue_script( 'vue' );
		wp_enqueue_script( 'wapu-core' );

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

wapu_core()->init_shortcode( 'Wapu_Core_Themes_Shortcode' );
