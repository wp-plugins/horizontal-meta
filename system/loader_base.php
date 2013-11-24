<?php

if(!class_exists("hmeta_loader_base")) {

	class hmeta_loader_base {

		function __construct() {}

		/**
		 * getter to use loaded models that are stored in a global class
		 */
		function &__get($name) {

			$plugin = $this->get_plugin_name();

			$GLOBALS["hmeta"][$plugin]["models"] = (empty($GLOBALS["hmeta"][$plugin]["models"]) ? array() : $GLOBALS["hmeta"][$plugin]["models"]);
			$GLOBALS["hmeta"][$plugin]["libraries"] = (empty($GLOBALS["hmeta"][$plugin]["libraries"]) ? array() : $GLOBALS["hmeta"][$plugin]["libraries"]);

			// retrieve the loaded model if found
			foreach($GLOBALS["hmeta"][$plugin]["models"] as $namespace=>$inst) {
				if($namespace == $name)
					return $GLOBALS["hmeta"][$plugin]["models"][$namespace];
			}

			// retrieve a loaded library if one is loaded
			foreach($GLOBALS["hmeta"][$plugin]["libraries"] as $namespace=>$inst) {
				if($namespace == $name)
					return $GLOBALS["hmeta"][$plugin]["libraries"][$namespace];
			}

			return null;

		}

		/**
		 * The generic classes may be used in multiple plugins, so we use reflection to get the called class to determine what plugin is loaded.
		 */
		function get_plugin_name() {
			$reflection = new ReflectionClass(get_called_class());
			$filename = $reflection->getFileName();
			$filename = str_replace(ABSPATH . "wp-content/plugins/","",$filename);
			if(strpos($filename,"/") !== false) {
				$plugin = substr($filename, 0, strpos($filename,"/"));
				return $plugin;
			} else {
				return false;
			}
		}

		/**
		 * Load a model into memory.
		 * This will load a model into each loaded controller's isntance
		 */
		function model($model, $namespace="") {
			$plugin = $this->get_plugin_name();
			$plugin_dir = ABSPATH . "/wp-content/plugins/" . $plugin;

			// make sure we are referencing a php file
			$model_path = pathinfo($model);
			if(substr($model, strlen($model)-4) != ".php") $model .= ".php";
			if(empty($namespace)) $namespace = $model_path["filename"];
			$model_base = $model_path["filename"];


			// check to see if the model has already been loaded
			$GLOBALS["hmeta"][$plugin]["models"] = (empty($GLOBALS["hmeta"][$plugin]["models"]) ? array() : $GLOBALS["hmeta"][$plugin]["models"]);
			$GLOBALS["hmeta"][$plugin]["controllers"] = (empty($GLOBALS["hmeta"][$plugin]["controllers"]) ? array() : $GLOBALS["hmeta"][$plugin]["controllers"]);

			foreach($GLOBALS["hmeta"][$plugin]["models"] as $loaded_model=>$inst) {
				if($loaded_model == $namespace) return true;
			}

			if(file_exists($plugin_dir . "/models/" . $model)) {
				include_once $plugin_dir . "/models/" . $model;

				// store a new instance of the model
				$GLOBALS["hmeta"][$plugin]["models"][$namespace] = new $model_base();
				return $GLOBALS["hmeta"][$plugin]["models"][$namespace];
			} else {
				return false;
			}
		}

		/**
		 * Load a library into memory.
		 * This will load a model into each loaded controller's isntance
		 */
		function library($library, $namespace="") {
			$plugin = $this->get_plugin_name();
			$plugin_dir = ABSPATH . "/wp-content/plugins/" . $plugin;

			// make sure we are referencing a php file
			$library_path = pathinfo($library);
			if(substr($library, strlen($library)-4) != ".php") $library .= ".php";
			if(empty($namespace)) $namespace = $library_path["filename"];
			$library_base = $library_path["filename"];


			// check to see if the library has already been loaded
			$GLOBALS["hmeta"][$plugin]["libraries"] = (empty($GLOBALS["hmeta"][$plugin]["libraries"]) ? array() : $GLOBALS["hmeta"][$plugin]["libraries"]);

			foreach($GLOBALS["hmeta"][$plugin]["libraries"] as $loaded_library=>$inst) {
				if($loaded_library == $namespace) return true;
			}

			if(file_exists($plugin_dir . "/libraries/" . $library)) {
				include_once $plugin_dir . "/libraries/" . $library;

				// store a new instance of the model
				$GLOBALS["hmeta"][$plugin]["libraries"][$namespace] = new $library_base();
				return $GLOBALS["hmeta"][$plugin]["libraries"][$namespace];
			} else {
				return false;
			}

		}

		/**
		 * Widgets
		 */
		function widget($widget) {
			$plugin = $this->get_plugin_name();
			$plugin_dir = ABSPATH . "/wp-content/plugins/" . $plugin;

			// make sure we are referencing a php file
			$widget = $widget . "/" . $widget . ".php";
			$widget_path = pathinfo($widget);
			$widget_base = $widget_path["filename"];

			if(file_exists($plugin_dir . "/widgets/" . $widget)) {
				require_once $plugin_dir . "/widgets/" . $widget;
			} else {
				return false;
			}

		}

		/**
		 * external
		 */
		function external($script) {
			$plugin = $this->get_plugin_name();
			$plugin_dir = ABSPATH . "/wp-content/plugins/" . $plugin . "/3rdparty";

			// make sure we are referencing a php file
			$script = rtrim($script, ".php");
			$script = $script . ".php";

			if(file_exists($plugin_dir . "/" . $script)) {
				require_once $plugin_dir . "/" . $script;
			} else {
				return false;
			}

		}

		/**
		 * Load a view to display on the page
		 */
		function view($view, $data=array(), $return=false) {
			$plugin = $this->get_plugin_name();

			$templates_dir = array(get_template_directory() . "/views/", ABSPATH . "/wp-content/plugins/" . $plugin . "/views/");
			$templates_dir = apply_filters("hmeta_get_views_paths", $templates_dir, $plugin);

			if(substr($view, strlen($view)-4) != ".php") $view .= ".php";
			$view_path = pathinfo($view);

			foreach($templates_dir as $template_dir) {
				if(file_exists($template_dir . $view)) {

					extract($data);

					if($return) ob_start();
					include $template_dir . $view;
					if($return) $contents = ob_get_clean();

					return $contents;
				}
			}

			return false;
		}

		function get_plugin_dir() {
			$plugin_name = $this->get_plugin_name();
			return ABSPATH . "wp-content/plugins/" . $plugin_name;
		}

		function get_plugin_url() {
			$plugin_name = $this->get_plugin_name();
			$url = plugins_url() . "/" . $plugin_name;
			return $url;
		}

	}
}