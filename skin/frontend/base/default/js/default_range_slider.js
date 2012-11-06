jQuery(document).ready(function() {
	jQuery('#aoe_solr').live('solr-init-range-slider',function(event, data){

		if(typeof jQuery.fn.slider == 'function') {

			jQuery('#range-'+data.fieldname).slider({
				range: true,
				min: data.valid_min,
				max: data.valid_max,
				step: data.step,
				values: [data.selected_min, data.selected_max],
				slide: function(event, ui) {
					jQuery('#range-'+data.fieldname+' .ui-slider-handle').
						eq(0).html('<span>'+ui.values[0] + data.unit+'</span>').
						end().
						eq(1).html('<span>'+ui.values[1] + data.unit+'</span>');

					data.facetingservice.updateRangeFormValues(data.fieldname, ui.values[0],  ui.values[1]);
				},
				change: function(event, ui) {
					var url = data.facetingservice.getAjaxUrlForRangeFacet(data.fieldname,ui.values[0],ui.values[1]);
					data.controller.performAjaxReload(url, null, false);
				},
				create: function() {
					jQuery('#range-'+data.fieldname+' .ui-slider-handle').
					eq(0).addClass('ui-slider-first').
					end().
					eq(1).addClass('ui-slider-second');

					jQuery('#range-'+data.fieldname).append('<span class="ui-slider-min">' + data.valid_min + '</span><span class="ui-slider-max">' + data.valid_max + '</span>');
				}
			});

			jQuery('#range-'+data.fieldname+' .ui-slider-handle').
			eq(0).html('<span>'+ jQuery('#range-'+data.fieldname).slider('values', 0) + data.unit+'</span>').
			end().
			eq(1).html('<span>'+ jQuery('#range-'+data.fieldname).slider('values', 1) + data.unit+'</span>');
		}
	});
});