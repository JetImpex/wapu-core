<?php
/**
 * Posts loop item
 */
?>
<li class="wapu-post">
	<a href="<?php the_permalink(); ?>"><?php
		the_title();
	?></a>
</li>