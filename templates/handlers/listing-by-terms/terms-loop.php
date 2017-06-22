<?php
/**
 * Terms loop item
 *
 * Available variables:
 *
 * $term     - current term object (look https://developer.wordpress.org/reference/functions/get_terms/ for structure).
 * $posts    - posts listing for current term.
 * $taxonomy - current taxonomy.
 */
?>
<div class="wapu-term">
	<h4 class="wapu-term__title"><?php echo $term->name; ?></h4>

	<?php echo $posts; ?>

	<div class="wapu-term__actions">
		<a href="<?php echo get_term_link( $term, $taxonomy ); ?>"><?php esc_html_e( 'View All', 'wapu-core' ); ?></a>
	</div>
</div>