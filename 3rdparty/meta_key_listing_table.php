<?php

class meta_key_listing_table extends WP_List_Table {

	private $interface;

	/**
	 * Constructor, we override the parent to pass our own arguments
	 * We usually focus on three parameters: singular and plural labels, as well as whether the class supports AJAX.
	 */
	function __construct() {

		parent::__construct( array(
			'singular'=> 'wp_list_text_link', //Singular label
			'plural' => 'wp_list_text_links', //plural label, also this well be one of the table css class
			'ajax'	=> false //We won't support Ajax for this table
		) );

		// Grab this instance of the adoption_table library so we can interface into the adoptions_model
		// it's a little bit fruity but it will work and means I don't need to double up on code.
		$this->interface = $GLOBALS["hmeta"]["controllers"]["hm"];
		$this->interface->library("hm_mappings_library"); // make sure the adoptions model is loaded
	}

	function check_permissions() {
		if ( !current_user_can('manage_options') )
			wp_die(__('You do not have permission to view this resource.', 'horizontal-meta'));
	}

	function get_bulk_actions() {
		$actions = array();

		return $actions;
	}

	function display_tablenav($which) {

		if($which=="top") {
			$this->add_scripts();
		}

		parent::display_tablenav($which);
	}

function add_scripts() {
	?>
	<script type="text/javascript">
		jQuery(function($){
			$("select[name=action]").bind("change", update_members_action_value);
			$("select[name=action2]").bind("change", update_members_action_value);
		});

		function update_members_action_value() {
			var $ = jQuery;
			var $selects = $("select[name=action],select[name=action2]");
			var $other = $selects.not(this);

			$other.unbind("change", update_members_action_value);
			$other.val($(this).val());
			$other.bind("change", update_members_action_value);

		}
	</script>
	<?php
}

	/**
	 * Define the columns that are going to be used in the table
	 * @return array $columns, the array of columns to use with the table
	 */
	function get_columns() {
		$columns = array(
			'cb'    => '<input type="checkbox" />',
			'col_type'=>__('Type', 'horizontal-meta'),
			'col_meta_key'=>__('Meta Key', 'horizontal-meta'),
			'col_column'=>__('Mapping', 'horizontal-meta'),
			'col_data_type'=>__('Date Type', 'horizontal-meta')
		);

		return $columns;
	}

	function column_cb($item) {
		$meta_type = $item["type"];
		$meta_key = $item["meta_key"];

		$form_key = md5($meta_type . "|" . $meta_key . "|" . get_current_blog_id());

		$display = '<input class="chk-remove-mapping" type="checkbox" name="hm_remove_mapping[' . $form_key . '][remove]" value="on">';
		$display .= '<input type="hidden" name="hm_remove_mapping[' . $form_key . '][meta_key]" value="' . $meta_key . '" />';
		$display .= '<input type="hidden" name="hm_remove_mapping[' . $form_key . '][meta_type]" value="' . $meta_type . '" />';

		return $display;
	}
	function column_col_type($item) {
		$display = ucwords($item["type"]);
		return $display;
	}
	function column_col_meta_key($item) {
		$display = $item["meta_key"];
		return $display;
	}

	function column_col_column($item) {
		$display = $item["column"];
		return $display;
	}

	function column_col_data_type($item) {
		$display = $item["data_type"];
		return $display;
	}

	function single_row( $item ) {

		?>
		<tr data-meta-type="<?php print $item["type"]; ?>" data-meta-key="<?php print $item["meta_key"]; ?>" class="hm-row-data">
			<?php echo $this->single_row_columns( $item ); ?>
		</tr>
		<tr class="hm-data-container" data-meta-type="<?php print $item["type"]; ?>" data-meta-key="<?php print $item["meta_key"]; ?>">
			<td colspan="5" class="alternate">
				<div class="hm-loader"></div>
			</td>
		</tr>
		<?php
	}

	public function get_sortable_columns() {
		$sortable = array(
			'col_type'=> array('type', false),
			'col_meta_key'=>array('meta_key', false),
			'col_column'=>array('column', false),
			'col_data_type'=>array('data_type', false)
		);

		return $sortable;
	}

	/**
	 * Prepare the table with different parameters, pagination, columns and table elements
	 */
	function prepare_items() {
		$this->interface->library("hm_mappings_library");

		global $_wp_column_headers;

		$order_by = "type";
		foreach($this->get_sortable_columns() as $column) {
			if(in_array($_REQUEST["orderby"], $column)) {
				$order_by = $_REQUEST["orderby"];
				break;
			}
		}

		$order = (empty($_REQUEST["order"]) || !in_array(strtolower($_REQUEST["order"]), array("asc", "desc")) ? "asc" :  strtolower($_REQUEST["order"]));
		$paged = (!empty($_REQUEST["paged"]) && is_numeric($_REQUEST["paged"]) && intval($_REQUEST["paged"]) > 0 ? $_REQUEST["paged"] : 0);
		if($paged == "0") $paged = 1;
		$per_page = $this->get_items_per_page('mappings_per_page', 20);
		$current_page_step = floatval($paged - 1) * floatval($per_page);

		$options = array(
			"order" => $order,
			"order_by" => $order_by,
			"per_page" => $per_page,
			"current_page" => $paged,
		);

		// use previously loaded franklin interface to get the adoption list
		$record_count = 0;
		$mappings = $this->interface->hm_mappings_library->query_mappings($options, $record_count);

		/* -- Register the pagination -- */
		$this->set_pagination_args( array(
			"total_items" => $record_count,
			"total_pages" => ceil($record_count / $per_page),
			"per_page" => $per_page,
		) );

		$this->_column_headers = array(
			$this->get_columns(),
			array(),
			$this->get_sortable_columns()
		);

		$this->items = $mappings;
	}

}