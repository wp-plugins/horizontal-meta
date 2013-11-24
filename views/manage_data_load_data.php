<div class="wrapper-meta-data">

	<p><?php _e("The table below represents data stored in both the WordPress meta table and the Horizontal meta table for this particular meta key.", 'horizontal-meta'); ?></p>

	<table border="0" class="widefat">
		<thead>
			<th><?php print $meta_type_config["obj_id"]; ?></th>
			<th><?php print $meta_type_config["owner_title"]; ?></th>
			<th><?php print $meta_type_config["owner_type"]; ?></th>
			<th><?php _e("WordPress Data", 'horizontal-meta'); ?></th>
			<th><?php _e("Horizontal Data", 'horizontal-meta'); ?></th>
			<th><?php _e("Sync to Horizontal Meta", 'horizontal-meta'); ?></th>
		</thead>
		<tbody>
			<?php

			//$res = array();
			//for($i=0;$i<10000;$i++) {
			//	$c = uniqid(rand(0, 10000000), true);
			//	if(in_array($c, $res)) {
			//		print "duplicate";
			//	}
			//	$res[] = $c;
			//}

			$i = 0;
			$form_key = md5($meta_type . "|" . $meta_key . "|" . get_current_blog_id());
			foreach($meta_data as $obj_id => $row) {
				$item_key = $form_key . $i;


				$wordpress = implode("<br>", $row["wordpress"]);
				$horizontal = implode("<br>", $row["horizontal"]);

				if($row["in_sync"]) {
					$class = "test-passed";
				} else {
					$class = "test-fail";
				}

				?>
				<tr>
					<td class="<?php print $class; ?>"><a href="<?php print str_replace("{edit}", $obj_id, $meta_type_config["edit"]); ?>" target="_blank"><?php print $obj_id; ?></a></td>
					<td class="<?php print $class; ?>"><a href="<?php print str_replace("{edit}", $obj_id, $meta_type_config["edit"]); ?>" target="_blank"><?php print $row["owner"]; ?></a></td>
					<td class="<?php print $class; ?>"><?php print $row["owner_type"]; ?></td>
					<td class="<?php print $class; ?>">
						<?php print $wordpress; ?>

						<input type="hidden" name="hm_manage_data[<?php print $item_key; ?>][meta_key]" value="<?php print $meta_key; ?>" />
						<input type="hidden" name="hm_manage_data[<?php print $item_key; ?>][meta_type]" value="<?php print $meta_type; ?>" />
						<input type="hidden" name="hm_manage_data[<?php print $item_key; ?>][obj_id]" value="<?php print $obj_id; ?>" />
						<input type="hidden" name="hm_manage_data[<?php print $item_key; ?>][meta_value]" value="<?php print $wordpress; ?>" />
					</td>
					<td class="<?php print $class; ?>"><?php print $horizontal; ?></td>
					<td class="<?php print $class; ?>" style="text-align:center;">
						<input type="checkbox" name="hm_manage_data[<?php print $item_key; ?>][sync]" value="on" />
					</td>
				</tr>
				<?php
				$i++;
			}

			if(empty($meta_data)) {
				?>
				<tr>
					<td colspan="6"><?php _e("No Data Found", 'horizontal-meta'); ?></td>
				</tr>
				<?php
			}
			?>
		</tbody>
	</table>

	<?php
	if(!empty($meta_data)) {
		?>
		<div class="pager">
			<img src="<?php print plugins_url($this->get_plugin_name() . "/css/tablesorter/first.png"); ?>" class="first"/>
			<img src="<?php print plugins_url($this->get_plugin_name() . "/css/tablesorter/prev.png"); ?>" class="prev"/>
			<input type="text" class="pagedisplay"/>
			<img src="<?php print plugins_url($this->get_plugin_name() . "/css/tablesorter/next.png"); ?>" class="next"/>
			<img src="<?php print plugins_url($this->get_plugin_name() . "/css/tablesorter/last.png"); ?>" class="last"/>
			<select class="pagesize">
				<option value="5"><?php _e("5", 'horizontal-meta'); ?></option>
				<option value="10"><?php _e("10", 'horizontal-meta'); ?></option>
				<option value="20"><?php _e("20", 'horizontal-meta'); ?></option>
				<option value="30"><?php _e("30", 'horizontal-meta'); ?></option>
				<option value="40"><?php _e("40", 'horizontal-meta'); ?></option>
				<option selected="selected" value="50"><?php _e("50", 'horizontal-meta'); ?></option>
				<option value="100"><?php _e("100", 'horizontal-meta'); ?></option>
				<option value="200"><?php _e("200", 'horizontal-meta'); ?></option>
			</select>
		</div>
		<?php
	}
	?>


</div>