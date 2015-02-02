<?php

if(!class_exists("hmeta_controller_base")) {

	class hmeta_controller_base extends hmeta_loader_base {

		/**
		 * @var array Array of predefined notices
		 */
		protected $notice_maps = array();

		/**
		 * @var array List of custom url hooks
		 */
		private $url_hooks = array();

		/**
		 * @var array Admin panels notices.
		 */
		protected $notice_queue = array(
			"errors" => array(),
			"notice" => array()
		);

		protected $default_load_priority = 2;

		protected $default_url_hook_load_priority = 0;

		/**
		 * Constructor
		 */
		function __construct() {
			// Process any custom url hooks
			add_action("init", array($this, "process_url_hooks"), $this->get_url_hook_load_priority());

			//$controller_name = $this->get_controller_name();
			//add_action("load-" . $controller_name, array($this, "load_controller_page"));

			$this->apply_generic_notices();

			$this->init();
		}

		/**
		 * Fired after __construct()
		 */
		function init() {}

		/**
		 * Get the current uri without the WordPress install directory/domain.
		 */
		function get_current_script() {
			$https = @$_SERVER["HTTPS"];
			$host = $_SERVER["HTTP_HOST"];
			$site_url = get_bloginfo("url");
			$uri = $_SERVER["REQUEST_URI"];
			$base_url = "";
			if(!empty($https)) $base_url = "https://";
			if(empty($https)) $base_url = "http://";
			$base_url .= $host . "/";

			$wpdir = str_replace($base_url, "", $site_url) . "/";
			$uri = str_replace($wpdir,"", $uri);

			return $uri;
		}

		function process_url_hooks() {

			$current_script = $this->get_current_script();

			foreach($this->url_hooks as $url_hook) {
				$url_pattern = $url_hook[0];
				$function = $url_hook[1];
				$admin_base = $url_hook[2];

				if($admin_base) $url_pattern = "/wp-admin" . $url_pattern;
				$url_pattern = '/' . str_replace("/", '\/', $url_pattern) . '/i';

				if((bool)@preg_match($url_pattern, $current_script, $matches, PREG_OFFSET_CAPTURE) === true) {
					// Found a hook match
					if(is_callable($function)) {

						// pass matches
						$params = array();
						for($i=1;$i<count($matches);$i++) {
							$params[] = $matches[$i][0];
						}
						$params = array($params, $current_script);

						call_user_func_array($function, $params);
					}
				}

			}
		}

		/**
		 * Apply any notices that were passed from a GET or POST
		 * Applies only for admin pages
		 */
		function apply_generic_notices() {
			@session_start();
			$generice_notice = (empty($_SESSION["notice"]) ? "" : $_SESSION["notice"]);
			if(!empty($_REQUEST["notice"])) $generice_notice = $_REQUEST["notice"];

			if(!empty($generice_notice)) {
				$this->notice_maps = $this->get_generic_notices();

				$displays = explode(",", $generice_notice);
				$displays = empty($displays) ? array() : $displays;
				$this->notice_maps = (empty($this->notice_maps) || !is_array($this->notice_maps) ? array() : $this->notice_maps);
				foreach($this->notice_maps as $notice_key=>$notice) {
					foreach($displays as $display) {
						if($display == $notice_key) {
							$notice_type = (is_array($notice) && count($notice) > 1 ? $notice[0] : "notice");
							$notice_text = (is_array($notice) && count($notice) > 1 ? $notice[1] : $notice);
							$this->add_admin_notice($notice_type, $notice_text);
						}
					}
				}
			}
		}

		/**
		 * Function this is called after the class has been instanced.
		 */
		function init_hooks() {
			if(is_admin()) {
				// Call for setting up admin pages in plugin
				add_action('admin_menu', array($this, 'register_pages'), $this->get_load_priority());
				add_action("admin_init", array($this, "admin_init"), $this->get_load_priority());
			} else {

			}

			add_action("init", array($this, "init_action"));

			$this->init_hooks_complete();
		}

		function init_action() {}

		function init_hooks_complete() { }

		/**
		 * Add a menu page for the admin.
		 * register_pages is called from admin_menu hook. register_pages shoudl then invoke the register_page function.
		 * register_page should not be called outside the register_pages function.
		 */
		function register_page($page_title, $menu_title, $cap, $icon, $position) {
			$controller = $this->get_controller_name();
			$screen_id = add_menu_page($page_title, $menu_title, $cap, $controller, array($this, "dispatch"), $icon, $position);
			return $screen_id;
		}

		/**
		 * Add a menu page for the admin.
		 * register_pages is called from admin_menu hook. register_pages shoudl then invoke the register_page function.
		 * register_page should not be called outside the register_pages function.
		 */
		function register_sub_page($parent_slug, $page_title, $menu_title, $capability) {
			$controller = $this->get_controller_name();
			$screen_id = add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $controller, array($this, "dispatch"));
			return $screen_id;
		}

		/**
		 * Help function to add additional hooks.
		 */
		function hook($action, $function, $param_count = false, $hook_priority=false) {
			$hook_priority = (empty($hook_priority) ? $this->get_load_priority() : $hook_priority);

			if(!empty($param_count)) {
				add_action($action, $function, $hook_priority, $param_count);
			} else {
				add_action($action, $function, $hook_priority);
			}
		}

		/**
		 * Hook a particular url call using a Regex formatted url string
		 */
		public function hook_url($url_regex, $function, $admin_base, $hook_on="init", $hook_priority=0) {
			$this->url_hooks[] = array($url_regex, $function, $admin_base, $hook_on);
		}

		/**
		 * Returns the load priority for a class when it calls it's default hooks.
		 * Users may override this function to change the loading priority.
		 */
		function get_load_priority() {
			return $this->default_load_priority;
		}

		/**
		 * Load priority for hooking custom urls. This is the priority when firing the init action.
		 */
		function get_url_hook_load_priority() {
			return $this->default_url_hook_load_priority;
		}

		function get_controller_name() {
			$class_name = get_class($this);
			$class_name = str_replace("_controller", "", $class_name);
			return $class_name;
		}

		/**
		 * Called to route to a function in controller when the ?page= parameter invokes it.
		 */
		function dispatch() {
			if(is_admin()) {

				//$page = $_REQUEST["page"];
				$action = $_REQUEST["action"];

				//if(empty($action) && !empty($page))
				//	$action = $page;

				$action = (empty($action) ? "index" : $action);
				$action = ($action=="-1" ? "index" : $action);

				$callable = array($this, $action);

				$mappings = $this->get_action_mappings();
				if(!empty($mappings) && is_array($mappings)) {
					if(array_key_exists($action, $mappings)) {
						$callable = $mappings[$action];
					}
				}

				if(is_callable($callable)) {
					call_user_func($callable, $action);
				}
			}
		}

		/**
		 * This is called when an admin page is being loaded.
		 * load-{controller_name} calls this callback.
		 *
		 * We use this to distribute actions if we need to
		 * MESSY NEEDS RECODING
		 */
		function load_controller_page() {
			$action = $_REQUEST["action"];
			$controller_name = $this->get_controller_name();
			if(!empty($action) && $_SERVER["REQUEST_METHOD"] == "POST" && substr($action, 0, strlen($controller_name)) == $controller_name) {
				$callable = array($this, $action);
				if(is_callable($callable)) {
					call_user_func($callable, $action);
				}
			}
		}

		/*******************
		 * Add new notice to the queue
		 */
		function add_admin_notice($notice_type, $notice, $screens=array()) {
			$this->notice_queue[$notice_type][] = array(
				"notice" => $notice,
				"screens" => $screens
			);
		}

		/**
		 * Alias to show_admin_notice()
		 */
		function show_user_notice($return) {
			return $this->show_admin_notice($return);
		}

		function show_admin_notice($return = false) {
			global $current_screen;

			if($return) ob_start();

			foreach($this->notice_queue as $notice_type=>$notices) {
				foreach($notices as $notice) {
					if(is_array($notice)) {
						if(!empty($notice["screens"])) {
							$found_screen = array();
							foreach($notice["screens"] as $screen) {
								if($current_screen->parent_base == $screen) {
									$found_screen = true;
									break;
								}
							}
							if(!$found_screen) continue;
						}

						$notice = $notice["notice"];
					}

					if($notice_type == "error") {
						?>
					<div class="error"><p><?php print $notice; ?></p></div>
					<?php
					} else {
						?>
					<div class="notice updated fade"><p><?php print $notice; ?></p></div>
					<?php
					}
				}
			}

			unset($_SESSION["notice"]);

			if($return) {
				$content = ob_get_clean();
				return $content;
			}
		}

		/**
		 * Overridable used to set up additional notices
		 */
		function get_generic_notices() {}

		/*************************
		 * Overridable admin hook admin_init hook
		 *************************/
		function admin_init() { }

		/*************************
		 * Overridable admin hook admin_menu hook
		 *************************/
		function register_pages() {}

		/**
		 * Used to map ?page=*&action=xxx to a particular function overriding the default of sending it to xxx
		 */
		function get_action_mappings() {}

	}
}