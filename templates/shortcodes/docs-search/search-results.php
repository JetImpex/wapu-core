<?php
/**
 * Search results template
 */
?>
<?php $this->html( $title, '<h4 class="docs-search-results__title">%s</h4>' ); ?>
<?php $this->html( $text, '<div class="docs-search-results__text">%s</div>' ); ?>
<div class="docs-search-results__actions">
	<button class="button btn-primary copy-to-clipboard" data-default-label="<?php esc_html_e( 'Copy to clipboard', 'wapu-core' ); ?>" data-success-label="<?php esc_html_e( 'Copied!', 'wapu-core' ); ?>" data-copy="<?php echo $result; ?>"><?php
		esc_html_e( 'Copy', 'wapu-core' );
	?></button>
	<a class="btn btn-primary" href="<?php echo $result; ?>" target="_blank"><?php
		esc_html_e( 'Open', 'wapu-core' );
	?></a>
</div>