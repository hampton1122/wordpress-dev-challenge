jQuery(document).ready(function ($) {
    jQuery("#clueSearch").click(function () {
        var $form = $("form#clueSearchForm");
        var clueSearchResults = jQuery("#clueSearchResults");
        var data = {
            action: 'sp_jservice_clue_search_action',
            value: $form.find("input[name='value']").val(),
            category: $form.find("input[name='category']").val(),
            minDate: $form.find("input[name='minDate']").val(),
            maxDate: $form.find("input[name='maxDate']").val(),
            offset: $form.find("input[name='offset']").val()
        };

        $.post(ajaxurl, data, function (response) {
            clueSearchResults.empty();
            clueSearchResults.append(response);
        });

        return false;
    });

    return false;
});