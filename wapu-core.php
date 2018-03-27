<?php
/**
 * Plugin Name: Wapuu Core
 * Plugin URI:  http://www.cherryframework.com/plugins/
 * Description: Core for jetimpex.com.
 * Version:     3.0.1
 * Author:      JetImpex
 * Author URI:  http://cherryframework.com/
 * Text Domain: wapu-core
 * License:     GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path: /languages
 *
 * @package Wapu_Core
 * @author  Cherry Team
 * @version 1.0.0
 * @license GPL-3.0+
 * @copyright  2002-2016, Cherry Team
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

// If class `Wapu_Core` doesn't exists yet.
if ( ! class_exists( 'Wapu_Core' ) ) {

	/**
	 * Sets up and initializes the plugin.
	 */
	class Wapu_Core {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since  1.0.0
		 * @access private
		 * @var    object
		 */
		private static $instance = null;

		/**
		 * A reference to an instance of cherry framework core class.
		 *
		 * @since  1.0.0
		 * @access private
		 * @var    object
		 */
		private $core = null;

		/**
		 * Holder for base plugin URL
		 *
		 * @since  1.0.0
		 * @access private
		 * @var    string
		 */
		private $plugin_url = null;

		/**
		 * Plugin version
		 *
		 * @var string
		 */
		private $version = '3.0.1';

		/**
		 * Core page trigger
		 *
		 * @var boolean
		 */
		private $is_core_page = false;

		/**
		 * EDD-related instances
		 * @var null
		 */
		public $edd = null;

		/**
		 * Holder for base plugin path
		 *
		 * @since  1.0.0
		 * @access private
		 * @var    string
		 */
		private $plugin_path = null;

		public $deps = array(
			'post-types' => array(
				'core'            => 'abstract/class-wapu-core-post-type.php',
				'knowledge-base'  => 'post-types/knowledge-base/class-wapu-core-knowledge-base.php',
				'how-to'          => 'post-types/how-to/class-wapu-core-how-to.php',
				'faq'             => 'post-types/faq/class-wapu-core-faq.php',
				'video-tutorials' => 'post-types/video-tutorials/class-wapu-core-video-tutorials.php',
			),
			'handlers' => array(
				'templates'        => 'handlers/class-wapu-core-template-handler.php',
				'post-rating'      => 'handlers/class-wapu-core-post-rating.php',
				'related'          => 'handlers/class-wapu-core-related-posts.php',
				'listing-by-terms' => 'handlers/class-wapu-core-listing-by-terms.php',
				'popup'            => 'handlers/class-wapu-core-popup.php',
				'posts-aggregator' => 'handlers/class-wapu-core-posts-aggregator.php',
				'docs-search'      => 'handlers/class-wapu-core-docs-search.php',
				'settings-page'    => 'handlers/class-wapu-core-settings-page.php',
				'db-updater'       => 'handlers/class-wapu-core-db-updater.php',
			),
			'widgets' => array(
				'related-posts'     => 'widgets/related-posts/class-wapu-core-related-posts-widget.php',
				'submit-ticket'     => 'widgets/submit-ticket/class-wapu-core-submit-ticket-widget.php',
				'parent-categories' => 'widgets/parent-categories/class-wapu-core-parent-categories-widget.php',
			),
			'shortcodes' => array(
				'core' => 'abstract/class-wapu-core-shortcode.php',
			),
			'entities' => array(
				'global-settings'    => 'entities/class-wapu-core-global-settings.php',
				'network-set'        => 'entities/class-wapu-core-mutisite-set-data.php',
				'custom-breadcrumbs' => 'entities/class-wapu-core-custom-breadcrumbs.php',
				'blog-banner'        => 'entities/class-wapu-core-blog-banner.php',
				'search-tax'         => 'entities/class-wapu-core-search-tax.php',
				'parent-terms'       => 'entities/class-wapu-core-parent-terms.php',
				'post-type-switcher' => 'entities/class-wapu-core-post-type-switcher.php',
			),
		);

		/**
		 * Sets up needed actions/filters for the plugin to initialize.
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function __construct() {

			// Load the installer core.
			add_action( 'after_setup_theme', require( dirname( __FILE__ ) . '/cherry-framework/setup.php' ), 0 );

			// Load the core functions/classes required by the rest of the plugin.
			add_action( 'after_setup_theme', array( $this, 'get_core' ), 1 );
			// Load the modules.
			add_action( 'after_setup_theme', array( 'Cherry_Core', 'load_all_modules' ), 2 );

			// Internationalize the text strings used.
			add_action( 'init', array( $this, 'lang' ), 0 );
			// Load the admin files.
			add_action( 'init', array( $this, 'admin' ), 0 );
			// Load the admin files.
			add_action( 'init', array( $this, 'init' ), 0 );

			add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );

			add_filter( 'the_content', array( $this, 'remove_p' ) );

			// Register activation and deactivation hook.
			register_activation_hook( __FILE__, array( $this, 'activation' ) );
			register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );
		}

		/**
		 * Load required dependencies if not loaded before
		 *
		 * @param  array $load Array of required dependencies to load
		 * @return void
		 */
		public function dependencies( $load = array(), $from = false ) {

			if ( false === $from ) {
				$from = $this->deps;
			}

			foreach ( $load as $key => $handle ) {

				if ( is_array( $handle ) && isset( $from[ $key ] ) ) {
					$this->dependencies( $handle, $from[ $key ] );
				}

				if ( is_string( $handle ) && isset( $from[ $handle ] ) ) {

					if ( is_array( $from[ $handle ] ) ) {
						array_walk( $from[ $handle ], array( $this, '_require' ) );
					} else {
						$this->_require( $from[ $handle ] );
					}
				}

			}

		}

		/**
		 * Check if currently is core page displayed.
		 *
		 * @return boolean
		 */
		public function is_core_page() {
			return $this->is_core_page;
		}

		/**
		 * Set code_page trigger to true
		 *
		 * @return void
		 */
		public function the_core_page() {
			$this->is_core_page = true;
		}

		/**
		 * Remove empty paragraphs
		 *
		 * @param  string $content Page content.
		 * @return string
		 */
		public function remove_p( $content ) {

			// opening tag
			$rep = preg_replace( "/(<p>)?\[([a-z-]+)(\s[^\]]+)?\](<\/p>|<br \/>)?/", "[$2$3]", $content );

			// closing tag
			$rep = preg_replace( "/(<p>)?\[\/([a-z-]+)](<\/p>|<br \/>)?/", "[/$2]", $rep );

			return $rep;
		}

		/**
		 * Callback function to include file if not included before
		 *
		 * @param  string $file Relateive path to file.
		 * @return void
		 */
		public function _require( $file ) {
			require_once $this->plugin_path( 'includes/' . $file );
		}

		/**
		 * Loads the core functions. These files are needed before loading anything else in the
		 * plugin because they have required functions for use.
		 *
		 * @since  1.0.0
		 * @access public
		 * @return object
		 */
		public function get_core() {

			/**
			 * Fires before loads the plugin's core.
			 *
			 * @since 1.0.0
			 */
			do_action( 'wapu_core_core_before' );

			global $chery_core_version;

			if ( null !== $this->core ) {
				return $this->core;
			}

			if ( 0 < sizeof( $chery_core_version ) ) {
				$core_paths = array_values( $chery_core_version );
				require_once( $core_paths[0] );
			} else {
				die( 'Class Cherry_Core not found' );
			}

			$this->dependencies( array( 'handlers' => array( 'db-updater' ) ) );

			$this->core = new Cherry_Core( array(
				'base_dir' => $this->plugin_path( 'cherry-framework' ),
				'base_url' => $this->plugin_url( 'cherry-framework' ),
				'modules'  => array(
					'cherry-js-core' => array(
						'autoload' => true,
					),
					'cherry-ui-elements' => array(
						'autoload' => false,
					),
					'cherry-handler' => array(
						'autoload' => false,
					),
					'cherry-interface-builder' => array(
						'autoload' => false,
					),
					'cherry-utility' => array(
						'autoload' => true,
						'args'     => array(
							'meta_key' => array(
								'term_thumb' => 'cherry_terms_thumbnails'
							),
						)
					),
					'cherry-widget-factory' => array(
						'autoload' => true,
					),
					'cherry-term-meta' => array(
						'autoload' => false,
					),
					'cherry-post-meta' => array(
						'autoload' => false,
					),
					'cherry-dynamic-css' => array(
						'autoload' => false,
					),
					'cherry5-insert-shortcode' => array(
						'autoload' => false,
					),
					'cherry5-assets-loader' => array(
						'autoload' => false,
					),
					'cherry-db-updater' => array(
						'autoload' => true,
						'args'     => array(
							'slug'      => 'wapu-core',
							'version'   => $this->get_version(),
							'callbacks' => array(
								'1.0.1' => array(
									array( wapu_core_db_updater(), 'update101' ),
								),
							),
						),
					),
				),
			) );

			return $this->core;
		}

		/**
		 * Returns plugin version
		 *
		 * @return string
		 */
		public function get_version() {
			return $this->version;
		}

		/**
		 * Manually init required modules.
		 *
		 * @return void
		 */
		public function init() {

			$this->dependencies( array( 'post-types', 'widgets', 'handlers', 'shortcodes', 'entities' ) );

			/**
			 * Initialize search category
			 */
			wapu_core_search_tax()->init();

			/**
			 * Initialize parent terms management
			 */
			wapu_core_parent_terms()->init();

			/**
			 * Initialize post types
			 */
			wapu_core_knowldege_base()->init();
			wapu_core_how_to()->init();
			wapu_core_faq()->init();
			wapu_core_video_tutorials()->init();

			/**
			 * Initialize post type switcer
			 */
			wapu_core_post_type_switcher()->init();

			/**
			 * Initalize post rating
			 */
			wapu_core_post_rating()->init();

			/**
			 * Initalize related posts
			 */
			wapu_core_related_posts()->init();

			/**
			 * Initalize posts aggregator module
			 */
			//wapu_core_posts_aggregator()->init();

			$this->init_shortcodes();

			/**
			 * Init banner after first blog post.
			 *
			 * Note! Must be before wapu_core_global_settings()->init() call
			 */
			//wapu_core_blog_banner()->init();

			/**
			 * Init global settings
			 */
			wapu_core_global_settings()->init();

			/**
			 * Initialize network set data handler
			 */
			wapu_core_mutisite_set_data()->init();

			/**
			 * Init custom breadcrumbs
			 */
			wapu_core_custom_breadcrumbs()->init();

			require $this->plugin_path( 'includes/handlers/class-wapu-core-meta-cache.php' );
			require $this->plugin_path( 'includes/api/class-wapu-core-api-manager.php' );
			require $this->plugin_path( 'includes/entities/class-wapu-core-header-cart.php' );

			if ( class_exists( 'Easy_Digital_Downloads' ) ) {

				require $this->plugin_path( 'includes/entities/class-wapu-core-edd-account.php' );
				require $this->plugin_path( 'includes/entities/class-wapu-core-edd-settings.php' );
				require $this->plugin_path( 'includes/entities/class-wapu-core-edd-meta.php' );
				require $this->plugin_path( 'includes/entities/class-wapu-core-edd-badges.php' );
				require $this->plugin_path( 'includes/entities/class-wapu-core-edd-single-download.php' );
				require $this->plugin_path( 'includes/handlers/class-wapu-core-mailchimp.php' );

				$this->edd = new stdClass();

				$this->edd->settings = new Wapu_Core_EDD_Settings();
				$this->edd->account  = new Wapu_Core_EDD_Account();
				$this->edd->badges   = new Wapu_Core_EDD_Badges();
				$this->edd->meta     = new Wapu_Core_EDD_Meta();
				$this->edd->single   = new Wapu_Core_EDD_Single_Download();

			}

			wapu_core_header_cart();

			wapu_core_api_manager()->init();
		}

		/**
		 * Initialize shortocdes.
		 *
		 * @return void
		 */
		public function init_shortcodes() {

			foreach ( glob( $this->plugin_path( 'includes/shortcodes/*.php' ) ) as $file ) {
				include_once $file;
			}

		}

		/**
		 * Init shortcode by class name
		 *
		 * @param  [type] $class [description]
		 * @return [type]        [description]
		 */
		public function init_shortcode( $class ) {

			if ( class_exists( $class ) ) {
				$instance = call_user_func( array( $class, 'get_instance' ) );
				$instance->init();
			}
		}

		/**
		 * Returns path to file or dir inside plugin folder
		 *
		 * @param  string $path Path inside plugin dir.
		 * @return string
		 */
		public function plugin_path( $path = null ) {

			if ( ! $this->plugin_path ) {
				$this->plugin_path = trailingslashit( plugin_dir_path( __FILE__ ) );
			}

			return $this->plugin_path . $path;
		}
		/**
		 * Returns url to file or dir inside plugin folder
		 *
		 * @param  string $path Path inside plugin dir.
		 * @return string
		 */
		public function plugin_url( $path = null ) {

			if ( ! $this->plugin_url ) {
				$this->plugin_url = trailingslashit( plugin_dir_url( __FILE__ ) );
			}

			return $this->plugin_url . $path;
		}

		/**
		 * Loads admin files.
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function admin() {

		}

		/**
		 * Loads the translation files.
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function lang() {
			load_plugin_textdomain( 'wapu-core', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}

		/**
		 * Get the template path.
		 *
		 * @return string
		 */
		public function template_path() {
			return apply_filters( 'wapu-core/template-path', 'wapu-core/' );
		}

		/**
		 * Returns path to template file.
		 *
		 * @return string|bool
		 */
		public function get_template( $name = null ) {

			$template = locate_template( $this->template_path() . $name );

			if ( ! $template ) {
				$template = $this->plugin_path( 'templates/' . $name );
			}

			if ( file_exists( $template ) ) {
				return $template;
			} else {
				return false;
			}
		}

		/**
		 * Enqueue public-facing stylesheets.
		 *
		 * @since 1.0.0
		 * @access public
		 * @return void
		 */
		public function register_assets() {

			if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
				$prefix = '';
			} else {
				$prefix = '.min';
			}

			wp_register_script(
				'wapu-core',
				$this->plugin_url( 'assets/js/wapu-core.js' ),
				array( 'jquery' ),
				$this->get_version(),
				true
			);

			wp_register_script(
				'clipboard',
				$this->plugin_url( 'assets/js/vendor/clipboard.min.js' ),
				array( 'jquery' ),
				'1.7.1',
				true
			);

			wp_register_script(
				'vue',
				$this->plugin_url( 'assets/js/vendor/vue' . $prefix . '.js' ),
				array(),
				'2.5.13',
				true
			);

			$data = apply_filters( 'wapu_core/localize_data', array(
				'ajaxurl' => esc_url( admin_url( 'admin-ajax.php' ) ),
				'api'     => array(
					'uri'       => is_multisite() ? network_home_url( '/wp-json/' ) : home_url( '/wp-json/' ),
					'ajaxUri'   => is_multisite() ? network_home_url( '/wp-admin/admin-ajax.php' ) : esc_url( admin_url( 'admin-ajax.php' ) ),
					'endpoints' => array(
						'themes'           => 'wapu/v1/themes/',
						'cart'             => 'wapu_cart',
						'addToCart'        => 'wapu/v1/add-to-cart/',
						'getWishListModal' => 'wapu_wishlist_get_modal',
						'addToWishlist'    => 'wapu/v1/wishlist-add/',
					),
				)
			) );

			wp_localize_script( 'wapu-core', 'wapuCoreSettings', $data );

			wp_enqueue_style(
				'wapu-core', $this->plugin_url( 'assets/css/wapu-core.css' ), false, $this->get_version()
			);

			wp_enqueue_style(
				'nucleo-outline', $this->plugin_url( 'assets/css/nucleo-outline.css' ), false, $this->get_version()
			);

			$this->get_core()->init_module(
				'cherry5-assets-loader',
				array(
					'css' => array( 'nucleo-outline' ),
				)
			);
		}

		/**
		 * Do some stuff on plugin activation
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function activation() {
			flush_rewrite_rules();
		}

		/**
		 * Do some stuff on plugin activation
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function deactivation() {
			flush_rewrite_rules();
		}

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @access public
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

if ( ! function_exists( 'wapu_core' ) ) {

	/**
	 * Returns instanse of the plugin class.
	 *
	 * @since  1.0.0
	 * @return object
	 */
	function wapu_core() {
		return Wapu_Core::get_instance();
	}
}

wapu_core();
