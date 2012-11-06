/***
@title:
Live Search

@version:
2.0

@author:
Andreas Lagerkvist

@date:
2008-08-31

@url:
http://andreaslagerkvist.com/jquery/live-search/

@license:
http://creativecommons.org/licenses/by/3.0/

@copyright:
2008 Andreas Lagerkvist (andreaslagerkvist.com)

@requires:
jquery, jquery.liveSearch.css

@does:
Use this plug-in to turn a normal form-input in to a live ajax search widget. The plug-in displays any HTML you like in the results and the search-results are updated live as the user types.

@howto:
jQuery('#q').liveSearch({url: '/ajax/search.php?q='}); would add the live-search container next to the input#q element and fill it with the contents of /ajax/search.php?q=THE-INPUTS-VALUE onkeyup of the input.

@exampleHTML:
<form method="post" action="/search/">

	<p>
		<label>
			Enter search terms<br />
			<input type="text" name="q" />
		</label> <input type="submit" value="Go" />
	</p>

</form>

@exampleJS:
jQuery('#jquery-live-search-example input[name="q"]').liveSearch({url: Router.urlForModule('SearchResults') + '&q='});
***/

var KEY_DOWN	= 40;
var KEY_UP		= 38;
var KEY_LEFT	= 37;
var KEY_RIGHT	= 39;
var KEY_ESC		= 27;
var KEY_ENTER	= 13;

