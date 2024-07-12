jQuery(document).ready(function($) {
    $('#allow-list-method-toggle').click(function() {
        $("input[name=allow-list-method\\[\\]]").trigger('click');
            
            if(this.checked) {
                $('#allow-list-method-toggle-label').html('Allow List loaded from file');
                this.value='file';
            } 
            else {
                $('#allow-list-method-toggle-label').html('Allow List loaded from URL');
                this.value='url';
            }
        $.ajax({
            url: ajax_object2.ajax_url,
            type: 'POST',
            data: {
                action: 'allow_list_method_toggle',
                nonce: ajax_object.nonce,
                value: this.value
            },
            success: function(response, data) {
                console.log("allow_list_method_clicked_js: " + data)
                console.log(response);
            },
            error: function(errorThrown){
                console.log(errorThrown);
            }
        });
    });
});
