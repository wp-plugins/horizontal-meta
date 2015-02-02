<?php

class hm_query_library extends hmeta_library_base {

	public function user_pre_user_query(&$query) {
		global $wpdb;

		$this->library("hm_mappings_library");
		$this->model("hm_settings_model");
		$settings = $this->hm_settings_model->get_settings();

		if(class_exists("WP_Meta_Query") && !empty($query->query_vars)) {
			$meta_key_prefix = $this->hm_mappings_library->get_meta_key_prefix();
			$meta_query = new WP_Meta_Query();
			$meta_query->parse_query_vars( $query->query_vars );

			if(!empty($meta_query->queries)) {

				$new_meta_query = array();
				$horz_keys = array();
				$normal_keys = array();

				foreach($meta_query->queries as $mq) {
					$key = "";
					if(substr(strtolower($mq["key"]), 0, strlen($meta_key_prefix)) == $meta_key_prefix) {
						$key = substr($mq["key"], strlen($meta_key_prefix));
						if(!$this->hm_mappings_library->meta_key_mapped("user", $key)) {
							$key = "";
						}
					} else if($this->hm_mappings_library->meta_key_mapped("user", $mq["key"]) && !empty($settings["user_intercept_keys"]) && $settings["user_intercept_keys"] == "1") {
						$key = $mq["key"];
					}
					if(!empty($key)) {
						$mq["key"] = $key;
						$horz_keys[] = $mq;
					} else {
						$normal_keys[] = $mq;
					}
				}

				// Don't do anything if we don't need to.
				if(empty($horz_keys))
					return;

				// Load the original meta sql so it can be removed from the query and then rebuilt
				$orig_meta_sql = $meta_query->get_sql('user', $wpdb->users, 'ID', $query);
				$join = str_replace($orig_meta_sql['join'], "", $query->query_from);
				$where = str_replace($orig_meta_sql['where'], "", $query->query_where);


				// normal EAV keys
				$relation = $meta_query->relation;
				if(empty($relation)) $relation = "AND";
				if(!empty($normal_keys)) {
					$normal_keys["relation"] = $relation;
					$meta_query_obj = new WP_Meta_Query($normal_keys);
					$sql = $meta_query_obj->get_sql('user', $wpdb->users, 'ID', $query);
					$where .= $sql["where"];
					$join .= $sql["join"];
				}

				// add a table reference for the mapped keys
				if(!empty($horz_keys)) {
					$db_table = $this->hm_mappings_library->get_db_table_by_type("user");
					$join .= "\nINNER JOIN {$db_table} as hm_meta ON hm_meta.obj_id = {$wpdb->users}.ID";
					// LEFT OUTER
				}

				$trail_bracket = false;
				$where = trim($where);
				if(substr($where, strlen($where)-1, 1) == ")") {
					$where = substr($where, 0, strlen($where)-1);
					$trail_bracket = true;
				}

				// add a where clauses for
				$generated_where = "";
				$mappings = $this->hm_mappings_library->get_user_mappings();
				foreach($horz_keys as $meta) {
					$generated_where .= $this->generate_where($meta, $relation, $mappings);
				}

				if(empty($normal_keys)) {
					$where .= " AND (" . preg_replace('/\s' . $relation . '/', '', $generated_where, 1) . ") ";
				} else {
					$where .= $generated_where;
				}

				if($trail_bracket) {
					$where .= " ) ";
				}

				$query->query_where = $where;
				$query->query_from = $join;

				// change order by if needed (future feature that i'm sure wordpress will release)
				$key = "";
				if(substr($query->query_vars["meta_key"], 0, strlen($meta_key_prefix)) == $meta_key_prefix) {
					$key = substr($query->query_vars["meta_key"], strlen($meta_key_prefix));
					if(!$this->hm_mappings_library->meta_key_mapped("user", $key)) {
						// key is not mapped, don't proceed
						$key = "";
					}
				} else if($this->hm_mappings_library->meta_key_mapped("user", $query->query_vars["meta_key"]) && !empty($settings["user_intercept_keys"]) && $settings["user_intercept_keys"] == "1") {
					$key = $query->query_vars["meta_key"];
				}
				if(!empty($key)) {
					$query->query_orderby = str_replace($wpdb->usermeta . ".meta_value", "hm_meta." . $mappings[$key]["column"], $query->query_orderby);
				}

			}

		}

		//print_r($query);
	}

