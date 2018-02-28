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

		private $subpage = '';

		/**
		 * Constructor for the class
		 */
		public function __construct() {

			remove_filter( 'the_content', 'edd_after_download_content' );
			$this->register_rewrite_rules();

			add_filter( 'query_vars', array( $this, 'subpage_query_vars' ) );
			add_filter( 'the_title', array( $this, 'page_title' ) );

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

	}

}
