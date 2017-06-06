<?php
/**
 * Default banner template
 */
?>
<div class="blurb <?php echo $class; ?>" <?php echo $id; ?>>
	<?php echo $img_tag; ?>
	<?php $this->html( $font_icon, '<i class="nc-icon-outline %s"></i>' ); ?>
	<?php $this->html( $title, '<h4 class="blurb__title">%s</h4>' ); ?>
	<?php $this->html( $text, '<div class="blurb__text">%s</div>' ); ?>
	<?php $this->html( $link, '<a href="%1$s" class="blurb__link"%3$s>%2$s</a>', array( $link_text, $target ) ); ?>
</div>