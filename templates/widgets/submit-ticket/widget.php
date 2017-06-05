<div class="link_box">
	<?php echo $title; ?>
	<div>
		<a href="#" class="btn btn-primary" data-init="popup" data-widget="ticket_<?php echo $this->args['widget_id']; ?>">
			<?php echo $ticket_text; ?>
		</a>
		<a href="#" class="btn btn-primary" data-init="popup" data-widget="chat_<?php echo $this->args['widget_id']; ?>"><?php
			echo $chat_text;
		?></a>
	</div>
</div>