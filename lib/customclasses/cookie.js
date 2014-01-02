
function setcookie( name, value, expires, path, domain, secure ) {
	
	var cookie_string = name + "=" + escape ( value );

	if ( expires !== undefined )
		cookie_string += "; expires=" + expires.toGMTString();

	if ( path === undefined ){
		cookie_string += "; path=/dev/TMN/";
	} else {
		cookie_string += "; path=" + escape ( path );
	}

	if ( domain !== undefined )
		cookie_string += "; domain=" + escape ( domain );

	if ( secure !== undefined )
		cookie_string += "; secure";

	document.cookie = cookie_string;
}

function getcookie(){
	var cookie = [];
	
	if ( document.cookie.length > 0 ){ //check if the cookie exists
	
		var cookie_array = document.cookie.split("; "); //break up each cookie into array entries (ie ['first=michael', 'last=harrison'])
		
		for (count = 0; count < cookie_array.length; count++)
		{
			key_val = cookie_array[count].split("="); //for each array entry split it into key/value pair (ie ['first', 'michael'])
			cookie[key_val[0]] = key_val[1]; //store each cookie as a key value pair (ie cookie['first'] = 'michael')
		}
	}
	
	return cookie;
}

function purgecookies(){
	
	if ( document.cookie.length > 0 ){ //check if the cookie exists
	
		var cookie_array = document.cookie.split("; "); //break up each cookie into array entries (ie ['first=michael', 'last=harrison'])
		
		for (count = 0; count < cookie_array.length; count++)
		{
			key_val = cookie_array[count].split("="); //for each array entry split it into key/value pair (ie ['first', 'michael'])
			cookiestring = key_val[0] + "=; expires=" + (new Date() + 3600) + "; path=/dev/TMN/";
			//alert(document.cookie);
			document.cookie = cookiestring;
		}
	}
}
