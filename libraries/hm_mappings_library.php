<?php

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

class hm_mappings_library extends hmeta_library_base {

	// chances are someone will eventually instance this class directly
	// so I have made these variables static to ensure each instance of this class stays in sync with each other.
	private static $_post_mappings = null;
	private static $_user_mappings = null;

	private $_mapping_key = "";

	function __construct() {
		parent::__construct();

		self::initialise_mappings();
	}

	function get_meta_key_prefix() {
		return "_horzm_";
	}

	/**
	 * Initialise the mappings storage device.
	 */
	static function initialise_mappings() {
		global $wpdb;

		if(self::$_post_mappings === null) {
			if(hm_is_multisite()) {
				$blogs = $wpdb->get_col("Select blog_id From {$wpdb->blogs}");
				foreach($blogs as $blog_id) {
					self::$_post_mappings["blog_" . $blog_id] == null;
				}
			} else {
				self::$_post_mappings["blog_1"] == null;
			}
		}

		// Regardless of multisite or not users meta is always accessed from the root blog.
		if(self::$_user_mappings === null) {
			self::$_user_mappings["blog_1"] == null;
		}
	}

	/**
	 * Used with WP_List_table to list meta keys they are currently mapped.
	 *
	 * @param array $options
	 * @param $record_count
	 * @return array
	 */
	function query_mappings($options=array(), &$record_count) {
		$options = (!is_array($options) ? array() : $options);

		$mappings = array();

		if(!hm_is_multisite() || (hm_is_multisite() && get_current_blog_id() == 1)) {
			$user_mappings = $this->get_user_mappings();
			$user_mappings = (empty($user_mappings) ? array() : $user_mappings);
			foreach($user_mappings as $key=>$mapping) {
				if(is_array($mapping)) {
					$mappings[] = array_merge($mapping, array("type" => "user", "meta_key" => $key));
				}
			}
		}

		$post_mappings = $this->get_post_mappings();
		$post_mappings = (empty($post_mappings) ? array() : $post_mappings);
		foreach($post_mappings as $key=>$mapping) {
			if(is_array($mapping)) {
				$mappings[] = array_merge($mapping, array("type" => "post", "meta_key" => $key));
			}
		}

		$mappings = apply_filters("hm_query_mappings", $mappings, $options);
		$record_count = count($mappings);

		$orders = array("type", "meta_key", "column", "data_type");
		if(!empty($options["order_by"]) && in_array($options["order_by"], $orders)) {
			$order_by = $options["order_by"];
		} else {
			$order_by = "type";
		}

		if(!empty($options["order"]) && in_array($options["order"], array("asc", "desc"))) {
			$order = $options["order"];
		} else {
			$order = "asc";
		}

		if(empty($options["per_page"]) || !is_numeric($options["per_page"])) {
			$options["per_page"] = 20;
		}

		if(empty($options["current_page"]) || !is_numeric($options["current_page"])) {
			$options["current_page"] = 1;
		}

		$this->_mapping_key = $order_by;
		if($order == "asc") {
			uasort($mappings, array($this, "mappings_sort_by_asc"));
		} else {
			uasort($mappings, array($this, "mappings_sort_by_desc"));
		}

		$starting_index = $options["per_page"] * ($options["current_page"] - 1);
		$length = $options["per_page"];

		$mappings = array_slice($mappings, $starting_index, $length);

		return $mappings;
	}

	function mappings_sort_by_asc($x, $y) {
		if ( $x[$this->_mapping_key] == $y[$this->_mapping_key] )
			return 0;
		else if ( $x[$this->_mapping_key] < $y[$this->_mapping_key] )
			return -1;
		else
			return 1;
	}

	function mappings_sort_by_desc($x, $y) {
		if ( $x[$this->_mapping_key] == $y[$this->_mapping_key] )
			return 0;
		else if ( $x[$this->_mapping_key] > $y[$this->_mapping_key] )
			return -1;
		else
			return 1;
	}

//	function reset_mappings() {
//		self::$_post_mappings = null;
//		self::$_user_mappings = null;
//		update_option("hm_usermeta_mappings", self::$_user_mappings);
//		update_option("hm_postmeta_mappings", self::$_post_mappings);
//	}

