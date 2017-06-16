<?php
/**
 * Abstract post type registration class
 */
if ( ! class_exists( 'Wapu_Core_Post_Type' ) ) {

	abstract class Wapu_Core_Post_Type {

		/**
		 * Post type slug
		 *
		 * format $slug = 'post-type-slug';
		 */
		public $slug = null;

		/**
		 * Post type single name
		 *
		 * format $single_name = esc_html__( 'Item, 'wapu-core' );
		 */
		public $single_name = null;

		/**
		 * Post type plural name
		 *
		 * format $plural_name = esc_html__( 'Items, 'wapu-core' );
		 */
		public $plural_name = null;

		/**
		 * Post type arguments
		 *
		 * format $args = array(
		 *     'public'             => true,
		 *     'publicly_queryable' => true,
		 *     'show_ui'            => true,
		 * );
		 */
		public $args = array();

		/**
		 * Post type options
		 *
		 * format $options = array();
		 */
		public $options = array();

		/**
		 * Add or not rewrite options
		 *
		 * format $rewrite_options = true/false;
		 */
		public $rewrite_options = true;
		public $rewrite_suffix  = '_slug';
		public $rewrite_save    = array();

		/**
		 * Post type slug
		 *
		 * format $taxonomies = array(
		 *     'category' => array(
		 *         'single_name' => esc_html__( 'Category', 'wapu-core' ),
		 *         'plural_name' => esc_html__( 'Categories', 'wapu-core' ),
		 *         'args'        => array(
		 *             'hierarchical'          => false,
		 *             'show_ui'               => true,
		 *             'show_admin_column'     => true,
		 *         ),
		 *     ),
		 * );
		 */
		public $taxonomies  = array();

		/**
		 * Is views counter required for current post type
		 *
		 * @var boolean
		 */
		public $views_counter = true;

		/**
		 * Post meta name for views
		 *
		 * @var string
		 */
		public $views_meta = '_wapu_views';

		/**
		 * Post meta name for order
		 *
		 * @var string
		 */
		public $order_meta = '_wapu_order';

		/**
		 * Initalize post type
		 * @return void
		 */
		public function init() {

			if ( empty( $this->slug ) ) {
				return;
			}

			add_action( 'init', array( $this, 'register_post' ) );
			add_action( 'wapu_core/' . $this->slug . '/archive/page_title', array( $this, 'archive_title' ) );

			if ( ! empty( $this->taxonomies ) ) {
				add_action( 'init', array( $this, 'register_tax' ) );
				// Adds Cherry Search compatibility
				add_filter( 'cherry_search_support_categories', array( $this, 'search_tax' ) );

				$this->add_taxonomy_templates();
			}

			if ( true === $this->rewrite_options && is_admin() ) {
				add_action( 'admin_init', array( $this, 'register_rewrite_options' ), 99 );
			}

			if ( true === $this->views_counter ) {
				add_action( 'wp_head', array( $this, 'count_views' ) );
				add_action( 'save_post', array( $this, 'set_zero_views' ), 10, 3 );
			}

			add_action( 'pre_get_posts', array( $this, 'show_all_posts_on_tax_page' ) );

			wapu_core_template_handler( $this->slug, $this->taxonomies() );
			wapu_core_search_tax()->add_post_type( $this->slug );

			add_filter( 'wapu_core/post_type_switcher/allowed_post_types', array( $this, 'pass_to_switcher' ) );
		}

		/**
		 * Pass current post type into allowed post types for switcher
		 *
		 * @return array
		 */
		public function pass_to_switcher( $post_types = array() ) {
			$post_types[ $this->slug ] = $this->plural_name;
			return $post_types;
		}

		/**
		 * Show all availbale posts on taxonomy archive page
		 *
		 * @return null
		 */
		public function show_all_posts_on_tax_page( $query ) {

			if ( ! $query->is_main_query() ) {
				return;
			}

			foreach ( $this->taxonomies() as $tax ) {
				if ( is_tax( $tax ) ) {
					$query->set( 'posts_per_page', -1 );
				}
			}

		}

		/**
		 * Add default taxonomy templates
		 */
		public function add_taxonomy_templates() {

			foreach ( $this->taxonomies as $taxonomy => $data ) {
				$tax = $this->tax( $taxonomy );
				add_action( 'wapu_core/taxonomy/' . $tax . '/loop_start', array( $this, 'set_tax_template' ) );
				add_action( 'wapu_core/taxonomy/' . $tax . '/loop', array( $this, 'set_tax_template' ) );
				add_action( 'wapu_core/taxonomy/' . $tax . '/loop_end', array( $this, 'set_tax_template' ) );
			}

		}

		/**
		 * Show archive title
		 *
		 * @return void
		 */
		public function archive_title() {

			$title    = apply_filters( 'wapu_core/' . $this->slug . '/page_title_text/archive', $this->plural_name );
			$template = wapu_core()->get_template( 'post-types/' . $this->slug . '/archive-title.php' );
			if ( $template ) {
				include $template;
			}

		}

		/**
		 * Set taxonomy templates
		 *
		 * @param string $tax Taxonomy name.
		 */
		public function set_tax_template( $tax ) {

			$filter   = current_filter();
			$file     = basename( $filter );
			$file     = str_replace( '_', '-', $file ) . '.php';
			$path     = 'post-types/' . $this->slug . '/taxonomy/' . $file;
			$template = wapu_core()->get_template( $path );

			if ( ! $template ) {
				$template = wapu_core()->plugin_path( 'templates/handlers/listing-by-terms/posts-' . $file );
			}

			include $template;
		}

		/**
		 * Get all registered taxomoie names for current post type
		 *
		 * @return array
		 */
		public function taxonomies() {

			$result = array();

			if ( empty( $this->taxonomies ) ) {
				return $result;
			}

			foreach ( $this->taxonomies as $tax => $data ) {
				$result[] = $this->tax( $tax );
			}

			return $result;
		}

		/**
		 * Update post meta on each page reload.
		 *
		 * @return void
		 */
		public function count_views() {

			if ( ! is_singular( $this->slug ) ) {
				return;
			}

			global $post;

			$current = get_post_meta( $post->ID, $this->views_meta, true );
			$current = intval( $current );

			if ( ! $current ) {
				$current = 0;
			}

			$current++;

			update_post_meta( $post->ID, $this->views_meta, $current );
		}

		/**
		 * Set zaro views on create post (for correct ordering)
		 *
		 * @param int $post_id Saved post ID.
		 */
		public function set_zero_views( $post_id, $post, $update ) {

			if ( $this->slug !== $post->post_type ) {
				return;
			}

			$current = get_post_meta( $post_id, $this->views_meta, true );

			if ( ! $current ) {
				update_post_meta( $post_id, $this->views_meta, 0 );
			}

		}

		/**
		 * Register rewrite options for current post type
		 *
		 * @return void
		 */
		public function register_rewrite_options() {

			$title_format = esc_html__( '%s base', 'wapu-core' );
			$option_id    = $this->slug . $this->rewrite_suffix;

			add_settings_field(
				$option_id,
				$this->plural_name,
				array( $this, 'settings_field' ),
				'permalink',
				'optional',
				array(
					'id'      => $option_id,
					'default' => $this->slug,
				)
			);

			$this->rewrite_save[ $option_id ] = $this->slug;

			if ( ! empty( $this->taxonomies ) ) {
				foreach ( $this->taxonomies as $tax => $data ) {
					$id      = $this->tax( $tax ) . $this->rewrite_suffix;
					$default = $this->slug . '-' . $tax;

					add_settings_field(
						$id,
						$this->plural_name . ' ' . $data['plural_name'],
						array( $this, 'settings_field' ),
						'permalink',
						'optional',
						array(
							'id'      => $id,
							'default' => $default,
						)
					);

					$this->rewrite_save[ $id ] = $default;
				}
			}

			$this->save_rewrite_options();
		}

		/**
		 * Save permalinks rewrite options
		 *
		 * @return void|null
		 */
		public function save_rewrite_options() {

			if ( ! is_admin() ) {
				return;
			}

			// We need to save the options ourselves; settings api does not trigger save for the permalinks page.
			if ( ! isset( $_POST['permalink_structure'] ) ) {
				return;
			}

			if ( empty( $this->rewrite_save ) ) {
				return;
			}

			foreach ( $this->rewrite_save as $option => $default ) {
				$value = isset( $_POST[ $option ] ) ? esc_attr( $_POST[ $option ] ) : $default;
				update_option( $option, $value );
			}
		}

		/**
		 * Callback to show rewrite option
		 *
		 * @param  array $args Arguments aray.
		 * @return void
		 */
		public function settings_field( $args ) {

			$id    = $args['id'];
			$value = get_option( $id, $args['default'] );

			printf( '<input name="%1$s" id="%1$s" type="text" value="%2$s" class="regular-text code">', $id, $value );
		}

		/**
		 * Register the custom post type.
		 *
		 * @since 1.0.0
		 * @link  https://codex.wordpress.org/Function_Reference/register_post_type
		 */
		public function register_post() {

			$labels = array(
				'name'               => $this->plural_name,
				'singular_name'      => $this->single_name,
				'add_new'            => esc_html__( 'Add New', 'wapu-core' ),
				'add_new_item'       => sprintf( __( 'Add New %s', 'wapu-core' ), $this->single_name ),
				'edit_item'          => sprintf( __( 'Edit %s', 'wapu-core' ), $this->single_name ),
				'new_item'           => sprintf( __( 'New %s', 'wapu-core' ), $this->single_name ),
				'view_item'          => sprintf( __( 'View %s', 'wapu-core' ), $this->single_name ),
				'search_items'       => sprintf( __( 'Search %s', 'wapu-core' ), $this->plural_name ),
				'not_found'          => sprintf( __( 'No %s found', 'wapu-core' ), $this->plural_name ),
				'not_found_in_trash' => sprintf( __( 'No %s found in trash', 'wapu-core' ), $this->plural_name ),
			);

			$args = array_merge( array( 'labels' => $labels ), $this->args );
			$args = apply_filters( 'wapu-core/post-type/' . $this->slug . '/args', $args );

			$args['rewrite'] = array(
				'slug' => $this->get_post_rewrite_slug(),
			);

			register_post_type( $this->slug, $args );
		}

		/**
		 * Register taxonomy for custom post type.
		 *
		 * @since 1.0.0
		 * @link  https://codex.wordpress.org/Function_Reference/register_taxonomy
		 */
		public function register_tax() {

			foreach ( $this->taxonomies as $tax => $data ) {

				$data = wp_parse_args( $data, array(
					'single_name' => null,
					'plural_name' => null,
					'args'        => array(),
				) );

				$labels = array(
					'name'                       => sprintf( '%s %s', $this->plural_name, $data['plural_name'] ),
					'singular_name'              => sprintf( __( 'Edit %s', 'wapu-core' ), $data['single_name'] ),
					'search_items'               => sprintf( __( 'Search %s', 'wapu-core' ), $data['plural_name'] ),
					'popular_items'              => sprintf( __( 'Popular %s', 'wapu-core' ), $data['plural_name'] ),
					'all_items'                  => sprintf( __( 'All %s', 'wapu-core' ), $data['plural_name'] ),
					'parent_item'                => null,
					'parent_item_colon'          => null,
					'edit_item'                  => sprintf( __( 'Edit %s', 'wapu-core' ), $data['single_name'] ),
					'update_item'                => sprintf( __( 'Update %s', 'wapu-core' ), $data['single_name'] ),
					'add_new_item'               => sprintf( __( 'Add New %s', 'wapu-core' ), $data['single_name'] ),
					'new_item_name'              => sprintf( __( 'New %s Name', 'wapu-core' ), $data['single_name'] ),
					'separate_items_with_commas' => sprintf( __( 'Separate %s with commas', 'wapu-core' ), $data['plural_name'] ),
					'add_or_remove_items'        => sprintf( __( 'Add or remove %s', 'wapu-core' ), $data['plural_name'] ),
					'choose_from_most_used'      => sprintf( __( 'Choose from the most used %s', 'wapu-core' ), $data['plural_name'] ),
					'not_found'                  => sprintf( __( 'No %s found.', 'wapu-core' ), $data['plural_name'] ),
					'menu_name'                  => $data['plural_name'],
				);

				$args = array_merge( array( 'labels' => $labels ), $data['args'] );
				$args['rewrite'] = array(
					'slug' => $this->get_tax_rewrite_slug( $tax ),
				);

				register_taxonomy( $this->tax( $tax ), $this->slug, $args );

			}

		}

		/**
		 * Returns prefixed taxonomy slug.
		 *
		 * @param  string $tax Tax slug.
		 * @return string
		 */
		public function tax( $tax ) {
			return $this->slug . '_' . $tax;
		}

		/**
		 * Returns post rewrite slug.
		 *
		 * @return string
		 */
		public function get_post_rewrite_slug() {
			if ( true === $this->rewrite_options ) {
				return get_option( $this->slug . $this->rewrite_suffix, $this->slug );
			} else {
				return $this->slug;
			}
		}

		/**
		 * Returns post rewrite slug.
		 *
		 * @return string
		 */
		public function get_tax_rewrite_slug( $tax ) {

			if ( ! array_key_exists( $tax, $this->taxonomies ) ) {
				return null;
			}

			$default = $this->slug . '-' . $tax;

			if ( true === $this->rewrite_options ) {
				return get_option( $this->tax( $tax ) . $this->rewrite_suffix, $default );
			} else {
				return $default;
			}
		}

		/**
		 * Pass services taxonomy into search plugin
		 *
		 * @param  array $taxonomies Supported taxonomies.
		 * @return array
		 */
		public function search_tax( $taxonomies ) {

			foreach ( $this->taxonomies as $tax => $data ) {
				$taxonomies[] = $this->tax( $tax );
			}

			return $taxonomies;
		}

	}
}
