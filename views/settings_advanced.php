<form action="<?php print admin_url('admin.php'); ?>" method="post">


	<div class='wrap hm-horizontal-meta'>

		<?php $this->view("tabs", array("action" => "advanced")); ?>

		<?php $this->show_admin_notice(); ?>

		<div class="wrapper-hm_settings_advanced">

			<h3><?php _e("Post Query Settings", 'horizontal-meta'); ?></h3>

			<table class="form-table">
				<tbody>
					<tr valign="top" class="">
						<th scope="row" class="titledesc"><?php _e("Rewrite Queries", 'horizontal-meta'); ?></th>
						<td class="forminp forminp-checkbox">
							<fieldset>
								<legend class="screen-reader-text"><span><?php _e("Rewrite Queries", 'horizontal-meta'); ?></span></legend>

								<label for="post_rewrite_queries">
									<select name="post_rewrite_queries" id="post_rewrite_queries">
										<option
											value="1" <?php print ($settings["post_rewrite_queries"] == "1" ? "selected" : ""); ?> ><?php _e("Yes", 'horizontal-meta'); ?></option>
										<option
											value="0" <?php print ($settings["post_rewrite_queries"] == "0" ? "selected" : ""); ?> ><?php _e("No", 'horizontal-meta'); ?></option>
									</select>

								<p class="description"><?php _e("Hook into WP_Query and rewrite meta queries? Disabling this option will turn Horizontal Meta off for post queries.", 'horizontal-meta'); ?> <?php _e("NOTE: Horizontal Meta currently does not support advanced querying features that are available in WordPress 4.1 and later. This may become available sometime in the future.", 'horizontal-meta'); ?></p>
							</fieldset>
						</td>
					</tr>
					<tr valign="top" class="">
						<th scope="row" class="titledesc"><?php _e("Intercept Meta Keys", 'horizontal-meta'); ?></th>
						<td class="forminp forminp-checkbox">
							<fieldset>
								<legend class="screen-reader-text"><span><?php _e("Intercept Meta Keys", 'horizontal-meta'); ?></span></legend>

								<label for="post_intercept_keys">
									<select name="post_intercept_keys" id="post_intercept_keys">
										<option
											value="1" <?php print ($settings["post_intercept_keys"] == "1" ? "selected" : ""); ?> ><?php _e("Yes", 'horizontal-meta'); ?></option>
										<option
											value="0" <?php print ($settings["post_intercept_keys"] == "0" ? "selected" : ""); ?> ><?php _e("No", 'horizontal-meta'); ?></option>
									</select>

									<p class="description"><?php _e("Allow Horizontal Meta to redirect meta key queries to itself. When this option is disabled, you must use the _horzm_ prefix to redirect a meta key query to Horziontal Meta.", 'horizontal-meta'); ?></p>
							</fieldset>
						</td>
					</tr>
				</tbody>
			</table>

			<?php
			if ((hm_is_multisite() && get_current_blog_id() == 1) || !hm_is_multisite()) {
				?>

				<h3><?php _e("User Query Settings", 'horizontal-meta'); ?></h3>

				<table class="form-table">
					<tbody>
					<tr valign="top" class="">
						<th scope="row" class="titledesc"><?php _e("Rewrite Queries", 'horizontal-meta'); ?></th>
						<td class="forminp forminp-checkbox">
							<fieldset>
								<legend class="screen-reader-text"><span><?php _e("Rewrite Queries", 'horizontal-meta'); ?></span></legend>

								<label for="user_rewrite_queries">
									<select name="user_rewrite_queries" id="user_rewrite_queries">
										<option
											value="1" <?php print ($settings["user_rewrite_queries"] == "1" ? "selected" : ""); ?> ><?php _e("Yes", 'horizontal-meta'); ?></option>
										<option
											value="0" <?php print ($settings["user_rewrite_queries"] == "0" ? "selected" : ""); ?> ><?php _e("No", 'horizontal-meta'); ?></option>
									</select>

									<p class="description"><?php _e("Hook into WP_User_Query and rewrite meta queries? Disabling this option will turn Horizontal Meta off for user queries.", 'horizontal-meta'); ?> <?php _e("NOTE: Horizontal Meta currently does not support advanced querying features that are available in WordPress 4.1 and later. This may become available sometime in the future.", 'horizontal-meta'); ?></p>
							</fieldset>
						</td>
					</tr>
					<tr valign="top" class="">
						<th scope="row" class="titledesc"><?php _e("Intercept Meta Keys", 'horizontal-meta'); ?></th>
						<td class="forminp forminp-checkbox">
							<fieldset>
								<legend class="screen-reader-text"><span><?php _e("Intercept Meta Keys", 'horizontal-meta'); ?></span></legend>

								<label for="user_intercept_keys">
									<select name="user_intercept_keys" id="user_intercept_keys">
										<option
											value="1" <?php print ($settings["user_intercept_keys"] == "1" ? "selected" : ""); ?> ><?php _e("Yes", 'horizontal-meta'); ?></option>
										<option
											value="0" <?php print ($settings["user_intercept_keys"] == "0" ? "selected" : ""); ?> ><?php _e("No", 'horizontal-meta'); ?></option>
									</select>

									<p class="description"><?php _e("Allow Horizontal Meta to redirect meta key queries to itself. When this option is disabled, you must use the _horzm_ prefix to redirect a meta key query to Horziontal Meta.", 'horizontal-meta'); ?></p>
							</fieldset>
						</td>
					</tr>
					</tbody>
				</table>
				<div class="clear"></div>
				<?php
			}
			?>

			<input type="hidden" name="post_override_suppress_filters" value="0"/>
			<input type="hidden" name="user_override_suppress_filters" value="0"/>
			<input type="hidden" name="post_override_disable_other_filters" value="0"/>
		</div>
	</div>

	<input name="update_settings" type="submit" class="button button-primary button-large" id="publish" accesskey="p"
	       value="<?php _e("Save Settings", 'horizontal-meta'); ?>" style="float:left;clear:both;">

	<input type="hidden" name="action" value="hm_advanced_save"/>

</form>
