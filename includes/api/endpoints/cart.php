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

if ( ! class_exists( 'Wapu_Core_API_Cart' ) ) {

	/**
	 * Define Wapu_Core_API_Cart class
	 */
	class Wapu_Core_API_Cart extends Wapu_Core_Base_Endpoint {

		public function is_ajax() {
			return true;
		}

		public function methods() {
			return 'GET';
		}

		public function route() {
			return 'wapu_cart';
		}

		public function callback( $params ) {

			$count   = 0;
			$content = '';
			$account = '';

			if ( function_exists( 'edd_get_cart_quantity' ) ) {
				$count = edd_get_cart_quantity();
			}

			if ( wapu_core()->edd ) {
				$content = wapu_core()->edd->account->get_cart_contents();
			}

			$current_user = wp_get_current_user();

			ob_start();

			$redirect = get_permalink();

			if ( ! $redirect ) {
				$redirect = home_url( '/' );
			}

			if ( ! is_user_logged_in() ) {
				printf(
					'<div class="cart-title">%s</div>',
					wapu_core()->edd->account->login_link()
				);
			} else {
				printf(
					'<div class="cart-title">Hello, %s <a href="%s">(Log Out)</a></div>',
					wp_logout_url( $redirect )
				);
			}

			wapu_core()->edd->account->render_account_menu();

			$account = ob_get_clean();

			return array(
				'response' => array(
					'count'    => $count,
					'contents' => $content,
					'account'  => $account,
				),
				'code' => 200,
			);

		}

	}

}
