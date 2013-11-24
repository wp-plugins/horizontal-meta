jQuery(function($) {

	$("#cb-select-all-1, #cb-select-all-2").click(function() {
		setTimeout(function() {
			$(".hm-horizontal-meta tr.hm-row-data .chk-remove-mapping").trigger("change");
		}, 10);
	});

	$(".hm-horizontal-meta tr.hm-row-data .chk-remove-mapping").change(function() {

		var $row = $(this).closest("tr");
		var meta_key = $row.attr("data-meta-key");
		var meta_type = $row.attr("data-meta-type");

		hm_toggle_meta_data_state(meta_type, meta_key);
	});

	$(".hm-horizontal-meta").on("click", "input[type=checkbox].remove-mapping-option", function() {
		var $row = $(this).closest("tr");

		var meta_key = $row.attr("data-meta-key");
		var meta_type = $row.attr("data-meta-type");

		hm_update_display(meta_type, meta_key);
	});

	$("input[name=remove_mappings][type=button]").click(function() {
		if(hm_validate_data()) {
			if(confirm(horizontal_meta_l10n.sure_remove_mappings)) {
				// trigger form submission
				var $form = $(this).closest("form");
				$form.trigger("submit");
			}
		}
	});

});

function hm_toggle_meta_data_state(meta_type, meta_key) {
	var $ = jQuery;
	var $container = $("tr.hm-data-container[data-meta-type='" + meta_type + "'][data-meta-key='" + meta_key + "']");

	if($container.prev().find(".chk-remove-mapping").is(":checked")) {
		$container.addClass("visible");
	} else {
		$container.removeClass("visible");
	}

	if($container.is(":visible") && hm_empty($container.attr("data-loaded"))) {
		var mysack = new sack(hm_params.ajax_admin_url);
		mysack.execute = 1;
		mysack.method = 'POST';
		mysack.setVar("action", "hm_remove_mapping_load_data");
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

	$(".hm-horizontal-meta #wrapper-errors").html("");
	if(errors.length == 0) return;

	var output = "<ul>";
	for(var e in errors) {
		output += "<li>" + errors[e] + "</li>";
	}
	output += "</ul>";

	$(".hm-horizontal-meta #wrapper-errors").html("<div class='error'>" + output + "</div>");

	// scroll to errors
	$('html, body').animate({
		scrollTop: ($("#errors-beginning").offset().top - 50)
	}, 200, "easeOutQuart");

}

function hm_set_nonce(value) {
	jQuery("#mapping_nonce").val(value);
}

function hm_validate_data() {
	var $ = jQuery;

	var errors = [];

	$("tr.hm-row-data").each(function() {
		var $row = $(this);
		var meta_key = $row.attr("data-meta-key");
		var meta_type = $row.attr("data-meta-type");
	});

	if(errors.length > 0) {
		hm_set_validation_errors(errors);
		return false;
	} else {
		return true;
	}
}

function hm_set_data(meta_type, meta_key, html) {
	var $container = jQuery(".hm-data-container[data-meta-type=" + meta_type + "][data-meta-key=" + meta_key + "]");
	if($container.length == 0) return;
	$container.find("td").html(html);

	set_tablesorter($container.find("table"));

	$container.attr("data-loaded", true);

	hm_update_display(meta_type, meta_key);
}

function hm_update_display(meta_type, meta_key) {
	var $ = jQuery;

	var $container = $("tr.hm-data-container[data-meta-type='" + meta_type + "'][data-meta-key='" + meta_key + "']");

	if($container.find("input[type=radio].option-custom").is(":checked")) {
		$container.find(".remove-custom-options").show();
	} else {
		$container.find(".remove-custom-options").hide();
	}
}

if(hm_empty(jQuery.easing.easeOutQuart)) {
	jQuery.easing.easeOutQuart = function (x, t, b, c, d) {
		return -c * ((t=t/d-1)*t*t*t - 1) + b;
	};
}