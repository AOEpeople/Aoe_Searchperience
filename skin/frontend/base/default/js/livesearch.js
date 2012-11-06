
function AoeSolrLiveSearchFieldEventManager() {
	var self = this;

	var lastTriggeredQuery = '';

	var searchController = null;

	this.init = function(searchController) {
		self.searchController = searchController;

		var searchField = searchController.getLiveSearchFieldObject();
		searchField.unbind('keyup').bind('keyup', self.handleKeyPress);
	}

	this.handleKeyPress = function(event) {
		self.startSearchIfFieldContentChanged();
	}

	this.startSearchIfFieldContentChanged = function() {
		var currentFieldContent = self.getCurrentFieldContent();

		if (currentFieldContent != self.lastTriggeredQuery) {
			self.lastTriggeredQuery = currentFieldContent;
			if (typeof currentFieldContent == 'string' && currentFieldContent.length >= 3) {
				self.searchController.performLiveSearch(currentFieldContent, '');
			} else {
				self.searchController.closeSearch();
			}
		}
	}

	this.getCurrentFieldContent = function() {
		return self.searchController.getLiveSearchFieldObject().val();
	}

}

/*******************************************************************************
 * JavaScript object to manage global key events ()
 */
function AoeSolrLiveSearchGlobalEventManager() {

	var self = this;

	var searchController;

	this.init = function(searchController) {
		self.searchController = searchController;
		jQuery(document).unbind('keyup').bind('keyup', self.handleKeyPress);
	}

	this.handleKeyPress = function(event) {
		key = event.keyCode;
		switch (key) {
		case 27:
			// escape key
			self.searchController.closeSearch();
			break;

		case 37:
			// left
			var url = jQuery('.paging li.cur').prev().find('a').attr('href');
			if (typeof url == 'string') {
				self.searchController.performLiveSearch('', url);
			}

			break;

		case 39:
			// right
			var url = jQuery('.paging li.cur').next().find('a').attr('href');
			if (typeof url == 'string') {
				self.searchController.performLiveSearch('', url);
			}
			break;
		}
	};
}

/**
 * JavaScript controller for live searches.
 *
 * @copyright AOE media GmbH , 2011 <http://www.aoemedia.de>
 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
 * @package aoe_solr
 * @subpackage controller
 */
