<?php
/**
 * Row shortcode
 */

class Wapu_Core_Tuts_Shortcode extends Wapu_Core_Shortcode {

	/**
	 * Init shortcode properties
	 */
	public function __construct() {

		$this->tag = 'tuts-list';

		$this->info = array(
			'group' => array(
				'name' => esc_html__( 'Content', 'wapu-core' ),
				'icon' => 'welcome-add-page',
				'slug' => 'wapu-content',
			),
			'shortcode' => array(
				'name'      => esc_html__( 'Tutoroals List', 'wapu-core' ),
				'icon'      => 'admin-links',
			),
		);

		$this->args = array(
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

		$post_types = array(
			'video-tutorials',
			'knowledge-base',
			'how-to',
			'faq',
		);

		$args = array(
			'post_type'      => $post_types,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		);

		$posts = get_posts( $args );

		if ( ! empty( $posts ) ) {
			include $this->get_template( 'content.php' );
		}

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
