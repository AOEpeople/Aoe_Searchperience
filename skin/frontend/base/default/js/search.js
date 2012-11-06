
var aoeSolrSearchController = null;

/**
 * JavaScript controller for searches.
 *
 * @copyright AOE media GmbH , 2011 <http://www.aoemedia.de>
 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
 * @package aoe_solr
 * @subpackage controller
 */
function AoeSolrSearchController() {

	AbstractSolrController.call(this);

	var self = this;

	var solrContainer = null;

	var solrParentContainer = null;

	var solrParentContainerSelector = null;

	var solrSelctionContainer = null;

	var searching = false;

	var currentQueryString = '';

	var previousQueryString = '';

	var lastQueryString = '';

	var currentInitialQueryString = '';

	var lastInitialQueryString = '';

	var currentActiveFilterCount = 0;

	var lastActiveFilterCount = 0;

	var searchFieldSelector = '';

	var resultCount = 0;

	var historyCache = null;

	var lastActiveHistoryHash = '';

	var performedSearches = 0;


	this.applyConfiguration = function() {

		self.setLastActiveFilterCount(0);
		self.setCurrentActiveFilterCount(0);
		if(typeof searchUrl == "string") {
			self.setBaseUrl(searchUrl);
		}

		if (typeof aoeSolrDataTypeSearch == "string") {
			self.setDataType(aoeSolrDataTypeSearch);
		}

		if (typeof aoeSolrPluginNamespace == "string") {
			self.setPluginNamespace(aoeSolrPluginNamespace);
		} else {
			self.setPluginNamespace("tx_aoesolr");
		}

		if (typeof aoeSolrSearchfieldSelector == "string") {
			self.setSearchFieldSelector(aoeSolrSearchfieldSelector);
		} else {
			self.setSearchFieldSelector("input.solr-search-field");
		}

		if (typeof aoeSolrForceOnlyFilenameFromAppendix == 'boolean' && aoeSolrForceOnlyFilenameFromAppendix == true) {
			self.setForceOnlyFilenameFromAppendix(true);
		}

		if (typeof aoeSolrHistoryNamespace == "string") {
			self.setHistoryNamespace(aoeSolrHistoryNamespace);
		} else {
			self.setHistoryNamespace('aoesolr');
		}

		if (typeof aoeSolrParentContainerSelector == "string") {
			self.setSolrParentContainerSelector(aoeSolrParentContainerSelector);
		}
	};

	/**
	 * Contructor
	 */
	this.init = function() {
		self.applyConfiguration();

		if(self.solrParentContainerSelector == null) {
			self.solrParentContainer =  jQuery('div#aoe_solr').parent();
		} else {
			self.solrParentContainer = jQuery(self.solrParentContainerSelector);
		}

		if (typeof solrSelctionContainerSelector == 'undefined') {
			solrSelctionContainerSelector = 'div#aoe_solr #section';
		}
		self.solrSelctionContainer =  jQuery(solrSelctionContainerSelector);

		var solr_initial_querystring = jQuery('span#solr_initial_querystring').text();
		if(typeof solr_initial_querystring == "string") {
			aoeSolrSearchController.setInitialQueryString(solr_initial_querystring);
		}

		var solr_resultcount = jQuery('span#solr_resultcount').text();
		if(typeof solr_resultcount == "string") {
			aoeSolrSearchController.setResultCount(solr_resultcount);
		}

		var solr_performed_querystring = jQuery('span#solr_performed_querystring').text();
		if(typeof solr_performed_querystring == "string") {
			aoeSolrSearchController.setQueryString(solr_performed_querystring);
			if (typeof solr_querystring_updateselector == "string") {
				//update searchword in search input field:
				jQuery(solr_querystring_updateselector).val(solr_performed_querystring.replace(/&quot;/g, '"'));
			}
		}

		if(typeof self.performedSearches != "number"){
			self.performedSearches = 0;
		}

		self.registerHashChangedHandler();
		self.storeActiveFilterCounts();
		self.registerToggleHandler();
		self.registerSubmitHandler();
		self.registerAjaxHandlers();
		self.restoreToggleStates();
		self.fireSearchEvent();

		jQuery('body').trigger('solr-after-init',{
			controller: self
		});

		if (this.currentQueryString != '' && this.currentQueryString != this.previousQueryString) {
			this.previousQueryString = this.currentQueryString;
			jQuery('body').trigger('solr-new-query');
		}

	};

	this.doNewSearch = function(searchword) {
		jQuery(this.searchFieldSelector).val(searchword);
		var form = jQuery(this.searchFieldSelector).closest('form');
		var url = jQuery(form).attr('action');
		var data = jQuery(form).serialize();
		var searchurl = url + '&' +  data;
		self.performAjaxReload(searchurl, null, true, true);
	};

	this.setSearchFieldSelector = function(selector) {
		return self.searchFieldSelector = selector;
	};

	this.getSearchFieldSelector = function() {
		return self.searchFieldSelector;
	};

	this.setResultCount = function(count) {
		return self.resultCount = count;
	};

	this.getResultCount = function() {
		return self.resultCount;
	};

	this.setCurrentActiveFilterCount = function(count) {
		self.currentActiveFilterCount = count;
	};

	this.setLastActiveFilterCount = function(count) {
		self.lastActiveFilterCount = count;
	};

	this.getCurrentActiveFilterCount = function() {
		return self.currentActiveFilterCount;
	};

	this.getLastActiveFilterCount = function() {
		return self.lastActiveFilterCount;
	};

	this.setQueryString = function(queryString) {
		self.lastQueryString 	= self.currentQueryString;
		self.currentQueryString = queryString;
	};

	this.getQueryString = function() {
		return self.currentQueryString;
	};

	this.getIsNewSearch = function() {
		return (self.currentQueryString !== self.lastQueryString);
	};

	this.setInitialQueryString = function(queryString) {
		self.lastInitialQueryString 	= self.currentInitialQueryString;
		self.currentInitialQueryString = queryString;
	};

	this.getInitialQueryString = function() {
		return self.currentInitialQueryString;
	};

	this.getIsNewInitialQueryString = function() {
		return (self.currentInitialQueryString !== self.lastInitialQueryString);
	};

	this.getNewActiveFilterCount = function() {
		return jQuery('.b-filter li.cur').size();
	};

	this.storeActiveFilterCounts = function() {
			//save the current count in the last count to keep it
		self.setLastActiveFilterCount ( self.getCurrentActiveFilterCount() );
		self.setCurrentActiveFilterCount ( self.getNewActiveFilterCount() );
	};

	this.getIsFilterCountChanged = function() {
		return self.getCurrentActiveFilterCount() !== self.getLastActiveFilterCount();
	};


	this.registerSubmitHandler = function() {
		var searchSubmitHandler = function() {
			//this is the submit element
			var data = jQuery(this).serialize();
			var url  = jQuery(this).attr('action');
			var searchurl = '?'+data;

			if(url.indexOf('#') == -1) {
				var searchurl = url + '&' +  data;
			}

			self.performAjaxReload(searchurl, null, true);
			return false;
		};

		jQuery('body').trigger('solr-search-submitted');
		jQuery(self.getSearchFieldSelector()).closest('form').unbind('submit').submit(searchSubmitHandler);
	};

	this.registerPlaceholder = function() {
		if(typeof jQuery.fn.placeholder == 'function') {
			jQuery('input[placeholder]').addClass('placeholder').placeholder();
		}
	};

	this.registerToggleHandler = function() {
			//by default we hide all toggle items and show them only
			//when they have toggleable elements
		jQuery('.b-filter-toggle').hide();

		jQuery('.b-filter-toggle-element').each(function(){
			jQuery(this).data('height', jQuery(this).height());
			jQuery(this).closest('.b-filter').find('.b-filter-toggle').show();

			if(jQuery(this).closest('.b-filter').hasClass('b-filter-toggle-off')){
				jQuery(this).height(0);
			}
		});

		jQuery('.b-filter-toggle').click(function() {
			var bFilterToggleElement = jQuery(this).closest('.b-filter').find('.b-filter-toggle-element'),
				obj = jQuery(this),
				bFilter = jQuery(this).closest('.b-filter');

			var filterId = bFilter.attr('id');

			if(!bFilterToggleElement.is(':animated')) {
				if(bFilterToggleElement.is(':visible')) {
					self.closeFilter(bFilter, true);
				} else {
					self.openFilter(bFilter);
				}
			}
		});
	};

	this.openFilter = function(filter) {
		if(typeof filter == 'object' && typeof filter.removeClass == 'function' && typeof filter.addClass == 'function') {
			filter.removeClass('b-filter-toggle-off').addClass('b-filter-toggle-on');

				//here we restore the height of the element
			var bFilterToggleElement = filter.find('.b-filter-toggle-element');
			heightval = bFilterToggleElement.data('height');
			bFilterToggleElement.show().stop().animate({height: heightval });
		}
	};

	this.closeFilter = function(filter, animate) {
		if(typeof filter == 'object' && typeof filter.removeClass == 'function' && typeof filter.addClass == 'function') {
			//here we restore the height of the element
			var bFilterToggleElement = filter.find('.b-filter-toggle-element');

			if(animate) {
				bFilterToggleElement.stop().animate({ height: 0 }, function(){
					jQuery(this).hide();
					filter.removeClass('b-filter-toggle-on').addClass('b-filter-toggle-off');
				});
			} else {
				bFilterToggleElement.hide();
				filter.removeClass('b-filter-toggle-on').addClass('b-filter-toggle-off');
			}
		}
	};

	this.saveToggleStates = function() {
		var openToggles	= '';
		var closedToggles	= '';

		jQuery('.b-filter').each(function(){
			var filter = jQuery(this);
			if(filter.hasClass('b-filter-toggle-off')){
				closedToggles = closedToggles + ' ' + filter.attr('id');
			}

			if(filter.hasClass('b-filter-toggle-on')){
				openToggles = openToggles + ' ' + filter.attr('id');
			}
		});

		jQuery.cookie('open_toggles', openToggles);
		jQuery.cookie('closed_toggles', closedToggles);
	};

	this.restoreToggleStates = function() {
		var openToggles		= jQuery.cookie('open_toggles');
		var closedToggles	= jQuery.cookie('closed_toggles');


		if(openToggles !== null && typeof openToggles == 'string') {
			var open_ids = openToggles.split(' ');

			jQuery.each(open_ids, function(){
				if(this != '') {
					var selector = '#'+this;
					self.openFilter(jQuery(selector));
				}
			})
		}

		if(closedToggles !== null && typeof closedToggles == 'string') {
			var closed_ids = closedToggles.split(' ');

			jQuery.each(closed_ids, function(){
				if(this != '') {
					var selector = '#'+this;
					self.closeFilter(jQuery(selector), false);
				}
			});
		}
	};

	this.fireSearchEvent = function() {
		if(self.getInitialQueryString() && self.getIsNewInitialQueryString()) {
			jQuery('body').trigger('internalSearch', self.getInitialQueryString());
		}
	};

	/**
 	 * Center the given jQuery element
 	 *
 	 * @param jQuery elem
 	 *
 	 * @access private
 	 * @return void
 	 *
 	 * @author Michael Klapper <michael.klapper@aoemedia.de>
 	 */
	this.centerLoader = function (elem) {
		var left =  parseInt(jQuery(solrSelctionContainerSelector).width() / 2) + jQuery(solrSelctionContainerSelector).offset().left;

		elem.css({
			position: 'fixed',
			left:     left + 50,
			top:      '50%',
			zIndex:   '9999'
		});

	};

	this.scrollToTop = function() {
		if (jQuery(window).scrollTop()> self.solrParentContainer.offset().top) {
			jQuery('html, body').animate({
				scrollTop: self.solrParentContainer.offset().top
			}, 900);
		}
	};

	this.performAjaxReload = function(url, data, scrollup, skiphash) {
		self.performedSearches	= self.performedSearches  + 1;


		var appendix			= url + '&eID=tx_aoesolr_search';
		var ajaxUrl				= self.getFinalAjaxUrl(appendix);

		self.saveToggleStates();

		if(!self.searching) {

			self.searching = true;
			jQuery.ajax({
				url: ajaxUrl,
				dataType: self.dataType,
				data: data,
				cache: false,
				processData: false,

				beforeSend: function() {
					self.searching = true;
					self.solrSelctionContainer.css({ opacity: 0.3 });

					self.showLoader('body');
					self.centerLoader( self.getLoader() );

					if(scrollup) {
						self.scrollToTop();
					}

				},
				success: function(res) {
					var current_hash = location.hash;
					if(current_hash == '' || current_hash == '#') {
						self.storeInitialContentInCache();
					}

					self.setResultContent(res);
					self.init();

					jQuery('body').trigger('solr-after-ajax-init');

					self.searching = false;
					self.hideLoader();
					self.solrSelctionContainer.css({ opacity: 1.0 });

					var hash		= self.getHistoryNamespace()+'@'+url;
					self.setLastActiveHistoryHash('#'+hash);

					if(skiphash != true) {
						if(location.hash != hash) {
							location.hash = hash;
						}
					}

					self.historyCache.set('#'+hash, res);
					setTimeout(function() {
						jQuery('body').trigger('solr-after-ajax-reload');
					},
					10);
				},
				error: function(XMLHttpRequest, textStatus, errorThrown) {
					if(typeof console == 'object') {
						console.log("Error during search: "+textStatus+errorThrown);
					}
					self.searching = false;
					jQuery('body').trigger('solr-after-ajax-error');
					self.hideLoader();
				}
			});
		}
	};

	this.setResultContent = function(res) {
		if (typeof enableInnerShiv != "undefined" && enableInnerShiv == 1) {
			self.solrParentContainer.html(innerShiv(res), false);
		} else {
			self.solrParentContainer.html(res);
		}

			//sometimes jquery events are to slow and a callback functions need to be called, when it was defined.
		if(typeof aoeSolrAfterSetContent == 'function') {
			aoeSolrAfterSetContent();
		}

		jQuery('body').trigger('solr-after-ajax-content-replace');
	};

	this.registerAjaxHandlers = function() {
		jQuery('#aoe_solr').find('a.ajaxabled').each(
			function() {
				jQuery(this).bind('click',

					function() {
						var url = jQuery(this).attr('href');
						self.performAjaxReload(url, null, true);

						return false;
					}
				);
			}
		);
	};

	/**
	 * Method to bind the hash changed event.
	 */
	this.registerHashChangedHandler = function() {
		jQuery(window).unbind('hashchange').bind('hashchange', self.hashChangedEventHandler);
	};

	/**
	 * @param e
	 */
	this.hashChangedEventHandler = function(e) {
		self.handleHashChange();
	};

	this.handleHashChange = function() {
		var hash = location.hash;

			//when the hash is the current hash or it is empty we do not need todo anything
		if(self.getIsLastActiveHistoryHash(hash) ) {
			//nothing todo
		} else {
			if(hash == '' || hash == '#') {
				if(self.performedSearches > 0) {
					//when we have an empty hash but search we performed we get back from
					//search in history an may reload the initial page from where the search was performed.
					window.location.reload();
				}
			} else {
				if(self.isRelevantHash(hash)) {
					if(self.historyCache.has(hash)) {
						var content = self.historyCache.get(hash);
						self.setResultContent(content);
						self.setLastActiveHistoryHash(hash);
						self.init();

						jQuery('body').trigger('solr-after-history-cache-restore');
					} else {
						if(typeof hash == 'string') {
								//the hash is not in the cache(maybe a bookmarked url, so we perform the ajax request)
							var appendix = hash.replace( '#'+self.getHistoryNamespace()+'@','');
							if(appendix.trim() != '') {
								self.performAjaxReload(appendix,null,false,true);
							}
						}
					}
				}
			}
		}
	};

	/**
	 * @return void
	 */
	this.storeInitialContentInCache = function() {
		var initialContent = self.solrParentContainer.html();
		self.historyCache.set('',initialContent);
		self.historyCache.set('#',initialContent);
	};

	/**
	 * @param hash
	 * @return boolean
	 */
	this.getIsLastActiveHistoryHash = function(hash) {
		return self.lastActiveHistoryHash == hash;
	};

	/**
	 * @param hash
	 */
	this.setLastActiveHistoryHash = function(hash) {
		this.lastActiveHistoryHash = hash;
	};

	/**
	 * Method to configure the solr parent container css selector.
	 *
	 * @param selector
	 */
	this.setSolrParentContainerSelector = function(selector) {
		this.solrParentContainerSelector = selector;
	};

	/**
	 * @return string
	 */
	this.getSolrParentContainerSelector = function() {
		return this.solrParentContainerSelector;
	}

	/**
	 * Method to check if the prefix of the hash is a hash generated by aoe_solr and the
	 * hash contains more information then the namespace.
	 *
	 * @param hash
	 */
	this.isRelevantHash = function (hash) {
		var expectedPrefix = '#' + self.getHistoryNamespace() + '@';
		var prefix = hash.substring(0, expectedPrefix.length);
		var isRelevant = false;

		//does the hash have an valid namespace prefix?
		if (prefix == expectedPrefix) {
			isRelevant = true;
		}

		return isRelevant;
	};
}


AoeSolrSearchController.prototype = new AbstractSolrController();

jQuery(document).ready(function() {
	aoeSolrHistoryCache 	= new  AoeSolrHashCache();
	aoeSolrHistoryCache.init(20);

	aoeSolrSearchController = new AoeSolrSearchController();
	aoeSolrSearchController.historyCache = aoeSolrHistoryCache;
	aoeSolrSearchController.init();

			//placeholder is only needed in the first call
	aoeSolrSearchController.registerPlaceholder();

	var hash = location.hash;

	if(typeof hash == 'string'){
		if(hash.substring(0,1)=='#') {
			jQuery(window).trigger('hashchange');
		}
	}
});