function AoeSolrLiveSearchController() {

	AbstractSolrController.call(this);

	var self = this;

	var liveSearchFieldname = '';

	var liveContentContainerName = '';

	var liveContentContainerObject = null;

	var livesearchFieldExposedClass = '';

	var livesearchFieldObject = null;

	var loadContainerBeforeResultsLoaded = false;

	var fancyBoxOptions = null;

	var globalKeyEventManager = null;

	var searchFieldEventManager = null;

	var currentAjaxRequest = null;

	var queuedAjaxRequest = null;

	var resultsFound = false;

	var intervall = null;


	/**
	 * Method for reconstruction
	 */
	this.reinit = function() {

		self.initLiveSearchContainer();
		self.registerAjaxHandlers();
		jQuery('body').trigger('solr-livesearch-after-reinit');
	}

	/**
	 * Contructor
	 */
	this.init = function() {
		self.intervall = null;
		self.resultsFound = false;
		self.queuedAjaxRequest = null;
		self.currentAjaxRequest = null;

		self.searchFieldEventManager = new AoeSolrLiveSearchFieldEventManager();
		self.searchFieldEventManager.init(self, self.getLiveSearchfieldName());

		self.globalKeyEventManager = new AoeSolrLiveSearchGlobalEventManager();
		self.globalKeyEventManager.init(self);


		self.reinit();
		jQuery('body').trigger('solr-livesearch-after-init');
	};



	this.setLastStartedSearch = function(lastStartedSearch) {
		self.lastStartedSearch = lastStartedSearch;
	};

	this.getLastStartedSearch = function() {
		return self.lastStartedSearch;
	}

	this.setLiveSearchfieldExposeClass = function(exposedClass) {
		self.livesearchFieldExposedClass = exposedClass;
	};

	this.getLiveSearchfieldExposeClass = function() {
		return self.livesearchFieldExposedClass;
	};

	this.setLoadContainerBeforeResultsLoaded = function(boolean) {
		self.loadContainerBeforeResultsLoaded = boolean;
	};

	this.getLoadContainerBeforeResultsLoaded = function() {
		return self.loadContainerBeforeResultsLoaded;
	};

	this.getLiveSearchfieldName = function() {
		return self.liveSearchFieldname;
	};

	this.setLiveSearchfieldName = function(fieldname) {
		self.liveSearchFieldname = fieldname;
	};

	this.initLiveSearchContainer = function() {
		var container = jQuery('#' + self.getLiveContentContainerName());

		if (container.size() == 0) {
			// if no container exists create it
			jQuery('body').append('<div id="' + self.getLiveContentContainerName()+ '" style="display: none;"></div>');

		} else {
			// container exists, ensure the its empty
			container.html('<div>Loading</div>');
		}
	};

	this.getAjaxRequestUrlFromQueryStringAndInitialUrl = function(querystring, url) {
		var append = '';

		if ((url != '') && (typeof url != 'undefined')) {
			url = url + '&';
		} else {
			url = '';
		}

		if (querystring != '' && (typeof querystring != 'undefined')) {
			append = '&tx_aoesolr_pi1[querystring]=' + querystring;
		}

		return self.getFinalAjaxUrl(url+ 'eID=tx_aoesolr_livesearch&tx_aoesolr_pi1[action]=search&tx_aoesolr_pi1[controller]=LiveSearch'+ append);
	};
	/**
	 * Saves a request for later processing. Starts a timer so that the queue is processed.
	 * @param string querystring
	 * @param string url
	 */
	this.queueRequest = function(querystring, url){
		self.queuedAjaxRequest = {
				url :url,
				querystring:querystring
		}
		if(self.intervall == null){
			self.intervall = window.setInterval(self.resolveQueue,500);
		}
	};
	/**
	 * Does the waiting request. If no request or wait for more results were found for the timer is stopped.
	 */
	this.resolveQueue = function (){
		if(self.queuedAjaxRequest == null){
			clearInterval(self.intervall);
			self.intervall = null;
		}else{
			if(self.currentAjaxRequest == null){
				self.performLiveSearch(self.queuedAjaxRequest.querystring,self.queuedAjaxRequest.url);
				self.queuedAjaxRequest = null;
			}else{
			}
		}
	};
	this.performLiveSearch = function(querystring, url) {
		//when there is a running request, we queue it
		if(self.currentAjaxRequest != null) {
			self.queueRequest(querystring, url);
			return;
		}
		jQuery('body').trigger('solr-livesearch-search-started');

		var liveContainer = jQuery('#' + self.getLiveContentContainerName());
		// in the case the querystring is same as the last started search we do
		// not have to
		// buffer it and we do not need to execute it
		if (self.getLoadContainerBeforeResultsLoaded()) {
			if (liveContainer.html() == '') {
			// show fast dummy content
				self.fancyBoxLiveResultContainer('<div class="live-dummy-content"></div>');
			}
		}

		if (self.getLoadContainerBeforeResultsLoaded()) {
			jQuery.fancybox.showActivity();
		}

		var ajaxUrl = self.getAjaxRequestUrlFromQueryStringAndInitialUrl(querystring, url);

		self.currentAjaxRequest = jQuery.ajax({
			url : ajaxUrl,
			dataType : self.dataType,
			cache : false,
			processData : false,
			success : function(res) {
				self.currentAjaxRequest = null;
				if (res.count > 0) {
					jQuery('body').trigger('solr-livesearch-after-successfull-search');
					self.fancyBoxLiveResultContainer(res.content);
					self.resultsFound = true;
					jQuery.fancybox.hideActivity();
				}

				self.reinit();
			},
			error : function() {
				jQuery('body').trigger('solr-livesearch-search-error');
				jQuery.fancybox.hideActivity();
				self.reinit();
			}
		});
	};

	this.registerAjaxHandlers = function() {
		jQuery('#aoe_solr_livesearch').find('a.ajaxabled').each(function() {
			jQuery(this).unbind('click').bind('click',

			function() {
				var url = jQuery(this).attr('href')
				self.performLiveSearch('', url);

				return false;
			});
		});
	};

	this.setFancyBoxOptions = function(options) {
		self.fancyBoxOptions = options;
	};

	this.getFancyBoxOptions = function() {
		return self.fancyBoxOptions;
	}

	this.setLiveContentContainerName = function(container) {
		self.liveContentContainerName = container;
	};

	this.getLiveContentContainerName = function() {
		return self.liveContentContainerName;
	};

	this.getLiveContentContainerObject = function() {
		if (typeof self.liveContentContainerObject == 'undefined') {
			self.liveContentContainerObject = jQuery('#'
					+ self.getLiveContentContainerName());
		}
		return self.liveContentContainerObject;
	};

	this.getLiveSearchFieldObject = function() {
		return jQuery('.' + self.getLiveSearchfieldName());
	}

	this.fancyBoxLiveResultContainer = function(content) {
		self.getLiveSearchFieldObject().addClass(
				self.getLiveSearchfieldExposeClass());

		if (jQuery('#fancybox-content').html() != '') {
			// the fancy box is allready open, therefore we replace the content
			jQuery('#fancybox-content').children(':first-child').html(content);

		} else {
			// use the general options and append the content
			var options = self.getFancyBoxOptions();
			options.content = content;
			options.onClose = function() { self.stopRunningSearches(); return false; }
			jQuery.fancybox(options);

			jQuery('.b-ajax-search-results-close').live('click', function() {
				self.closeSearch();
			});
		}
	};

	this.closeSearch = function() {
		self.unfancyBoxLiveResultContainer();
		self.stopRunningSearches();
		self.setLastStartedSearch('');
	};

	this.stopRunningSearches = function() {
		if (self.currentAjaxRequest != null) {
			self.currentAjaxRequest.abort();
			self.currentAjaxRequest = null;
			self.reinit();
		}
	};

	this.unfancyBoxLiveResultContainer = function() {
		var container = jQuery('#' + self.getLiveContentContainerName());
		self.getLiveSearchFieldObject().removeClass(self.getLiveSearchfieldExposeClass());

		container.html('');
		jQuery.fancybox.close();
	}
}

