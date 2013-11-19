jQuery(function($) {

	$(".wrapper_mapping_manage_data_table tr.hm-pointer").click(function() {
		var meta_key = $(this).attr("data-meta-key");
		var meta_type = $(this).attr("data-meta-type");

		hm_toggle_meta_data_state(meta_type, meta_key);
	});

});

function hm_toggle_meta_data_state(meta_type, meta_key) {
	var $ = jQuery;
	var $container = $("tr.hm-data-container[data-meta-type='" + meta_type + "'][data-meta-key='" + meta_key + "']");
	$container.toggleClass("visible");
	if($container.is(":visible") && hm_empty($container.attr("data-loaded"))) {

		var mysack = new sack(hm_params.ajax_admin_url);
		mysack.execute = 1;
		mysack.method = 'POST';
		mysack.setVar("action", "hm_manage_load_data");
		mysack.setVar("meta_key", meta_key);
		mysack.setVar("meta_type", meta_type);
		mysack.onError = function() { };
		mysack.runAJAX();
	}
}

function set_tablesorter($table) {
	var $ = jQuery;
	if($table.find("tbody tr").length == 0) return;
	if($table.parent().find(".pager").length == 0) return;
	$table.tablesorter().tablesorterPager({container: $table.parent().find(".pager")});
	$table.parent().find(".pager").find(".pagesize").trigger("change");
}

function hm_set_validation_errors(errors) {
	var $ = jQuery;

	$("#wrapper_mapping_errors").html("");
	if(errors.length == 0) return;

	var output = "<ul>";
	for(var e in errors) {
		output += "<li>" + errors[e] + "</li>";
	}
	output += "</ul>";

	$("#wrapper_mapping_errors").html("<div class='error'>" + output + "</div>");
}

function hm_set_nonce(value) {
	jQuery("#mapping_nonce").val(value);
}

function hm_set_data(meta_type, meta_key, html) {
	var $container = jQuery(".hm-data-container[data-meta-type=" + meta_type + "][data-meta-key=" + meta_key + "]");
	if($container.length == 0) return;
	$container.find("td").html(html);

	set_tablesorter($container.find("table"));

	$container.attr("data-loaded", true);
}