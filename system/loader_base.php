<?php

if(!class_exists("hmeta_loader_base")) {

	class hmeta_loader_base {

		function __construct() {}

		/**
		 * getter to use loaded models that are stored in a global class
		 */
		function &__get($name) {

			$plugin = $this->get_plugin_name();

			$GLOBALS["hmeta"]["models"] = (empty($GLOBALS["hmeta"]["models"]) ? array() : $GLOBALS["hmeta"]["models"]);
			$GLOBALS["hmeta"]["libraries"] = (empty($GLOBALS["hmeta"]["libraries"]) ? array() : $GLOBALS["hmeta"]["libraries"]);

			// retrieve the loaded model if found
			foreach($GLOBALS["hmeta"]["models"] as $namespace=>$inst) {
				if($namespace == $name)
					return $GLOBALS["hmeta"]["models"][$namespace];
			}

			// retrieve a loaded library if one is loaded
			foreach($GLOBALS["hmeta"]["libraries"] as $namespace=>$inst) {
				if($namespace == $name)
					return $GLOBALS["hmeta"]["libraries"][$namespace];
			}

			return null;

		}

		/**
		 * The generic classes may be used in multiple plugins, so we use reflection to get the called class to determine what plugin is loaded.
		 */
		function get_plugin_name() {
			$pathinfo = pathinfo(plugin_basename(dirname(__FILE__)));
			$plugin_name = $pathinfo["dirname"];
			return $plugin_name;
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
			$GLOBALS["hmeta"]["models"] = (empty($GLOBALS["hmeta"]["models"]) ? array() : $GLOBALS["hmeta"]["models"]);
			$GLOBALS["hmeta"]["controllers"] = (empty($GLOBALS["hmeta"]["controllers"]) ? array() : $GLOBALS["hmeta"]["controllers"]);

			foreach($GLOBALS["hmeta"]["models"] as $loaded_model=>$inst) {
				if($loaded_model == $namespace) return true;
			}

			if(file_exists($plugin_dir . "/models/" . $model)) {
				include_once $plugin_dir . "/models/" . $model;

				// store a new instance of the model
				$GLOBALS["hmeta"]["models"][$namespace] = new $model_base();
				return $GLOBALS["hmeta"]["models"][$namespace];
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
			$GLOBALS["hmeta"]["libraries"] = (empty($GLOBALS["hmeta"]["libraries"]) ? array() : $GLOBALS["hmeta"]["libraries"]);

			foreach($GLOBALS["hmeta"]["libraries"] as $loaded_library=>$inst) {
				if($loaded_library == $namespace) return true;
			}

			if(file_exists($plugin_dir . "/libraries/" . $library)) {
				include_once $plugin_dir . "/libraries/" . $library;

				// store a new instance of the model
				$GLOBALS["hmeta"]["libraries"][$namespace] = new $library_base();
				return $GLOBALS["hmeta"]["libraries"][$namespace];
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