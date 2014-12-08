<?php


if(!function_exists("hmeta_load_init")) {
	function hmeta_load_init($plugin_dir) {
		$GLOBALS["hmeta"] = (!empty($GLOBALS["hmeta"]) ? $GLOBALS["hmeta"] : array());
		$GLOBALS["hmeta"][$plugin_dir] = array();

		$plugins_dir = ABSPATH . "wp-content/plugins/";

		$init_file = $plugins_dir . $plugin_dir . "/init.php";
		if(file_exists($init_file)) {
			include $init_file;
		}

		// Dispatch plugin
		hmeta_load_runtime($plugin_dir);
	}
}

if(!function_exists("hmeta_load_runtime")) {

	function hmeta_load_runtime($plugin_dir) {
		$plugins_dir = ABSPATH . "wp-content/plugins/";

		// Load generic classes into memory
		$system_dir = $plugins_dir . $plugin_dir . "/system";

		if(file_exists($system_dir . "/loader_base.php")) include $system_dir . "/loader_base.php";
		if(file_exists($system_dir . "/controller_base.php")) include $system_dir . "/controller_base.php";
		if(file_exists($system_dir . "/model_base.php")) include $system_dir . "/model_base.php";
		if(file_exists($system_dir . "/library_base.php")) include $system_dir . "/library_base.php";

		// Load controllers for this runtime
		hmeta_load_controllers($plugin_dir);
	}
}

if(!function_exists("hmeta_load_controllers")) {
	/**
	 * Get a list of the available controllers to load for this plugin.
	 */
	function hmeta_load_controllers($plugin_dir) {
		$plugins_dir = ABSPATH . "wp-content/plugins/";

		$plugin = $plugin_dir;
		$controller_dir = $plugins_dir . $plugin_dir . "/controllers";

		$GLOBALS["hmeta"] = array();
		$GLOBALS["hmeta"]["controllers"] = array();

		// Load applicable controllers
		$target_dir = $controller_dir;

		if(file_exists($target_dir)) {
			$files = hmeta_get_files_in_folder($target_dir);

			foreach($files as $file) {
				$path_info = pathinfo($file);
				if(strtolower($path_info["extension"]) == "php") {
					include $file;
					$controller = $path_info["filename"];
					$controller_class = $controller . "_controller";
					$GLOBALS["hmeta"]["controllers"][$controller] = new $controller_class();
				}
			}
		}

		// initialise based on priority
		uasort($GLOBALS["hmeta"]["controllers"], "hmeta_controller_priority");

		// Load all the standard hooks needed for a controller.
		foreach($GLOBALS["hmeta"]["controllers"] as $controller=>$inst) {
			$GLOBALS["hmeta"]["controllers"][$controller]->init_hooks();
		}

	}

	function hmeta_get_files_in_folder($dir) {
		$file_list = array();

		$files = scandir($dir);
		foreach($files as $file) {
			if(!in_array($file, array(".",".."))) {
				if(is_dir($dir . "/" . $file)) {
					$file_list = array_merge($file_list, hmeta_get_files_in_folder($dir . "/" . $file));
				} else {
					$file_list[] = $dir . "/" . $file;
				}
			}
		}

		return $file_list;

	}

	function hmeta_controller_priority($a, $b) {
		if ($a->get_load_priority() == $b->get_load_priority()) {
			return 0;
		}
		return ($a->get_load_priority() < $b->get_load_priority()) ? -1 : 1;
	}
}