jQuery.fn.liveSearch = function (conf) {
	var config = jQuery.extend({
		url:			'/search-results.php?q=',
		id:				'jquery-live-search',
		duration:		400,
		loadingClass:	'loading',
		openClass: 		'open',
		dataType:		'html',
		offsetTop:		0,
		offsetLeft:		0,
		width: null,
		height: null,
		onSlideUp:		function () {},
		uptadePosition:	false,
		slideSpeed: 10,
		minChars: 3
	}, conf);

	var liveSearch	= jQuery('#' + config.id);

	// Create live-search if it doesn't exist
	if (!liveSearch.length) {
		liveSearch = jQuery('<div id="' + config.id + '"></div>').appendTo(document.body).hide().slideUp(config.slideSpeed);

		// Close live-search when clicking outside it
		jQuery(document.body).click(function(event) {
			var clicked = jQuery(event.target);

			if (!(clicked.is('#' + config.id) || clicked.parents('#' + config.id).length || clicked.is('input'))) {
				liveSearch.slideUp(config.duration, function () {
					config.onSlideUp();
				});
			}
		});
	}


	return this.each(function () {
		var self = this;
		var hxr = null;

		var queueIntervall					= null;
		var bufferedQuery 					= null;
		var input							= jQuery(this).attr('autocomplete', 'off');
		var liveSearchPaddingBorderHoriz	= parseInt(liveSearch.css('paddingLeft'), 10) + parseInt(liveSearch.css('paddingRight'), 10) + parseInt(liveSearch.css('borderLeftWidth'), 10) + parseInt(liveSearch.css('borderRightWidth'), 10);
		var lockLayer 						= false;


		var stopAllRequests = function() {
				//we lock the layer until the next keypress to avoid popups of late responses
			lockLayer = true;
			hideLiveSearch();
			clearBuffer();

			if(self.hxr != null) {
				self.hxr.abort();
			}
		};

		jQuery('body').unbind('solr-search-submitted',stopAllRequests).bind('solr-search-submitted',stopAllRequests);

		// Re calculates live search's position
		var repositionLiveSearch = function () {
			var tmpOffset	= input.offset();

			var width = (config.width != null) ? config.width : input.outerWidth();
			var height = (config.height != null) ? config.height : input.outerHeight();

			var inputDim	= {
				left:		tmpOffset.left + config.offsetLeft,
				top:		tmpOffset.top + config.offsetTop,
				width:		width,
				height:		height
			};

			inputDim.topPos		= inputDim.top + inputDim.height;
			inputDim.totalWidth	= inputDim.width - liveSearchPaddingBorderHoriz;

			liveSearch.css({
				position:	'absolute',
				left:		inputDim.left + 'px',
				top:		inputDim.topPos + 'px',
				width:		inputDim.totalWidth + 'px'
			});
		};


		// Shows live-search for this input
		var showLiveSearch = function () {
			if(!self.lockLayer) {
				// Always reposition the live-search every time it is shown
				// in case user has resized browser-window or zoomed in or whatever
				repositionLiveSearch();

				// We need to bind a resize-event every time live search is shown
				// so it resizes based on the correct input element
				jQuery(window).unbind('resize', repositionLiveSearch);
				jQuery(window).bind('resize', repositionLiveSearch);

				liveSearch.slideDown(config.duration);
				input.addClass(config.openClass);
			} else {
				hideLiveSearch();
			}
		};

		// Hides live-search for this input
		var hideLiveSearch = function () {
			liveSearch.slideUp(config.duration, function () {
				config.onSlideUp();
			});
			input.removeClass(config.openClass);
		};

		var selectSuggestion = function () {
			input.val(jQuery(this).attr('rel'));
			jQuery(config.id).fadeOut('slow','swing');

			input.closest('form').submit();
		};

		var clickSuggestLink = function () {
			jQuery(config.id).fadeOut('slow','swing');

			var target = jQuery(this).attr('rel');
			window.location = target;
		};

		var peformAjaxUpdate = function (q) {
			input.addClass(config.loadingClass);
			self.running = true;
			self.hxr = jQuery.ajax({
				url: config.url+q,
				dataType: config.dataType,
				jsonpCallback: 'livesuggestResponse',
				cache: true,
				processData: false,
				success: function(res) {
					input.removeClass(config.loadingClass);

					if(typeof config.processResultCallback == 'function') {
						res = config.processResultCallback(res);
					}

					// Show live-search if results and search-term aren't empty
					if (res.length && q.length && jQuery.trim(jQuery(res).text()) != '')  {
							//tagstart prefix highlighsubject postfix tagend
						var regex 		= '(<[^>]*>)([^<>]*)('+q+')([^<>]*)(<[^>]*>)';
						var replacer 	= new RegExp(regex,"gi");
						var res 		= res.replace(replacer,'$1$2<strong>$3</strong>$4$5');
						liveSearch.html(res);
						showLiveSearch();

						//focus the first item
						jQuery('.suggestitem').removeClass('ac_over');
						jQuery('.suggestionquery').unbind('click',selectSuggestion).bind('click',selectSuggestion);
						jQuery('.suggestionlink').unbind('click',clickSuggestLink).bind('click',clickSuggestLink);
					} else {
						hideLiveSearch();
					}
					self.running = false;
				},
				error: function() {
					self.running = false;
				}
			});
		};

		var bufferQuery = function (q) {
			self.bufferedQuery = q;
			if(self.queueIntervall == null){
				self.queueIntervall = window.setInterval(resolveBuffer,100);
			}
		};

		var resolveBuffer = function() {
			if(self.bufferedQuery == null){
				clearInterval(self.queueIntervall);
				self.queueIntervall = null;
			} else {
				if(self.running == false && self.bufferedQuery != null){
					peformAjaxUpdate(self.bufferedQuery);
					self.bufferedQuery = null;
				}
			}
		};

		var clearBuffer = function() {
			self.bufferedQuery = null;
		};

		var selectPrevSuggestItem = function() {
			var activeOverlay = jQuery('.suggestionitem.ac_over');

			if(activeOverlay.size() == 0) {
				jQuery('.suggestionitem:last').addClass('ac_over').focus();
			} else {
				if(activeOverlay.prevAll('.suggestionitem').length == 0) {
					activeOverlay.removeClass('ac_over');
					activeOverlay.parent('.ac_list').prev('.ac_list').find('.suggestionitem:last').addClass('ac_over').focus();
				} else {
					activeOverlay.removeClass('ac_over').prevAll('.suggestionitem').first().addClass('ac_over').focus();
				}
			}
		};

		var selectNextSuggestItem = function() {
			var activeOverlay = jQuery('.suggestionitem.ac_over');
			if(activeOverlay.size() == 0) {
				jQuery('.suggestionitem:first').addClass('ac_over').focus();
			} else {
				if(activeOverlay.nextAll('.suggestionitem').length == 0) {
					activeOverlay.removeClass('ac_over');
					activeOverlay.parent('.ac_list').next('.ac_list').find('.suggestionitem:first').addClass('ac_over').focus();
				} else {
					activeOverlay.removeClass('ac_over').nextAll('.suggestionitem').first().addClass('ac_over').focus();
				}
			}
		};

		var selectFirstLeftSuggestItem = function() {
			var activeOverlay = jQuery('.suggestionitem.ac_over');
			activeOverlay.removeClass('ac_over');
			jQuery('.ac_list.ac_list_left:first').find('.suggestionitem:first').addClass('ac_over').focus();

		};

		var selectFirstRightSuggestItem = function() {
			var activeOverlay = jQuery('.suggestionitem.ac_over');
			activeOverlay.removeClass('ac_over');
			jQuery('.ac_list.ac_list_right:first').find('.suggestionitem:first').addClass('ac_over').focus();

		};

		var useCurrentSelection = function() {
			var lastActiveOverlay = jQuery('.ac_over:last');
			if(lastActiveOverlay.length > 0) {
				lastActiveOverlay.click();
				return false;
			}
		};

		input
			// On focus, if the live-search is empty, perform an new search
			// If not, just slide it down. Only do this if there's something in the input
			.focus(function () {
				if (this.value !== '') {
					// Perform a new search if there are no search results
					if (liveSearch.html() == '') {
						this.lastValue = '';
						input.keyup();
					}
					// If there are search results show live search
					else {
						// HACK: In case search field changes width onfocus
						setTimeout(showLiveSearch, 1);
					}
				}
			}).keydown(
				function (event) {
					var isLayerOpen		= input.hasClass(config.openClass);
					var key				= event.keyCode;
					if(key == KEY_ENTER && isLayerOpen)	{
						useCurrentSelection();
					}
				}
			)
			// Auto update live-search onkeyup
			.keyup(function (event) {
				var isLayerOpen		= input.hasClass(config.openClass);
				var key				= event.keyCode;

				var isControlKey	= key == KEY_DOWN || key == KEY_UP || key == KEY_ESC || key == KEY_LEFT || key == KEY_RIGHT;
				if(isControlKey && isLayerOpen) {
					//we only need to make special activities when the layerIsOpen and the key was a controlKey
					if(key == KEY_DOWN)		{ 	selectNextSuggestItem();		}
					if(key == KEY_UP)		{ 	selectPrevSuggestItem();		}
					if(key == KEY_ESC)		{	stopAllRequests();				}
					if(key == KEY_LEFT)		{	selectFirstLeftSuggestItem();	}
					if(key == KEY_RIGHT)	{	selectFirstRightSuggestItem();	}
				} else {
						lockLayer = false;
					// Don't update live-search if it's got the same value as last time
					if (this.value != this.lastValue && this.value.length >= config.minChars) {
						var q = this.value;
						if(self.running) {
							bufferQuery(q);
							return;
						}
						peformAjaxUpdate(q);
						this.lastValue = this.value;
					} else {
						if(this.value == '') {
							hideLiveSearch();
						}
					}
				}

			});
	});
};
