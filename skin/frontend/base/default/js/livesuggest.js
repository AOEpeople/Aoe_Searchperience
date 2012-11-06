//holds the global instance
var aoeSolrLiveSuggestController = null;

/**
 * JavaScript controller for live suggestions
 *
 * @copyright AOE media GmbH , 2012 <http://www.aoemedia.de>
 * @author Timo Schmidt <timo.schmidt@aoemedia.de>
 * @package aoe_solr
 * @subpackage controller
 */
function AoeSolrLiveSuggestController() {

	AbstractSolrController.call(this);

	var self = this;

	var liveSuggestSelector = "input.solr-livesuggest-field";

	var offsetTop = 0;

	var offsetLeft = 0;

	var width = null;

	var height = null;

	this.applyConfiguration = function() {
		if (typeof liveSuggestUrl !== "undefined") {
			self.setBaseUrl(liveSuggestUrl);
		}

		if(typeof aoeSolrLivesuggestSelector == 'string') {
			self.setLiveSuggestSelector(aoeSolrLivesuggestSelector);
		}

		if (typeof aoeSolrDataTypeLiveSuggest == "string") {
			self.setDataType(aoeSolrDataTypeLiveSuggest);
		}

		if (typeof aoeSolrPluginNamespace == "string") {
			self.setPluginNamespace(aoeSolrPluginNamespace);
		} else {
			self.setPluginNamespace("tx_aoesolr");
		}

		if (typeof aoeSolrLiveSuggestOffsetTop == "number") {
			self.setOffsetTop(aoeSolrLiveSuggestOffsetTop);
		}
		if (typeof aoeSolrLiveSuggestOffetLeft == "number") {
			self.setOffsetLeft(aoeSolrLiveSuggestOffetLeft);
		}

		if (typeof aoeSolrLiveSuggestHeight == "number") {
			self.setHeight(aoeSolrLiveSuggestHeight);
		}

		if (typeof aoeSolrLiveSuggestWidth == "number") {
			self.setWidth(aoeSolrLiveSuggestWidth);
		}

		if (typeof aoeSolrForceOnlyFilenameFromAppendix == 'boolean' && aoeSolrForceOnlyFilenameFromAppendix == true) {
			self.setForceOnlyFilenameFromAppendix(true);
		}
	};

	this.setLiveSuggestSelector = function(livesuggestselector) {
		self.liveSuggestSelector  = livesuggestselector;
	};

	this.getLiveSuggestSelector = function() {
		return self.liveSuggestSelector;
	};

	this.setOffsetLeft = function(offsetLeft) {
		self.offsetLeft = offsetLeft;
	};

	this.setOffsetTop = function(offsetTop) {
		self.offsetTop = offsetTop;
	};

	this.setWidth = function(width) {
		self.width = width;
	};

	this.setHeight = function(height) {
		self.height = height;
	}

	this.init = function() {
		self.applyConfiguration();
		self.registerLiveSearchHandler();
	};

	this.reinit = function() {

	};

	this.registerLiveSearchHandler = function() {
		var urlPrefix	= 'eID=tx_aoesolr_livesuggest&'+self.getPluginNamespace()+'_pi1[action]=suggest&'+self.getPluginNamespace()+'_pi1[controller]=LiveSuggest';
		var ajaxUrl		= self.getFinalAjaxUrl(urlPrefix);
		var ajaxUrl		= ajaxUrl+'&'+self.getPluginNamespace()+'_pi1[querystring]=';

		jQuery(self.getLiveSuggestSelector()).liveSearch({
			url: ajaxUrl,
			dataType: self.dataType,
			id: 'aoe_solr_livesuggest',
			offsetLeft: self.offsetLeft,
			offsetTop: self.offsetTop,
			height: self.height,
			width: self.width,
			processResultCallback: function(res) {
				var result  = '';
				if(res.iserror == 0) {
					result = res.content;
				}

				return result;
			},
			openClass: 'solr-livesuggest-open'
		});

	}
}

AoeSolrLiveSuggestController.prototype = new AbstractSolrController();

jQuery(document).ready(function() {
	aoeSolrLiveSuggestController = new AoeSolrLiveSuggestController();
	aoeSolrLiveSuggestController.init();

	jQuery('body').live('solr-after-ajax-reload',function() {
		aoeSolrLiveSuggestController = new AoeSolrLiveSuggestController();
		aoeSolrLiveSuggestController.init();
	});
});