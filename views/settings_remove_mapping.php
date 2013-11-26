<?php
$form_key = md5($meta_type . "|" . $meta_key . "|" . get_current_blog_id());
?>
<div class="wrapper-meta-data">
	<p><b><?php _e("WARNING!!!", 'horizontal-meta'); ?></b> <?php _e("You are selecting to remove this mapping from the Horizontal meta table.", 'horizontal-meta'); ?> <?php _e("You will no longer be able to access this data from Horizontal Meta.", 'horizontal-meta'); ?></p>
</div>