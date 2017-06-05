<?php
/**
 * Widget related posts
 */

class Wapu_Core_Related_Posts_Widget extends Cherry_Abstract_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->widget_cssclass    = 'widget-related-posts';
		$this->widget_description = esc_html__( 'Show related posts list.', 'wapu-core' );
		$this->widget_id          = 'wapu_related_posts';
		$this->widget_name        = esc_html__( 'Wapu Related Posts', 'wapu-core' );
		$this->settings           = array(
			'title'  => array(
				'type'  => 'text',
				'value' => '',
				'label' => esc_html__( 'Title:', 'wapu-core' ),
			),
			'number' => array(
				'type'        => 'text',
				'value'       => '5',
				'max_value'   => '50',
				'min_value'   => '1',
				'step_value'  => '1',
				'label'       => esc_html__( 'Related posts number:', 'wapu-core' ),
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

		$related = wapu_core_related_posts()->query();

		if ( ! empty( $related ) ) {

			$loop_start = wapu_core()->get_template( 'widgets/related-posts/loop-start.php' );
			$loop_end   = wapu_core()->get_template( 'widgets/related-posts/loop-end.php' );
			$loop       = wapu_core()->get_template( 'widgets/related-posts/loop.php' );

			include $loop_start;

			global $post;

			foreach ( $related as $post ) {

				setup_postdata( $post );

				include $loop;

			}

			wp_reset_postdata();

			include $loop_end;

		}

		$this->widget_end( $args );
		$this->reset_widget_data();

	}
}

add_action( 'widgets_init', 'wapu_core_register_relate_posts_widget' );

/**
 * Register about widget.
 */
function wapu_core_register_relate_posts_widget() {
	register_widget( 'Wapu_Core_Related_Posts_Widget' );
}