	public function extract_mapped_meta_keys($type, &$query_vars) {
		$meta_query = (empty($query_vars["meta_query"]) ? false : $query_vars["meta_query"]);

		$this->model("hm_settings_model");
		$this->library("hm_mappings_library");
		$settings = $this->hm_settings_model->get_settings();
		$mappings = $this->hm_mappings_library->get_mappings_by_type($type);
		$meta_key_prefix = $this->hm_mappings_library->get_meta_key_prefix();

		$output = array("extracted" => array(), "meta_query" => array());

		if(!empty($meta_query)) {
			foreach($meta_query as $index=>$query) {
				if(!empty($query["key"])) {
					$key = $query["key"];
					if(substr(strtolower($key), 0, strlen($meta_key_prefix)) == $meta_key_prefix) {
						$key = substr($key, strlen($meta_key_prefix));
						if(array_key_exists($key, $mappings)) {
							$query["key"] = $key;
							$output["extracted"][] = $query;
							unset($meta_query[$index]);
						}
					} else if(!empty($settings[$type . "_intercept_keys"]) && $settings[$type . "_intercept_keys"] == "1" && array_key_exists($key, $mappings)) {
						$query["key"] = $key;
						$output["extracted"][] = $query;
						unset($meta_query[$index]);
					}
				}
			}

			$output["meta_query"] = $meta_query;
		}

		// check for the root level meta_key
		// this attribute is also used to perform an orderby.
		if((!empty($query_vars["meta_key"]) && substr(strtolower($query_vars["meta_key"]), 0, strlen($meta_key_prefix)) == $meta_key_prefix && array_key_exists(substr($query_vars["meta_key"], strlen($meta_key_prefix)), $mappings)) ||
			(!empty($settings[$type . "_intercept_keys"]) && $settings[$type . "_intercept_keys"] == "1" && array_key_exists($query_vars["meta_key"], $mappings))) {

			if(substr(strtolower($query_vars["meta_key"]), 0, strlen($meta_key_prefix)) == $meta_key_prefix) {
				$key = substr($query_vars["meta_key"], strlen($meta_key_prefix));
			} else {
				$key = $query_vars["meta_key"];
			}

			$meta = array(
				"key" => $key
			);

			// DO MORE TESTING ON META_VALUE
			// it seems meta_value is set as '' even if it's not parsed.
			// this means if the user wants to do a comparison on '' (unlikely) it will no longer.
			// TODO: Look for alternate method.
			if(!empty($query_vars["meta_value"])) $meta["value"] = $query_vars["meta_value"];
			if(isset($query_vars["meta_compare"])) $meta["compare"] = $query_vars["meta_compare"];
			if(isset($query_vars["meta_type"])) $meta["type"] = $query_vars["meta_type"];
			if(!empty($query_vars["orderby"]) && (strpos($query_vars["orderby"], "meta_value") !== false || strpos($query_vars["orderby"], $key) !== false)) $meta["orderby"] = true;

			$output["extracted"][] = $meta;

			// Unfortunately we need to maintain the meta-key for the purposes of ordering our data in the query.
			// If we remove this from the query completely there is no clean way to order the data as needed.

			// if a meta_key is being ordered, we remove the value from the query so no standard comparison is done on the value.
			// this means wordpress will simply check for the existence of the key which is a small performance tradeoff for allowing us to maintain our data order.
			// horizontal meta will still perform the value comparison.
			if(!empty($meta["orderby"])) {
				unset($query_vars["meta_value"]);
				$query_vars["meta_key"] = $key;
			} else {
				unset($query_vars["meta_key"]);
				unset($query_vars["meta_value"]);
				unset($query_vars["meta_compare"]);
				unset($query_vars["meta_type"]);
			}
		}

		$query_vars["meta_query"] = $meta_query;
		$query_vars["_horzm_meta_query"] = $output["extracted"];
	}


