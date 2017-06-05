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

if ( ! class_exists( 'Wapu_Core_Post_Rating' ) ) {

	/**
	 * Define Wapu_Core_Post_Rating class
	 */
	class Wapu_Core_Post_Rating {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Meta key name for rating data holder
		 *
		 * @var string
		 */
		public $meta_key = 'wapu-rating';

		/**
		 * Attach rating callback to apropriate hook
		 */
		public function init() {

			add_action( 'wapu_core/handlers/post_rating/show', array( $this, 'show_rating_buttons' ) );
			add_action( 'wapu_core/listing_by_term/order_fields', array( $this, 'pass_rate_field' ) );
			add_filter( 'wapu_core/localize_data', array( $this, 'add_localize_data' ) );

			$actions = $this->rating_actions();

			add_action( 'wp_ajax_' . $actions['like'], array( $this, 'rate_post' ) );
			add_action( 'wp_ajax_nopriv_' . $actions['like'], array( $this, 'rate_post' ) );
			add_action( 'wp_ajax_' . $actions['dislike'], array( $this, 'rate_post' ) );
			add_action( 'wp_ajax_nopriv_' . $actions['dislike'], array( $this, 'rate_post' ) );

			add_action( 'save_post', array( $this, 'set_zero_views' ), 10, 3 );

		}

		/**
		 * Set zaro views on create post (for correct ordering)
		 *
		 * @param int $post_id Saved post ID.
		 */
		public function set_zero_views( $post_id, $post, $update ) {

			$diff = get_post_meta( $post_id, $this->meta_key . '_diff', true );

			if ( ! $diff ) {
				update_post_meta( $post_id, $this->meta_key . '_diff', 0 );
			}

		}

		/**
		 * Add posts rating to allowed sorting keys.
		 *
		 * @param  array $fields Existing fields.
		 * @return array
		 */
		public function pass_rate_field( $fields ) {
			$fields['rating'] = $this->meta_key . '_diff';
			return $fields;
		}

		/**
		 * Add post rating related data into localize array.
		 *
		 * @param  array $data Post rating data to add.
		 * @return array
		 */
		public function add_localize_data( $data ) {
			$data['postRating'] = array(
				'rated' => esc_html__( 'You already rated this article', 'wapu-core' ),
			);

			return $data;
		}

		/**
		 * Like passed post
		 *
		 * @return void
		 */
		public function rate_post() {

			$data   = $this->verify_request();
			$id     = $data['id'];
			$action = $data['action'];
			$rating = get_post_meta( $id, $this->meta_key, true );

			if ( ! $rating ) {
				$rating = array(
					'likes'    => 0,
					'dislikes' => 0,
				);
			}

			if ( 'like' === $action ) {
				$rating['likes']++;
			} else {
				$rating['dislikes']++;
			}

			$diff = $rating['likes'] - $rating['dislikes'];

			update_post_meta( $id, $this->meta_key, $rating );
			update_post_meta( $id, $this->meta_key . '_diff', $diff );

			wp_send_json_success( array(
				'replace' => $this->get_replace_message(),
				'rating'  => array(
					'likes'    => $rating['likes'],
					'dislikes' => $rating['dislikes'],
					'diff'     => $diff,
				),
			) );
		}

		/**
		 * Get replace message content
		 *
		 * @return string
		 */
		public function get_replace_message() {
			return apply_filters(
				'wapu_core/handlers/post_rating/replace_message',
				sprintf(
					'<div class="wapu-post-rating__replace_msg">%s</div>',
					esc_html__( 'Thank you for vote', 'wapu-core' )
				)
			);
		}

		/**
		 * Verify like/dislike request
		 *
		 * @return void
		 */
		public function verify_request() {

			$id      = isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : false;
			$actions = $this->rating_actions();
			$action  = ( $actions['like'] === $_REQUEST['action'] ) ? 'like' : 'dislike';

			if ( ! $id ) {
				wp_send_json_error( array(
					'error' => esc_html__( 'Please, provide valid data', 'wapu-core' ),
				) );
			}

			return array(
				'id'     => $id,
				'action' => $action,
			);
		}

		/**
		 * Render rating buttons html
		 *
		 * @return void|null
		 */
		public function show_rating_buttons() {

			$template = wapu_core()->get_template( 'handlers/post-rating.php' );

			if ( ! $template ) {
				return null;
			}

			wp_enqueue_script( 'wapu-core' );

			$post_id  = get_the_id();
			$meta     = get_post_meta( $post_id, $this->meta_key, true );
			$likes    = isset( $meta['likes'] ) ? intval( $meta['likes'] ) : 0;
			$dislikes = isset( $meta['dislikes'] ) ? intval( $meta['dislikes'] ) : 0;
			$diff     = $likes - $dislikes;
			$rate     = $this->rating_actions();
			$like     = sprintf( 'data-init="post-raing" data-action="%s" data-id="%s"', $rate['like'], $post_id );
			$dislike  = sprintf( 'data-init="post-raing" data-action="%s" data-id="%s"', $rate['dislike'], $post_id );

			include $template;
		}

		/**
		 * Returns rating action
		 *
		 * @return array
		 */
		public function rating_actions() {

			return apply_filters( 'wapu_core/handlers/post_rating/actions', array(
				'like'    => 'wapu_core_like',
				'dislike' => 'wapu_core_dislike',
			) );

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
 * Returns instance of Wapu_Core_Post_Rating
 *
 * @return object
 */
function wapu_core_post_rating() {
	return Wapu_Core_Post_Rating::get_instance();
}
