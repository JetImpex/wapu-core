<?php
/**
 * Duplicate posts for main blog.
 *
 * @package   package_name
 * @author    Cherry Team
 * @license   GPL-2.0+
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Wapu_Core_Posts_Aggregator' ) ) {

	/**
	 * Define Wapu_Core_Posts_Aggregator class
	 */
	class Wapu_Core_Posts_Aggregator {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * ID of main blog
		 *
		 * @var integer
		 */
		public $main_blog_id = 1;

		/**
		 * Alias keys
		 *
		 * @var string
		 */
		public $child_alias  = '_alias_child';
		public $parent_alias = '_alias_parent';

		/**
		 * Register relate posts taxonomy.
		 *
		 * @return void
		 */
		public function init() {

			if ( ! is_multisite() ) {
				return;
			}

			add_action( 'save_post', array( $this, 'duplicate_post' ), 10, 2 );

			if ( is_main_site() ) {
				add_filter( 'post_link', array( $this, 'post_link' ), 10, 3 );
				add_filter( 'the_excerpt', array( $this, 'post_excerpt' ) );
				add_filter( 'the_content', array( $this, 'post_excerpt' ) );
				add_filter( 'get_post_metadata', array( $this, 'thumb_id' ), 10, 4 );
				add_filter( 'begin_fetch_post_thumbnail_html', array( $this, 'set_alias_blog' ), 10, 3 );
				add_filter( 'end_fetch_post_thumbnail_html', array( $this, 'reset_alias_blog' ), 10, 3 );
			}

		}

		/**
		 * Set post blog
		 *
		 * @param int          $post_id           The post ID.
		 * @param string       $post_thumbnail_id The post thumbnail ID.
		 * @param string|array $size              The post thumbnail size. Image size or array of width
		 */
		public function set_alias_blog( $post_id, $post_thumbnail_id, $size ) {

			$alias_blog = $this->get_alias( $post_id, 'blog_id' );

			if ( ! $alias_blog ) {
				return;
			}

			switch_to_blog( $alias_blog );
		}

		/**
		 * Reset default blog.
		 *
		 * @param int          $post_id           The post ID.
		 * @param string       $post_thumbnail_id The post thumbnail ID.
		 * @param string|array $size              The post thumbnail size. Image size or array of width
		 */
		public function reset_alias_blog( $post_id, $post_thumbnail_id, $size ) {
			restore_current_blog();
		}

		/**
		 * Return thumbnail URL as thumb ID.
		 *
		 * @param mixed   $value     The value get_metadata() should return - a single metadata value,
		 *                          or an array of values.
		 * @param int     $object_id Object ID.
		 * @param string  $meta_key  Meta key.
		 * @param bool    $single    Whether to return only the first value of the specified $meta_key.
		 * @return mixed
		 */
		public function thumb_id( $value, $object_id, $meta_key, $single ) {

			if ( '_thumbnail_id' !== $meta_key ) {
				return $value;
			}

			$alias_thumb = $this->get_alias( $object_id, 'thumb' );

			if ( ! empty( $alias_thumb ) ) {
				return $alias_thumb;
			} else {
				return $value;
			}

		}

		/**
		 * Set alias post excerpt
		 *
		 * @param  string $default Default excerpt/content text.
		 * @return string
		 */
		public function post_excerpt( $default ) {

			$alias_excerpt = $this->get_alias( get_the_id(), 'excerpt' );

			if ( ! empty( $alias_excerpt ) ) {
				return $alias_excerpt;
			} else {
				return $default;
			}

		}

		/**
		 * Set alias link.
		 *
		 * @param  string  $permalink The post's permalink.
		 * @param  WP_Post $post      The post in question.
		 * @param  bool    $leavename Whether to keep the post name.
		 * @return string
		 */
		public function post_link( $permalink, $post, $leavename ) {

			$alias_link = $this->get_alias( $post->ID, 'url' );

			if ( ! empty( $alias_link ) ) {
				return $alias_link;
			} else {
				return $permalink;
			}

		}

		/**
		 * Get alias data.
		 *
		 * @param  int    $post_id Post ID to get value for.
		 * @param  string $key     Alias key.
		 * @return mixed
		 */
		public function get_alias( $post_id, $key ) {

			$alias = get_post_meta( $post_id, $this->parent_alias, true );

			if ( ! $alias ) {
				return false;
			}

			if ( isset( $alias[ $key ] ) ) {
				return $alias[ $key ];
			} else {
				return false;
			}

		}

		/**
		 * Duplicate posts or update duplicated post data
		 *
		 * @param  int    $post_id Saved post ID.
		 * @param  object $post    Saved post object.
		 * @return void|null
		 */
		public function duplicate_post( $post_id, $post ) {

			$current_blog = get_current_blog_id();

			if ( is_main_site( $current_blog ) ) {
				return;
			}

			if ( 'post' !== $post->post_type ) {
				return;
			}

			if ( 'publish' !== $post->post_status ) {
				return;
			}

			$alias = get_post_meta( $post_id, $this->child_alias, true );

			if ( ! $alias || ! $this->is_alias_exists( $alias ) ) {

				$alias_data = array(
					'post_title'  => $post->post_title,
					'post_status' => 'publish',
				);
				switch_to_blog( $this->main_blog_id );
				$alias = wp_insert_post( $alias_data );
				wp_remove_object_terms( $alias, 'uncategorized', 'category' );
				restore_current_blog();

				update_post_meta( $post_id, $this->child_alias, $alias );

			}

			$this->update_aliases( $alias, $post_id, $post );

		}

		/**
		 * Check if alias is exists
		 *
		 * @param  int  $post_id Alias (main site) post ID.
		 * @return boolean
		 */
		public function is_alias_exists( $post_id ) {
			switch_to_blog( $this->main_blog_id );
			$post = get_post( $post_id );
			restore_current_blog();

			return ( ! empty( $post ) );
		}

		/**
		 * Update aliases data for main blog post.
		 *
		 * @param  int    $main_post_id    Post ID in main blog (child alias).
		 * @param  int    $current_post_id Post ID in current blog (parent alias).
		 * @param  object $current_post    Current posst object.
		 *
		 * @return bool
		 */
		public function update_aliases( $main_post_id, $current_post_id, $current_post ) {

			$data = array(
				'id'      => $current_post_id,
				'blog_id' => get_current_blog_id(),
				'url'     => get_permalink( $current_post_id ),
				'thumb'   => get_post_thumbnail_id( $current_post_id ),
				'excerpt' => $this->get_parent_excerpt( $current_post ),
				'author'  => array(
					'name' => get_bloginfo( 'name' ),
					'url'  => home_url( '/' ),
				),
			);

			$terms = wp_get_post_terms( $current_post_id, array( 'post_tag', 'category' ) );

			switch_to_blog( $this->main_blog_id );

			update_post_meta( $main_post_id, $this->parent_alias, $data );

			if ( ! empty( $terms ) ) {
				foreach ( $terms as $term ) {
					$this->update_alias_terms( $main_post_id, $term );
				}
			}

			restore_current_blog();

		}

		/**
		 * Returns excerpt for parent post.
		 *
		 * @return string
		 */
		public function get_parent_excerpt( $current_post ) {

			if ( ! empty( $current_post->post_excerpt ) ) {
				return $current_post->post_excerpt;
			}

			global $post;

			$tmp_post = $post;
			$post     = $current_post;
			setup_postdata( $post );

			$excerpt = wp_trim_excerpt();

			$post = $tmp_post;
			wp_reset_postdata();

			return $excerpt;
		}

		/**
		 * Upadte alias terms.
		 *
		 * @param  int    $main_post_id Post ID from main blog.
		 * @param  object $term         Terms object from curretn blog.
		 * @return bool
		 */
		public function update_alias_terms( $main_post_id, $term ) {

			if ( ! term_exists( $term->slug, $term->taxonomy, $term->parent ) ) {

				$append_term = wp_insert_term(
					$term->name,
					$term->taxonomy,
					array(
						'description' => $term->description,
						'parent'      => $term->parent,
						'slug'        => $term->slug,
					)
				);

			} else {
				$append_term = get_term_by( 'slug', $term->slug, $term->taxonomy, ARRAY_A );
			}

			if (
				! is_wp_error( $append_term )
				&& isset( $append_term['term_id'] )
				&& ! has_term( $append_term['term_id'], $term->taxonomy, $main_post_id )
			) {
				$terms = wp_set_post_terms(
					$main_post_id,
					array( intval( $append_term['term_id'] ) ),
					$term->taxonomy,
					true
				);
				return true;
			}

			return false;
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
 * Returns instance of Wapu_Core_Posts_Aggregator
 *
 * @return object
 */
function wapu_core_posts_aggregator() {
	return Wapu_Core_Posts_Aggregator::get_instance();
}
