jQuery(document).ready(function() {
	jQuery( ".settings-error" ).hide();
	get_ajax("#fbstar");
});
jQuery(function(){
	// Tooltips
	jQuery(".tips, .help_tip").tipTip({
    	'attribute' : 'data-tip',
    	'fadeIn' : 50,
    	'fadeOut' : 50,
    	'delay' : 200
    });
});

//Change target Currency
	jQuery('#target_cur').change(function() {
		jQuery(this).parents('form').submit();
	});
//Accept Currency
	jQuery('#selected_currency').click(function() {
	jQuery('#cr').val(currency());
	jQuery('#cr').css('color', 'green');
	});		

function currency(){
	//var currate=jQuery('#currency_converter_result span.bld').text().replace(/[^\d\.]/g, '');
	var currate=php_data.amount;
	//currate = Math.round(currate * 1000) / 1000000;
	//jQuery('#cr').val(currate);
	return currate;
}

function oc_currency(){
	//var currate=jQuery('#currency_converter_result span.bld').text().replace(/[^\d\.]/g, '');
	var currate=php_data.amount;
	//currate = Math.round(currate * 1000) / 1000000;
	//jQuery('#cr').val(crate);
	return currate;
}

function get_ajax(my_target){
		var answer = jQuery.get(php_data.proxy,{ requrl: php_data.requrl,fb_name: php_data.fb_name,temp_file: php_data.temp_file},
			function(data) {
				jQuery(my_target).html(data);
			}
		);
	}