var aoeSolrLiveSearchController = null;
jQuery(document).ready(
	function() {
		AoeSolrLiveSearchController.prototype = new AbstractSolrController();

		aoeSolrLiveSearchController = new AoeSolrLiveSearchController();

		if (typeof liveSearchUrl == "string") {
			aoeSolrLiveSearchController.setBaseUrl(liveSearchUrl);
		}
		if (typeof aoeSolrDataTypeLivesearch == "string") {
			aoeSolrLiveSearchController.setDataType(aoeSolrDataTypeLivesearch);
		}
		aoeSolrLiveSearchController.setLastStartedSearch('');
		aoeSolrLiveSearchController.setLiveSearchfieldName('solr-livesearch-field');
		aoeSolrLiveSearchController.setLiveSearchfieldExposeClass('solr-livesearch-field-exposed');
		aoeSolrLiveSearchController.setLiveContentContainerName('solr-livesearch-content');
		aoeSolrLiveSearchController.setFancyBoxOptions({
			modal : 'true',
			padding : 0,
			overlayColor : '#000',
			overlayOpacity : '0.8',
			speedIn : 0,
			opacity : 0.3
		});

		if (typeof aoeSolrPluginNamespace == "string") {
			aoeSolrLiveSearchController.setPluginNamespace(aoeSolrPluginNamespace);
		} else {
			aoeSolrLiveSearchController.setPluginNamespace("tx_aoesolr");
		}

		if (typeof aoeSolrForceOnlyFilenameFromAppendix == 'boolean'  && aoeSolrForceOnlyFilenameFromAppendix == true) {
			aoeSolrLiveSearchController.setForceOnlyFilenameFromAppendix(true);
		}

		aoeSolrLiveSearchController.init();

	}
);
