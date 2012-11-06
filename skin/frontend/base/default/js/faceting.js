
function AoeSolrFacetingService() {
	var self = this;

	this.init = function(data) {
		if(data.controller != undefined) {
			self.controller = data.controller;

			self.initAllRangeFacets();
			self.initAllGeoFacets();
			self.registerRangeFormHandler();
		} else {
			console.log('no controller instance was passed to faceting service.');
		}

	};

	this.initAllGeoFacets = function() {
		getFacets = self.getAllGeoFacets();
			//restore the range filters stored in the solr_range_facets js array
		if(typeof getFacets == 'object') {
			jQuery.each(getFacets, function() {
				if(typeof this == 'object') {
					self.initGeoFacet(this['lat'],this['long'],this['fieldname']);
				}
			});
		}
	};

	this.getAllGeoFacets = function() {
		var geoFacets = new Object();

		jQuery('.geo-data').each(function() {
			var node = jQuery(this);
			var fieldname = node.find('.fieldname').val();
			geoFacets[fieldname] = new Object();
			geoFacets[fieldname]['lat'] = node.find('.lat').val();
			geoFacets[fieldname]['long'] = node.find('.long').val();
			geoFacets[fieldname]['fieldname'] = node.find('.fieldname').val();
		});

		return geoFacets;
	};

	this.registerRangeFormHandler = function() {
		var searchHandler = function() {
			var data = jQuery(this).serialize();
			var url = jQuery(this).attr('action');
			self.controller.performAjaxReload(url, data, true);
			return false;
		};

		jQuery('form.range-form').unbind('submit',searchHandler).submit(searchHandler);
	};

	this.initAllRangeFacets = function() {
		theRangeFacets = self.getAllRangeFacets();
			//restore the range filters stored in the solr_range_facets js array
		if(typeof theRangeFacets == 'object') {
			jQuery.each(theRangeFacets, function() {
				if(typeof this == 'object') {
					self.initRangeFacet(this['min'],this['max'],this['currentmin'],this['currentmax'],this['unit'],this['fieldname'],this['step'],this['type']);
				}
			});
		}
	};

	this.getAllRangeFacets = function() {
		var facets = new Object();
		jQuery('.range-data').each(function() {
			var node = jQuery(this);
			var fieldname = node.find('.fieldname').text();

			facets[fieldname] = new Object();
			facets[fieldname]['min'] = parseFloat(node.find('.min').text());
			facets[fieldname]['max'] = parseFloat(node.find('.max').text());
			facets[fieldname]['currentmin'] = parseFloat(node.find('.currentmin').text());
			facets[fieldname]['currentmax'] = parseFloat(node.find('.currentmax').text());
			facets[fieldname]['unit'] = node.find('.unit').text();
			facets[fieldname]['step'] = parseFloat(node.find('.step').text());
			facets[fieldname]['type'] = node.find('.type').text();
			facets[fieldname]['fieldname'] = node.find('.fieldname').text();
		});

		return facets;
	};

	this.initRangeFacet = function(min,max,currentmin,currentmax,unit,fieldname,step,type) {
		if(!jQuery('#rangeactive_'+fieldname).is(':visible')) {
			//if no reset link is visible the range can be reseted
			self.setSelectedRangeValue(fieldname, null, 'min');
			self.setSelectedRangeValue(fieldname, null, 'max');
			self.setValidRangeValue(fieldname, null, 'min');
			self.setValidRangeValue(fieldname, null, 'max');
		}

		selected_min = self.getSelectedRangeValue(fieldname, currentmin, 'min');
		selected_max = self.getSelectedRangeValue(fieldname, currentmax, 'max');

		valid_min 	 = self.getValidRangeValue(fieldname,min,'min');
		valid_max	 = self.getValidRangeValue(fieldname,max,'max');

		used_min	 = Math.max (valid_min, selected_min);
		used_max	 = Math.min (valid_max, selected_max);

			//since the range form value, may not reflect the current values, they will be upated
		self.updateRangeFormValues(fieldname, used_min, used_max);

		if(type == '' || type == 'RangeSlider' || type == undefined) {
			self.initRangeSlider(valid_min, valid_max, selected_min, selected_max, unit, fieldname, step);
		} else if ( type == 'RangeDate' ){
			self.initRangeDatepicker(valid_min, valid_max, selected_min, selected_max, fieldname);
		}

		this.registerRangeFormEventHandler(fieldname);
	};

	this.initRangeSlider = function(valid_min,valid_max,selected_min,selected_max, unit,fieldname,step) {
		jQuery('#aoe_solr').trigger('solr-init-range-slider',{
			valid_min: valid_min,
			valid_max: valid_max,
			selected_min: selected_min,
			selected_max: selected_max,
			unit: unit,
			fieldname: fieldname,
			step: step,
			controller: self.controller,
			facetingservice: self
		});
	};

	this.initRangeDatepicker = function(valid_min,valid_max,selected_min,selected_max,fieldname) {
		jQuery('#aoe_solr').trigger('solr-init-range-date',{
			valid_min: valid_min,
			valid_max: valid_max,
			selected_min: selected_min,
			selected_max: selected_max,
			fieldname: fieldname,
			controller: self.controller,
			facetingservice: self
		});
	};

	this.initGeoFacet = function(long,lat,fieldname) {
		jQuery('#aoe_solr').trigger('solr-init-geo', {
			long: long,
			lat: lat,
			fieldname: fieldname,
			controller: self.controller,
			facetingservice: self
		});
	};

	this.registerRangeFormEventHandler = function(fieldname) {
		jQuery('.facet-'+fieldname+' form.range-form').unbind('submit').bind('submit', function() {
			var minField = jQuery('#min-'+fieldname);
			var maxField = jQuery('#max-'+fieldname);

			var min = parseFloat(minField.attr('value'));
			var max = parseFloat(maxField.attr('value'));

			if(max > min) {
				var url = self.controller.getAjaxUrlForRangeFacet(fieldname, min, max);
				self.controller.performAjaxReload(url, null, false);
			} else {
				minField.addClass('b-search-form-text-error');
				maxField.addClass('b-search-form-text-error');
			}

			return false;
		});
	};

	this.getAjaxUrlForRangeFacet = function(fieldname, min, max) {
		var url = jQuery('#rangelinktemplate_'+fieldname).attr('href');
		var newurl = url;
		newurl = newurl.replace("___min_"+fieldname+"___",min);
		newurl = newurl.replace("___max_"+fieldname+"___",max);

		self.setSelectedRangeValue(fieldname, min, 'min');
		self.setSelectedRangeValue(fieldname, max, 'max');

		newurl = self.controller.getFinalAjaxUrl(newurl);

		return newurl;
	};

	this.getAjaxUrlForGeoFacet = function(fieldname, long, lat, radius, locationName) {
		var url = jQuery('#geolinktemplate_'+fieldname).attr('href');

		var newurl = url;
		newurl = newurl.replace("___long_"+fieldname+"___",long);
		newurl = newurl.replace("___lat_"+fieldname+"___",lat);
		newurl = newurl.replace("___radius_"+fieldname+"___",radius);
		newurl = newurl.replace("___locationName_"+fieldname+"___",locationName);
		newurl = self.controller.getFinalAjaxUrl(newurl);

		return newurl;
	};

	/**
	 *
	 */
	this.updateRangeFormValues = function(fieldname,min,max) {
		jQuery('#min-'+fieldname).attr('value',min);
		jQuery('#max-'+fieldname).attr('value',max);
	};

	/**
	 * This method is used to retrieve the stored min max value for a range filter.
	 * If it was a new search or nothing was stored, it returns the passed value, that
	 * allways makes sence.
	 *
	 */
	this.getValidRangeValue = function(fieldname, passedDefaultValue, minMax) {
		var selector	= fieldname+'_valid_'+minMax;
		return self.storeInCookieOrGetDefaultForNewRequest(selector, passedDefaultValue,true);
	};

	this.setSelectedRangeValue = function(fieldname, value, minMax) {
		jQuery.cookie(fieldname+'_selected_'+minMax, value);
	};

	this.setValidRangeValue = function(fieldname, value, minMax) {
		jQuery.cookie(fieldname+'_valid_'+minMax, value);
	};

	this.getSelectedRangeValue = function(fieldname, passedDefaultValue, minMax) {
		var selector = fieldname+'_selected_'+minMax;
		return self.storeInCookieOrGetDefaultForNewRequest(selector, passedDefaultValue,false);
	};

	/**
	 * This method is to return a default value, when it is a new search, otherwise it returns
	 * a stored value from a cookie. This value is set from previous request for ranges settings.
	 *
	 * @return int
	 */
	this.storeInCookieOrGetDefaultForNewRequest = function(selector, passedDefaultValue, storeOnFilterChange) {
		var storedValue	= jQuery.cookie(selector);

		var result		= 0;
		var filterchange = false;

		if(storeOnFilterChange) {
			filterchange = self.controller.getIsFilterCountChanged();
		}

		if(filterchange || self.controller.getIsNewSearch() || storedValue == null ) {
			jQuery.cookie(selector, passedDefaultValue);
			result = passedDefaultValue;
		} else {
			result = parseInt(storedValue);
		}

		return result;
	};
}


jQuery(document).ready(function() {
	initFacets = function(event, data) {
		service = new AoeSolrFacetingService();
		service.init(data);
	}

	jQuery('body').unbind('solr-after-init',initFacets).bind('solr-after-init',initFacets);
});