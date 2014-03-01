<?php

class hm_post_meta_controller extends hmeta_controller_base {

	private $_current_query = array();

	function get_load_priority() {
		return 1;
	}

	function __construct() {
		parent::__construct();

		// after a meta key has been updated, we update horizontal meta.
		add_action("updated_post_meta", array($this, "save_post_meta"), 10, 4);
		add_action("added_post_meta", array($this, "save_post_meta"), 10, 4);

		// remove meta data override if mapping exists
		add_filter("delete_post_metadata", array($this, "delete_horizontal_post_metadata"), 10, 5);

		// get meta data override if mapping exists
		add_filter("get_post_metadata", array($this, "get_horizontal_post_metadata"), 10, 4);

		// Remove the entire record for this object because the object has been deleted from the system.
		add_action("delete_post", array($this, "remove_deleted_post_meta"));

		// Use this to remove all the filters that occur from this hook to posts_selection in the get_posts of WP_Query
		// This is so we can still insert our hooks to reroute the meta mappings if needed.
		add_action("pre_get_posts", array($this, "pre_get_posts_add_hooks"), 999);

		// Use this to restore the supress_filters variable back to the query
		add_action("posts_selection", array($this, "posts_selection_restore_filters"), 999);

	}

	/**
	 * We need a way to still fire our sql functions so we can remap the meta query to our horizontal mapping.
	 * The only way I can see this happening is to
	 * 1. catch the pre_query_posts,
	 * 2. if supress_filters is true then remove all the filters that are applicable for query functions
	 * 3. ensure supress_filters is false
	 * 4. add our filters for changing where the meta data is coming from
	 * 5. our sql functions are called and sql is changed to include the horizontal meta locations
	 * 6. when posts_selection is called, restore all filters and dyanmically change the query to supress_filters true (if that wasn't already the value)
	 *
	 * @param $query
	 */
	function pre_get_posts_add_hooks(&$query) {

		$this->model("hm_settings_model");
		$this->library("hm_mappings_library");
		$this->library("hm_query_library");

		if(empty($this->settings))
			$this->settings = $this->hm_settings_model->get_settings();

		if(!class_exists("WP_Meta_Query"))
			return;

		if($this->settings["post_rewrite_queries"] != "1")
			return;

		// query rewriting will only work when suppress_filters is disabled
		if(isset($query->query_vars["suppress_filters"]) && $query->query_vars["suppress_filters"])
			return;

//		global $wp_filter;
//
//		$obj = array("query" => &$query, "suppress_filters" => $query->query_vars["suppress_filters"], "filters"=>array());
//
//		if(!empty($query->query_vars["suppress_filters"]) &&
//			$this->settings["post_override_suppress_filters"] == "1" &&
//			$this->settings["post_override_disable_other_filters"] == "1") {
//
//			// suppress filters remove all the filters that will be run and then ensure filters are not suppressed
//			$remove_filters = array(
//				"posts_where",
//				"posts_join",
//				"posts_orderby",
//				"posts_groupby",
//				"post_limits",
//				"comment_feed_join",
//				"comment_feed_where",
//				"comment_feed_groupby",
//				"comment_feed_orderby",
//				"comment_feed_limits",
//				"posts_where_paged",
//				"posts_join_paged",
//				"posts_distinct",
//				"posts_fields",
//				"posts_clauses"
//			);
//
//			foreach($remove_filters as $filter) {
//				if(array_key_exists($filter, $wp_filter)) {
//					// save a list of the filters so we can restore them later
//					$obj["filters"][$filter] = $wp_filter[$filter];
//
//					// remove all the filters
//					$wp_filter[$filter] = array();
//				}
//			}
//
//		}
//
//		if(!empty($query->query_vars["suppress_filters"]) && $this->settings["post_override_suppress_filters"] == "1") {
//			// make sure that our filters are going to run.
//			$query->query_vars["suppress_filters"] = false;
//		}
//
//		// store a reference to the query so we can access it later.
//		$this->_current_query[] = $obj;

		if(empty($query->query_vars["suppress_filters"])) {

			// EXPERIMENTAL
			// remove the mapped meta keys from the query which we will manually build them into the query in later hooks
			$this->hm_query_library->extract_mapped_meta_keys("post", $query->query_vars);

			// over the default joins and add our mappings into the query
			add_filter('posts_join', array($this, 'post_join'), 0, 2);

			// over the default where clauses and add our mappings into the query
			add_filter('posts_where', array($this, 'post_where'), 0, 2);

			// over the default orderby and add our mappings into the query
			add_filter('posts_orderby', array($this, 'post_orderby'), 0, 2);

			// over the default groupby and add our mappings into the query
			add_filter('posts_groupby', array($this, 'post_groupby'), 0, 2);
		}

	}

