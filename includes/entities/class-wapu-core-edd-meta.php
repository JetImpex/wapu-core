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
