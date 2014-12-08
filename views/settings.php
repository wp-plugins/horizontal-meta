

<div class='wrap hm-horizontal-meta'>

	<?php $this->view("tabs", array("action" => "index")); ?>

	<a id="errors-beginning"></a>
	<div id="wrapper-errors">
		<?php $this->show_admin_notice(); ?>
	</div>

	<div class="introduction">
		<p><?php _e("The table you see below contains a list of meta keys that are being 'watched' by Horizontal Meta.", 'horizontal-meta'); ?></p>
	</div>

	<div class="wrapper_mapping_table">
		<form action="<?php print admin_url('admin.php'); ?>" method="post" id="frm-remove-mappings">
			<?php $list_table->display(); ?>
			<input type="button" class="button button-primary button-large" name="remove_mappings" value="<?php print __("Remove Selected Mappings",'horizontal-meta'); ?>" />
			<input type="hidden" name="remove_mappings_nonce" value="<?php print $nonce; ?>" />
			<input type="hidden" name="action" value="hm_process_delete_mappings" />
		</form>
	</div>

</div>

<script type="text/javascript">
	var hm_params = <?php print $params ?>;
</script>