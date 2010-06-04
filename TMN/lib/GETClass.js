/**
 * @class GET
 * Creates a get Class.
 * @extends Object
 * @constructor
 * @author Michael Harrison (Adapted from http://www.netlobo.com/url_query_string_javascript.html)
 * @namespace Misc
 */
GET = function() {
	
	/**
	 * Grabs a value from the url. Acts like the php $_GET variable
	 * @param {String} name The name of the parameter you want to grab from the url.
	 */
	get: function( name )
	{
		name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
		var regexS = "[\\?&]"+name+"=([^&#]*)";
		var regex = new RegExp( regexS );
		var results = regex.exec( window.location.href );
		if( results == null )
			return "";
		else
			return results[1];
	}
}();