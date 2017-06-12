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

		$this->setup_widget_data( $args, $instance );
		$this->widget_start( $args, $instance );

		if ( is_multisite() && ! is_main_site() ) {
			switch_to_blog( wapu_core_posts_aggregator()->main_blog_id );
		}

		echo '<ul class="categories-list">';
			wp_list_categories( array( 'title_li' => false ) );
		echo '</ul>';

		if ( is_multisite() && ! is_main_site() ) {
			restore_current_blog();
		}

		$this->widget_end( $args );
		$this->reset_widget_data();

	}
}

add_action( 'widgets_init', 'wapu_core_register_parent_categories_widget' );

/**
 * Register about widget.
 */
function wapu_core_register_parent_categories_widget() {
	register_widget( 'Wapu_Core_Parent_Categories_Widget' );
}
