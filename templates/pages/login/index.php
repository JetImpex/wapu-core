<?php
/**
 * Main account page template.
 * Should be rewritten in theme
 */
?>
<div class="login-page"><?php
	if ( ! is_user_logged_in() ) {
		echo edd_login_form();
	}
?></div>
