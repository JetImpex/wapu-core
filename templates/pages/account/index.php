<?php
/**
 * Main account page template.
 * Should be rewritten in theme
 */

if ( ! is_user_logged_in() ) {
	echo edd_login_form();
	return;
}

?>
<div class="account-page"><?php
	wapu_core()->edd->account->render_account_tabs();
?></div>
