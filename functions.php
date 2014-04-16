<?php

function hm_is_multisite() {
	if(function_exists('is_multisite') && is_multisite()) {
		return true;
	} else {
		return false;
	}
}

/**
 * SQL Statement helper
 */
function hm_build_post_sql() {
	hm_controller::$instance->library("hm_sql_library");
	return hm_controller::$instance->hm_sql_library->build_sql("post");
}

/**
 * SQL Statement helper
 */
function hm_build_user_sql() {
	hm_controller::$instance->library("hm_sql_library");
	return hm_controller::$instance->hm_sql_library->build_sql("user");
}