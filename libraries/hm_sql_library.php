<?php

class hm_sql_library extends hmeta_library_base {

	/**
	 * Build the sql parts used to query the database for HM data. This will return the parts of the query so they can be changed if needed.
	 *
	 * @param $type post|user
	 */
	public function build_sql($type) {
		$this->library("hm_mappings_library");
		$mappings = $this->hm_mappings_library->get_mappings_by_type($type);

		if(empty($mappings))
			return false;

		if($type=="user") {
			return $this->build_user_sql($mappings);
		}  else {
			return $this->build_post_sql($mappings);
		}
	}

	private function build_user_sql($mappings) {
		global $wpdb;
		$output = array();
		$output["select"] = $this->get_select($mappings);
		$output["from"] = " FROM " . $wpdb->users . " LEFT OUTER JOIN " . $wpdb->prefix . "usermeta_hm as hm_meta ON " . $wpdb->users . ".ID = hm_meta.obj_id ";
		return $output;
	}

	private function build_post_sql($mappings) {
		global $wpdb;
		$output = array();
		$output["select"] = $this->get_select($mappings);
		$output["from"] = " FROM " . $wpdb->posts . " LEFT OUTER JOIN " . $wpdb->prefix . "postmeta_hm as hm_meta ON " . $wpdb->posts . ".ID = hm_meta.obj_id ";
		return $output;
	}

	private function get_select($mappings) {
		$select = "";
		foreach($mappings as $key=>$mapping) {
			$select .= ($select != "" ? ", " : "") . $mapping["column"] . " AS " . $key;
		}
		return "SELECT " . $select;
	}

}