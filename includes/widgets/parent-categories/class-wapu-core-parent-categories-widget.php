<?php
/**
 * Widget related posts
 */

class Wapu_Core_Parent_Categories_Widget extends Cherry_Abstract_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->widget_cssclass    = 'widget-parent-categories';
		$this->widget_description = esc_html__( 'Show categories list from parent site.', 'wapu-core' );
		$this->widget_id          = 'wapu_parent_categories';
		$this->widget_name        = esc_html__( 'Wapu Parent Categories', 'wapu-core' );
		$this->settings           = array(
			'title'  => array(
				'type'  => 'text',
				'value' => '',
				'label' => esc_html__( 'Title:', 'wapu-core' ),
			),
		);

		parent::__construct();
	}

	/**
	 * Widget function.
	 *
	 * @see   WP_Widget
	 * @since 1.0.1
	 * @param array $args     Widget arguments.
	 * @param array $instance Instance.
	 */
	public function widget( $args, $instance ) {

		if ( ! is_multisite() || is_main_site() ) {

			$this->setup_widget_data( $args, $instance );
			$this->widget_start( $args, $instance );

			echo '<ul class="categories-list">';
				wp_list_categories( array( 'title_li' => false ) );
			echo '</ul>';

			$this->widget_end( $args );
			$this->reset_widget_data();

			return;
		}

		switch_to_blog( wapu_core_posts_aggregator()->main_blog_id );

		$this->setup_widget_data( $args, $instance );
		$this->widget_start( $args, $instance );

		echo '<ul class="categories-list">';
			ob_start();
			wp_list_categories( array( 'title_li' => false ) );
			$result = ob_get_clean();

			echo $this->fix_url_in_content( $result );

		echo '</ul>';

		$this->widget_end( $args );
		$this->reset_widget_data();

		restore_current_blog();

	}

	/**
	 * Fix URLs in content
	 *
	 * @param  [type] $content [description]
	 * @return [type]          [description]
	 */
	public function fix_url_in_content( $content ) {

		if ( false === strpos( home_url(), 'jetimpex.com' ) ) {
			return $content;
		}

		if ( false === strpos( $content, home_url( '/blog/' ) ) ) {
			$content = str_replace( home_url( '/' ), home_url( '/blog/' ), $content );
		}

		return $content;
	}

}

add_action( 'widgets_init', 'wapu_core_register_parent_categories_widget' );

/**
 * Register about widget.
 */
function wapu_core_register_parent_categories_widget() {
	register_widget( 'Wapu_Core_Parent_Categories_Widget' );
}
