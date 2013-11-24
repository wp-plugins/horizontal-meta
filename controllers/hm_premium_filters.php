<?php

class hm_premium_filters_controller extends hmeta_controller_base {

	function __construct() {
		parent::__construct();
		add_filter("horizontal_meta_get_data_types", array($this, "get_data_types"));
	}

	function get_data_types($data_types) {
		$this->library("hm_mappings_library");
		return $this->hm_mappings_library->get_data_types();
	}

}