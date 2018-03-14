<?php
/**
 * Template part reviews
 */

edd_get_template_part( 'reviews' );

if ( get_option( 'thread_comments' ) ) {
	edd_get_template_part( 'reviews-reply' );
}
