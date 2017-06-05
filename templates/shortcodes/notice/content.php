<?php
/**
 * Notice shortcode template
 */
?>
<div class="notice type-<?php echo $type; ?> <?php echo $class; ?>" <?php echo $id; ?>><?php
	echo wp_kses_post( $content );
?></div>
