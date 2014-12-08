<?php

class hm_controller extends hmeta_controller_base {

	public static $instance;

	function __construct() {

		self::$instance = $this;

		add_action("admin_action_hm_process_create_mapping", array($this, "process_create_mapping"));
		add_action("admin_action_hm_process_delete_mappings", array($this, "process_delete_mappings"));
		add_action("admin_action_hm_process_manage_update_data", array($this, "process_manage_update_data"));
		add_action("admin_action_hm_advanced_save", array($this, "advanced_save"));

		parent::__construct();
	}


	function init_action() {
		add_filter('set-screen-option', array($this, 'set_screen_option'), 10, 3);

		// Ajax
		add_action('wp_ajax_hm_key_test', array($this, 'ajax_test_key'));
		add_action('wp_ajax_hm_manage_load_data', array($this, 'ajax_load_manage_data'));
		add_action('wp_ajax_hm_remove_mapping_load_data', array($this, 'ajax_remove_mapping_load_data'));



	}

	function set_screen_option($status, $option, $value) {
		return $value;
	}

	/**
	 * admin_menu action call.
	 * Add additional pages to the admin dashboard.
	 */
	function register_pages() {
		$screen_id = $this->register_sub_page("options-general.php", "Horizontal Meta", "Horizontal Meta", "manage_options");

		// Create some screen options if they are needed.
		add_action("load-" . $screen_id,array($this, "add_mappings_screen_options"));
	}

	function add_mappings_screen_options() {
		$this->external("meta_key_listing_table");

		$option = 'per_page';
		$args = array(
			'label' => __('Mappings', 'horizontal-meta'),
			'default' => 10,
			'option' => 'mappings_per_page'
		);
		add_screen_option( $option, $args );

		// Used so screen options for column display and hiding can be set.
		new meta_key_listing_table();
	}

	function ajax_remove_mapping_load_data() {
		$meta_key = $_POST["meta_key"];
		$meta_type = $_POST["meta_type"];

		$this->library("hm_mappings_library");
		$this->library("hm_tools_library");

		$output = "";

		$meta_data = $this->hm_tools_library->get_merged_meta_data($meta_type, $meta_key);
		$meta_type_config = $this->hm_mappings_library->get_meta_type_config($meta_type);

		// Get a list of column names based on type

		$data = array(
			"meta_key" => $meta_key,
			"meta_type" => $meta_type,
			"meta_data" => $meta_data,
			"meta_type_config" => $meta_type_config
		);

		$html = $this->view("settings_remove_mapping", $data, true);
		$html = str_replace("\n", "", $html);
		$html = str_replace("\r", "", $html);
		$html = str_replace("'", "\\'", $html);

		$output .= "hm_set_data('" . $meta_type . "','" . $meta_key . "','" . $html . "');";


		print $output;
		exit;
	}

	/**
	 * AJAX call to load the data for a particular key.
	 * This will return the data stored in the wordpress meta table and the data stored in the horizontal table.
	 */
	function ajax_load_manage_data() {

		$meta_key = $_POST["meta_key"];
		$meta_type = $_POST["meta_type"];

		$this->library("hm_mappings_library");
		$this->library("hm_tools_library");

		$output = "";

		$meta_data = $this->hm_tools_library->get_merged_meta_data($meta_type, $meta_key);
		$meta_type_config = $this->hm_mappings_library->get_meta_type_config($meta_type);

		// Get a list of column names based on type

		$data = array(
			"meta_key" => $meta_key,
			"meta_type" => $meta_type,
			"meta_data" => $meta_data,
			"meta_type_config" => $meta_type_config
		);

		$html = $this->view("manage_data_load_data", $data, true);
		$html = str_replace("\n", "", $html);
		$html = str_replace("\r", "", $html);
		$html = str_replace("'", "\\'", $html);

		$output .= "hm_set_data('" . $meta_type . "','" . $meta_key . "','" . $html . "');";


		print $output;
		exit;
	}

