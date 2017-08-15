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

if ( ! class_exists( 'Wapu_Core_Blog_Banner' ) ) {

	/**
	 * Define Wapu_Core_Blog_Banner class
	 */
	class Wapu_Core_Blog_Banner {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Check if we need to show banner after post
		 *
		 * @var boolean
		 */
		private $allowed = true;

		/**
		 * Has banners option key
		 *
		 * @var string
		 */
		public $has_banners_option = 'wapu_core_has_banners';

		/**
		 * Was blog switched or not.
		 * @var boolean
		 */
		public $blog_switched = false;

		/**
		 * Constructor for the class
		 */
		public function init() {

			add_filter( 'wapu_core/general_setting', array( $this, 'add_banners_settings' ) );
			add_action( 'wapu_after_post', array( $this, 'add_banner' ) );

		}

		/**
		 * Add banners settings
		 */
		public function add_banners_settings( $settings ) {

			if ( is_multisite() && is_main_site() ) {
				return $settings;
			}

			$settings['tabs']['banners']         = esc_html__( 'Banners', 'wapu-core' );
			$settings['controls']['blog-banner'] = array(
				'type'         => 'repeater',
				'title'        => esc_html__( 'Blog Banners', 'wapu-core' ),
				'add_label'    => esc_html__( 'Add Banner', 'wapu-core' ),
				'title_field'  => 'link',
				'parent'       => 'banners',
				'sanitize_cb'  => array( $this, 'pass_banner_to_parent' ),
				'fields'       => array(
					'banner' => array(
						'type'               => 'media',
						'id'                 => 'banner',
						'name'               => 'banner',
						'label'              => esc_html__( 'Image', 'wapu-core' ),
						'multi_upload'       => true,
						'library_type'       => 'image',
						'upload_button_text' => esc_html__( 'Select Image', 'wapu-core' ),
					),
					'link' => array(
						'type'        => 'text',
						'id'          => 'link',
						'name'        => 'link',
						'placeholder' => esc_html__( 'Banner URL', 'wapu-core' ),
						'label'       => esc_html__( 'URL', 'wapu-core' ),
					),
					'title' => array(
						'type'        => 'text',
						'id'          => 'title',
						'name'        => 'title',
						'placeholder' => esc_html__( 'Banner Title', 'wapu-core' ),
						'label'       => esc_html__( 'Title', 'wapu-core' ),
					),
				),
			);

			return $settings;
		}

		/**
		 * Pass banner to parent site
		 * @return [type] [description]
		 */
		public function pass_banner_to_parent( $input ) {

			if ( ! function_exists( 'switch_to_blog' ) ) {
				return $input;
			}

			if ( ! empty( $input ) ) {

				$current_blog = get_current_blog_id();
				switch_to_blog( wapu_core_posts_aggregator()->main_blog_id );

				$has_banners   = get_option( $this->has_banners_option, array() );
				$has_banners[] = $current_blog;
				$has_banners   = array_unique( $has_banners );

				update_option( $this->has_banners_option, $has_banners );

				restore_current_blog();
			}

			return $input;

		}

		/**
		 * Add banner after first post.
		 */
		public function add_banner() {

			if ( ! $this->allowed ) {
				return;
			}

			$this->maybe_switch_blog();
			$this->show_current_site_banner();
			$this->maybe_restore_blog();

		}

		/**
		 * Check if we was reffered
		 */
		public function is_referred() {

			if ( empty( $_SERVER['HTTP_REFERER'] ) ) {
				return false;
			}

			return ( false !== strpos( $_SERVER['HTTP_REFERER'], home_url( '/' ) ) );
		}

		/**
		 * Maybe switch blog to reffered
		 *
		 * @return [type] [description]
		 */
		public function maybe_switch_blog() {

			if ( ! is_multisite() ) {
				return;
			}

			if ( ! is_main_site() ) {
				return;
			}

			$ref   = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : false;
			$sites = get_sites( array( 'site__not_in' => get_current_blog_id() ) );

			if ( ! $ref ) {
				$this->switch_to_random_blog();
				return;
			}

			foreach ( $sites as $site ) {

				$path = $site->domain . $site->path;

				if ( false !== strpos( $ref, $path ) ) {
					$this->blog_switched = true;
					switch_to_blog( $site->blog_id );
					return;
				}

			}

		}

		/**
		 * Switch to random blog if no refferer was passed
		 *
		 * @return void
		 */
		public function switch_to_random_blog() {

			$sites = get_option( $this->has_banners_option, array() );

			if ( empty( $sites ) ) {
				return;
			}

			$count  = count( $sites );
			$wp_key = array_search( 2, $sites );

			if ( ! $wp_key ) {
				$max = $count - 1;
			} else {
				$max = $count + ceil( $count / 2 );
			}

			$rand_id = rand( 0, $max );

			if ( $rand_id > $count - 1 ) {
				$rand_id = $wp_key;
			}

			$site_id = $sites[ $rand_id ];

			$this->blog_switched = true;
			switch_to_blog( $site_id );
		}

		/**
		 * Restre blog if it was switched
		 * @return [type] [description]
		 */
		public function maybe_restore_blog() {

			if ( ! $this->blog_switched ) {
				return;
			}

			restore_current_blog();
		}

		/**
		 * Show blog banner for currently active blog
		 *
		 * @return void
		 */
		public function show_current_site_banner() {

			$banners = wapu_core_global_settings()->get( 'blog-banner' );

			if ( empty( $banners ) ) {
				return;
			}

			$this->allowed = false;

			$banners  = array_values( $banners );
			$key      = $this->get_current_banner_key( count( $banners ) );
			$data     = $banners[ $key ];
			$template = wapu_core()->get_template( 'entities/blog-banner.php' );

			$link  = isset( $data['link'] ) ? esc_url( $data['link'] ) : false;
			$src   = isset( $data['banner'] ) ? wp_get_attachment_image_url( $data['banner'], 'full' ) : false;
			$title = isset( $data['title'] ) ? esc_attr( $data['title'] ) : false;

			if ( file_exists( $template ) ) {
				include $template;
			}

		}

		/**
		 * Get banner key to show
		 *
		 * @param  int $count Banners count.
		 * @return int
		 */
		public function get_current_banner_key( $count = 1 ) {

			$count = $count - 1;

			if ( ! $count ) {
				return $count;
			}

			return rand( 0, $count );
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
 * Returns instance of Wapu_Core_Blog_Banner
 *
 * @return object
 */
function wapu_core_blog_banner() {
	return Wapu_Core_Blog_Banner::get_instance();
}
