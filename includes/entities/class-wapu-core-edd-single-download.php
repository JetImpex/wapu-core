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

if ( ! class_exists( 'Wapu_Core_EDD_Single_Download' ) ) {

	/**
	 * Define Wapu_Core_EDD_Single_Download class
	 */
	class Wapu_Core_EDD_Single_Download {

		private $subpage    = '';
		private $taxes      = array();
		public  $meta_cache = null;

		/**
		 * Constructor for the class
		 */
		public function __construct() {

			remove_filter( 'the_content', 'edd_after_download_content' );
			$this->register_rewrite_rules();

			add_filter( 'query_vars', array( $this, 'subpage_query_vars' ) );
			add_filter( 'the_title', array( $this, 'page_title' ) );

			add_filter( 'edd_add_schema_microdata', '__return_false' );

			add_action( 'wp_head', array( $this, 'set_single_post_data' ), 0 );
			add_action( 'wp_enqueue_scripts', array( $this, 'dequeue_wishlist_css' ), 101 );

		}

		/**
		 * Dequeue wishlist CSS
		 * @return [type] [description]
		 */
		public function dequeue_wishlist_css() {
			wp_dequeue_style( 'edd-wl-styles' );
		}

		public function set_single_post_data() {

			if ( ! is_singular( 'download' ) ) {
				return;
			}

			add_filter( 'wapu_core/localize_data', array( $this, 'add_localized_data' ) );
			$this->meta_cache = new Wapu_Core_Meta_Cache( get_the_ID() );

		}

		public function add_localized_data( $data ) {

			$data['addToCart'] = array(
				'processing'   => 'Processing...',
				'added'        => 'Added To Cart',
				'checkoutLink' => '<div class="to-checkout"><a href="' . edd_get_checkout_uri() . '">Go to Checkout</a></div>',
			);

			return $data;
		}

		/**
		 * Change page title
		 *
		 * @param  [type] $title [description]
		 * @return [type]        [description]
		 */
		public function page_title( $title ) {

			if ( ! is_singular( 'download' ) ) {
				return $title;
			}

			$subpage = $this->get_subpage();

			if ( ! $subpage ) {
				return $title;
			}

			switch ( $subpage ) {
				case 'reviews':
					$title = 'Reviews for ' . $title;
					break;
			}

			return $title;

		}

		public function subpage_query_vars( $vars ) {
			$vars[] = 'subpage';
			return $vars;
		}

		/**
		 * Register additional rewrite rules
		 *
		 * @return [type] [description]
		 */
		public function register_rewrite_rules() {

			add_rewrite_rule(
				'^downloads/([^/]*)/([^/]*)/?',
				'index.php?download=$matches[1]&subpage=$matches[2]',
				'top'
			);

		}

		/**
		 * Returns single nav links
		 *
		 * @return array
		 */
		public function get_nav_links() {

			return apply_filters( 'wapu-core/single-download/nav', array(
				array(
					'id'    => 'item_details',
					'label' => 'Item Details',
					'type'  => 'sub',
					'page'  => '',
				),
				array(
					'id'    => 'item_reviews',
					'label' => 'Reviews',
					'type'  => 'sub',
					'page'   => 'reviews',
				),
				array(
					'id'     => 'item_support',
					'label'  => 'Support',
					'type'   => 'remote',
					'url'    => '#',
					'target' => '_blank',
				),
			) );

		}

		/**
		 * Get current subpage
		 *
		 * @return [type] [description]
		 */
		public function get_subpage() {

			if ( '' === $this->subpage ) {
				global $wp_query;
				$this->subpage = isset( $wp_query->query_vars['subpage'] ) ? $wp_query->query_vars['subpage'] : false;
			}

			return $this->subpage;

		}

		/**
		 * Returns subpage template
		 *
		 * @return [type] [description]
		 */
		public function get_subpage_template_part() {

			$page = $this->get_subpage();

			if ( ! $page ) {
				$page = 'index';
			}

			$template = wapu_core()->get_template( 'pages/download/' . $page . '.php' );

			if ( $template ) {
				include $template;
			}

		}

		/**
		 * Returns thumbnail image
		 *
		 * @param  string $type [description]
		 * @return [type]       [description]
		 */
		public function get_thumb( $type = 'default', $size = 'full', $atts = array() ) {

			switch ( $type ) {
				case 'large':
					$thumb_id = get_post_meta( get_the_ID(), '_wapu_single_large_thumb', true );
					break;

				default:
					$thumb_id = get_post_thumbnail_id();
					break;
			}

			if ( ! $thumb_id ) {
				return;
			}

			return wp_get_attachment_image( $thumb_id, $size, false, $atts );

		}

		/**
		 * Render actions buttons
		 *
		 * @return void
		 */
		public function actions() {

			$download_id = get_the_ID();

			if ( ! function_exists( 'edd_wl_item_in_wish_list' ) ) {
				$in_wishlist = false;
			} else {
				$in_wishlist = edd_wl_item_in_wish_list( $download_id );
			}

			if ( ! empty( $in_wishlist ) ) {
				$wl_url   = edd_wl_get_wish_list_uri();
				$wl_label = 'In Wishlist';
				$wl_class = 'button button-wish-list';
				$wl_icon  = 'nc-icon-mini ui-1_check';
				$wl_atts  = array();
			} else {
				$wl_url   = '#';
				$wl_label = 'Add to Wishlist';
				$wl_class = 'button button-wish-list edd-wl-open-modal edd-wl-action';
				$wl_icon  = 'nc-icon-mini tech_desktop-screen';
				$wl_atts  = array(
					'data-action'         => 'edd_wl_open_modal',
					'data-download-id'    => $download_id,
					'data-variable-price' => 'no',
					'data-price-mode'     => 'single',
				);
			}

			$actions = array(
				array(
					'url'    => $this->get_live_demo_url(),
					'label'  => 'Live Demo',
					'class'  => 'button button-live-demo',
					'icon'   => 'nc-icon-mini tech_desktop-screen',
					'target' => '_blank',
					'atts'   => array(),
				),
				array(
					'url'    => $wl_url,
					'label'  => $wl_label,
					'class'  => $wl_class,
					'icon'   => $wl_icon,
					'target' => '',
					'atts'   => $wl_atts,
				),
			);

			if ( function_exists( 'edd_wl_add_to_list_shortcode' ) ) {
				wp_enqueue_script( 'edd-wl' );
				wp_enqueue_script( 'edd-wl-modal' );
			}

			foreach ( $actions as $action ) {

				$atts     = ! empty( $action['atts'] ) ? $action['atts'] : array();
				$atts_str = '';

				array_walk( $action['atts'], function( $value, $attr ) use ( &$atts_str ) {
					$atts_str .= sprintf( ' %1$s="%2$s"', $attr, $value );
				} );

				printf(
					'<a href="%1$s" class="%3$s"%5$s%6$s>%4$s<span class="action-inner">%2$s</span></a>',
					$action['url'],
					$action['label'],
					$action['class'],
					( ! empty( $action['icon'] ) ) ? '<i class="' . $action['icon'] . '"></i>' : '',
					( ! empty( $action['target'] ) ) ? ' target="' . $action['target'] . '"' : '',
					$atts_str
				);
			}

		}

		/**
		 * Price label
		 *
		 * @return [type] [description]
		 */
		public function price_label() {

			$label   = edd_get_option( 'wapu_price_title', 'Regular Licesne' );
			$tooltip = edd_get_option( 'wapu_price_tooltip' );

			if ( ! empty( $tooltip ) ) {
				$tooltip_html = sprintf(
					'<div class="download-tooltip">
						<i class="nc-icon-mini ui-e_round-e-info download-tooltip__icon"></i>
						<div class="download-tooltip__text">%s</div>
					</div>',
					$tooltip
				);
			} else {
				$tooltip_html = '';
			}

			printf(
				'<div class="download-single-price__label">
					<div class="download-single-price__label-text">%1$s</div>%2$s
				</div>',
				$label, $tooltip_html
			);

		}

		/**
		 * Price features list
		 *
		 * @param  string $item_class [description]
		 * @return [type]             [description]
		 */
		public function price_features( $item_class = '' ) {

			$features = edd_get_option( 'wapu_price_features' );

			if ( ! $features ) {
				return;
			}

			$features = explode( '|', $features );
			array_walk( $features, 'trim' );

			foreach ( $features as $feature ) {
				printf( '<div class="%2$s">%1$s</div>', $feature, $item_class );
			}

		}

		/**
		 * Print sales count
		 *
		 * @return [type] [description]
		 */
		public function sales( $format = '%s', $post_id = null ) {

			if ( ! $post_id ) {
				global $post;
				$post_id = $post->ID;
			}

			$sales      = edd_get_download_sales_stats( $post_id );
			$fake_sales = get_post_meta( $post_id, '_fake_sales', true );

			if ( $fake_sales ) {
				$sales = absint( $sales ) + absint( $fake_sales );
			}

			printf( $format, $sales );
		}

		/**
		 * Price notes
		 *
		 * @return void
		 */
		public function price_notes() {

			$notes = edd_get_option( 'wapu_price_notes' );

			if ( ! $notes ) {
				return;
			}

			printf(
				'<div class="donwload-single-notes">%s</div>',
				wp_kses_post( wpautop( $notes ) )
			);

		}

		/**
		 * Returns live demo URL
		 *
		 * @return string
		 */
		public function get_live_demo_url( $post_id = null ) {
			if ( ! $post_id ) {
				$post_id = get_the_ID();
			}
			return get_post_meta( $post_id, '_wapu_ld_url', true );
		}

		/**
		 * Single nav
		 *
		 * @return void
		 */
		public function render_nav_links() {

			$links     = $this->get_nav_links();
			$premalink = get_permalink();
			$subpage   = $this->get_subpage();

			ob_start();

			foreach ( $links as $link ) {

				if ( 'sub' === $link['type'] ) {
					$url = ! empty( $link['page'] ) ? $premalink . $link['page'] : $premalink;
				} else {
					$url = $link['url'];
				}

				$target = ! empty( $link['target'] ) ? ' target="' . $link['target'] . '"' : '';

				$classes = 'download-nav__item-link';

				if ( 'sub' === $link['type'] && ! $subpage && empty( $link['page'] ) ) {
					$classes .= ' link-active';
				}

				if ( ! empty( $link['page'] ) && $subpage === $link['page'] ) {
					$classes .= ' link-active';
				}

				echo '<div class="download-nav__item">';
					echo '<a href="' . $url . '" class="' . $classes . '"' . $target . '>';
					echo $link['label'];
					echo '</a>';
				echo '</div>';
			}

			$links_html = ob_get_clean();

			printf( '<div class="download-nav">%s</div>', $links_html );

		}

		/**
		 * Print download rating HTML
		 *
		 * @return [type] [description]
		 */
		public function rating() {

			if ( ! function_exists( 'edd_reviews' ) ) {
				return;
			}

			if ( true === $this->meta_cache->get( '_rating_cache' ) ) {
				return;
			}

			$rating      = edd_reviews()->average_rating( false );
			$reviews_num = edd_reviews()->count_reviews();

			ob_start();

			echo '<div class="single-rating">';
				echo '<div class="single-rating__heading">';
					echo 'Item Rating:';
					echo $this->get_rating_stars( $rating );
				echo '</div>';
				echo '<div class="single-rating__info">';
					printf(
						'%1$s average based on %2$s rating%3$s.',
						$rating,
						$reviews_num,
						( 1 == $reviews_num ) ? '' : 's'
					);
				echo '</div>';
				echo '<div class="single-rating__reviews-link">';
					echo '<a href="' . get_permalink() . 'reviews/">More information</a>';
				echo '</div>';
			echo '</div>';

			$content = ob_get_clean();
			$this->meta_cache->update( '_rating_cache', $content );
			echo $content;
		}

		public function add_tax( $tax, $name ) {
			$this->taxes[ $tax ] = $name;
		}

		/**
		 * Return available taxes list
		 *
		 * @return [type] [description]
		 */
		public function get_taxes_list() {
			return $this->taxes;
		}

		/**
		 * Returns tems list
		 *
		 * @return [type] [description]
		 */
		public function terms() {

			if ( true === $this->meta_cache->get( '_terms_cache' ) ) {
				return;
			}

			$taxes = $this->get_taxes_list();

			ob_start();

			foreach ( $taxes as $tax => $name ) {

				$terms = wp_get_post_terms( get_the_ID(), $tax );

				if ( empty( $terms ) ) {
					continue;
				}

				echo '<div class="download-terms-row">';
				printf( '<div class="download-terms-row__title">%s</div>', $name );
				echo '<div class="download-terms-row__items">';
				$items = array();
				foreach ( $terms as $term ) {
					$items[] = $term->name;
				}
				echo implode( ', ', $items );
				echo '</div>';
				echo '</div>';

			}

			$content = ob_get_clean();
			$this->meta_cache->update( '_terms_cache', $content );

			echo $content;

		}

		/**
		 * Retuirns rating stars HTML
		 *
		 * @param  [type] $rating_val [description]
		 * @return [type]             [description]
		 */
		public function get_rating_stars( $value ) {

			$max     = 5;
			$width   = round( ( 100 * $value ) / $max, 3 );
			$stars_f = str_repeat( '<i class="nc-icon-mini ui-2_favourite-31" aria-hidden="true"></i>', 5 );
			$stars_e = str_repeat( '<i class="nc-icon-mini ui-2_favourite-31" aria-hidden="true"></i>', 5 );

			return sprintf(
				'<div class="review__stars"><div class="review__stars-filled" style="width:%1$s%%">%3$s</div><div class="review__stars-empty" style="width:%2$s%%">%4$s</div><div class="review__stars-adjuster">%4$s</div></div>',
				$width,
				100 - $width,
				$stars_f,
				$stars_e
			);

		}

	}

}