	/**
	 * AJAX call to test the value of a particular key to ensure that the datatype conversion will be successful.
	 */
	function ajax_test_key() {

		$type = $_POST["type"];
		$key = $_POST["key"];
		$data_type = $_POST["data_type"];

		if(empty($type) || empty($key) || empty($data_type)) {
			// error
			$output = "hm_set_validation_errors(['" . __('A validation error has occurred.', 'horizontal-meta') . "']);hm_update_display();hm_hide_wait();";
		} else {
			$this->library("hm_mappings_library");

			$output = "";
			if($this->hm_mappings_library->is_meta_key_excluded($key)) {
				$output .= "hm_set_validation_errors(['" . __("Validation errors occurred while testing data. This meta key is in the exclusions list.", 'horizontal-meta') . "']);hm_update_display();hm_hide_wait();";
			} else if(!$this->hm_mappings_library->is_meta_key_compatible($type, $key)) {
				$output .= "hm_set_validation_errors(['" . __("Validation errors occurred while testing data. There are multiple entries for this by per object. Only single valued meta keys are supported.", 'horizontal-meta') . "']);hm_update_display();hm_hide_wait();";
			} else {
				$results = array();
				$result = $this->hm_mappings_library->test_data($type, $key, $data_type, $results);
				if(!$result) {
					$output = "hm_set_validation_errors(['" . __("Validation errors occurred while testing data. Please review the results of the data tested.", 'horizontal-meta') . "']);";
				}

				// Load the results for display
				$html = "";
				if(!empty($results)) {
					$data = array("results" => $results);
					$html = $this->view("test_results", $data, true);
					$html = str_replace("\n", "", $html);
					$html = str_replace("\r", "", $html);
					$html = str_replace("'", "\\'", $html);
				}

				// create a nonce value
				$nonce = wp_create_nonce($type . "|" . $key . "|" . $data_type);

				// lol ?
				$output .= "hm_set_test_results(" . ($result ? "true" : "false") . ",'" . htmlentities($type) . "','" . htmlentities($key) . "','" . $data_type . "','" . $html . "');
							hm_update_display();
							hm_set_nonce('" . $nonce . "');
							hm_hide_wait();";
			}
		}

		print $output;
		exit;
	}

	/**
	 * This is called after the user clicks "Update Data" on the manage data page.
	 * The user would be clicking this button if the data wasn't in sync between HM and WordPress
	 * If plugin/theme authors use the correct wordpress api to update meta then this shouldn't be an issue.
	 */
	function process_manage_update_data() {
		$this->library("hm_mappings_library");
		$this->library("hm_tools_library");

		$hm_manage_data = $_POST["hm_manage_data"];

		if(!empty($hm_manage_data) && is_array($hm_manage_data)) {
			foreach($hm_manage_data as $row) {
				$meta_type = $row["meta_type"];
				$meta_key = $row["meta_key"];
				$obj_id = $row["obj_id"];
				$meta_value = $row["meta_value"];
				$sync = ($row["sync"] == "on" ? true : false);

				if($sync) {
					$this->hm_tools_library->sync_key($meta_type, $meta_key, $obj_id, $meta_value);
				}
			}
		}

		header("Location: " . admin_url("options-general.php?page=hm&action=manage_data&notice=data_maintenance_success"));
		exit;
	}

	function process_create_mapping() {

		$this->library("hm_mappings_library");

		//mapping_nonce
		$mapping_nonce = $_POST["mapping_nonce"];
		$type = $_POST["new_type"];
		$data_type = $_POST["new_data_type"];
		$new_post_meta_key = $_POST["new_post_meta_key"];
		$new_user_meta_key = $_POST["new_user_meta_key"];
		$new_post_meta_key_other = $_POST["new_post_meta_key_other"];
		$new_user_meta_key_other = $_POST["new_user_meta_key_other"];

		if($type == "user" && $new_user_meta_key == "OTHER") {
			$key = $new_user_meta_key_other;
		} else if($type == "post" && $new_post_meta_key == "OTHER") {
			$key = $new_post_meta_key_other;
		} else if($type == "user") {
			$key = $new_user_meta_key;
		} else if($type == "post") {
			$key = $new_post_meta_key;
		}

		if(wp_verify_nonce($mapping_nonce, $type . "|" . $key . "|" . $data_type)) {

			if(empty($type) || empty($key) || empty($data_type)) {
				// error
				$this->add_admin_notice("error", __("A validation error has occurred. Please ensure you have filled out all the fields correctly.", 'horizontal-meta'));
				$this->create_mappings();
				return;
			}

			// At this point we are ready to create the mapping
			$error_message = "";
			$result = $this->hm_mappings_library->assign_auto_mapping($type, $key, $data_type, true, true, $error_message);
			if(empty($result)) {
				$this->add_admin_notice("error", __("Validation errors occurred.", 'horizontal-meta') . " " . $error_message);
				$this->create_mappings();
				return;
			} else {
				header("Location: " . admin_url("options-general.php?page=hm&notice=mapping_created"));
				exit;
			}
		} else {
			$this->add_admin_notice("error", __("Could not verify nonce.", 'horizontal-meta'));
			$this->create_mappings();
			return;
		}
	}

