jQuery(document).ready(function($) {

    // Click the refresh button to trigger function
    $('#refresh-plugin-allow-list-button').click(function() {

        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'allow_list_refresh',
                nonce: ajax_object.nonce
            },
            success: function(response) {
                $('#allow-list-refresh-label').html('Allow List refreshed');
                console.log(response.message);
            },
            error: function(errorThrown){
                console.log('allow list refresh error');
                console.log(errorThrown);
            }
        });
    });
});


