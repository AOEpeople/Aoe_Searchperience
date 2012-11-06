
//in an environment without baseurl you may want to use the following
// var	suggestUrl 							= location.protocol + '//' + location.hostname + '/';
// var	searchUrl 							= location.protocol + '//' + location.hostname + '/';
// var	widgetUrl 							= location.protocol + '//' + location.hostname + '/';
// var	liveSearchUrl						= location.protocol + '//' + location.hostname + '/';
// var	liveSuggestUrl						= location.protocol + '//' + location.hostname + '/';

var enableInnerShiv							= false;
var doWidgetInit 							= false;

var aoeSolrDataTypeSuggest 					= 'jsonp';
var aoeSolrDataTypeLivesearch 				= 'jsonp';
var aoeSolrDataTypeSearch 					= 'jsonp';
var aoeSolrDataTypeLiveSuggest				= 'jsonp';

var aoeSolrAutosuggestSelector				= 'input.solr-suggest-field_disabled';
var aoeSolrSearchfieldSelector				= 'input#search';
var aoeSolrLivesuggestSelector				= 'input#search';
var aoeSolrForceOnlyFilenameFromAppendix 	= true;
var aoeSolrProfilerEnable					= true;
var solrSelctionContainerSelector 			= '.page div.main';
var aoeSolrInitRemoteWidgets				= false;
var aoeSolrLiveSuggestOffsetTop				= 0;
var aoeSolrLiveSuggestOffetLeft				= -249;
var aoeSolrLiveSuggestWidth = 500;

//add this to use the fast cached TSFE
//var aoeSolrSearchPid						= 0;

//if you need to specify the solr parentcontainer you need to uncomment the following
//this is typically needed when the search should be displayed on a remote site, where initially is
//no search container in place
var aoeSolrParentContainerSelector		= '.page div.main';
