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