	/**
	 * Use this function to restore the filters we removed prior in pre_get_posts_add_hooks.
	 * This is the last possible hook we can use and occurs after the query has been fully built
	 *
	 * @param $announcement
	 */
	function posts_selection_restore_filters($announcement) {
//
//		if(empty($this->settings))
//			$this->settings = $this->hm_settings_model->get_settings();
//
//		if(!class_exists("WP_Meta_Query"))
//			return;
//
//		if($this->settings["post_rewrite_queries"] != "1")
//			return;
//
//		global $wp_filter;
//
//		if(!empty($this->_current_query)) {
//			$obj = array_shift($this->_current_query);
//			if(!empty($obj) && $obj["suppress_filters"] === true) {
//				if(!empty($obj["filters"])) {
//					foreach($obj["filters"] as $filter=>$filter_data) {
//						$wp_filter[$filter] = $filter_data;
//					}
//				}
//
//				// reset the supress filters
//				$obj["query"]->query_vars["suppress_filters"] = true;
//			}
//		}
	}

	function post_groupby($groupby, &$query) {
		$this->library("hm_query_library");
		$output = $this->hm_query_library->post_groupby($groupby, $query);
		return $output;
	}
	function post_join($join, &$query) {
		$this->library("hm_query_library");
		$output = $this->hm_query_library->post_join($join, $query);
		return $output;
	}
	function post_where($where, &$query) {
		$this->library("hm_query_library");
		$output = $this->hm_query_library->post_where($where, $query);
		return $output;
	}
	function post_orderby($orderby, &$query) {
		$this->library("hm_query_library");
		$output = $this->hm_query_library->post_orderby($orderby, $query);
		return $output;
	}

	function remove_deleted_post_meta($post_id) {
		$this->library("hm_mappings_library");
		$this->hm_mappings_library->remove_object("post", $post_id);
	}

	/**
	 * This callback is fired after any post meta data has been updated in the system. This is a low level hook to catch the data and ensure it is transferred across to horizontal meta.
	 */
	function save_post_meta($meta_id, $object_id, $meta_key, $_meta_value) {
		$this->library("hm_mappings_library");

		if($this->hm_mappings_library->meta_key_mapped("post", $meta_key)) {
			$this->hm_mappings_library->save_value("post", $object_id, $meta_key, $_meta_value);
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
	function delete_horizontal_post_metadata($check, $object_id, $meta_key, $meta_value, $delete_all) {
		if(empty($check)) {
			$this->library("hm_mappings_library");

			if($this->hm_mappings_library->meta_key_mapped("post", $meta_key)) {
				$this->hm_mappings_library->remove_value("post", $object_id, $meta_key, $meta_value, $delete_all);
			}
		}

		return $check;
	}

	/**
	 * When the developer/plugin needs to retrieve some data from the postmeta table that is mapped to the horizontal table,
	 * We intercept this query to see if they are requesting data from horizontal meta or not.
	 *
	 * @param $check
	 * @param $object_id
	 * @param $meta_key
	 * @param $single
	 * @return bool
	 */
	function get_horizontal_post_metadata($check, $object_id, $meta_key, $single) {

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

			if(!empty($meta_key) && $use_hm && $this->hm_mappings_library->meta_key_mapped("post", $meta_key)) {
				$value = $this->hm_mappings_library->get_value("post", $object_id, $meta_key);
				if(!empty($value)) {
					$check = $value;
				}
			} else if(empty($meta_key)) {

				// call the get meta function without linking to the horizontal structure
				remove_filter("get_post_metadata", array($this, "get_horizontal_post_metadata"), 10);

				// get the standard meta data based on the arguments passed in
				$meta_data = get_metadata("post", $object_id, $meta_key, $single);

				// relink to the horizontal structure
				add_filter("get_post_metadata", array($this, "get_horizontal_post_metadata"), 10, 4);

				// get all the mapped values and merge the standard and the horizontal values together
				$horizontal = $this->hm_mappings_library->get_all_values("post", $object_id);
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