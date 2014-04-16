<?php

class hm_tools_library extends hmeta_library_base {

	/**
	 * Called to update the data stored in the system when the user clicks the Update Data button from the Manage Data page.
	 * This function supports copying data from wordpress meta to horizontal meta and/or removing the data from wordpress meta
	 */
	function sync_key($type, $meta_key, $obj_id, $value) {

		global $wpdb;

		// only proceed if the meta key is mapped
		$this->library("hm_mappings_library");
		if(!$this->hm_mappings_library->meta_key_mapped($type, $meta_key)) return;

		// copy the data to horizontal meta
		$this->hm_mappings_library->save_value($type, $obj_id, $meta_key, $value);
	}

	function get_merged_meta_data($type, $meta_key) {

		$output = array();

		$wordpress = $this->get_wordpress_meta($type, $meta_key);
		$horizontal = $this->get_horizontal_meta($type, $meta_key);

		foreach($wordpress as $row) {
			$output[$row["obj_id"]] = (!empty($output[$row["obj_id"]]) ? $output[$row["obj_id"]] : array());
			$output[$row["obj_id"]]["wordpress"] = (!empty($output[$row["obj_id"]]["wordpress"]) ? $output[$row["obj_id"]]["wordpress"] : array());

			$output[$row["obj_id"]]["wordpress"][] = $row["wordpress_value"];
			$output[$row["obj_id"]]["owner"] = $row["owner_display_column"];
			$output[$row["obj_id"]]["owner_type"] = $row["owner_type"];
		}

		foreach($horizontal as $row) {
			$output[$row["obj_id"]] = (!empty($output[$row["obj_id"]]) ? $output[$row["obj_id"]] : array());
			$output[$row["obj_id"]]["horizontal"] = (!empty($output[$row["obj_id"]]["horizontal"]) ? $output[$row["obj_id"]]["horizontal"] : array());

			$output[$row["obj_id"]]["horizontal"][] = $row["horizontal_value"];
			$output[$row["obj_id"]]["owner"] = $row["owner_display_column"];
			$output[$row["obj_id"]]["owner_type"] = $row["owner_type"];
		}

		foreach($output as $obj_id=>$row) {
			$output[$obj_id]["wordpress"] = (!empty($output[$obj_id]["wordpress"]) ? $output[$obj_id]["wordpress"] : array());
			$output[$obj_id]["horizontal"] = (!empty($output[$obj_id]["horizontal"]) ? $output[$obj_id]["horizontal"] : array());

			// add a test comparison to ensure
			$output[$obj_id]["in_sync"] = $this->meta_data_in_sync($output[$obj_id]["wordpress"], $output[$obj_id]["horizontal"]);

			if($type == "user" && !empty($row["owner_type"])) {
				$owner_type = maybe_unserialize($row["owner_type"]);
				$output[$obj_id]["owner_type"] = (!empty($owner_type) && is_array($owner_type) ? implode("", array_keys($owner_type)) : "");
			}

		}

		return $output;
	}

	/**
	 * Function is used to compare the values currently stored in wordpress and horizontal meta
	 */
	public function meta_data_in_sync($wp, $hm) {
		if(empty($wp) && empty($hm)) {
			return true;
		} else if(empty($wp) && !empty($hm)) {
			return false;
		} else if(!empty($wp) && empty($hm)) {
			return false;
		} else if(count($wp) != count($hm)) {
			return false;
		} else {
			$wp_values = $wp;
			$hm_values = $hm;

			foreach($wp_values as $wp_value) {
				if(!in_array($wp_value, $hm_values)) {
					return false;
				}
			}

			foreach($hm_values as $hm_value) {
				if(!in_array($hm_value, $wp_values)) {
					return false;
				}
			}

			return true;
		}


	}

	function get_wordpress_meta($type, $meta_key) {
		global $wpdb;

		$this->library("hm_mappings_library");
		$obj_field = $this->hm_mappings_library->get_wordpress_obj_column($type);
		$obj_table = $wpdb->{$type . "s"};
		$owner_column = $this->hm_mappings_library->get_wordpress_owner_display_column($type);
		$wordpress_table = $this->hm_mappings_library->get_db_meta_table_by_type($type);

		if($type == "user") {
			$owner_type_col = "(SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'wp_capabilities' AND {$obj_table}.ID = {$wpdb->usermeta}.user_id) as owner_type";
		} else {
			$owner_type_col = "{$obj_table}.post_type as owner_type";
		}

		$sql = $wpdb->prepare("SELECT {$obj_field} AS obj_id, meta_value as wordpress_value, {$obj_table}.{$owner_column} as owner_display_column, {$owner_type_col}
								FROM {$wordpress_table}
								INNER JOIN {$obj_table}
									ON {$obj_table}.ID = {$wordpress_table}.{$obj_field}
								WHERE meta_key = %s", $meta_key);

		$results = $wpdb->get_results($sql, ARRAY_A);
		return $results;
	}


	function get_horizontal_meta($type, $meta_key) {
		global $wpdb;

		$this->library("hm_mappings_library");
		$horizontal_table = $this->hm_mappings_library->get_db_table_by_type($type);
		$obj_table = $wpdb->{$type . "s"};
		$owner_column = $this->hm_mappings_library->get_wordpress_owner_display_column($type);

		if($type == "user") {
			$owner_type_col = "(SELECT meta_value FROM $wpdb->usermeta WHERE meta_key = 'wp_capabilities' AND {$obj_table}.ID = {$wpdb->usermeta}.user_id) as owner_type";
		} else {
			$owner_type_col = "{$obj_table}.post_type as owner_type";
		}

		if($this->hm_mappings_library->meta_key_mapped($type, $meta_key)) {
			$mappings = $this->hm_mappings_library->get_mappings_by_type($type);
			$mapping = $mappings[$meta_key];

			$sql = "SELECT obj_id, `{$mapping["column"]}` AS horizontal_value, {$obj_table}.{$owner_column} as owner_display_column, {$owner_type_col}
					FROM {$horizontal_table}
					INNER JOIN {$obj_table}
						ON {$obj_table}.ID = {$horizontal_table}.obj_id
					WHERE not `{$mapping["column"]}` is null ";

			$results = $wpdb->get_results($sql, ARRAY_A);
			return $results;
		} else {
			return array();
		}
	}

	function get_historical_mappings_by_key($type, $meta_key) {

	}

}