	function process_delete_mappings() {
		$this->library("hm_mappings_library");

		$nonce = $_POST["remove_mappings_nonce"];
		$remove_mappings = $_POST["hm_remove_mapping"];

		if(!wp_verify_nonce($nonce, "remove_mappings_nonce")) {
			$this->add_admin_notice("error", __("There was a problem removing the selected mappings. Please try again.", 'horizontal-meta'));
			$this->index();
			return;
		} else {
			if(!empty($remove_mappings) && is_array($remove_mappings)) {
				foreach($remove_mappings as $remove_mapping) {
					$meta_key = $remove_mapping["meta_key"];
					$meta_type = $remove_mapping["meta_type"];
					$remove = $remove_mapping["remove"];

					if($remove == "on" && $this->hm_mappings_library->meta_key_mapped($meta_type, $meta_key)) {
						$this->hm_mappings_library->delete_assigned_mapping($meta_type, $meta_key);
					}

				}

				header("Location: " . admin_url("options-general.php?page=hm&notice=mappings_deleted"));
				exit;

			} else {
				$this->add_admin_notice("error", __("You must specify a mapping to remove.", 'horizontal-meta'));
				$this->index();
				return;
			}
		}
	}

	/**
	 * Display the manage meta options screen.
	 */
	function index() {

		$this->external("meta_key_listing_table");

		$this->library("hm_mappings_library");

		$user_meta_keys = $this->hm_mappings_library->get_meta_keys_in_db("user");
		$post_meta_keys = $this->hm_mappings_library->get_meta_keys_in_db("post");
		$data_types = $this->hm_mappings_library->get_data_type_list();

		wp_enqueue_script("jquery.tablesorter", plugins_url($this->get_plugin_name() . "/js/jquery.tablesorter.min.js"));
		wp_enqueue_script("jquery.tablesorter.pager", plugins_url($this->get_plugin_name() . "/js/jquery.tablesorter.pager.js"));
		wp_enqueue_script("sack");
		wp_enqueue_script("hm_functions", plugins_url($this->get_plugin_name() . "/js/functions.js"));
		wp_enqueue_script("hm_settings", plugins_url($this->get_plugin_name() . "/js/settings.js"));

		wp_enqueue_style("hm_css", plugins_url($this->get_plugin_name() . "/css/styles.css"));
		wp_enqueue_style("tablesorter", plugins_url($this->get_plugin_name() . "/css/tablesorter/style.css"));
		$this->add_localisation();

		$nonce = wp_create_nonce("remove_mappings_nonce");


//		wp_localize_script( 'hm-localise', 'horizontal_meta_l10n', array(
//			'sure_remove_mappings' => __("Are you sure you wish to remove the selected mappings. You will not be able to undo this operation!", 'horizontal-meta'),
//			'select_type' => __('You must select a removal method for meta data. ', 'horizontal-meta'),
//			'meta_type' => __("Meta Type:", 'horizontal-meta'),
//			'meta_key' => __("Meta Key:", 'horizontal-meta')
//		));

		$list_table = new meta_key_listing_table();
		$list_table->prepare_items();

		$data = array(
			"extender_activated" => is_plugin_active("horizontal-meta-extender/loader.php"),
			"nonce" => $nonce,
			"is_multisite" => hm_is_multisite(),
			"data_types" => $data_types,
			"user_meta_keys" => $user_meta_keys,
			"post_meta_keys" => $post_meta_keys,
			"list_table" => $list_table,
			"current_page" => "settings",
			"params" => json_encode(array(
				"ajax_admin_url" => admin_url("admin-ajax.php")
			))
		);

		$this->view("settings", $data);
	}

