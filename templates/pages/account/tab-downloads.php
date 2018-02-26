<?php
/**
 * Downloads tab template
 */

if( ! edd_user_pending_verification() ) {

	edd_get_template_part( 'history', 'downloads' );

} else {

	edd_get_template_part( 'account', 'pending' );

}