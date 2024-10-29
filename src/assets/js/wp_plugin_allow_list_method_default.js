jQuery(document).ready(function($) {

    $.ajax({
        url: ajax_object3.ajax_url,
        type: 'POST',
		data: {
			action: 'allow_list_method_default',
			nonce: ajax_object.nonce,
			value: this.value
		},
		success: function(response) {
			$('#allow-list-method-toggle-label').html('Allow List loaded from ' + response.allow_list_method_default.toUpperCase());
		},
		error: function(errorThrown){
			console.log(errorThrown);
		}
    });
});