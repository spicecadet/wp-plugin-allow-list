jQuery(document).ready(function($) {
    $('#refresh-plugin-allow-list-button').click(function() {
        
        $('#allow-list-refresh-label').html('Allow List refreshed');

        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'allow_list_refresh',
                nonce: ajax_object.nonce
            },
            success: function(response) {
                console.log("refresh plugin list clicked: " + ajaxurl)
                console.log(response);
            },
        });
    });
});