	public function post_groupby($groupby, $query) {
		$this->library("hm_mappings_library");
		return $groupby;
	}

	/**
	 * Rewrite the order by.
	 *
	 * @param $orderby
	 * @param $query
	 * @return mixed
	 */
	public function post_orderby($orderby, $query) {
		global $wpdb;

		if(empty($query->query_vars["_horzm_meta_query"])) {
			return $orderby;
		}

		// Grab the horzm_meta_query from the $query object.
		// We use this to find which key was requested to be ordered.
		// There other ways to do this but, whatever...
		$this->library("hm_mappings_library");
		$meta_key_prefix = $this->hm_mappings_library->get_meta_key_prefix();
		$mappings = $this->hm_mappings_library->get_post_mappings();
		$horz_query = $query->query_vars["_horzm_meta_query"];
		foreach($horz_query as $meta) {
			if(!empty($meta["orderby"])) {
				// we need to build this item into the order by statement
				if(substr(strtolower($meta["key"]), 0, strlen($meta_key_prefix)) == $meta_key_prefix) {
					$key = substr($meta["key"], strlen($meta_key_prefix));
				} else {
					$key = $meta["key"];
				}

				if($this->hm_mappings_library->meta_key_mapped("post", $key)) {
					$orderby = str_replace($wpdb->postmeta . ".meta_value", "hm_meta." . $mappings[$key]["column"], $orderby);
					break;
				}
			}
		}

//		if($query->query_vars["nathan"]) {
//			print $orderby;
//			exit;
//		}

		return $orderby;
	}

	public function post_join($join, $query) {
		//print $join . "\n\n";
		//return $join;

		global $wpdb;

		$this->library("hm_mappings_library");

		if(!empty($query->query_vars["_horzm_meta_query"])) {
			$db_table = $this->hm_mappings_library->get_db_table_by_type("post");
			$join .= " INNER JOIN {$db_table} as hm_meta ON hm_meta.obj_id = {$wpdb->posts}.ID ";
			//LEFT OUTER
		}

		return $join;
	}

	public function post_where($where, $query) {
//		if($query->query_vars["nathan"]) {
//			print $where . "\n\n";
//			//exit;
//		}
//		//return $where;

		global $wpdb;

		$this->library("hm_mappings_library");

		// Don't do anything if we don't need to.
		if(empty($query->query_vars["_horzm_meta_query"])) {
			return $where;
		}

		$relation = $query->meta_query->relation;
		if(empty($relation)) $relation = "AND";

		$trail_bracket = false;
		$where = trim($where);
		if(substr($where, strlen($where)-1, 1) == ")" && !empty($query->meta_query->queries)) {
			$where = substr($where, 0, strlen($where)-1);
			$trail_bracket = true;
		}

//		if($query->query_vars["nathan"]) {
//			//print $where . "\n\n";
//			print_r($query->meta_query->queries);
//			exit;
//		}

		// add a where clauses for
		$mappings = $this->hm_mappings_library->get_post_mappings();
		$generated_where = "";
		foreach($query->query_vars["_horzm_meta_query"] as $meta) {
			$key = $meta["key"];
			if($this->hm_mappings_library->meta_key_mapped("post", $key)) {
				$generated_where .= $this->generate_where($meta, $relation, $mappings);
			}
		}

		if(empty($query->meta_query->queries)) {
			$where .= " AND (" . preg_replace('/\s' . $relation . '/', '', $generated_where, 1) . ") ";
		} else {
			$where .= $generated_where;
		}

		if($trail_bracket) {
			$where .= " ) ";
		}
//
//		if($query->query_vars["nathan"]) {
//			print $where;
//			exit;
//		}

		return $where;
	}

