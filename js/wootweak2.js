jQuery(document).ready(function($) {
    
    $('.variations select').live('change', function(){
	
	var variation = $('input[name=variation_id]').attr('value');
	
	if(variation != '')
	{
	    $('#wt2_var_meta_sku').html(String(wt2_product_variations_meta[variation]._sku));
	    $('#wt2_var_meta_weight').html(String(wt2_product_variations_meta[variation]._weight));
	    $('#wt2_var_meta_dimentions').html(String(wt2_product_variations_meta[variation]._length)+'x'+String(wt2_product_variations_meta[variation]._width)+'x'+String(wt2_product_variations_meta[variation]._height));
	    if(wt2_product_variations_meta[variation]._description != null)
	    {
		$('#wt2_var_meta_description').html(String(wt2_product_variations_meta[variation]._description));
		console.log(wt2_product_variations_meta[variation]._description);
	    }
	    
	    $('#wt2_variation_meta').fadeIn(1000);
	}
	
	});
});
