jQuery(document).ready(function($) {
    $('#allow_list_method_toggle-button').click(function() {
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'allow_list_method_toggle',
                nonce: ajax_object.nonce
            },
            success: function(response) {
                console.log("allow_list_method_clicked: " + ajaxurl)
                console.log(response);
            },
        });
    });
});