	/**
	 * Get a list of the user mappings stored in the blog.
	 * If the install is a multisite, this function will always return the root site mappings.
	 */
	function get_user_mappings($archived_mappings=false) {
		$key = "blog_1";

		if(self::$_user_mappings[$key] === null) {
			if(hm_is_multisite() && get_current_blog_id() !== 1) {
				global $wpdb;
				$table = $wpdb->base_prefix . "options";
				self::$_user_mappings[$key] = $wpdb->get_var("Select option_value From {$table} Where option_name = 'hm_usermeta_mappings'");
			} else {
				self::$_user_mappings[$key] = get_option("hm_usermeta_mappings");
			}
		}
		if(empty(self::$_user_mappings[$key])) self::$_user_mappings[$key] = array();
		self::$_user_mappings[$key] = maybe_unserialize(self::$_user_mappings[$key]);
		self::$_user_mappings[$key] = apply_filters("hm_get_user_mappings", self::$_user_mappings[$key]);
		return self::$_user_mappings[$key];
	}

	function get_post_mappings($archived_mappings=false) {
		$key = "blog_" . get_current_blog_id();
		if(self::$_post_mappings[$key] === null) self::$_post_mappings[$key] = get_option("hm_postmeta_mappings");
		if(empty(self::$_post_mappings[$key])) self::$_post_mappings[$key] = array();
		self::$_post_mappings[$key] = maybe_unserialize(self::$_post_mappings[$key]);
		self::$_post_mappings[$key] = apply_filters("hm_get_post_mappings", self::$_post_mappings[$key]);
		return self::$_post_mappings[$key];
	}

	/**
	 * Remove an object from the meta tables. This is in event of a user/post being completely removed from the system.
	 *
	 * @param $type
	 * @param $obj_id
	 */
	public function remove_object($type, $obj_id) {
		global $wpdb;
		$type = $this->filter_supported_type($type);
		$db_table = $this->get_db_table_by_type($type);
		$wpdb->delete($db_table, array("obj_id" => $obj_id));
	}

	/**
	 * Remove the meta key value from the database
	 *
	 * @param $type
	 * @param $obj_id
	 * @param $key
	 */
	public function remove_value($type, $obj_id, $key, $meta_value=false, $delete_all = false) {
		global $wpdb;

		$type = $this->filter_supported_type($type);
		$db_table = $this->get_db_table_by_type($type);
		$mappings = $this->get_mappings_by_type($type);

		if(array_key_exists($key, $mappings) && !empty($mappings[$key])) {

			$column = $mappings[$key]["column"];

			if(!empty($delete_all)) {
				$where = " WHERE 1 = 1 ";
				if(!empty($meta_value)) {
					$where = $wpdb->prepare(" AND `{$column}` = %s", $meta_value);
				}
				$sql = "Update {$db_table} Set `{$column}` = null {$where}";
				$wpdb->query($sql);
			} else if(!empty($meta_value)) {
				$sql = $wpdb->prepare("Update {$db_table} Set `{$column}` = null WHERE `{$column}` = %s AND obj_id = %s", $meta_value, $obj_id);
				$wpdb->query($sql);
			} else {
				$sql = $wpdb->prepare("Update {$db_table} Set `{$column}` = null Where obj_id = %s", $obj_id);
				$wpdb->query($sql);
			}

			return true;
		}

		return false;
	}

