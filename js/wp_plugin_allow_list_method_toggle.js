jQuery(document).ready(function($) {
    $('#allow-list-method-toggle').click(function() {
        alert( 'clicked' );
        $("input[name=allow-list-method\\[\\]]").trigger('click');
        $.ajax({
            url: ajax_object2.ajax_url,
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