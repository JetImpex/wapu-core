<?php
/**
 * Manage cart-related actions
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Wapu_Core_EDD_Account' ) ) {

	/**
	 * Define Wapu_Core_EDD_Account class
	 */
	class Wapu_Core_EDD_Account {

		private $tabs = array();

		/**
		 * Constructor for the class
		 */
		public function __construct() {

			add_filter( 'template_include', array( $this, 'template_loader' ) );
			add_action( 'wp_ajax_wapu_core_request_refund', array( $this, 'request_refund' ) );
			add_filter('show_admin_bar', array( $this, 'disable_admin_bar' ) );

			add_action( 'edd_profile_editor_after_password_fields', array( $this, 'subscription_settings' ) );
			add_action( 'edd_user_profile_updated', array( $this, 'update_subscription' ) );

			// Subscribe user on registration by default
			add_action( 'user_register', array( $this, 'subscribe_user_on_register' ) );
			//add_action( 'wp_enqueue_scripts', array( $this, 'subscribe_user_on_register' ) );

			add_action( 'wapu_header_cart', array( $this, 'add_header_cart' ) );

			add_action( 'wp_ajax_wapu_get_cart_content', array( $this, 'get_cart_contents' ) );
			add_action( 'wp_ajax_nopriv_wapu_get_cart_content', array( $this, 'get_cart_contents' ) );

			add_filter( 'edd_cart_item', array( $this, 'cart_elements' ), 10, 2 );

		}

		public function cart_elements( $item, $id ) {

			$item = str_replace( '{item_url}', get_permalink( $id ), $item );
			$item = str_replace( '{item_thumb}', get_the_post_thumbnail( $id, 'search-thumbnail' ), $item );

			return $item;
		}

		/**
		 * Returns cart content on ajax request
		 *
		 * @return [type] [description]
		 */
		public function get_cart_contents() {

			$cart_items = edd_get_cart_contents();

			ob_start();

			?>
			<div class="cart-title">
				Your Cart Contains
				<div class="cart-title__count"><?php echo edd_get_cart_quantity(); ?> Product(s)</div>
			</div>
			<ul class="edd-cart">
			<?php if( $cart_items ) : ?>

				<?php foreach( $cart_items as $key => $item ) : ?>

					<?php echo edd_get_cart_item_template( $key, $item, true ); ?>

				<?php endforeach; ?>

				<?php edd_get_template_part( 'widget', 'cart-checkout' ); ?>

			<?php else : ?>

				<?php edd_get_template_part( 'widget', 'cart-empty' ); ?>

			<?php endif; ?>
			</ul>

			<?php

			$content = ob_get_clean();

			wp_send_json_success( $content );
		}

		/**
		 * Add header cart
		 */
		public function add_header_cart() {
			include wapu_core()->get_template( 'entities/header-cart/cart-link.php' );
			add_action( 'wp_footer', array( $this, 'add_cart_popups' ) );
		}

		/**
		 * Render cart popups
		 */
		public function add_cart_popups() {

			echo '<div class="cart-popups">';
				include wapu_core()->get_template( 'entities/header-cart/account-popup.php' );
				include wapu_core()->get_template( 'entities/header-cart/cart-popup.php' );
				echo '<div class="cart-overlay"></div>';
			echo '</div>';
		}

		/**
		 * Disable admin bar for non-admins
		 *
		 * @return [type] [description]
		 */
		public function disable_admin_bar() {

			if ( current_user_can( 'manage_options' ) ) {
				return true;
			} else {
				return false;
			}

		}

		/**
		 * Retrieve an account page URL
		 *
		 * @return [type] [description]
		 */
		public function get_account_page_url() {

			global $edd_options;

			$page_id = isset( $edd_options['wapu_core_account_page'] ) ? $edd_options['wapu_core_account_page'] : edd_get_option( 'wapu_core_account_page' );

			if ( ! $page_id ) {
				return '';
			}

			return get_page_link( $page_id );

		}

		/**
		 * Render account menu list
		 *
		 * @return [type] [description]
		 */
		public function render_account_menu() {

			$account_page = $this->get_account_page_url();

			$menu = array(
				array(
					'label' => 'Account',
					'url'   => $account_page,
				),
				array(
					'label' => 'Orders',
					'url'   => $account_page . '#purchase-details',
				),
				array(
					'label' => 'Downloads',
					'url'   => $account_page . '#downloads',
				),
			);

			if ( function_exists( 'edd_wl_get_wish_list_uri' ) ) {
				$menu[] = array(
					'label' => 'Wish List',
					'url'   => edd_wl_get_wish_list_uri(),
				);
			}

			$menu_html = '';

			foreach ( $menu as $menu_item ) {
				$menu_html .= sprintf(
					'<a class="account-menu__item" href="%1$s">%2$s</a>',
					$menu_item['url'], $menu_item['label']
				);
			}

			printf( '<div class="account-menu">%s</div>', $menu_html );

		}

		/**
		 * Update user subscription status
		 *
		 * @param  [type] $user_id [description]
		 * @return [type]          [description]
		 */
		public function update_subscription( $user_id ) {

			$current_status = get_user_meta( $user_id, '_wapu_core_subscription', true );
			$new_status     = ! empty( $_POST['_wapu_core_subscription'] ) ? 'yes' : '';

			if ( $new_status === $current_status ) {
				return;
			}

			$api_key = edd_get_option( 'wapu_core_mc_api_key' );
			$list_id = edd_get_option( 'wapu_core_mc_list_id' );

			if ( ! $api_key || ! $list_id ) {
				return;
			}

			$mailchimp = new Wapu_Core_MailChimp( $api_key, $list_id );
			$user_data = get_userdata( $user_id );
			$name      = ! empty( $user_data->first_name ) ? $user_data->first_name : $user_data->user_email;
			$email     = $user_data->user_email;

			if ( 'yes' === $new_status ) {

				$subscribe = $mailchimp->change_status( $email, 'subscribed' );

				if ( isset( $subscribe['status'] ) && 'subscribed' === $subscribe['status'] ) {
					update_user_meta( $user_id, '_wapu_core_subscription', 'yes' );
				} elseif ( isset( $subscribe['status'] ) && 404 === $subscribe['status'] ) {

					$subscribe = $mailchimp->subscribe( array(
						'email' => $email,
						'fname' => $name,
					) );

					if ( isset( $subscribe['status'] ) && 'subscribed' === $subscribe['status'] ) {
						update_user_meta( $user_id, '_wapu_core_subscription', 'yes' );
					}
				}

			} else {
				$unsubscribe = $mailchimp->change_status( $email, 'unsubscribed' );
				delete_user_meta( $user_id, '_wapu_core_subscription' );
			}

		}

		/**
		 * Subscribtion settings
		 *
		 * @return void
		 */
		public function subscription_settings() {

			$user_id = get_current_user_id();
			$checked = get_user_meta( $user_id, '_wapu_core_subscription', true );

			?>
			<fieldset id="edd_profile_subscription">
				<legend id="edd_profile_subscription_label"><?php
					_e( 'Subscription', 'crocoblock-core' );
				?></legend>
				<label>
					<input type="checkbox" name="_wapu_core_subscription" value="yes" <?php checked( $checked, 'yes', true ); ?>>
					<?php _e( 'I agree for subscribtion to know about latest news', 'wapu-core' ); ?>
				</label>
			</fieldset>
			<?php
		}

		/**
		 * Subscribe user
		 *
		 * @return [type] [description]
		 */
		public function subscribe_user_on_register( $user_id = null ) {

			//$user_id = get_current_user_id();

			$user_data = get_userdata( $user_id );

			$name  = ! empty( $user_data->first_name ) ? $user_data->first_name : $user_data->user_email;
			$email = $user_data->user_email;

			$api_key = edd_get_option( 'croco_mc_api_key' );
			$list_id = edd_get_option( 'croco_mc_list_id' );

			if ( ! $api_key || ! $list_id ) {
				return;
			}

			$mailchimp = new Croco_MailChimp( $api_key, $list_id );

			$subscribe = $mailchimp->subscribe( array(
				'email' => $email,
				'fname' => $name,
			) );

			if ( isset( $subscribe['status'] ) && 'subscribed' === $subscribe['status'] ) {
				update_user_meta( $user_id, '_wapu_core_subscription', 'yes' );
			}

		}

		/**
		 * Process refund request
		 *
		 * @return void
		 */
		public function request_refund() {

			$user = wp_get_current_user();

			if ( ! $user ) {
				wp_send_json_error( array(
					'message' => __( 'You are not logged in', 'wapu-core' ),
				) );
			}

			wp_send_json_success( array(
				'message' => edd_get_option( 'wapu_core_refund_message' ),
			) );

		}

		/**
		 * Register account tabs
		 * @return [type] [description]
		 */
		public function register_default_tabs() {

			$refund_period = edd_get_option( 'wapu_core_moneyback_period', 14 );

			$this->tabs = array(
				'settings' => array(
					'title'    => esc_html__( 'Settings', 'wapu-core' ),
					'template' => wapu_core()->get_template( 'pages/account/tab-settings.php' ),
				),
				'purchase-details' => array(
					'title'    => esc_html__( 'Purchases', 'wapu-core' ),
					'template' => wapu_core()->get_template( 'pages/account/tab-purchase.php' ),
				),
				'downloads' => array(
					'title'     => esc_html__( 'Downloads', 'wapu-core' ),
					'template'  => wapu_core()->get_template( 'pages/account/tab-downloads.php' ),
				),
				'license-keys' => array(
					'title'    => esc_html__( 'License Keys', 'wapu-core' ),
					'template' => wapu_core()->get_template( 'pages/account/tab-license-keys.php' ),
				),
				'support' => array(
					'title'     => esc_html__( 'Support', 'wapu-core' ),
					'template'  => wapu_core()->get_template( 'pages/account/tab-support.php' ),
				),
			);

		}

		/**
		 * Return account tabs list
		 *
		 * @return void
		 */
		public function get_account_tabs() {
			return apply_filters( 'wapu-core/edd-account/tabs', $this->tabs );
		}

		/**
		 * Load an account template.
		 *
		 * @param string $template Template to load.
		 * @return string
		 */
		public function template_loader( $template ) {

			$account_page = edd_get_option( 'wapu_core_account_page' );

			if ( $account_page && is_page( $account_page ) ) {
				$template = wapu_core()->get_template( 'pages/account/index.php' );
			}

			return $template;
		}

		/**
		 * Render registered account tabs
		 *
		 * @return void
		 */
		public function render_account_tabs() {

			$this->register_default_tabs();

			$tabs_nav     = '';
			$tabs_content = '';

			wp_enqueue_script( 'wapu-core' );

			foreach ( $this->get_account_tabs() as $tab_id => $tab ) {

				if ( ! empty( $tab['condition'] ) && false === call_user_func( $tab['condition'] ) ) {
					continue;
				}

				ob_start();
				include wapu_core()->get_template( 'pages/account/tab-menu-item.php' );
				$tabs_nav .= ob_get_clean();

				ob_start();
				include $tab['template'];
				$tabs_content .= sprintf(
					'<div class="account-tabs__content-item" data-tab="%1$s" id="%1$s">%2$s</div>',
					$tab_id,
					ob_get_clean()
				);
			}

			include wapu_core()->get_template( 'pages/account/tabs.php' );

		}

	}

}
