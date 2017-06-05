<?php
/**
 * Class description
 *
 * @package   package_name
 * @author    Cherry Team
 * @license   GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Wapu_Core_Video_Tutorials' ) ) {

	/**
	 * Define Wapu_Core_Video_Tutorials class
	 */
	class Wapu_Core_Video_Tutorials extends Wapu_Core_Post_Type {

		/**
		 * Holder for settings instance for this post type
		 *
		 * @var null
		 */
		public $settings = null;

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		public function init() {

			$this->slug = 'video-tutorials';
			$this->single_name = esc_html__( 'Video Tutorial', 'wapu-core' );
			$this->plural_name = esc_html__( 'Video Tutorials', 'wapu-core' );
			$this->args = array(
				'menu_icon'          => 'dashicons-video-alt3',
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'query_var'          => true,
				'capability_type'    => 'post',
				'has_archive'        => true,
				'hierarchical'       => false,
				'menu_position'      => 26,
				'supports'           => array(
					'title',
					'editor',
					'thumbnail',
					'excerpt',
				),
			);
			$this->rewrite_options = true;
			$this->taxonomies  = array(
				'category' => array(
					'single_name' => esc_html__( 'Category', 'wapu-core' ),
					'plural_name' => esc_html__( 'Categories', 'wapu-core' ),
					'args'        => array(
						'hierarchical'      => true,
						'show_admin_column' => true,
					),
				),
			);

			wapu_core_related_posts()->add( $this->slug );

			parent::init();

			wapu_core()->get_core()->init_module( 'cherry-post-meta', array(
				'id'            => 'video-url',
				'title'         => esc_html__( 'Video URL', 'wapu-core' ),
				'page'          => array( $this->slug ),
				'context'       => 'normal',
				'priority'      => 'high',
				'callback_args' => false,
				'fields' => array(
					'_wapu_video_url' => array(
						'type'  => 'text',
						'value' => '',
						'title' => esc_html__( 'YouTube video URL', 'wapu-core' ),
					),
				),
			) );

			$this->settings = wapu_core_settings_page( array(
				'slug'     => $this->slug . '-settings',
				'parent'   => 'edit.php?post_type=' . $this->slug,
				'tabs'     => array(
					'general' => esc_html__( 'General', 'wapu-core' ),
				),
				'controls' => array(
					'single-width' => array(
						'type'       => 'slider',
						'id'         => 'single-width',
						'name'       => 'single-width',
						'max_value'  => 2000,
						'min_value'  => 320,
						'value'      => 900,
						'step_value' => 1,
						'label'      => esc_html__( 'Single Video Width', 'wapu-core' ),
						'parent'     => 'general',
					),
					'single-height' => array(
						'type'       => 'slider',
						'id'         => 'single-height',
						'name'       => 'single-height',
						'max_value'  => 1000,
						'min_value'  => 320,
						'value'      => 450,
						'step_value' => 1,
						'label'      => esc_html__( 'Single Video Height', 'wapu-core' ),
						'parent'     => 'general',
					),
					'loop-width' => array(
						'type'       => 'slider',
						'id'         => 'loop-width',
						'name'       => 'loop-width',
						'max_value'  => 2000,
						'min_value'  => 320,
						'value'      => 900,
						'step_value' => 1,
						'label'      => esc_html__( 'Loop Video Width', 'wapu-core' ),
						'parent'     => 'general',
					),
					'loop-height' => array(
						'type'       => 'slider',
						'id'         => 'loop-height',
						'name'       => 'loop-height',
						'max_value'  => 1000,
						'min_value'  => 320,
						'value'      => 450,
						'step_value' => 1,
						'label'      => esc_html__( 'Loop Video Height', 'wapu-core' ),
						'parent'     => 'general',
					),
				),
			) );

			add_action( 'wp_ajax_wapu_core_get_video', array( $this, 'ajax_get_video' ) );
			add_action( 'wp_ajax_nopriv_wapu_core_get_video', array( $this, 'ajax_get_video' ) );

			add_action( 'pre_get_posts', array( $this, 'set_videos_per_page' ) );
		}

		/**
		 * Set posts per archive page
		 */
		public function set_videos_per_page( $query ) {

			if ( ! $query->is_main_query() ) {
				return;
			}

			if ( $query->get( 'post_type' ) !== $this->slug ) {
				return;
			}

			if ( $query->is_post_type_archive !== true ) {
				return;
			}

			$query->set( 'posts_per_page', '-1' );
		}

		/**
		 * Get embed video.
		 *
		 * @return string|bool
		 */
		public function get_video( $post_id = null, $context = 'single' ) {

			if ( ! $post_id ) {
				$post_id = get_the_id();
			}

			if ( ! $post_id ) {
				return;
			}

			$video_url = get_post_meta( $post_id, '_wapu_video_url', true );

			if ( ! $video_url ) {
				return false;
			}

			$embed = wp_oembed_get( $video_url );

			$width_opt  = 'single-width';
			$height_opt = 'single-height';

			if ( 'single' !== $context ) {
				$width_opt  = 'loop-width';
				$height_opt = 'loop-height';
			}

			$width  = $this->settings->get( $width_opt );
			$height = $this->settings->get( $height_opt );

			foreach ( array( 'width' => $width, 'height' => $height ) as $reg => $value ) {
				$embed = preg_replace( '/(?<=' . $reg . '=[\'\"])\d+(?=[\'\"])/', $value, $embed );
			}

			return $embed;
		}

		/**
		 * Try to get video iframe by AJAX request
		 *
		 * @return void
		 */
		public function ajax_get_video() {

			$id = isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : false;
			$error = esc_html__( 'Incorrect Data', 'wapu-core' );

			if ( ! $id || $this->slug !== get_post_type( $id ) ) {
				echo $error;
				die();
			}

			echo $this->get_video( $id, 'loop' );
			die();
		}

		/**
		 * Loop video
		 *
		 * @return void
		 */
		public function loop_video( $post_id = null ) {

			if ( ! $post_id ) {
				$post_id = get_the_id();
			}

			if ( ! $post_id ) {
				return;
			}

			$thumb = $this->get_video_thumb( $post_id );

			if ( ! $thumb ) {
				return;
			}

			ob_start();
			include wapu_core()->get_template( 'post-types/video-tutorials/loop-video.php' );
			return ob_get_clean();
		}

		/**
		 * Returns post video thumbnail
		 *
		 * @param  int $post_id Post ID to get thumbnail for.
		 * @return string
		 */
		public function get_video_thumb( $post_id = null ) {

			$video_url = get_post_meta( $post_id, '_wapu_video_url', true );

			if ( ! $video_url ) {
				return;
			}

			$thumb_size = apply_filters( 'wapu_core/post_types/video_tutorials/loop_thumb_size', 'thumbnail' );

			if ( ! has_post_thumbnail( $post_id ) ) {
				return $this->get_youtube_thumb( $video_url );
			}

		}

		/**
		 * Returns YouTube thumbnails URL
		 *
		 * @param  string $url URL to get thumbnail for.
		 * @return string
		 */
		public function get_youtube_thumb( $url ) {

			preg_match( '/(youtube\.com\/watch\?v=|youtu.be\/)([a-zA-Z0-9]+)/', $url, $matches );

			if ( ! isset( $matches[2] ) ) {
				return;
			}

			$id = $matches[2];

			$img = sprintf( 'https://img.youtube.com/vi/%s/0.jpg', $id );

			return sprintf( '<img src="%s" alt="" class="wapu-video-thumb">', $img );
		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @return object
		 */
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}
	}

}

/**
 * Returns instance of Wapu_Core_Video_Tutorials
 *
 * @return object
 */
function wapu_core_video_tutorials() {
	return Wapu_Core_Video_Tutorials::get_instance();
}
