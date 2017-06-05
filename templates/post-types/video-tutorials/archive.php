<?php
/**
 * How To archive page
 */

wapu_core_popup()->init();

/**
 * Show page title
 */
do_action( 'wapu_core/' . wapu_core_video_tutorials()->slug . '/archive/page_title' );

while ( have_posts() ) :

	the_post();
	?>
	<div class="wapu-video-item">
		<?php echo wapu_core_video_tutorials()->loop_video(); ?>
		<h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
	</div>
	<?php
endwhile;