	function get_cast_for_type($type, $data_type) {
		if(empty($type))
			return '';

		$meta_type = strtoupper($type);

		if(!in_array($meta_type, array('BINARY', 'CHAR', 'DATE', 'DATETIME', 'DECIMAL', 'SIGNED', 'TIME', 'UNSIGNED', 'NUMERIC')))
			return '';

		if($meta_type == 'NUMERIC')
			$meta_type = 'SIGNED';

		if($data_type == "string" && $meta_type == "CHAR")
			$meta_type = "";

		if($data_type == "int" && $meta_type == "SIGNED")
			$meta_type = "";

		if($data_type == "int" && $meta_type == "UNSIGNED")
			$meta_type = "";

		return $meta_type;
	}

	private function generate_where($meta, $relation, $mappings) {
		global $wpdb;

		$meta_key = !empty($meta["key"]) ? $meta["key"] : "";
		$meta_value = isset($meta["value"]) ? $meta["value"] : null;
		$meta_compare = strtoupper(!isset($meta["compare"]) ? (is_array($meta_value) ? "IN" : "=") : $meta["compare"]);
		$meta_type = !empty($meta["type"]) ? $meta["type"] : "";

		if(empty($mappings[$meta_key]))
			return "";

		$mapping = $mappings[$meta_key];
		$cast_type = $this->get_cast_for_type($meta_type, $mapping["data_type"]);

		if(!empty($cast_type)) {
			$column_name = "cast(hm_meta." . $mapping["column"] . " as " . $cast_type . ")";
		} else {
			$column_name = "hm_meta." . $mapping["column"];
		}

		$supported_comparisons = array(
			'=', '!=', '>', '>=', '<', '<=',
			'LIKE', 'NOT LIKE',
			'IN', 'NOT IN',
			'BETWEEN', 'NOT BETWEEN',
			'NOT EXISTS', 'EXISTS',
			'REGEXP', 'NOT REGEXP', 'RLIKE'
		);

		if (!in_array($meta_compare, $supported_comparisons))
			$meta_compare = '=';

		if(!isset($meta["compare"]) && !isset($meta["value"])) {
			// do an exists check
			$where = " {$relation} NOT {$column_name} IS NULL ";
			return $where;
		} else if ($meta_compare == 'NOT EXISTS' ) {
			$where = " {$relation} {$column_name} IS NULL ";
			return $where;
		} else if ($meta_compare == 'EXISTS' ) {
			$where = " {$relation} NOT {$column_name} IS NULL ";
			return $where;
		}

		if(is_null($meta_value))
			return "";

		if(in_array($meta_compare, array('IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN'))) {
			if (!is_array($meta_value))
				$meta_value = preg_split( '/[,\s]+/', $meta_value);

			if(empty($meta_value)) {
				return "";
			}
		} else {
			$meta_value = trim($meta_value);
		}

		if (substr($meta_compare, 0, 2) == 'IN') {
			$meta_compare_string = '(' . substr(str_repeat(',%s', count($meta_value)), 1) . ')';
		} else if (substr( $meta_compare, 0, 7) == 'BETWEEN') {
			$meta_value = array_slice($meta_value, 0, 2);
			$meta_compare_string = '%s AND %s';
		} else if (substr($meta_compare, 0, 4) == 'LIKE') {
			$meta_value = '%' . wpdb::esc_like($meta_value) . '%';
			$meta_compare_string = '%s';
		} else {
			$meta_compare_string = '%s';
		}

		$where = $wpdb->prepare(" {$relation} {$column_name} {$meta_compare} {$meta_compare_string} ", $meta_value);

		return $where;
	}

}