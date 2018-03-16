<?php
/**
 * Main content template
 */
global $post;

?>
<div class="download-single-preview downloads-content-box">
	<?php echo wapu_core()->edd->single->get_thumb(
		'large',
		'full',
		array( 'alt' => $post->post_title, 'class' => 'download-single-thumb' )
	);
	?>
	<div class="download-single-actions"><?php
		wapu_core()->edd->single->actions();
	?></div>
	<div class="download-single-sharing">
		<?php do_action( 'wapu-core/single-download/sharing' ); ?>
	</div>
</div>
<div class="download-single-description downloads-content-box">
	<div class="downloads-content-box__inner">
		<?php echo $post->post_content; ?>
	</div>
	<div class="content-trigger">
		<div class="content-trigger__expand">
			More
			<i class="nc-icon-mini arrows-1_minimal-down"></i>
		</div>
		<div class="content-trigger__collapse">
			Less
			<i class="nc-icon-mini arrows-1_minimal-up"></i>
		</div>
	</div>
</div>
