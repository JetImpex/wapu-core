<?php
/**
 * Cart popup template. Account
 */
?>
<div class="cart-popup" data-popup="account"><?php

	if ( is_user_logged_in() ) {

		$current_user = wp_get_current_user();

		printf(
			'<div class="cart-title">Hello, %s <a href="%s">(Log Out)</a></div>',
			$current_user->display_name,
			wp_logout_url( get_permalink() )
		);
		wapu_core()->edd->account->render_account_menu();
	} else {
		echo edd_login_form();
	}

?>
<a href="#" class="cart-close"><i class="nc-icon-mini ui-1_simple-remove"></i></a>
</div>