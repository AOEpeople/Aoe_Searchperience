
function AbstractSolrController(){

	/**
	 * Reference to this.
	 *
	 * @var object
	 */
	var self = this;

	var baseUrl = '';

	var pluginNamespace = '';

	var historyNamespace = '';

	var forceOnlyFilenameFromAppendix = false;

	this.setDataType = function(dataType) {
		self.dataType = dataType;
	};
	this.getDataType = function() {
		return self.dataType;
	};
	this.setBaseUrl = function(baseUrl) {
		self.baseUrl = baseUrl;
	};

	this.getBaseUrl = function() {
		return self.baseUrl;
	};

	this.setForceOnlyFilenameFromAppendix = function(boolean) {
		self.forceOnlyFilenameFromAppendix = boolean;
	};

	this.getForceOnlyFilenameFromAppendix = function() {
		return self.forceOnlyFilenameFromAppendix;
	};

	/**
	 * This method is very important and tested with test cases in the tests folder
	 * @param appendix
	 */
	this.getFinalAjaxUrl = function(appendix) {
		/**
		 * In some cases it is needed to use only the filename from the appendix, for
		 * example when aoe_solr is used in combination with jsonp and IE7
		 */
		if(self.getForceOnlyFilenameFromAppendix()) {
			var base= appendix.substring(0,appendix.lastIndexOf("/") + 1);
			appendix = appendix.replace(base, "");
		}

		var baseUrl 	= self.getBaseUrl();
		var	ajaxUrl 	= '';

			//empty base url
		if(baseUrl == '' || typeof baseUrl == 'undefined'){
			if(appendix.indexOf('?',0) == -1) {
					//when the appendix contains no ? sign we prepend it
				if(appendix.indexOf('&',0) == 0) {
					appendix = appendix.substring(1);
				}

				ajaxUrl = '?'+appendix;
			}else {
				ajaxUrl = appendix;
			}
		} else {
			//non empty base url
			var appendixContainsCompleteUrl	= appendix.indexOf('//') == 0 || appendix.indexOf('http://') == 0 || appendix.indexOf('https://') == 0;

			if(appendixContainsCompleteUrl) {
					//when the appendix is an complete url, it needs to start with the base url, otherwise it's
					//not an allowed url
				if(appendix.indexOf(baseUrl) >= 0) {
					ajaxUrl = appendix;
				}
			} else {
				var devider = '';
				var baseUrlContainsQuestionSign = baseUrl.indexOf('?',0) != -1;
				var appendixContainsQuestionSign = appendix.indexOf('?',0) != -1;
				var baseUrlEndsWithSlash = baseUrl.substring(baseUrl.length-1,baseUrl.length ) == '/';
				var appendixBeginsWithSlah = appendix.substring(0,1) == '/';
				var appendixContainsFilename = appendix.indexOf('/',0) != -1 || appendix.indexOf('.',0) != -1;

				if(!baseUrlContainsQuestionSign && !appendixContainsQuestionSign){
					//no question sign in baseurl and appendix
					//so appendix is filename with querystring or plain querystring
					if(appendixContainsFilename) {
						//appendix is a filename
						devider = '';
					} else {
						//appedix is a query string
						devider = '?';
					}
				}else if(baseUrlContainsQuestionSign && !appendixContainsQuestionSign) {
					//appendix is only an addition to the query
					devider = '&';
				}else if (!baseUrlContainsQuestionSign && appendixContainsQuestionSign) {
					//appendix contains the query segment
					devider = '';
				}else if (baseUrlContainsQuestionSign && appendixContainsQuestionSign) {
				}

				if(devider=='&') {
					if(baseUrl.indexOf('&') == (baseUrl.length - 1)) {
						baseUrl = baseUrl.substring(0,baseUrl.length -1);
					}
					if(appendix.indexOf('&',0) == 0) {
						appendix = appendix.substring(1);
					}

					if(appendix.indexOf('?',0) == 0) {
						appendix = appendix.substring(1);
					}
				}

				if(devider=='?') {
					if(baseUrl.indexOf('?') == (baseUrl.length - 1)) {
						baseUrl = baseUrl.substring(0,baseUrl.length - 1);
					}

					if(appendix.indexOf('&',0) == 0) {
						appendix = appendix.substring(1);
					}
					if(appendix.indexOf('?',0) == 0) {
						appendix = appendix.substring(1);
					}
				}

				if(devider == '') {
					if(baseUrl.indexOf('?') == (baseUrl.length - 1)) {
						baseUrl = baseUrl.substring(0,baseUrl.length - 1);
					}
				}

				//when baseUrl ends with / and appendix begins with / we should remove it from on part
				if(baseUrlEndsWithSlash && appendixBeginsWithSlah) {
					baseUrl = baseUrl.substring(0,baseUrl.length - 1);
				}

				ajaxUrl = baseUrl + devider + appendix ;
			}
		}

		//always add the given searchPid as id parameter:
		if (typeof aoeSolrSearchPid !== 'undefined') {
			ajaxUrl = ajaxUrl + '&id='+aoeSolrSearchPid;
		}
		//add datatype:
		if(typeof self.dataType !== 'undefined') {
			ajaxUrl = ajaxUrl + '&dataType=' + self.dataType;
		}
		return ajaxUrl;
	}

	this.showLoader = function(parentSelector) {
		if(typeof parentSelector == 'undefined') {
			parentSelector = 'body';
		}
		self.addLoaderIfNotExist(parentSelector);
		jQuery(parentSelector).find('#aoe_solr_loader').show();
	}

	this.addLoaderIfNotExist = function(parentSelector) {
		if(typeof parentSelector == 'undefined') {
			parentSelector = 'body';
		}
		element = jQuery(parentSelector).find('#aoe_solr_loader');
		if (typeof element[0] == 'undefined') {
			jQuery(parentSelector).append('<div id="aoe_solr_loader" class="ajax-loader"></div>');
		}
	}

	this.hideLoader = function() {
		self.getLoader().hide();
	}

	this.getLoader = function() {
		return jQuery('#aoe_solr_loader');
	}

	/**
	 * Overwrite this method when you want to extend aoe_solr in your own plugin.
	 *
	 * @return string
	 */
	this.getPluginNamespace = function() {
		return self.pluginNamespace;
	}

	/**
	 * Method to set the plugin namespace.
	 *
	 * @param namespace
	 */
	this.setPluginNamespace = function(namespace) {
		self.pluginNamespace = namespace;
	}

	this.getHistoryNamespace = function() {
		return self.historyNamespace;
	};

	this.setHistoryNamespace = function(namespace) {
		self.historyNamespace = namespace;
	};
}