<table border="0" class="widefat test_results_table" id="metakey_results">
	<thead>
	<tr>
		<th><?php _e("Object", 'horizontal-meta'); ?></th>
		<th><?php _e("Original", 'horizontal-meta'); ?></th>
		<th><?php _e("New", 'horizontal-meta'); ?></th>
		<th><?php _e("Result", 'horizontal-meta'); ?></th>
	</tr>
	</thead>
	<tbody>
		<?php
		if(!empty($results) && is_array($results)) {
			foreach($results as $row) {
				$result = $row[0];
				$item = $row[1];

				if($result == "PASS") {
					$styles = "background-color:green;color:white;";
				} else if($result == "CAUTION") {
					$styles = "background-color:orange;color:white;";
				} else {
					// fail
					$styles = "background-color:red;color:white;";
				}
				?>
				<tr>
					<td><?php print $item["obj_id"]; ?></td>
					<td><?php print $item["thecolumn_orig"]; ?></td>
					<td><?php print $item["thecolumn"]; ?></td>
					<td style="<?php print $styles; ?>"><?php print $result; ?></td>
				</tr>
			<?php
			}
		}
		?>
	</tbody>
</table>
<div id="pager" class="pager">
	<form>
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
	</form>
</div>
