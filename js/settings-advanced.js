jQuery(function($) {

    $(".wrapper-hm_settings_advanced select").change(update_hm_advanced_display);

    update_hm_advanced_display();

});

function update_hm_advanced_display() {
    var $ = jQuery;
    var data = get_hm_advanced_settings_data();

    if(data.post_rewrite_queries == "1") {
        $("#override-suppress").show();
        $("#override-options-gotchya").show();
    } else {
        $("#override-suppress").hide();
        $("#override-options-gotchya").hide();
    }

    if(data.post_rewrite_queries == "1" && data.post_override_suppress_filters == "1") {
        $("#override-remove-other-filters").show();
        $("#remove-filters-gotchya").show();
    } else {
        $("#override-remove-other-filters").hide();
        $("#remove-filters-gotchya").hide();
    }

}

function get_hm_advanced_settings_data() {
    var $ = jQuery;

    data = {
        post_rewrite_queries: $("select[name=post_rewrite_queries]").val(),
        post_override_suppress_filters: $("select[name=post_override_suppress_filters]").val(),
        post_override_disable_other_filters: $("select[name=post_override_disable_other_filters]").val(),
        user_rewrite_queries: $("select[name=user_rewrite_queries]").val()
    }

    return data;
}