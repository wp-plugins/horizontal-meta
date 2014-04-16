<?php

class hm_settings_model extends hmeta_model_base {

	/**
	 * Load the settings used to run this plugin
	 */
	function get_settings($blog_id = false) {
		global $wpdb;
		if($blog_id !== false && hm_is_multisite() && $blog_id !== get_current_blog_id()) {
			// Get setting from another blog.
			// This will always be used for retrieving user settings from the root blog.
			$table = $wpdb->base_prefix . ($blog_id !== 1 ? $blog_id . "_" : "") . "options";
			$settings = maybe_unserialize($wpdb->get_var("Select option_value From {$table} Where option_name = 'horizontal_meta_settings'"));
			if(empty($settings)) {
				$settings = $this->get_default_settings();
			}
		} else {
			$settings = maybe_unserialize(get_option("horizontal_meta_settings"));
			if(empty($settings)) {
				$settings = $this->get_default_settings();
				$this->save_settings($settings);
			}
		}

		if($settings["post_override_suppress_filters"] == "") $settings["post_override_suppress_filters"] = "0";
		if($settings["post_override_disable_other_filters"] == "") $settings["post_override_disable_other_filters"] = "0";
		if($settings["post_rewrite_queries"] == "") $settings["post_rewrite_queries"] = "1";
		if($settings["user_rewrite_queries"] == "") $settings["user_rewrite_queries"] = "1";
		if($settings["post_intercept_keys"] == "") $settings["post_intercept_keys"] = "0";
		if($settings["user_intercept_keys"] == "") $settings["user_intercept_keys"] = "0";
		if(empty($settings["license_key"])) $settings["license_key"] = "";

		return $settings;
	}

	/**
	 * Save plugin settings
	 */
	function save_settings($settings) {
		update_option("horizontal_meta_settings", $settings);
	}

	function get_default_settings() {
		$settings = array(
			"post_override_suppress_filters" => "0",
			"post_override_disable_other_filters" => "0",
			"post_rewrite_queries" => "1",
			"user_rewrite_queries" => "1",
			"post_intercept_keys" => "0",
			"user_intercept_keys" => "0",
		);
		return $settings;
	}

}