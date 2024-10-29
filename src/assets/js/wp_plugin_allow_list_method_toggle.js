jQuery(document).ready(function($) {
    $('#allow-list-method-toggle').click(function() {

        $.ajax({
            url: ajax_object2.ajax_url,
            type: 'POST',
            data: {
                action: 'allow_list_method_toggle',
                nonce: ajax_object.nonce,
                value: this.value
            },
            success: function(response, data) {
                $('#allow-list-method-toggle-label').html('Allow List loaded from ' + response.allow_list_method.toUpperCase());
            },
            error: function(errorThrown){
                console.log(errorThrown);
            }
        }); 
    });
});
