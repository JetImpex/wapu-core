<div class="tuts-list <?php echo $class; ?>" <?php echo $id; ?>><?php
	foreach ( $posts as $post ) {

		$post_type = get_post_type_object( $post->post_type );

		printf(
			'<div class="tuts-list__item"><a href="%1$s">%2$s (%3$s)</a>',
			get_permalink( $post->ID ),
			$post->post_title,
			$post_type->labels->name
		);
	}
?></div>