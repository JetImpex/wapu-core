<?php
/**
 * Post rating buttons
 *
 * Available variables:
 *
 * $likes:    Total likes count.
 * $dislikes: Total dislikes count.
 * $diff:     Difference between likes and dislikes.
 * $like:     Like action attribute (must be inside opening action tag - button, a, etc).
 * $dislike:  Disike action attribute (must be inside opening action tag - button, a, etc).
 *
 * Note:
 *
 * Left class "wapu-post-rating__counts" on element which you want to count with JS.
 */
?>
<div class="wapu-post-rating">
	<h4 class="wapu-post-rating__title"><?php esc_html_e( 'Was this helpful?', 'wapu-core' ); ?></h4>
	<div class="wapu-post-rating__counts" data-likes="<?php echo $likes; ?>" data-dislikes="<?php echo $dislikes; ?>" data-diff="<?php echo $diff; ?>"></div>
	<div class="wapu-post-rating__buttons">
		<button class="wapu-post-rating__btn btn-like" <?php echo $like ?>>+1</button>
		<button class="wapu-post-rating__btn btn-dislike" <?php echo $dislike ?>>-1</button>
	</div>
</div>