	// no time to do this properly
	function add_localisation() {
		?>
		<script type="text/javascript">
			var horizontal_meta_l10n = {
				sure_remove_mappings: '<?php print __("Are you sure you wish to remove the selected mappings. You will not be able to undo this operation!", 'horizontal-meta'); ?>',
				select_type: '<?php print __('You must select a removal method for meta data. ', 'horizontal-meta'); ?>',
				meta_type: '<?php print __("Meta Type:", 'horizontal-meta'); ?>',
				meta_key: '<?php print __("Meta Key:", 'horizontal-meta'); ?>',
				agree_to_terms: '<?php print __('You must agree to terms and conditions before creating a mapping.', 'horizontal-meta'); ?>',
				are_you_sure: '<?php print __('Are you sure you wish to create this mapping?', 'horizontal-meta'); ?>',
				specify_meta_type: '<?php print __("You must specify a meta type.", 'horizontal-meta'); ?>',
				specify_meta_key: '<?php print __("You must specify a meta key.", 'horizontal-meta'); ?>',
				specify_data_type: '<?php print __("You must specify a data type.", 'horizontal-meta'); ?>'
			}
		</script>
		<?php
	}

	function create_mappings() {

		$this->library("hm_mappings_library");

		$user_meta_keys = $this->hm_mappings_library->get_meta_keys_in_db("user");
		$post_meta_keys = $this->hm_mappings_library->get_meta_keys_in_db("post");
		$data_types = $this->hm_mappings_library->get_data_type_list();

		wp_enqueue_script("jquery.tablesorter", plugins_url($this->get_plugin_name() . "/js/jquery.tablesorter.min.js"));
		wp_enqueue_script("jquery.tablesorter.pager", plugins_url($this->get_plugin_name() . "/js/jquery.tablesorter.pager.js"));
		wp_enqueue_script("sack");
		wp_enqueue_script("hm_functions", plugins_url($this->get_plugin_name() . "/js/functions.js"));
		wp_enqueue_script("hm_create_mappings", plugins_url($this->get_plugin_name() . "/js/create-mappings.js"));

		wp_enqueue_style("hm_css", plugins_url($this->get_plugin_name() . "/css/styles.css"));
		wp_enqueue_style("tablesorter", plugins_url($this->get_plugin_name() . "/css/tablesorter/style.css"));

		$this->add_localisation();

//		wp_localize_script( 'hm-localise', 'horizontal_meta_l10n', array(
//			'agree_to_terms' => __('You must agree to terms and conditions before creating a mapping.', 'horizontal-meta'),
//			'are_you_sure' => __('Are you sure you wish to create this mapping?', 'horizontal-meta'),
//			'specify_meta_type' => __("You must specify a meta type.", 'horizontal-meta'),
//			'specify_meta_key' => __("You must specify a meta key.", 'horizontal-meta'),
//			'specify_data_type' => __("You must specify a data type.", 'horizontal-meta')
//		));

		$data = array(
			"extender_activated" => is_plugin_active("horizontal-meta-extender/loader.php"),
			"is_multisite" => hm_is_multisite(),
			"data_types" => $data_types,
			"user_meta_keys" => $user_meta_keys,
			"post_meta_keys" => $post_meta_keys,
			"current_page" => "settings",
			"params" => json_encode(array(
				"ajax_admin_url" => admin_url("admin-ajax.php")
			)),
			"new_type" => $_POST["new_type"],
			"new_data_type" => $_POST["new_data_type"],
			"new_post_meta_key" => $_POST["new_post_meta_key"],
			"new_user_meta_key" => $_POST["new_user_meta_key"],
			"new_post_meta_key_other" => $_POST["new_post_meta_key_other"],
			"new_user_meta_key_other" => $_POST["new_user_meta_key_other"]
		);

		$this->view("create_mappings", $data);
	}

