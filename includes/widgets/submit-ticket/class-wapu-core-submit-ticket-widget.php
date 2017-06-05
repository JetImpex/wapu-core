<?php
/**
 * Widget related posts
 */

class Wapu_Core_Submit_Ticket_Widget extends Cherry_Abstract_Widget {

	/**
	 * Popup templates holder
	 *
	 * @var array
	 */
	public static $templates = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->widget_cssclass    = 'widget-submit-ticket';
		$this->widget_description = esc_html__( 'Show submit ticket buttons.', 'wapu-core' );
		$this->widget_id          = 'wapu_submit_ticket';
		$this->widget_name        = esc_html__( 'Wapu Submit Ticket', 'wapu-core' );
		$this->settings           = array(
			'title'  => array(
				'type'  => 'text',
				'value' => esc_html__( 'Still Have a Question?', 'wapu-core' ),
				'label' => esc_html__( 'Title:', 'wapu-core' ),
			),
			'ticket_text' => array(
				'type'  => 'text',
				'value' => esc_html__( 'Submit a Ticket', 'wapu-core' ),
				'label' => esc_html__( '"Submit Ticket" Button Text', 'wapu-core' ),
			),
			'chat_text' => array(
				'type'  => 'text',
				'value' => esc_html__( 'Chat', 'wapu-core' ),
				'label' => esc_html__( '"Chat" Button Text', 'wapu-core' ),
			),
			'popup_title' => array(
				'type'  => 'text',
				'value' => esc_html__( 'Want to Submit a Ticket?', 'wapu-core' ),
				'label' => esc_html__( 'Submit Ticket PopUp Title', 'wapu-core' ),
			),
			'popup_msg' => array(
				'type'  => 'textarea',
				'value' => esc_html__( 'Please, choose the marketplace where you\'ve purchased a template to enter the ticket system and submit a ticket!', 'wapu-core' ),
				'label' => esc_html__( 'Submit Ticket PopUp Message', 'wapu-core' ),
			),
			'popup_links' => array(
				'type'         => 'repeater',
				'title'        => esc_html__( 'Ticket Marketplaces', 'wapu-core' ),
				'add_label'    => esc_html__( 'Add Marketplace link', 'wapu-core' ),
				'title_field'  => 'link',
				'hidden_input' => true,
				'fields'       => array(
					'label' => array(
						'type'        => 'text',
						'id'          => 'label',
						'name'        => 'label',
						'placeholder' => esc_html__( 'Marketplace Label', 'wapu-core' ),
						'label'       => esc_html__( 'Label', 'wapu-core' ),
					),
					'link' => array(
						'type'        => 'text',
						'id'          => 'link',
						'name'        => 'link',
						'placeholder' => esc_html__( 'Marketplace Link', 'wapu-core' ),
						'label'       => esc_html__( 'Link', 'wapu-core' ),
					),
					'logo' => array(
						'type'               => 'media',
						'id'                 => 'logo',
						'name'               => 'logo',
						'label'              => esc_html__( 'Logo', 'wapu-core' ),
						'multi_upload'       => true,
						'library_type'       => 'image',
						'upload_button_text' => esc_html__( 'Select Image', 'wapu-core' ),
					),
				),
			),
			'chat_title' => array(
				'type'  => 'text',
				'value' => esc_html__( 'Want to Start a Chat?', 'wapu-core' ),
				'label' => esc_html__( 'Chat PopUp Title', 'wapu-core' ),
			),
			'chat_msg' => array(
				'type'  => 'textarea',
				'value' => esc_html__( 'Please, choose the marketplace where you\'ve purchased a template to enter the chat!', 'wapu-core' ),
				'label' => esc_html__( 'Chat PopUp Message', 'wapu-core' ),
			),
			'chat_links' => array(
				'type'         => 'repeater',
				'title'        => esc_html__( 'Chat Marketplaces', 'wapu-core' ),
				'add_label'    => esc_html__( 'Add Marketplace link', 'wapu-core' ),
				'title_field'  => 'link',
				'hidden_input' => true,
				'fields'       => array(
					'label' => array(
						'type'        => 'text',
						'id'          => 'label',
						'name'        => 'label',
						'placeholder' => esc_html__( 'Marketplace Label', 'wapu-core' ),
						'label'       => esc_html__( 'Label', 'wapu-core' ),
					),
					'link' => array(
						'type'        => 'text',
						'id'          => 'link',
						'name'        => 'link',
						'placeholder' => esc_html__( 'Marketplace Link', 'wapu-core' ),
						'label'       => esc_html__( 'Link', 'wapu-core' ),
					),
					'logo' => array(
						'type'               => 'media',
						'id'                 => 'logo',
						'name'               => 'logo',
						'label'              => esc_html__( 'Logo', 'wapu-core' ),
						'multi_upload'       => true,
						'library_type'       => 'image',
						'upload_button_text' => esc_html__( 'Select Image', 'wapu-core' ),
					),
				),
			),
		);

