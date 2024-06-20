jQuery(document).ready(function($) {
    $('#refresh-plugin-allow-list-button').click(function() {
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'refresh_plugin_allow_list',
                nonce: ajax_object.nonce
            },
            success: function(response) {
                console.log("refresh plugin list clicked: " + ajaxurl)
                console.log(response);
            },
        });
    });
});

