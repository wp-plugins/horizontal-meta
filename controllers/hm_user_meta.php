<?php

class hm_user_meta_controller extends hmeta_controller_base {

	function get_load_priority() {
		return 2;
	}

	function __construct() {
		parent::__construct();

		// add meta data to HM if the key is mapped
		add_action("updated_user_meta", array($this, "save_user_meta"), 10, 4);
		add_action("added_user_meta", array($this, "save_user_meta"), 10, 4);

		// remove meta data from HM if mapping exists
		add_filter("delete_user_metadata", array($this, "delete_horizontal_user_metadata"), 10, 5);

		// get meta data from HM if mapping exists
		add_filter("get_user_metadata", array($this, "get_horizontal_user_metadata"), 10, 4);

		// Remove the entire record for this object because the object has been deleted from the system.
		add_action("deleted_user", array($this, "remove_deleted_user_meta"));

		add_action("pre_user_query", array($this, "pre_user_query"));

	}

	/**
	 * Intercept the query that is being made and ensure that we reroute any meta keys that we need to.
	 */
	function pre_user_query(&$query) {

		$this->model("hm_settings_model");

		// get the root blog user settings as no user settings are stored in child blogs
		if(empty($this->settings))
			$this->settings = $this->hm_settings_model->get_settings(1);

		if(!class_exists("WP_Meta_Query"))
			return;

		if($this->settings["user_rewrite_queries"] != "1")
			return;

		$this->library("hm_query_library");
		$this->hm_query_library->user_pre_user_query($query);
	}

	function remove_deleted_user_meta($user_id) {
		$this->library("hm_mappings_library");
		$this->hm_mappings_library->remove_object("user", $user_id);
	}

	/**
	 * This callback is fired after any user meta data has been updated in the system. This is a low level hook to catch the data and ensure it is transferred across to horizontal meta.
	 */
	function save_user_meta($meta_id, $object_id, $meta_key, $_meta_value) {
		$this->library("hm_mappings_library");

		if($this->hm_mappings_library->meta_key_mapped("user", $meta_key)) {
			$this->hm_mappings_library->save_value("user", $object_id, $meta_key, $_meta_value);
		}
	}

	/**
	 * This callback hook is used to remove read only data stored in Horizontal Meta.
	 *
	 * We need to hook into the delete metadata routine early because the delete_all param is currently not included in the 'deleted_{$meta_type}_meta' action.
	 * It's safe to assume that the meta data is going to be deleted, so hooking in early should be OK
	 *
	 * We still want wordpress to continue it's processing so we ensure we pass the same $check value back.
	 * Doing this will cause WordPress to continue as normal and remove the data from the meta table.
	 */
	function delete_horizontal_user_metadata($check, $object_id, $meta_key, $meta_value, $delete_all) {
		if(empty($check)) {
			$this->library("hm_mappings_library");
			$updated = $this->hm_mappings_library->remove_value("user", $object_id, $meta_key, $meta_value, $delete_all);
			if(!empty($updated)) {
				$check = true;
			}
		}
		return $check;
	}

	/**
	 * When the developer/plugin needs to retrieve some data from the user meta table that is mapped to the horizontal table,
	 * We intercept this query to see if they are requesting data from horizontal meta or not.
	 *
	 * TODO: Add option in settings to give user option to allow Horizontal Meta to override the request and redirect a request for a particular meta key to HM rather than only intercepting meta_keys prefixed with _horzm_
	 *
	 * @param $check
	 * @param $object_id
	 * @param $meta_key
	 * @param $single
	 * @return bool
	 */
	function get_horizontal_user_metadata($check, $object_id, $meta_key, $single) {

		if(empty($check)) {
			$this->library("hm_mappings_library");

			$meta_key_prefix = $this->hm_mappings_library->get_meta_key_prefix();

			$use_hm = false;
			if(!empty($meta_key)) {
				if(substr(strtolower($meta_key), 0, strlen($meta_key_prefix)) == $meta_key_prefix) {
					// the user is requesting a horizontal meta watcher.
					$use_hm = true;
					$meta_key = substr($meta_key, strlen($meta_key_prefix));
				}
			}

			if(!empty($meta_key) && $use_hm && $this->hm_mappings_library->meta_key_mapped("user", $meta_key)) {
				$value = $this->hm_mappings_library->get_value("user", $object_id, $meta_key);
				if(!empty($value)) {
					$check = $value;
				}
			} else if(empty($meta_key)) {

				// call the get meta function without linking to the horizontal structure
				remove_filter("get_user_metadata", array($this, "get_horizontal_user_metadata"), 10);

				// get the standard meta data based on the arguments passed in
				$meta_data = get_metadata("user", $object_id, $meta_key, $single);

				// relink to the horizontal structure
				add_filter("get_user_metadata", array($this, "get_horizontal_user_metadata"), 10, 4);

				// get all the mapped values and merge the standard and the horizontal values together
				$horizontal = $this->hm_mappings_library->get_all_values("user", $object_id);
				foreach($horizontal as $key=>$value) {
					$meta_data[$key] = $value;
				}

				// set the result so the get_metadata function does not continue on.
				$check = $meta_data;

			}
		}

		return $check;
	}

}