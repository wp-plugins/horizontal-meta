<?php



/**
 * Auto assign a meta key mapping to the horizontal meta table structure.
 *
 * @param $meta_key string Meta Key to map
 * @param $data_type string One of the following values: string, date, datetime, decimal, int
 * @param $copy_data bool Copy already existing data from the original meta table to the horizontal meta structure, CAUTION if data is being copied, performance may be impacted when meta key is first assigned.
 * @return bool
 */
function assign_user_meta_mapping($meta_key, $data_type, $copy_data = true, $test_data = true, &$error_message = "") {
	hm_controller::$instance->library("hm_mappings_library");
	return hm_controller::$instance->hm_mappings_library->assign_auto_mapping("user", $meta_key, $data_type, $copy_data, $test_data, $error_message);
}


/**
 * Assign a manual meta key mapping to the horizontal meta table structure.
 * Function returns false if the column could not be allocated in addition to raising a warning.
 *
 * @param $meta_key string Meta Key to map
 * @param $data_type string One of the following values: string, date, datetime, decimal, int
 * @param $index int Index number of column
 * @param $copy_data bool Copy already existing data from the original meta table to the horizontal meta structure, CAUTION if data is being copied, performance may be impacted when meta key is first assigned.
 */
//	function assign_manual_user_meta_mapping($meta_key, $data_type, $index, $copy_data = true, $test_data = true, $remove_data = true, &$error_message = "") {
//		$inst = $GLOBALS["hmeta"]["horizontal-meta"]["controllers"]["hm"];
//		if(empty($inst)) return false;
//
//		$inst->library("hm_mappings_library");
//		return $inst->hm_mappings_library->assign_manual_mapping("user", $meta_key, $data_type, $index, $copy_data, $test_data, $remove_data, $error_message);
//	}


/**
 * Removes a meta key mapping from the horizontal data structure. This will remove all the data stored in horizontal meta and free up the resource.
 *
 * @param $meta_key string Meta Key to map
 * @return bool
 */
function remove_user_meta_mapping($meta_key) {
	hm_controller::$instance->library("hm_mappings_library");
	return hm_controller::$instance->hm_mappings_library->delete_assigned_mapping("user", $meta_key);
}

/**
 * Check if a user meta key mapping exists or not.
 *
 * @param $meta_key Meta Key
 * @return bool
 */
function user_mapping_exists($meta_key) {
	hm_controller::$instance->library("hm_mappings_library");
	return hm_controller::$instance->hm_mappings_library->meta_key_mapped("user", $meta_key);
}


/**
 * Checks if a user meta key is compatible to be mapped.
 *
 * @param $meta_key string Meta Key
 * @return bool
 */
function user_mapping_compatible($meta_key) {
	hm_controller::$instance->library("hm_mappings_library");
	return hm_controller::$instance->hm_mappings_library->is_meta_key_compatible("user", $meta_key);
}

/**
 * Creates a temp table to test whether the key will map successfully or not.
 *
 * @param $meta_key string Meta Key
 * @param $output array Output of tested data
 * @return bool
 */
function user_mapping_test_key($meta_key, $data_type, &$output) {
	hm_controller::$instance->library("hm_mappings_library");
	return hm_controller::$instance->hm_mappings_library->test_data("user", $meta_key, $data_type, $output);
}

/**
 * Retrieves the meta mappings stored in the database
 */
function get_user_mappings() {
	hm_controller::$instance->library("hm_mappings_library");
	return hm_controller::$instance->hm_mappings_library->get_user_mappings();
}



/**
 * This will retrieve all the HM meta data for the $ids and return it.
 *
 * @param array $ids Array of user ID's
 */
function hm_get_values_for_users($ids) {
	hm_controller::$instance->library("hm_manual_query_library");
	return hm_controller::$instance->hm_manual_query_library->get_values_by_type("user", $ids);
}

/**
 * Get the HM values for the $id. If $id is false, get_current_user_id() will be used.
 *
 * @param bool $id post id or get_the_ID() will be used.
 */
function hm_get_values_for_user($id = false) {
	if(empty($id))
		$id = get_current_user_id();

	hm_controller::$instance->library("hm_manual_query_library");
	return hm_controller::$instance->hm_manual_query_library->get_values_by_type("user", $id);
}