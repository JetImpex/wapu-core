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

if ( ! class_exists( 'Wapu_Core_EDD_Meta' ) ) {

	/**
	 * Define Wapu_Core_EDD_Meta class
	 */
	class Wapu_Core_EDD_Meta {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * EDD post type
		 *
		 * @var string
		 */
		public $post_type = 'download';

		/**
		 * Constructor for the class
		 */
		public function __construct() {

			$this->register_metaboxes();
			$this->extend_default_metaboxes();

			add_action( 'init', array( $this, 'register_taxes' ), 10 );
			add_action( 'admin_enqueue_scripts', array( $this, 'clear_caches' ) );
		}

		/**
		 * Clear post meta cache
		 *
		 * @return [type] [description]
		 */
		public function clear_caches() {

			if ( empty( $_GET['clear_meta_cache'] ) ) {
				return;
			}

			$cache = new Wapu_Core_Meta_Cache( get_the_ID() );
			$cache->delete( esc_attr( $_GET['clear_meta_cache'] ) );

		}

		/**
		 * Register taxonomies list
		 *
		 * @return [type] [description]
		 */
		public function register_taxes() {

			$taxes = array(
				'topic'               => 'Topic',
				'high-resolution'     => 'High Resolution',
				'widget-ready'        => 'Widget Ready',
				'compatible-browsers' => 'Compatible Browsers',
				'compatible-with'     => 'Compatible With',
				'software-version'    => 'Software Version',
			);

			foreach ( $taxes as $tax => $name ) {

				register_taxonomy(
					$tax,
					$this->post_type,
					array(
						'label'  => $name,
						'labels' => array(
							'name' => $name,
						),
					)
				);

				wapu_core()->edd->single->add_tax( $tax, $name );
			}

		}

		/**
		 * Register new metaboxes for EDD
		 * @return [type] [description]
		 */
		public function register_metaboxes() {

			wapu_core()->get_core()->init_module( 'cherry-post-meta', array(
				'id'            => 'wapu_gallery',
				'title'         => esc_html__( 'Gallery', 'wapu-core' ),
				'page'          => array( $this->post_type ),
				'context'       => 'normal',
				'priority'      => 'high',
				'callback_args' => false,
				'fields' => array(
					'_wapu_single_large_thumb' => array(
						'type'  => 'media',
						'title' => esc_html__( 'Single Large Image', 'wapu-core' ),
					),
				),
			) );

			wapu_core()->get_core()->init_module( 'cherry-post-meta', array(
				'id'            => 'wapu_clear_caches',
				'title'         => esc_html__( 'Clear Caches', 'wapu-core' ),
				'page'          => array( $this->post_type ),
				'context'       => 'side',
				'priority'      => 'high',
				'callback_args' => false,
				'fields' => array(
					'_wapu_clear_reviews_cache' => array(
						'type'  => 'html',
						'title' => esc_html__( 'Clear Reviews Cache', 'wapu-core' ),
						'html'  => sprintf(
							'<a href="%s" class="button">Clear Reviews Cache</a><br><br>',
							add_query_arg( array( 'clear_meta_cache' => '_rating_cache' ) )
						),
					),
					'_wapu_clear_terms_cache' => array(
						'type'  => 'html',
						'title' => esc_html__( 'Clear Terms Cache', 'wapu-core' ),
						'html'  => sprintf(
							'<a href="%s" class="button">Clear Terms Cache</a>',
							add_query_arg( array( 'clear_meta_cache' => '_terms_cache' ) )
						),
					),
				),
			) );

			wapu_core()->get_core()->init_module( 'cherry-post-meta', array(
				'id'            => 'wapu_misc',
				'title'         => esc_html__( 'Misc Options', 'wapu-core' ),
				'page'          => array( $this->post_type ),
				'context'       => 'normal',
				'priority'      => 'high',
				'callback_args' => false,
				'fields' => array(
					'_wapu_ld_url' => array(
						'type'  => 'text',
						'title' => esc_html__( 'Live Demo URL', 'wapu-core' ),
					),
				),
			) );

			wapu_core()->get_core()->init_module( 'cherry-term-meta', array(
				'tax'      => 'download_category',
				'priority' => 10,
				'fields'   => array(
					'_wapu_category_home' => array(
						'type'  => 'text',
						'label' => 'Home URL for current category (for breadcrumbs)',
					),
				),
			) );

		}

		/**
		 * Extend default metaboxes for EDD
		 *
		 * @return [type] [description]
		 */
		public function extend_default_metaboxes() {

			add_action( 'edd_save_download', array( $this, 'save_default_fields' ), 10, 2 );;

			add_action( 'edd_stats_meta_box', array( $this, 'add_fake_sales_input' ) );

		}

		public function add_fake_sales_input() {

			global $post;
			$sales = get_post_meta( $post->ID, '_fake_sales', true );
			?>
			<p class="product-sales-stats">
				<span class="label">Add Fake Sales:</span>
				<input type="number" name="_fake_sales" value="<?php echo $sales; ?>" class="small-text" style="margin: -5px 0 0 5px;">
			</p>
			<?php
		}

		/**
		 * Save fields added to default metaboxes
		 *
		 * @param  [type] $post_id [description]
		 * @param  [type] $post    [description]
		 * @return [type]          [description]
		 */
		public function save_default_fields( $post_id, $post ) {

			if ( ! empty( $_POST['_fake_sales'] ) ) {
				update_post_meta( $post_id, '_fake_sales', absint( $_POST['_fake_sales'] ) );
			} else {
				delete_post_meta( $post_id, '_fake_sales' );
			}

		}

	}

}
