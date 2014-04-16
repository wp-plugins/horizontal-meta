<?php

defined( 'ABSPATH' ) OR exit;

/**
 * Remove the HM mappings from the system before deactivating the plugin.
 * Archived mappings are stored in an archive mappings option which is used to reinstate the mappings if the plugin is reactivated.
 *
 * @param $blog_id
 * @param $lib
 * @param $log
 */
function hm_clear_mappings($blog_id, $lib) {

	// do posts
	$post_mappings = $lib->get_post_mappings();
	foreach($post_mappings as $meta_key => $mapping) {
		$lib->delete_assigned_mapping("post", $meta_key, true);
	}

	// save an archive of the mappings incase the plugin is reactivate we can restore them.
	if(empty($post_mappings)) {
		delete_option("hm_postmeta_mappings_archive");
	} else {
		update_option("hm_postmeta_mappings_archive", $post_mappings);
	}

	if($blog_id == 1) {
		$user_mappings = $lib->get_user_mappings();

		foreach($user_mappings as $meta_key => $mapping) {
			$lib->delete_assigned_mapping("user", $meta_key, true);
		}

		// save an archive of the mappings incase the plugin is reactivate we can restore them.
		if(empty($user_mappings)) {
			delete_option("hm_usermeta_mappings_archive");
		} else {
			update_option("hm_usermeta_mappings_archive", $user_mappings);
		}

	}

}

function hm_reinstate_data($current_blog_id, $mappings_lib) {
	global $wpdb;

	// Reinstate Post Data
	$archived_post_mappings = maybe_unserialize(get_option("hm_postmeta_mappings_archive"));

	if(!empty($archived_post_mappings)) {
		foreach($archived_post_mappings as $meta_key => $mapping) {
			$index = str_replace($mapping["data_type"], "", $mapping["column"]);
			if(is_numeric($index)) {
				$error_message = "";
				$result = $mappings_lib->assign_manual_mapping("post", $meta_key, $mapping["data_type"], $index, true, true, $error_message);
				if(empty($result)) {
				}
			} else {
			}
		}
	}

	if($current_blog_id == 1 || empty($current_blog_id)) {
		// Reinstate User data
		$archived_user_mappings = maybe_unserialize(get_option("hm_usermeta_mappings_archive"));

		if(!empty($archived_user_mappings)) {
			foreach($archived_user_mappings as $meta_key => $mapping) {
				$index = str_replace($mapping["data_type"], "", $mapping["column"]);
				if(is_numeric($index)) {
					$error_message = "";
					$result = $mappings_lib->assign_manual_mapping("user", $meta_key, $mapping["data_type"], $index, true, true, $error_message);
					if(empty($result)) {
					}
				} else {
				}
			}
		}

	}

}

function create_hm_meta_tables($prefix, $current_blog_id = false) {
	global $wpdb;

	$sql = "CREATE TABLE IF NOT EXISTS {$prefix}postmeta_hm (
			`obj_id` int(11) NOT NULL,
		    `string1` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
		    `string2` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
		    `string3` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
		    `string4` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
		    `string5` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
		    PRIMARY KEY (`obj_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

	dbDelta($sql);

	if($current_blog_id == 1 || empty($current_blog_id)) {
		$sql = "CREATE TABLE IF NOT EXISTS {$prefix}usermeta_hm (
				`obj_id` int(11) NOT NULL,
			    `string1` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
			    `string2` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
			    `string3` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
			    `string4` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
			    `string5` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
			    PRIMARY KEY (`obj_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

		dbDelta($sql);
	}
}
