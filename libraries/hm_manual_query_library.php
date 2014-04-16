<?php

/**
 * Class hm_manual_query_library
 */
class hm_manual_query_library extends hmeta_library_base {

	private $_post_values = array();
	private $_user_values = array();

	/**
	 * Load all the post mappings data into the posts object.
	 */
	function load_post_loop_values() {
		global $posts;

		if(!empty($posts)) {
			$ids = array();
			foreach($posts as $post) {
				$ids[] = $post->ID;
			}

			if(empty($ids))
				return false;

			$values = $this->load_values("post", $ids);
			if(!empty($values)) {
				$this->_post_values = $values + $this->_post_values; // $values will be merged into $this->_post_values while maintaining data from $values; SEE: http://stackoverflow.com/questions/3292044/php-merge-two-arrays-while-keeping-keys-instead-of-reindexing
			}
		}
	}

	/**
	 *
	 *
	 * @param $type post|user
	 * @param array|int $ids Either array of ids or single id.
	 * @return array|bool
	 */
	function get_values_by_type($type, $ids) {
		$id = false;
		$single = false;
		if(!is_array($ids) && is_numeric($ids)) {
			$id = $ids;
			if($this->is_data_loaded($type, $id)) {
				return $this->{"_" . $type . "_values"}[$id];
			}

			$ids = array($id);
			$single = true;
		}

		$output = $this->load_values($type, $ids);
		if(!empty($output)) {
			// save this value for runtime
			$this->{"_" . $type . "_values"} = $output + $this->{"_" . $type . "_values"}; // $values will be merged into $this->_post_values while maintaining data from $values; SEE: http://stackoverflow.com/questions/3292044/php-merge-two-arrays-while-keeping-keys-instead-of-reindexing

			if($single) {
				return $output[$id];
			} else {
				return $output;
			}
		} else {
			return false;
		}
	}

	private function load_values($type, $ids) {
		if(!is_array($ids))
			return false;

		global $wpdb;

		$this->library("hm_sql_library");
		$this->library("hm_mappings_library");

		$sql_builder = $this->hm_sql_library->build_sql($type);
		if(empty($sql_builder))
			return false;

		$mappings = $this->hm_mappings_library->get_mappings_by_type($type);

		$sql = $sql_builder["select"] . ", obj_id " . $sql_builder["from"];
		$sql .= " WHERE obj_id IN (" . implode(",", array_map($wpdb->escape, $ids)) . ") ";

		$rows = $wpdb->get_results($sql, ARRAY_A);
		$output = array();
		foreach($rows as $row) {
			$hm_row =  array();
			foreach($mappings as $key=>$mapping) {
				$hm_row[$key] = $row[$key];
			}

			$output[$row["obj_id"]] = $hm_row;
		}

		return $output;
	}

	function is_data_loaded($type, $obj_id) {
		if($type == "user") {
			if(array_key_exists($obj_id, $this->_user_values)) {
				return true;
			} else {
				return false;
			}
		} else {
			if(array_key_exists($obj_id, $this->_post_values)) {
				return true;
			} else {
				return false;
			}
		}
	}

}