	public function save_value($type, $object_id, $key, $value) {
		global $wpdb;

		$type = $this->filter_supported_type($type);
		$db_table = $this->get_db_table_by_type($type);

		$sql = $wpdb->prepare("Select count(*) From {$db_table} Where obj_id = %s", $object_id);
		$count = $wpdb->get_var($sql);
		if($count <= 0) {
			$wpdb->insert($db_table, array("obj_id" => $object_id));
		}

		$mappings = $this->get_mappings_by_type($type);
		if(array_key_exists($key, $mappings)) {
			$values = array($mappings[$key]["column"] => maybe_serialize($value));
			$wpdb->update($db_table, $values, array("obj_id" => $object_id));
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get a value from the horizontal meta store if the column is mapped there.
	 * Always return this an an array because that's what the WordPress API expects
	 *
	 * @param $user_id
	 * @param $key
	 * @param $value
	 * @return bool
	 */
	public function get_value($type, $object_id, $key) {
		global $wpdb;

		$type = $this->filter_supported_type($type);
		$db_table = $this->get_db_table_by_type($type);

		$sql = $wpdb->prepare("Select Count(*) From {$db_table} Where obj_id = %s", $object_id);
		$count = $wpdb->get_var($sql);
		if($count <= 0) {
			$wpdb->insert($db_table, array("obj_id" => $object_id));
		}

		$mappings = $this->get_mappings_by_type($type);

		if(array_key_exists($key, $mappings)) {
			$sql = $wpdb->prepare("Select `" . $mappings[$key]["column"] . "` From {$db_table} Where obj_id = %s", $object_id);
			$value = maybe_unserialize($wpdb->get_var($sql));
			return array((empty($value) ? false : $value));
		} else {
			return false;
		}
	}

	/**
	 *
	 *
	 * @param $type
	 * @param $object_id
	 */
	public function get_all_values($type, $object_id) {
		global $wpdb;

		$output = array();

		$type = $this->filter_supported_type($type);
		$db_table = $this->get_db_table_by_type($type);
		$mappings = $this->get_mappings_by_type($type);

		$sql = $wpdb->prepare("Select * From {$db_table} Where obj_id = %s", $object_id);
		$results = $wpdb->get_results($sql, ARRAY_A);
		if(!empty($results) && is_array($results)) {
			$result = $results[0];
			foreach($mappings as $key=>$mapping) {
				$output[$key] = array($result[$mapping["column"]]);
			}
		}

		return $output;
	}

	/**
	 * This will force a meta item to be assigned explicity to a particular column of the horizontal meta table.
	 * Function returns false if the column could not be allocated.
	 * Specific error message can be obtained using the outputted $error_message variable.
	 *
	 * @param $key
	 * @param $data_type
	 * @param $index
	 */
	public function assign_manual_mapping($type, $key, $data_type, $index, $copy_data = true, $test_data = true, &$error_message = "") {

		$type = $this->filter_supported_type($type);
		$manual_column = $this->get_data_type_prefix($data_type) . $index;
		if(!is_numeric($index)) return false;

		if($this->is_meta_key_excluded($key)) {
			$error_message = __("Meta Key can not be mapped, it is in the exclusion list.", 'horizontal-meta');
			return false;
		}

		if($this->column_in_use($type, $manual_column)) {
			$in_use_key = $this->get_meta_key_by_column($type, $manual_column);
			if($in_use_key == $key) {
				// all is well, the meta key is using the proposed column already,
				// therefore we do not need to do anything
				return true;
			} else {
				$error_message = __("Column could not be allocated. It is already in use.", 'horizontal-meta');
				return false;
			}
		}

		if(!$this->is_meta_key_compatible($type, $key)) {
			$error_message = __("Column could not be allocated. Meta key contains multiple values per " . $type . ".", 'horizontal-meta');
			return false;
		}

		if($this->meta_key_mapped($type, $key)) {
			$error_message = __("Column could not be allocated. Meta key is already mapped.", 'horizontal-meta');
			return false;
		}

		// Does the column exist
		if(!$this->db_column_exists($type, $manual_column)) {
			// We need to create it
			$manual_column = $this->allocate_resource($type, $data_type, $index);
			if(empty($manual_column)) {
				$error_message = __("Column could not be allocated. Resource limit reached.", 'horizontal-meta');
				return false;
			}
		}

		if($copy_data) {
			if($test_data) {
				$output = array();
				$result = $this->test_data($type, $key, $data_type, $output);
				if(!$result) {
					$error_message = __("Column could not be allocated. Data test failed.", 'horizontal-meta');
					return false;
				}
			}

			// Copy the data from the original meta table to the new column.
			$this->copy_data($type, $key, $manual_column);
		}

		// save the mappings to the database
		$this->add_and_save_mappings($type, $key, $manual_column, $data_type);

		return $manual_column;
	}

	/**
	 * This will auto assign a meta_key to a column in the horizontal meta table
	 * This will first check the existing mappings and return an existing mapping if one already exists.
	 * If the key is already mapped this function will do nothing.
	 */
	public function assign_auto_mapping($type, $key, $data_type, $copy_data = true, $test_data = true, &$error_message = "") {

		$type = $this->filter_supported_type($type);
		$data_prefix = $this->get_data_type_prefix($data_type);
		$mappings = $this->get_mappings_by_type($type);

		if(empty($key)) {
			$error_message = __("Meta key is empty.", 'horizontal-meta');
			return false;
		}

		if($this->is_meta_key_excluded($key)) {
			$error_message = __("Meta Key can not be mapped, it is in the exclusion list.", 'horizontal-meta');
			return false;
		}

		if(array_key_exists($key, $mappings)) {
			// all is well the mapping already exists
			return $mappings[$key]["column"];
		}

		if(!$this->is_meta_key_compatible($type, $key)) {
			$error_message = __("Column could not be allocated. Meta key contains multiple values per " . $type . ".", 'horizontal-meta');
			return false;
		}

		// No mapping found
		// Make a new one and save it to the database
		$found = false;
		$columns = $this->get_db_columns_by_datatype($type, $data_prefix);
		foreach($columns as $column) {
			if(!$this->column_in_use($type, $column)) {
				$found = true;
				break;
			}
		}

		if(!$found) {
			$column = $this->allocate_resource($type, $data_type, false);
			if(empty($column)) {
				$error_message = __("Column could not be allocated. Resource limit reached.", 'horizontal-meta');
				return false;
			}
		}

		if($copy_data) {
			if($test_data) {
				$output = array();
				$result = $this->test_data($type, $key, $data_type, $output);
				if(!$result) {
					$error_message = __("Column could not be allocated. Data test failed.", 'horizontal-meta');
					return false;
				}
			}

			// Copy the data from the original meta table to the new column.
			$this->copy_data($type, $key, $column);
		}

		// save the mappings to the database
		$this->add_and_save_mappings($type, $key, $column, $data_type);

		return $column;
	}

	/**
	 * Frees up the column allocated to a particular meta key.
	 * CAUTION this will remove any existing data in the horizontal meta table in this mapping.
	 * This will not delete the actual column from the database but will instead remove the data from the column.
	 */
	public function delete_assigned_mapping($type, $key) {
		global $wpdb;

		$type = $this->filter_supported_type($type);
		$mappings = $this->get_mappings_by_type($type);

		if(array_key_exists($key, $mappings)) {
			// Remove it and zero out data in the column
			$column_name = $mappings[$key]["column"];

			// Remove the key from the mappings array
			if($type == "user") {
				unset(self::$_user_mappings["blog_" . get_current_blog_id()][$key]);
			} else {
				unset(self::$_post_mappings["blog_" . get_current_blog_id()][$key]);
			}

			$db_table = $this->get_db_table_by_type($type);

			$sql = "Update {$db_table} Set `{$column_name}` = null";
			$wpdb->query($sql);

			$this->save_mappings($type);
		}
	}

	/**
	 * Test the data in a meta key to ensure it's compatible with the selected data type
	 *
	 * @param $type
	 * @param $key
	 * @param $data_type
	 */
	public function test_data($type, $key, $data_type, &$output = array()) {
		global $wpdb;

		$return = true;
		$output = array();
		$type = $this->filter_supported_type($type);
		$column_def = $this->get_data_type_definition($data_type);
		$table = $this->get_db_meta_table_by_type($type);
		$obj_column = $type . "_id";
		$table_id = "a" . str_replace(".","", uniqid());

		$sql = "CREATE TEMPORARY TABLE {$table_id} (thecolumn_orig longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci null, thecolumn {$column_def}, theindex int(11) not null, obj_id int(11) not null)";
		$wpdb->query($sql);

		$sql = $wpdb->prepare("Select meta_value,{$obj_column} as obj_id From {$table} Where meta_key = %s Order By {$obj_column}", $key);
		$rows = $wpdb->get_results($sql, ARRAY_A);
		$i = 0;
		foreach($rows as $row) {
			$insert = array("theindex" => $i, "thecolumn_orig" => $row["meta_value"], "thecolumn" => $row["meta_value"], "obj_id" => $row["obj_id"]);
			$result = $wpdb->insert($table_id, $insert);

			if(empty($result)) {
				$return = false;
				$output[$i] = array("FAIL", $row);
			}
			$i++;
		}

		$sql = "Select * From {$table_id}";
		$results = $wpdb->get_results($sql, ARRAY_A);
		foreach($results as $result) {
			if(strval($result["thecolumn_orig"]) != strval($result["thecolumn"])) {
				$output[$result["theindex"]] = array("CAUTION", $result);
			} else {
				$output[$result["theindex"]] = array("PASS", $result);
			}
		}

		$sql = "Drop Table {$table_id}";
		$wpdb->query($sql);

		// Sort the output by
		ksort($output);

		return $return;

	}

	/**
	 * Checks to ensure the meta key is compatible.
	 * Only single valued meta keys work within a horizontal data stucture.
	 */
	public function is_meta_key_compatible($type, $meta_key) {
		global $wpdb;
		if($type == "user") {
			$db_table = $wpdb->usermeta;
			$obj_field = "user_id";
		} else {
			$db_table = $wpdb->postmeta;
			$obj_field = "post_id";
		}

		$sql = $wpdb->prepare("Select Count(*)
								From (Select Count(*) as value_count From {$db_table} Where meta_key = %s Group By {$obj_field}) as t1
								Where value_count > 1", $meta_key);
		$compatible = !(bool)$wpdb->get_var($sql);
		return $compatible;
	}

	public function get_wordpress_obj_column($type) {
		if($type == "user") {
			$obj_field = "user_id";
		} else {
			$obj_field = "post_id";
		}

		return $obj_field;
	}

	public function get_wordpress_owner_display_column($type) {
		if($type == "user") {
			$obj_field = "display_name";
		} else {
			$obj_field = "post_title";
		}

		return $obj_field;
	}

	/**
	 * Returns a list of configuration data for a particular meta type.
	 *
	 * @param $type
	 */
	public function get_meta_type_config($type) {
		if($type == "user") {
			$output = array(
				"obj_id" => "User ID",
				"owner_title" => "Display Name",
				"owner_type" => "User Role",
				"edit" => admin_url('user-edit.php?user_id={edit}')
			);
		} else {
			$output = array(
				"obj_id" => "Post ID",
				"owner_title" => "Post Title",
				"owner_type" => "Post Type",
				"edit" => admin_url('post.php?action=edit&post={edit}')
			);
		}

		return $output;
	}

	/**
	 * Checks if the meta key is already mapped.
	 *
	 * @param $type
	 * @param $key
	 * @return bool
	 */
	public function meta_key_mapped($type, $key) {
		$mappings = $this->get_mappings_by_type($type);
		if(array_key_exists($key, $mappings)) {
			return true;
		} else {
			return false;
		}
	}

	public function get_meta_keys_in_db($type, $exclude_mapped = true) {
		global $wpdb;

		$mappings = $this->get_mappings_by_type($type);
		$mapping_keys = array_keys($mappings);
		$db_table = $this->get_db_meta_table_by_type($type);

		$inlist = array();
		$like_list = "";

		$exclusions = $this->get_meta_key_exclusions();

		foreach($exclusions as $exclusion) {
			if(strpos($exclusion, "*") !== false) {
				$like_list .= " AND meta_key not like '" . $wpdb->escape(str_replace("*", "%", $exclusion)) . "' ";
			} else {
				$inlist[] =  "'" . $wpdb->escape($exclusion) . "'";
			}
		}

		if(!empty($exclude_mapped) && !empty($mapping_keys)) {
			foreach($mapping_keys as $exclusion) {
				$inlist[] =  "'" . $wpdb->escape($exclusion) . "'";
			}
		}

		$sql = "Select meta_key From {$db_table}
				Where 1 = 1
				" . $like_list . "
				" . (!empty($inlist) ? " AND meta_key not in (" . implode(",", $inlist) . ") " : "") . "
				Group by meta_key
				Order By meta_key";

		$output = $wpdb->get_col($sql, 0);
		return $output;
	}

	public function get_data_type_list() {
		$data_types = $this->get_data_types();
		return $data_types;
	}

	private function get_meta_key_by_column($type, $column) {
		$mappings = $this->get_mappings_by_type($type);
		foreach($mappings as $meta_key=>$mapping) {
			if($mapping["column"] == $column) {
				return $meta_key;
			}
		}
		return false;
	}

	/**
	 * Copy data from the original meta table to the horizontal mappings.
	 *
	 * @param $type
	 * @param $meta_key
	 * @param $column_name
	 */
	private function copy_data($type, $meta_key, $column_name) {
		global $wpdb;

		$db_table = $this->get_db_table_by_type($type);
		$obj_column = $type . "_id";
		$table = $this->get_db_meta_table_by_type($type);

		$sql = $wpdb->prepare("Select meta_value, {$obj_column} From {$table} Where meta_key = %s", $meta_key);
		$rows = $wpdb->get_results($sql, ARRAY_A);
		foreach($rows as $row) {
			$obj_id = $row[$obj_column];

			$sql = $wpdb->prepare("Select count(*) From {$db_table} Where obj_id = %s", $obj_id);
			$count = $wpdb->get_var($sql);

			if($count == 0) {
				$wpdb->insert($db_table, array($column_name => $row["meta_value"], "obj_id" => $obj_id));
			} else {
				$wpdb->update($db_table, array($column_name => $row["meta_value"]), array("obj_id" => $obj_id));
			}

		}

	}

	/**
	 * Returns a list of restricted meta_keys that can not be mapped to the horizontal structure.
	 */
	public function get_meta_key_exclusions() {
		$exclusions = array("allorany", "_edit_*", "wp_*", "meta-box*", "metabox*", "closedpostboxes*", "screen_layout*");
		$exclusions = apply_filters("hm_meta_key_list_exclusions", $exclusions);
		$exclusions = (empty($exclusions) || !is_array($exclusions) ? array() : $exclusions);

		return $exclusions;
	}

	/**
	 * Tests a meta key to see if it is excluded or not.
	 */
	public function is_meta_key_excluded($meta_key) {
		$exclusions = $this->get_meta_key_exclusions();
		foreach($exclusions as $exclusion) {
			$pattern = str_replace("*", '.*?', $exclusion);
			$result = preg_match("/" . $pattern . "/i", $meta_key);
			if($result) {
				return true;
			}
		}

		return false;
	}

	/**
	 * When assigning a new column to a mapping we need to make check whether all columns are in use or not
	 *
	 * @param $type
	 * @param $data_type
	 * @param $index
	 */
	private function column_in_use($type, $column) {
		global $wpdb;

		$mappings = $this->get_mappings_by_type($type);

		foreach($mappings as $key => $mapping) {
			if($mapping["column"] == $column) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Add the column to the meta table because it is needed.
	 *
	 * @param $type
	 * @param $data_type
	 * @param $index
	 */
	private function allocate_resource($type, $data_type, $index) {

		global $wpdb;

		if(!$this->is_type_supported($type))
			return false;

		if(!empty($index) && !is_numeric($index))
			return false;

		$output = false;
		$data_prefix = $this->get_data_type_prefix($data_type);
		$columns = $this->get_db_columns_by_datatype($type, $data_prefix);
		$db_table = $this->get_db_table_by_type($type);
		$data_type_definition = $this->get_data_type_definition($data_type);

		if(!empty($index) && is_numeric($index) && intval($index) > 0) {
			// allocate multiple resources up to index
			// this ensures everything is kept in sync.
			for($i=(count($columns)+1);$i<=$index;$i++) {
				$column = $data_prefix . $i;
				if(!$this->column_in_use($type, $column) && !$this->db_column_exists($type, $column)) {
					$sql = "ALTER TABLE {$db_table}
							ADD COLUMN `{$column}` {$data_type_definition}";
					$wpdb->query($sql);
					$output = $column;
				}
			}
		} else {
			// allocate a single resource which will be the next available
			for($i=1;$i<=100;$i++) {
				$column = $data_prefix . (count($columns)+$i);
				if(!$this->column_in_use($type, $column) && !$this->db_column_exists($type, $column)) {
					$sql = "ALTER TABLE {$db_table}
							ADD COLUMN `{$column}` {$data_type_definition}";
					$wpdb->query($sql);
					$output = $column;
					break;
				}
			}
		}

		return $output;
	}

	/**
	 * Grabs all the columns that exist within the meta table
	 *
	 * @param $type
	 * @param $data_type
	 */
	private function get_db_columns_by_datatype($type, $data_type) {
		global $wpdb;

		$db_table = $this->get_db_table_by_type($type);
		$sql = $wpdb->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
           						WHERE TABLE_NAME=%s AND column_name LIKE %s AND TABLE_SCHEMA = %s", $db_table, $data_type . "%", DB_NAME);
		$results = $wpdb->get_col($sql);
		return $results;
	}

	/**
	 * Get the database definition for a particular data type
	 *
	 * @param $data_type
	 * @return mixed
	 */
	private function get_data_type_definition($data_type) {
		$data_types = $this->get_data_types();
		$output = $data_types["string"];
		if(array_key_exists($data_type, $data_types)) {
			$output = $data_types[$data_type]["definition"];
		}
		return $output;
	}

	/**
	 * Returns a list of the supported data types.
	 *
	 * @return array
	 */
	public function get_data_types() {
		$data_types = array(
			"string" => array(
				"label" => "short text (255)",
				"dbtype" => "varchar(255)",
				"definition" => "varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL"
			)
		);

		// moved horizontal-meta-extender to the free version
		$data_types = array_merge($data_types, $this->get_extended_data_types());

		$data_types = apply_filters("horizontal_meta_data_types", $data_types);
		if(empty($data_types) || !is_array($data_types)) $data_types = array();

		// make sure there aren't any malicious statements in the datatypes definitions
		$illegal = array("exec", "'", ";", "\"",":", "{", "}", "select", "update", "insert", "delete", "drop", "create", '%', '--', '//', '/*', '*/', '[', ']');
		foreach($data_types as $data_type=>$data_def) {
			if(!empty($data_def) && is_array($data_def)) {
				$string = strtolower($data_def["definition"]);
				foreach($illegal as $test) {
					if(strpos($string, $test) !== false) {
						unset($data_types[$data_type]);
						continue;
					}
				}
			} else {
				unset($data_types[$data_type]);
				continue;
			}
		}

		return $data_types;
	}

	function get_extended_data_types() {
		$output = array(
			"date" => array(
				"label" => "date",
				"dbtype" => "date",
				"definition" => "date DEFAULT NULL"
			),
			"datetime" => array(
				"label" => "datetime",
				"dbtype" => "datetime",
				"definition" => "datetime DEFAULT NULL",
			),
			"decimal" => array(
				"label" => "decimal",
				"definition" => "decimal(18,2) DEFAULT NULL",
				"dbtype" => "decimal(18,2)",
			),
			"int" => array(
				"label" => "int",
				"definition" => "int(11) DEFAULT NULL",
				"dbtype" => "int(11)",
			),
			"text" => array(
				"label" => "text",
				"dbtype" => "text",
				"definition" => "text CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL",
			),
			"longtext" => array(
				"label" => "long text",
				"dbtype" => "longtext",
				"definition" => "longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL",
			),
		);

		return $output;
	}

	private function get_data_type_prefix($data_type) {
		$data_types = $this->get_data_types();
		$data_type_key = array_keys($data_types);

		$prefix = "string";
		if(in_array($data_type, $data_type_key)) {
			$prefix = $data_type;
		}

		return $prefix;
	}

	public function get_mappings_by_type($type) {
		if($type == "user") {
			$mappings = $this->get_user_mappings();
		} else {
			$mappings = $this->get_post_mappings();
		}

		return $mappings;
	}

	private function add_and_save_mappings($type, $meta_key, $column_mapping, $data_type) {
		global $wpdb;
		if($type == "user") {

			// save to the database
			if(get_current_blog_id() != 1) {
				// Manually save the root blog user mappings because the user mappings don't exist on child blogs
				$table = $wpdb->base_prefix . "options";
				$count = $wpdb->get_var("Select Count(*) From {$table} Where option_name = 'hm_usermeta_mappings'");
				if($count > 0) {
					$wpdb->update($table, array("option_value" => maybe_serialize(self::$_user_mappings["blog_1"])), array("option_name" => "hm_usermeta_mappings"));
				} else {
					$wpdb->insert($table, array("option_name" => "hm_usermeta_mappings", "option_value" => maybe_serialize(self::$_user_mappings["blog_1"])));
				}
			} else {
				self::$_user_mappings["blog_1"][$meta_key] = array("column" => $column_mapping, "data_type" => $data_type);
				update_option("hm_usermeta_mappings", self::$_user_mappings["blog_1"]);
				self::$_user_mappings["blog_1"] = null;
				$this->get_user_mappings();
			}
		} else {
			// save to the database
			self::$_post_mappings["blog_" . get_current_blog_id()][$meta_key] = array("column" => $column_mapping, "data_type" => $data_type);
			update_option("hm_postmeta_mappings", self::$_post_mappings["blog_" . get_current_blog_id()]);
			self::$_post_mappings["blog_" . get_current_blog_id()] = null;
			$this->get_post_mappings();
		}
	}

	/**
	 * Save mappings without adding to them
	 * @param $type
	 */
	private function save_mappings($type) {
		global $wpdb;
		if($type == "user") {
			// save to the database
			if(get_current_blog_id() != 1) {
				// Manually save the root blog user mappings because the user mappings don't exist on child blogs
				$table = $wpdb->base_prefix . "options";
				$count = $wpdb->get_var("Select Count(*) From {$table} Where option_name = 'hm_usermeta_mappings'");
				if($count > 0) {
					$wpdb->update($table, array("option_value" => maybe_serialize(self::$_user_mappings["blog_1"])), array("option_name" => "hm_usermeta_mappings"));
				} else {
					$wpdb->insert($table, array("option_name" => "hm_usermeta_mappings", "option_value" => maybe_serialize(self::$_user_mappings["blog_1"])));
				}
			} else {
				update_option("hm_usermeta_mappings", self::$_user_mappings["blog_1"]);
				self::$_user_mappings["blog_1"] = null;
				$this->get_user_mappings();
			}
		} else {
			update_option("hm_postmeta_mappings", self::$_post_mappings["blog_" . get_current_blog_id()]);
			self::$_post_mappings["blog_" . get_current_blog_id()] = null;
			$this->get_post_mappings();
		}
	}

	private function db_column_exists($type, $column) {
		global $wpdb;

		$db_table = $this->get_db_table_by_type($type);

		$sql = $wpdb->prepare("SELECT count(*) FROM INFORMATION_SCHEMA.COLUMNS
           						WHERE TABLE_NAME=%s AND column_name=%s AND TABLE_SCHEMA = %s", $db_table, $column, DB_NAME);
		$column_exists = (bool)$wpdb->get_var($sql);
		return $column_exists;
	}

	private function filter_supported_type($type) {
		$supported_types = array("user", "post");
		if(in_array($type,$supported_types)) {
			return $type;
		} else {
			return "post";
		}
	}

	private function is_type_supported($type) {
		$supported_types = array("user", "post");
		if(in_array($type,$supported_types)) {
			return true;
		} else {
			return false;
		}
	}

	public function get_db_meta_table_by_type($type) {
		global $wpdb;
		$type = $this->filter_supported_type($type);
		$table = $wpdb->{$type . "meta"};
		return $table;
	}

	public function get_db_table_by_type($type) {
		global $wpdb;
		$type = $this->filter_supported_type($type);
		if($type == "user" && hm_is_multisite()) {
			$table = $wpdb->base_prefix . $type . "meta_hm";
		} else {
			$table = $wpdb->prefix . $type . "meta_hm";
		}
		return $table;
	}

}