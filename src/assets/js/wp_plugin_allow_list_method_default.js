jQuery(document).ready(function($) {
	
	// load default of the allow list load method
	$.ajax({
        url: ajax_object3.ajax_url,
        type: 'POST',
		dataType: 'json',
		data: {
			action: 'allow_list_method_default',
			nonce: ajax_object3.nonce,
			value: this.value
		},
		success: function(response) {
			$('#allow-list-method-toggle-label').html('Allow List loaded from ' + response.allow_list_method_default );
		},
		error: function(errorThrown){
			console.log('default error');
			console.log(errorThrown);
		}
    });
});