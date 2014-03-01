<?php

/**
 * Auto assign a meta key mapping to the horizontal meta table structure.
 *
 * @param $meta_key string Meta Key to map
 * @param $data_type string One of the following values: string, date, datetime, decimal, int
 * @param $copy_data bool Copy already existing data from the original meta table to the horizontal meta structure, CAUTION if data is being copied, performance may be impacted when meta key is first assigned.
 * @return bool
 */
function assign_post_meta_mapping($meta_key, $data_type, $copy_data = true, $test_data = true, &$error_message = "") {
	hm_controller::$instance->library("hm_mappings_library");
	return hm_controller::$instance->hm_mappings_library->assign_auto_mapping("post", $meta_key, $data_type, $copy_data, $test_data, $error_message);
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
//	function assign_manual_post_meta_mapping($meta_key, $data_type, $index, $copy_data = true, $test_data = true, $remove_data = true, &$error_message = "") {
//		$inst = $GLOBALS["hmeta"]["horizontal-meta"]["controllers"]["hm"];
//		if(empty($inst)) return false;
//
//		$inst->library("hm_mappings_library");
//		return $inst->hm_mappings_library->assign_manual_mapping("post", $meta_key, $data_type, $index, $copy_data, $test_data, $remove_data, $error_message);
//	}

/**
 * Removes a meta key mapping from the horizontal data structure.
 *
 * @param $meta_key string Meta Key to map
 * @return bool
 */
function remove_post_meta_mapping($meta_key) {
	hm_controller::$instance->library("hm_mappings_library");
	return hm_controller::$instance->hm_mappings_library->delete_assigned_mapping("post", $meta_key);
}

/**
 * Check if a post meta key mapping exists or not.
 *
 * @param $meta_key Meta Key
 * @return bool
 */
function post_mapping_exists($meta_key) {
	hm_controller::$instance->library("hm_mappings_library");
	return hm_controller::$instance->hm_mappings_library->meta_key_mapped("post", $meta_key);
}

/**
 * Checks if a user meta key is compatible to be mapped.
 *
 * @param $meta_key Meta Key
 * @return bool
 */
function post_mapping_compatible($meta_key) {
	hm_controller::$instance->library("hm_mappings_library");
	return hm_controller::$instance->hm_mappings_library->is_meta_key_compatible("post", $meta_key);
}

/**
 * Creates a temp table to test whether the key will map successfully or not.
 *
 * @param $meta_key Meta Key
 * @param $output array Output of tested data
 * @return bool
 */
function post_mapping_test_key($meta_key, $data_type, &$output) {
	hm_controller::$instance->library("hm_mappings_library");
	return hm_controller::$instance->hm_mappings_library->test_data("post", $meta_key, $data_type, $output);
}

/**
 * Retrieves the meta mappings stored in the database
 */
function get_post_mappings() {
	hm_controller::$instance->library("hm_mappings_library");
	return hm_controller::$instance->hm_mappings_library->get_post_mappings();
}

/**
 * This will retrieve all the HM meta data for the current loop and store it for later retrieval.
 * This is useful to save the number of database queries required to grab the data when in the loop.
 * Use: hm_get_values_for_post to retrieve the individual values.
 */
function hm_load_post_loop_values() {
	hm_controller::$instance->library("hm_manual_query_library");
	hm_controller::$instance->hm_manual_query_library->load_post_loop_values();
}

/**
 * This will retrieve all the HM meta data for the $ids and return it.
 *
 * @param array $ids Array of post ID's
 */
function hm_get_values_for_posts($ids) {
	hm_controller::$instance->library("hm_manual_query_library");
	return hm_controller::$instance->hm_manual_query_library->get_values_by_type("post", $ids);
}

/**
 * Get the HM values for the $id. If $id is false, get_the_ID() will be used.
 *
 * @param bool $id post id or get_the_ID() will be used.
 */
function hm_get_values_for_post($id = false) {
	if(empty($id))
		$id = get_the_ID();

	hm_controller::$instance->library("hm_manual_query_library");
	return hm_controller::$instance->hm_manual_query_library->get_values_by_type("post", $id);
}
