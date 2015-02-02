
<div class='wrap hm-horizontal-meta'>

	<?php $this->view("tabs", array("action" => "create_mappings")); ?>

	<a name="ac-errors"></a>
	<?php $this->show_admin_notice(); ?>

	<div class="introduction">
		<p><?php _e("Create new Horizontal mappings using the table below.", 'horizontal-meta'); ?></p>
	</div>

	<form action="<?php print admin_url('admin.php'); ?>" method="post" id="mapping_form">
		<div class="wrapper-new-field">
			<div id="wrapper_mapping_errors"></div>

			<table border="0" class="widefat">
				<thead>
				<tr>
					<th colspan="2"><?php _e("Create new mapping.", 'horizontal-meta'); ?></th>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td class="label">
						<label for="new_meta_key"><?php _e("Mapping type", 'horizontal-meta'); ?> <span class="req">*</span></label>
						<p class="description"><?php _e("Select the type of mapping you would like to add.", 'horizontal-meta'); ?></p>
					</td>
					<td>
						<select name="new_type">
							<option value=""><?php _e("Select Type", 'horizontal-meta'); ?></option>
							<?php
							if(!$is_multisite || ($is_multisite && get_current_blog_id() == 1)) {
								?>
								<option value="user" <?php print ($new_type == "user" ? "selected" : ""); ?> ><?php _e("User Meta", 'horizontal-meta'); ?></option>
							<?php
							}
							?>
							<option value="post" <?php print ($new_type == "post" ? "selected" : ""); ?> ><?php _e("Post Meta", 'horizontal-meta'); ?></option>
						</select>
					</td>
				</tr>
				</tbody>
				<tbody id="attributes_body">
				<tr id="user_meta_key_row">
					<td class="label">
						<label for="new_user_meta_key"><?php _e("Meta Key", 'horizontal-meta'); ?> <span class="req">*</span></label>
						<p class="description"><?php _e("Select the meta key you would like to migrate to a horizontal structure. You may also enter your own by selecting 'OTHER' from the list.", 'horizontal-meta'); ?></p>
					</td>
					<td>
						<select name="new_user_meta_key" class="key-select">
							<option value=""><?php _e("Select user meta key", 'horizontal-meta'); ?></option>
							<option value="OTHER" <?php print (!in_array($new_user_meta_key, $user_meta_keys) && !empty($new_user_meta_key) ? "selected" : ""); ?> ><?php _e("OTHER", 'horizontal-meta'); ?></option>
							<?php
							foreach($user_meta_keys as $item) {
								?>
								<option value="<?php print $item; ?>" <?php print ($item==$new_user_meta_key ? "selected" : ""); ?> ><?php print $item; ?></option>
							<?php
							}
							?>
						</select>

						<div class="other-key user_meta_other">
							<?php _e("OTHER:", 'horizontal-meta'); ?> <input type="text" size="10" name="new_user_meta_key_other" value="<?php print (!in_array($new_user_meta_key, $user_meta_keys) && !empty($new_user_meta_key) ? $new_user_meta_key : ""); ?>" />
						</div>
					</td>
				</tr>
				<tr id="post_meta_key_row">
					<td class="label">
						<label for="new_post_meta_key"><?php _e("Meta Key", 'horizontal-meta'); ?> <span class="req">*</span></label>
						<p class="description"><?php _e("Select the meta key you would like to migrate to a horizontal structure. You may also enter your own by selecting 'OTHER' from the list.", 'horizontal-meta'); ?></p>
					</td>
					<td>
						<select name="new_post_meta_key" class="key-select">
							<option value=""><?php _e("Select post meta key", 'horizontal-meta'); ?></option>
							<?php
							foreach($post_meta_keys as $item) {
								?>
								<option value="<?php print $item; ?>" <?php print ($item==$new_post_meta_key ? "selected" : ""); ?> ><?php print $item; ?></option>
							<?php
							}
							?>
							<option value="OTHER" <?php print (!in_array($new_post_meta_key, $post_meta_keys) && !empty($new_post_meta_key) ? "selected" : ""); ?> ><?php _e("OTHER", 'horizontal-meta'); ?></option>
						</select>

						<div class="other-key post_meta_other">
							<?php _e("OTHER:", 'horizontal-meta'); ?> <input type="text" size="10" name="new_post_meta_key_other" value="<?php print (!in_array($new_post_meta_key, $post_meta_keys) && !empty($new_post_meta_key) ? $new_post_meta_key : ""); ?>" />
						</div>
					</td>
				</tr>
				<tr id="data_type_row">
					<td class="label">
						<label for="new_data_type"><?php _e("Data Type", 'horizontal-meta'); ?> <span class="req">*</span></label>
						<p class="description"><?php _e("Select the data type this meta key should be mapped to.", 'horizontal-meta'); ?></p>
					</td>
					<td>
						<select name="new_data_type">
							<option value=""><?php _e("Select data type", 'horizontal-meta'); ?></option>
							<?php
							foreach($data_types as $data_type=>$item) {
								?>
								<option value="<?php print $data_type; ?>" <?php print ($data_type==$new_data_type ? "selected" : ""); ?> ><?php print $item["label"]; ?></option>
							<?php
							}
							?>
						</select>
					</td>
				</tr>
				<tr id="tested_meta">
					<td colspan="2">
						<b><?php _e("Mapping Test Results", 'horizontal-meta'); ?></b><br>
						<?php _e("Please review this list to ensure that the data contained within the meta key is mappable based on your current configuration. If errors are found below you will not be able to map the meta key. <b>REMEMBER,</b> These tests are based on existing data, incompatibilities may still occur with new data that is added.", 'horizontal-meta'); ?>

						<div id="wrapper-testresults">

						</div>

					</td>
				</tr>
				</tbody>
				<tfoot id="map_meta_foot">
				<tr>
					<th colspan="2">
						<div id="wrapper-buttons">
							<input name="test_mapping" type="button" class="button button-primary button-large left" id="publish" accesskey="p" value="<?php _e("Test Mapping", 'horizontal-meta'); ?>" style="display: inline-block;">
							<span id="wait"></span>
							<input name="create_mapping" type="button" class="button button-primary button-large right" id="publish" accesskey="p" value="<?php _e("Create Mapping", 'horizontal-meta'); ?>" style="display: inline-block;">
						</div>
					</th>
				</tr>
				</tfoot>
			</table>
		</div>

		<input type="hidden" name="action" value="hm_process_create_mapping" />
		<input type="hidden" name="mapping_nonce" id="mapping_nonce" value="" />
		<input type="hidden" name="noheader" value="true" />
	</form>

</div>

<script type="text/javascript">
	var hm_params = <?php print $params ?>;
</script>