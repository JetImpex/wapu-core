<?php
/**
 * Abstract post type registration class
 */
if ( ! class_exists( 'Wapu_Core_Shortcode' ) ) {

	abstract class Wapu_Core_Shortcode {

		/**
		 * Shortcode tag.
		 *
		 * @var string
		 */
		public $tag = null;

		/**
		 * Information about shortcode
		 *
		 * @var array
		 */
		public $info = array();

		/**
		 * Shortcode arguments
		 *
		 * @var array
		 */
		public $args = array();

		/**
		 * Initalize post type
		 * @return void
		 */
		public function init() {

			add_shortcode( $this->tag, array( $this, 'do_shortcode' ) );

			if ( is_admin() ) {
				$this->register_shortcode_for_builder();
			}

		}

		/**
		 * Register required shortcode for builder
		 *
		 * @return void
		 */
		public function register_shortcode_for_builder() {

			wapu_core()->get_core()->init_module( 'cherry5-insert-shortcode', array() );
			$iconf = '<span class="dashicons dashicons-%s"></span>';

			cherry5_register_shortcode(
				array(
					'title'       => $this->info['group']['name'],
					'icon'        => sprintf( $iconf, $this->info['group']['icon'] ),
					'slug'        => $this->info['group']['slug'],
					'shortcodes'  => array(
						array(
							'title'     => $this->info['shortcode']['name'],
							'icon'      => sprintf( $iconf, $this->info['shortcode']['icon'] ),
							'slug'      => $this->tag,
							'options'   => $this->args,
							'enclosing' => isset( $this->info['shortcode']['enclosing'] ) ? $this->info['shortcode']['enclosing'] : false,
						),
					),
				)
			);

		}

		/**
		 * Print HTML markup if passed text not empty.
		 *
		 * @param  string $text   Passed text.
		 * @param  string $format Required markup.
		 * @param  array  $args   Additional variables to pass into format string.
		 * @param  bool   $echo   Echo or return.
		 * @return string|void
		 */
		public function html( $text = null, $format = '%s', $args = array(), $echo = true ) {

			if ( empty( $text ) ) {
				return '';
			}

			$args   = array_merge( array( $text ), $args );
			$result = vsprintf( $format, $args );

			if ( $echo ) {
				echo $result;
			} else {
				return $result;
			}

		}

		/**
		 * Return defult shortcode attributes
		 *
		 * @return array
		 */
		public function default_atts() {

			$result = array();

			foreach ( $this->args as $attr => $data ) {
				$result[ $attr ] = isset( $data['value'] ) ? $data['value'] : false;
			}

			return $result;
		}

		/**
		 * Shortcode calback
		 *
		 * @return string
		 */
		public function do_shortcode( $atts = array(), $content = null ) {

			$atts = shortcode_atts( $this->default_atts(), $atts, $this->tag );
			$this->css_classes = array();

			if ( null !== $content ) {
				$content = do_shortcode( $content );
			}

			return $this->_shortcode( $atts, $content );
		}

		/**
		 * Get template depends to shortcode slug.
		 *
		 * @param  string $file Filename.
		 * @return string
		 */
		public function get_template( $file ) {
			return wapu_core()->get_template( 'shortcodes/' . $this->tag . '/' . $file );
		}

	}
}
