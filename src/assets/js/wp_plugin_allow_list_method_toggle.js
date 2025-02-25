jQuery(document).ready(function($) {

    // Toggle the allow list load method
    $('#allow-list-method-toggle').click(function() {
        
        $.ajax({
            url: ajax_object2.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'allow_list_method_toggle',
                nonce: ajax_object2.nonce,
                value: this.value
            },
            success: function(response) {
                $('#allow-list-method-toggle-label').html('Allow List loaded from ' + response.allow_list_method);
                console.log(response.message + ' to ' + response.allow_list_method);
            },
            error: function(errorThrown){
                console.log('allow list toggle error');
                console.log(errorThrown);
            }
        });
    });
});
