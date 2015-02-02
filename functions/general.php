<?php

/**
 * Retrieves the meta mappings stored in the database
 */
function get_hm_mappings() {
	hm_controller::$instance->library("hm_mappings_library");
	return hm_controller::$instance->hm_mappings_library->get_post_mappings();
}