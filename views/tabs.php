


<div id="icon-woocommerce" class="icon32 icon32-posts-shop_order"><br></div>
<h2 class="nav-tab-wrapper">
	<a class='nav-tab <?php print (empty($action) || $action == "index" ? "nav-tab-active" : ""); ?>' href='?page=hm'><?php _e("Horizontal Meta", 'horizontal-meta'); ?></a>
	<a class='nav-tab <?php print (empty($action) || $action == "create_mappings" ? "nav-tab-active" : ""); ?>' href='?page=hm&action=create_mappings'><?php _e("Create Mappings", 'horizontal-meta'); ?></a>
	<a class='nav-tab <?php print (empty($action) || $action == "manage_data" ? "nav-tab-active" : ""); ?>' href='?page=hm&action=manage_data'><?php _e("Manage Data", 'horizontal-meta'); ?></a>
	<a class='nav-tab <?php print ($action == "advanced" ? "nav-tab-active" : ""); ?>' href='?page=hm&action=advanced'><?php _e("Advanced Settings", 'horizontal-meta'); ?></a>
	<!--<a class='nav-tab <?php print ($action == "logs" ? "nav-tab-active" : ""); ?>' href='?page=hm&action=logs'>Logs</a>-->
</h2>