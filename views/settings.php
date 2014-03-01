

<div class='wrap hm-horizontal-meta'>

	<?php $this->view("tabs", array("action" => "index")); ?>

	<?php
	if(empty($extender_activated)) {
		?>
		<div class="need-extender" style="font-weight: bold;">
			<?php _e("Need more data types? Need more mappings? Why not upgrade to the Premium Version? Horizontal Meta Extender gives you unlimited mappings and 6 more data types to choose from! Upgrade here:"); ?> <a href="http://sllwi.re/p/we" target="_blank">http://sllwi.re/p/we</a>
		</div>
		<?php
	}
	?>

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