<?php
/**
 * Search results template
 */
?>
<?php $this->html( $title, '<h6 class="docs-search-results__title">%s</h6>' ); ?>
<div class="docs-search-results__link">

	<div class="docs-search-results__link-text">
		<a href="<?php echo $result; ?>" target="_blank"><?php echo $result; ?></a>
	</div>

	<div class="docs-search-results__actions">
		<a class="copy-to-clipboard" href="#" data-default-label="<?php esc_html_e( 'Copy to clipboard', 'wapu-core' ); ?>" data-success-label="<?php esc_html_e( 'Copied!', 'wapu-core' ); ?>" data-copy="<?php echo $result; ?>"><?php
			esc_html_e( 'Copy', 'wapu-core' );
		?></a>
		<span class="docs-search-results__delimiter"></span>
		<a href="<?php echo $result; ?>" target="_blank"><?php
			esc_html_e( 'Open', 'wapu-core' );
		?></a>
	</div>
</div>