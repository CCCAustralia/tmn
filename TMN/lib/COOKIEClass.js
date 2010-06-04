/**
 * @class COOKIE
 * Creates a COOKIE Class.
 * @extends Object
 * @author Michael Harrison
 * @namespace Misc
 * @param {String} path (optional) The path that the cookies are created from. (Defaults to "/")
 */
COOKIE = function( path ) {
	
	path === undefined ? this.path = "/" : this.path = path;
	
	return {
		/**
		 * Sets a cookie with the value and parameters that you send it.
		 * @param {String} 	name 	The name of the cookie you want to set (can be a cookie that already exist, it will update in this case).
		 * @param {Mixed} 	value 	The value of the cookie.
		 * @param {Number} 	expires The number of milliseconds till the cookie expires, 0 means on browser close)
		 * @param {String} 	path 	The path in the domain that the cookie can be accessed from
		 * @param {String} 	domain	The domain the cookie can be accessed from
		 * @param {Boolean} secure 	Set to true if you want it to be ssl encoded
		 */
		setCookie: function( name, value, expires, path, domain, secure ) {
			
			var cookie_string = name + "=" + escape ( value );
		
			if ( expires !== undefined )
				cookie_string += "; expires=" + expires.toGMTString();
		
			if ( path === undefined ){
				cookie_string += "; path=" + this.path;
			} else {
				cookie_string += "; path=" + escape ( path );
			}
		
			if ( domain !== undefined )
				cookie_string += "; domain=" + escape ( domain );
		
			if ( secure !== undefined )
				cookie_string += "; secure";
		
			document.cookie = cookie_string;
		},
		/**
		 * Gets the value of a cookie called name.
		 * @param {String} 	name 	The name of the cookie you want read.
		 * @returns {String}		The value of the cookie (null if not found)
		 */
		getCookie: function( name ){
			var cookie = this.getCookies();
			
			if ( cookie[name] !== undefined ){ 							//check if the cookie exists
				return cookie[name];									//if it exists return it
			} else {
				return null;											//otherwise return null
			}
		},
		/**
		 * Removes the cookie called name.
		 * @param {String} 	name 	The name of the cookie you want delete.
		 * @returns {Boolean}		Whether the delete succeeded or not.
		 */
		deleteCookie: function( name ) {
			var cookie = this.getCookies();
			
			if ( cookie[name] !== undefined ){ 							//check if the cookie exists
				document.cookie = cookie[name] + "=; expires=" + (new Date() - 3600) + "; path=" + this.path;									//if it exists delete it
				return true;											// and return true
			} else {
				return false;											//otherwise return false
			}
		},
		/**
		 * Gets all the cookies available to this domain and path and returns them as an associative array.
		 * @returns {Array}		An associative array that conatains all the cookies available to you.
		 */
		getCookieArray: function(){
			var cookie = [];
			
			if ( document.cookie.length > 0 ){ 							//check if the cookie exists
			
				var cookie_array = document.cookie.split("; "); 		//break up each cookie into array entries (ie ['first=michael', 'last=harrison'])
				
				for (count = 0; count < cookie_array.length; count++)
				{
					key_val = cookie_array[count].split("="); 			//for each array entry split it into key/value pair (ie ['first', 'michael'])
					cookie[key_val[0]] = key_val[1]; 					//store each cookie as a key value pair (ie cookie['first'] = 'michael')
				}
			}
			
			return cookie;
		},
		/**
		 * Removes all cookies from this domain and path.
		 */
		purgeCookies: function() {
			
			if ( document.cookie.length > 0 ){ //check if the cookie exists
			
				var cookie_array = document.cookie.split("; "); //break up each cookie into array entries (ie ['first=michael', 'last=harrison'])
				
				for (count = 0; count < cookie_array.length; count++)
				{
					key_val = cookie_array[count].split("="); //for each array entry split it into key/value pair (ie ['first', 'michael'])
					cookiestring = key_val[0] + "=; expires=" + (new Date() - 3600) + "; path=" + this.path;
					//alert(document.cookie);
					document.cookie = cookiestring;
				}
			}
		}
	};
}();
