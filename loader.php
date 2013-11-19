<?php
/*
Plugin Name: Horizontal Meta
Plugin URI: http://wordpress.org/plugins/horizontal-meta/
Description: Alters the way wordpress handles meta data to allow for queryable datatyped fields and performance.
Version: 0.1
Author: Nathan Franklin
Author URI: http://www.nathanfranklin.com.au
License:
Network: true
*/

ini_set("display_errors", "On");
error_reporting(E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR );

// General setup... getting plugin ready to load.

defined( 'ABSPATH' ) OR exit;

// include system file for loading plugin.
include  dirname(__FILE__) . '/system/plugin_init.php';

// init functions
if(file_exists(dirname(__FILE__) . '/functions.php')) {
	require_once dirname(__FILE__) . '/functions.php';
}

// Load languages
load_plugin_textdomain('horizontal-meta', false, basename(dirname(__FILE__)) . '/languages');

hmeta_load_init(basename(dirname(__FILE__)));
//add_action('plugins_loaded', $init_func, 0);

// Load moduled functions.
$function_files = scandir(dirname(__FILE__) . "/functions/");
foreach($function_files as $function_file) {
	if(substr($function_file, -4) == ".php") {
		require_once dirname(__FILE__) . "/functions/" . $function_file;
	}
}

register_activation_hook(__FILE__, 'horizontal_meta_activate');
register_deactivation_hook(__FILE__, 'horizontal_meta_deactivate');














/**
 * Ensure the we create the additional meta_tables needed for the new blog.
 */
function new_blog($blog_id, $user_id, $domain, $path, $site_id, $meta ) {
	global $wpdb;

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	require_once "system/loader_base.php";
	require_once "system/library_base.php";
	require_once "libraries/hm_mappings_library.php";
	require_once "libraries/hm_log_library.php";
	require_once plugin_dir_path( __FILE__ ) . "/setup.php";

	$current_blog = get_current_blog_id();

	if($current_blog != $blog_id)
		switch_to_blog($blog_id);

	// create our meta tables
	create_hm_meta_tables($wpdb->prefix, $blog_id);

	if($current_blog != $blog_id)
		restore_current_blog();

}
add_action( 'wpmu_new_blog', 'new_blog', 10, 100);



function horizontal_meta_deactivate() {
	global $wpdb;

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	require_once "system/loader_base.php";
	require_once "system/library_base.php";
	require_once "libraries/hm_mappings_library.php";
	require_once "libraries/hm_log_library.php";
	require_once plugin_dir_path( __FILE__ ) . "/setup.php";

	$lib = new hm_mappings_library();
	$log = new hm_log_library();

	$log->new_grouping();
	$log->add_to_log("/***************************\n *  Deactivating Plugin \n***************************/");

	if(!current_user_can( 'activate_plugins')) {
		$log->add_to_log("Could not deactivate plugin. User does not have permission.");
		return;
	}

	$plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
	check_admin_referer( "deactivate-plugin_{$plugin}" );

	$log->add_to_log("Proceeding to deactivate plugin.");

	// need to ensure we have enough time to run
	set_time_limit(1800);

	if (function_exists('is_multisite') && is_multisite()) {

		$current_blog = get_current_blog_id();

		$blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");

		$log->add_to_log("Multisite blogs to process:");
		$log->add_to_log(print_r($blogids, true));

		foreach ($blogids as $blog_id) {
			if($current_blog != $blog_id)
				switch_to_blog($blog_id);

			$log->add_to_log("Processing blog: " . $blog_id);

			// restore the meta data
			hm_clear_mappings($blog_id, $lib, $log);

			if($current_blog != $blog_id)
				restore_current_blog();
		}
	} else {

		// restore the meta data
		hm_clear_mappings(1, $lib, $log);
	}

	$log->add_to_log("Completed deactivation process.");
}

/**
 * @param bool $new_blog_id This will be passed when a new blog has just been created. This will force an activation for this blog.
 */
function horizontal_meta_activate() {
	global $wpdb;

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	require_once "system/loader_base.php";
	require_once "system/library_base.php";
	require_once "libraries/hm_mappings_library.php";
	require_once "libraries/hm_log_library.php";
	require_once plugin_dir_path( __FILE__ ) . "/setup.php";

	$lib = new hm_mappings_library();
	$log = new hm_log_library();

	$log->new_grouping();
	$log->add_to_log("/***************************\n *  Activating Plugin \n***************************/");

	if(!current_user_can( 'activate_plugins')) {
		$log->add_to_log("Could not activate plugin. User does not have permission.");
		return;
	}

	$plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
	check_admin_referer( "activate-plugin_{$plugin}" );

	$log->add_to_log("Proceeding to activate plugin.");

	// need to ensure we have enough time to run
	set_time_limit(1800);

	if (function_exists('is_multisite') && is_multisite()) {
		$current_blog = get_current_blog_id();

		$blogids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}");

		$log->add_to_log("Multisite blogs to process:");
		$log->add_to_log(print_r($blogids, true));

		foreach ($blogids as $blog_id) {
			if($current_blog != $blog_id)
				switch_to_blog($blog_id);

			$log->add_to_log("Processing blog: " . $blog_id);

			// create our meta tables
			create_hm_meta_tables($wpdb->prefix, $blog_id);

			$log->add_to_log("Reinstating data.");

			// restore the data that was originally in the table from the meta tables
			hm_reinstate_data($blog_id, $lib, $log);

			if($current_blog != $blog_id)
				restore_current_blog();
		}
	} else {

		$log->add_to_log("Single blog installation.");

		create_hm_meta_tables($wpdb->prefix);

		// restore the data that was originally in the table from the meta tables
		hm_reinstate_data(1, $lib, $log);
	}

	$log->add_to_log("Completed activation process.");

}

