

<div class='wrap hm-horizontal-meta'>

	<?php $this->view("tabs", array("action" => "manage_data")); ?>

	<p><?php print __("Click on any of the meta keys listed below to view a summary of the data stored in the database.", "horizontal-meta"); ?></p>

	<a name="ac-errors"></a>
	<?php $this->show_admin_notice(); ?>

	<div class="wrapper_mapping_manage_data_table">
		<form action="<?php print admin_url('admin.php'); ?>" method="post">
			<?php $list_table->display(); ?>


			<div class="hm-button-container">
				<input name="update_data" type="submit" class="button button-primary button-large" value="<?php _e("Update Data", 'horizontal-meta'); ?>">
			</div>

			<input type="hidden" name="action" value="hm_process_manage_update_data" />
		</form>
	</div>

</div>

<script type="text/javascript">
	var hm_params = <?php print $params ?>;
</script>