var tested_mappings = [];
var hm_has_test_results = false;

jQuery(function($) {

    hm_update_display();

    $("select[name=new_type],select[name=new_user_meta_key],select[name=new_post_meta_key],select[name=new_data_type]").change(function() {
        hm_has_test_results = false;
        hm_update_display();
    });

    $("input[name=test_mapping]").click(function() {
        if(hm_validate_data()) {
            // validation passed
            // now test the data
            hm_test_key();
        }
    });

    $("input[name=create_mapping]").click(function() {
        if(hm_validate_data() && hm_mapping_tested()) {
            if(confirm(horizontal_meta_l10n.are_you_sure)) {
                $("#mapping_form").trigger("submit");
            }
        }
    });
});

function set_tablesorter() {
    var $ = jQuery;

    if($("#metakey_results").length == 0) return;
    if($("#metakey_results tbody tr").length == 0) return;

    $("#metakey_results").tablesorter().tablesorterPager({container: $("#pager")});
    $("#pager").find(".pagesize").trigger("change");
}

function hm_get_data() {
    var $ = jQuery;
    var $type_select = $("select[name=new_type]");
    var type = $type_select.val();
    var new_user_meta_key = $("select[name=new_user_meta_key]").val();
    var new_post_meta_key = $("select[name=new_post_meta_key]").val();
    var new_data_type = $("select[name=new_data_type]").val();
    var new_post_meta_key_other = $("input[name=new_post_meta_key_other]").val();
    var new_user_meta_key_other = $("input[name=new_user_meta_key_other]").val();

    var meta_key = "";
    if(type == "user" && new_user_meta_key == "OTHER") {
        meta_key = new_user_meta_key_other;
    } else if(type == "post" && new_post_meta_key == "OTHER") {
        meta_key = new_post_meta_key_other;
    } else if(type == "user") {
        meta_key = new_user_meta_key;
    } else if(type == "post") {
        meta_key = new_post_meta_key;
    }

    var output = {
        type: type,
        user_meta_key: new_user_meta_key,
        post_meta_key: new_post_meta_key,
        key: meta_key,
        data_type: new_data_type
    };

    return output;
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

function hm_validate_data() {
    var $ = jQuery;
    var errors = [];
    var data = hm_get_data();
    if(hm_empty(data.type)) errors[errors.length] = horizontal_meta_l10n.specify_meta_type;
    if(hm_empty(data.key)) errors[errors.length] = horizontal_meta_l10n.specify_meta_key;
    if(hm_empty(data.data_type)) errors[errors.length] = horizontal_meta_l10n.specify_data_type;

    if(errors.length > 0) {
        hm_set_validation_errors(errors);
        return false;
    } else {
        return true;
    }
}

function hm_show_wait() {
	jQuery("input[name=test_mapping]").attr("disabled", true);
	jQuery("input[name=create_mapping]").attr("disabled", true);
	jQuery("#map_meta_foot span#wait").css("visibility", "visible");
}

function hm_hide_wait() {
	jQuery("#map_meta_foot span#wait").css("visibility", "hidden");
	jQuery("input[name=test_mapping]").attr("disabled", false);
	jQuery("input[name=create_mapping]").attr("disabled", false);
}

function hm_test_key() {
    var data = hm_get_data();

    hm_show_wait();

    var mysack = new sack(hm_params.ajax_admin_url);
    mysack.execute = 1;
    mysack.method = 'POST';
    mysack.setVar( "action", "hm_key_test" );
    for(var i in data) {
        mysack.setVar(i, data[i]);
    }
    mysack.onError = function() { };
    mysack.runAJAX();
}

function hm_set_test_results(result, type, key, data_type, html) {
    var $ = jQuery;

    $("#wrapper_mapping_errors").html("");
    $("#wrapper-testresults").html(html);
    set_tablesorter();

    hm_has_test_results = true;

    // store the result
    // currently only store the last result to force a retest everytime options change
    // tested_mappings[tested_mappings.length] = {
    tested_mappings[0] = {
        type: type,
        key: key,
        data_type: data_type,
        result: result
    };
}

function hm_mapping_tested(is_last_test) {
    var $ = jQuery;
    var data = hm_get_data();

    if(hm_empty(data.key))
        return false;

    for(var i in tested_mappings) {
        if(tested_mappings[i].type == data.type) {
            if(tested_mappings[i].key == data.key && tested_mappings[i].result == true && tested_mappings[i].data_type == data.data_type) {
                if(i==(tested_mappings.length-1)) {
                    is_last_test = true;
                }

                return true;
            }
        }
    }

    return false;
}

function hm_data_is_last_test() {
    var $ = jQuery;
    var data = hm_get_data();

    if(hm_empty(data.key))
        return false;

    if(tested_mappings.length == 0)
        return false;

    var i = tested_mappings.length - 1;
    if(tested_mappings[i].key == data.key && tested_mappings[i].result == true && tested_mappings[i].data_type == data.data_type)
        return true;

    return false;
}

function hm_update_display() {
    var $ = jQuery;

    var $type_select = $("select[name=new_type]");
    var type = $type_select.val();
    var new_user_meta_key = $("select[name=new_user_meta_key]").val();
    var new_post_meta_key = $("select[name=new_post_meta_key]").val();

    if(type == "" || type == null) {
        $("#attributes_body").hide();
        $("#map_meta_foot").hide();
    } else {
        $("#attributes_body").show();
        $("#map_meta_foot").show();
        if(type == "user") {
            $("#post_meta_key_row").hide();
            $("#user_meta_key_row").show();

            if(new_user_meta_key=="OTHER") {
                $(".user_meta_other").show();
            } else {
                $(".user_meta_other").hide();
            }
        } else {
            $("#post_meta_key_row").show();
            $("#user_meta_key_row").hide();

            if(new_post_meta_key=="OTHER") {
                $(".post_meta_other").show();
            } else {
                $(".post_meta_other").hide();
            }
        }
    }

    if(hm_has_test_results || hm_data_is_last_test()) {
        $("#tested_meta").show();
    } else {
        $("#tested_meta").hide();
    }

    var is_last_test = false;
    if(!hm_mapping_tested(is_last_test)) {
        $("input[name=create_mapping]").hide();
        $("input[name=test_mapping]").show();
    } else {
        $("input[name=create_mapping]").show();
        $("input[name=test_mapping]").show();
    }

}