		add_action( 'wp_footer', array( $this, 'print_popup_templates' ) );

		parent::__construct();
	}

	/**
	 * Output the html at the start of a widget
	 *
	 * @since  1.0.0
	 * @param  array $args     widget arguments.
	 * @param  array $instance widget instance.
	 * @return void
	 */
	public function widget_start( $args, $instance ) {
		echo $args['before_widget'];
	}

	/**
	 * Widget function.
	 *
	 * @see   WP_Widget
	 * @since 1.0.1
	 * @param array $args     Widget arguments.
	 * @param array $instance Instance.
	 */
	public function widget( $args, $instance ) {

		$this->setup_widget_data( $args, $instance );
		$this->widget_start( $args, $instance );

		$title = apply_filters(
			'widget_title',
			empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base
		);

		if ( $title ) {
			$title = '<h4>' . $title . '</h4>';
		}

		$ticket_text = ! empty( $instance['ticket_text'] )
			? $instance['ticket_text']
			: $this->settings['ticket_text']['value'];

		$chat_text = ! empty( $instance['chat_text'] )
			? $instance['chat_text']
			: $this->settings['chat_text']['value'];

		$template = wapu_core()->get_template( 'widgets/submit-ticket/widget.php' );

		include $template;

		$this->set_widget_templates();

		wapu_core_popup()->init();

		$this->widget_end( $args );
		$this->reset_widget_data();

	}

	/**
	 * Store template for current widget
	 */
	public function set_widget_templates() {

		$popup = wapu_core()->get_template( 'widgets/submit-ticket/popup.php' );

		$title   = ! empty( $this->instance['popup_title'] ) ? $this->instance['popup_title'] : '';
		$content = ! empty( $this->instance['popup_msg'] ) ? $this->instance['popup_msg'] : '';
		$links   = ! empty( $this->instance['popup_links'] ) ? $this->instance['popup_links'] : array();

		ob_start();
		include $popup;
		$this->store_template( 'ticket' );

		$title   = ! empty( $this->instance['chat_title'] ) ? $this->instance['chat_title'] : '';
		$content = ! empty( $this->instance['chat_msg'] ) ? $this->instance['chat_msg'] : '';
		$links   = ! empty( $this->instance['chat_links'] ) ? $this->instance['chat_links'] : array();

		ob_start();
		include $popup;
		$this->store_template( 'chat' );

	}

	/**
	 * Store template by prefix
	 *
	 * @return [type] [description]
	 */
	public function store_template( $prefix = 'ticket' ) {
		self::$templates[ $prefix . '_' . $this->args['widget_id'] ] = json_encode(
			str_replace( array( "\r\n", "\r" ), '', ob_get_clean() )
		);
	}

	/**
	 * Prinat available popup templates
	 *
	 * @return void
	 */
	public function print_popup_templates() {

		if ( empty( self::$templates ) ) {
			return;
		}

		$templates_list = array();

		foreach ( self::$templates as $template_id => $template ) {
			$templates_list[] = sprintf( '\'%1$s\' : %2$s', $template_id, $template );
		}

		?>
		<script type="text/javascript">
			var wapuCorePopupTemplates = {
			<?php
				echo implode( ',', $templates_list );
			?>
			}
		</script>
		<?php
	}

}

add_action( 'widgets_init', 'wapu_core_register_submit_ticket_widget' );

/**
 * Register about widget.
 */
function wapu_core_register_submit_ticket_widget() {
	register_widget( 'Wapu_Core_Submit_Ticket_Widget' );
}