	function manage_data() {
		$this->external("meta_key_manage_table");

		$this->library("hm_mappings_library");


		wp_enqueue_script("jquery.tablesorter", plugins_url($this->get_plugin_name() . "/js/jquery.tablesorter.min.js"));
		wp_enqueue_script("jquery.tablesorter.pager", plugins_url($this->get_plugin_name() . "/js/jquery.tablesorter.pager.js"));
		wp_enqueue_script("sack");
		wp_enqueue_script("hm_functions", plugins_url($this->get_plugin_name() . "/js/functions.js"));
		wp_enqueue_script("hm_manage_data", plugins_url($this->get_plugin_name() . "/js/manage-data.js"));

		wp_enqueue_style("hm_css", plugins_url($this->get_plugin_name() . "/css/styles.css"));
		wp_enqueue_style("tablesorter", plugins_url($this->get_plugin_name() . "/css/tablesorter/style.css"));

		$list_table = new meta_key_manage_table();
		$list_table->prepare_items();

		$data = array(
			"extender_activated" => is_plugin_active("horizontal-meta-extender/loader.php"),
			"is_multisite" => hm_is_multisite(),
			"list_table" => $list_table,
			"current_page" => "settings",
			"params" => json_encode(array(
				"ajax_admin_url" => admin_url("admin-ajax.php")
			))
		);

		$this->view("manage_data", $data);
	}

	function advanced() {

		$this->library("hm_mappings_library");
		$this->model("hm_settings_model");

		if($_SERVER["REQUEST_METHOD"] == "POST") {
			$settings = $this->get_request_settings();
		} else {
			$settings = $this->hm_settings_model->get_settings();
		}

		wp_enqueue_script("hm_settings_advanced", plugins_url($this->get_plugin_name() . "/js/settings-advanced.js"));
		wp_enqueue_style("hm_css", plugins_url($this->get_plugin_name() . "/css/styles.css"));

		$data = array(
			"extender_activated" => is_plugin_active("horizontal-meta-extender/loader.php"),
			"is_multisite" => hm_is_multisite(),
			"settings" => $settings,
			"extender_active" => is_plugin_active("horizontal-meta-extender/loader.php")
		);

		$this->view("settings_advanced", $data);
	}

	private function get_request_settings() {
		$settings = array(
			"user_rewrite_queries" => $_REQUEST["user_rewrite_queries"],
			"post_rewrite_queries" => $_REQUEST["post_rewrite_queries"],
			"post_intercept_keys" => $_REQUEST["post_intercept_keys"],
			"user_intercept_keys" => $_REQUEST["user_intercept_keys"],
			"post_override_suppress_filters" => $_REQUEST["post_override_suppress_filters"],
			"post_override_disable_other_filters" => $_REQUEST["post_override_disable_other_filters"],
			"license_key" => $_REQUEST["horzm_license_key"]
		);
		return $settings;
	}

	function advanced_save() {
		$notices = $this->get_generic_notices();

		if(!current_user_can("manage_options")) {
			$this->add_admin_notice("error", $notices["no_permission"][1]);
			$this->advanced();
			return;
		}

		$settings = $this->get_request_settings();

		if($settings["post_intercept_keys"] == "" ||
			$settings["post_rewrite_queries"] == "" ||
			$settings["post_override_suppress_filters"] == "" ||
			$settings["post_override_disable_other_filters"] == "" ||
			($settings["user_rewrite_queries"] == "" && ((hm_is_multisite() && get_current_blog_id() == 1) || !hm_is_multisite())) ||
			($settings["user_intercept_keys"] == "" && ((hm_is_multisite() && get_current_blog_id() == 1) || !hm_is_multisite()))) {

			// validation_errors
			$this->add_admin_notice("error", $notices["validation_errors"][1]);
			$this->advanced();
			return;
		}

		$this->model("hm_settings_model");
		$this->hm_settings_model->save_settings($settings);

		header("Location: " . admin_url("options-general.php?page=hm&action=advanced&notice=hm_updated"));
		exit;

	}

	function get_generic_notices() {
		$notices = array(
			"no_permission" => array("error", __("You do not have permission to access this resource.", 'horizontal-meta')),
			"validation_errors" => array("error", __("Please ensure all fields have been filled out correctly.", 'horizontal-meta')),
			"hm_updated" => array("notice", __("Settings have been saved.", 'horizontal-meta')),
			"mapping_created" => array("notice", __("The mapping has been created successfully.", 'horizontal-meta')),
			"mappings_deleted" => array("notice", __("The mappings have been deleted.", 'horizontal-meta')),
			"data_maintenance_success" => array("notice", __("The data has been updated successfully.", 'horizontal-meta'))
		);

		return $notices;
	}

}