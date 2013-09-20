/*
    This file is part of Cyclos (www.cyclos.org).
    A project of the Social Trade Organisation (www.socialtrade.org).

    Cyclos is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    Cyclos is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Cyclos; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA

 */

// Initialize application
function initApp() {
	setupViewport();
    splashScreen();  	
	setupLanguage();
	setupGWT();
	onDeviceReady();
}
// Initialize PhoneGap
function onDeviceReady() {
	document.addEventListener('deviceready', (function() { PhoneGap.available = true; }), false); 	
}
// Setup GWT 
function setupGWT() {
	loadScript('cyclos/cyclos.nocache.js');   
}
// Setup the correct cordova.js library depending on the device
function setupCordova() {	
	if(isBlackBerry()) {
		writeScript('js/cordova-blackberry.js');
	} else if(isAndroid()) {
		writeScript('js/cordova-android.js');
	} else if(isIos()) {
	  	writeScript('js/cordova-ios.js');
	}	
}
// Setup custom styles
function setupStyles() {
	// Imports styles from here because it can't be done 
	// using media queries due to screen size detection issues
	var width = getScreenWidth();
	var height = getScreenHeight();
	var ratio = getDevicePixelRatio();
	if(width > 600 && height > 600 && ratio == 2) {
		writeCss('css/custom.css');
	}
}
// Writes a script with the given path
function writeScript(path) {
	document.write('<script language="javascript" src="'+path+'">"<\/script>');
}
// Writes a css with the given path
function writeCss(path) {
	document.write('<link rel="stylesheet" href="'+path+'" />');
}
// Loads a script with the given path
function loadScript(path) {
    var script = document.createElement('script');
    script.setAttribute('src', path);
    var body = document.getElementsByTagName("head")[0];
    body.appendChild(script);    
}
// Returns if the device browser is Blackberry based
function isBlackBerry() {
	var ua = navigator.userAgent.toLowerCase();
	return ua.indexOf('blackberry') > -1;			
}
// Returns if the device browser is Android based
function isAndroid() {
	var ua = navigator.userAgent.toLowerCase();
	return ua.indexOf('android') > -1;
}
// Returns if the device browser is IOS based
function isIos() {
	var ua = navigator.userAgent.toLowerCase();
	return ua.indexOf('ipad') > -1 || ua.indexOf('iphone') > -1 || ua.indexOf('ipod') > -1;
}
// Starts the spinner with the given instance
function startSpinner(instance, degSum, degMulti, delay) {
	var div = instance;
	var property = getTransformProperty(div);
	if (property) {
		var d = 0;
		spinnerInterval = setInterval(function () { div.style[property] = 'rotate(' + ((d++ % degSum)*degMulti) + 'deg)'; }, delay);
	}
}
// Stops the current running spinner
function stopSpinner() {
	if (spinnerInterval) {
		clearInterval(spinnerInterval);
		spinnerInterval = null;
	}
}
// Returns the correct transform property depending on the browser
function getTransformProperty(element) {
    var properties = ['transform', 'WebkitTransform', 'MozTransform', 'msTransform', 'OTransform'];
    var p;
    while (p = properties.shift()) {
        if (isNotUndefined(element.style[p])) {
            return p;
        }
    }
    return false;
}
// Returns the browser language
function getBrowserLanguage() {
	// PhoneGap on Android would always return EN in navigator.*language. Parse userAgent instead
	if (navigator && navigator.userAgent && (lang = navigator.userAgent.match(/android.*\W(\w\w)-(\w\w)\W/i))) {
		lang = lang[1];
	}
	if (!lang && navigator) {
		if (navigator.language) {
			lang = navigator.language;
		} else if (navigator.browserLanguage) {
			lang = navigator.browserLanguage;
		} else if (navigator.systemLanguage) {
			lang = navigator.systemLanguage;
		} else if (navigator.userLanguage) {
			lang = navigator.userLanguage;
		}		
	}
	return lang;
}
// Scrolls the document to the top
function scrollTop() {
	return document.all ? document.scrollTop : window.pageYOffset;
}
// Scrolls the document to the given Y coordinate
function scroll(top) {
	scroll(0, top);
}
// Cleans the given variable returning a string variable instance
function cleanString(object) {
	if(isUndefined(object)) {
		return '';
	}
	if(object || object == 0) {
		return ''+object;
	}	
	return '';
}
// Cleans the given object returning null if undefined
function cleanObject(object) {
	if(isUndefined(object)) {
		return null;
	}
	return object;
}
// Returns if the browser supports HTML 5 Local Storage
function supportsLocal() {
	try {
		if(isBlackBerry() && !isNextGenerationBB()) {
			return false;
		}
		return 'localStorage' in window && window['localStorage'] !== null;
	} catch (e) {
	    return false;
	}
}
// Returns if the UI can be reloaded
function supportsReload() {
	if(isBlackBerry() && !isNextGenerationBB()) {
		return false;
	}
	return true;
}
// Returns if the splash screen should be the small one
function isSmallSplash() {
	var width = getScreenWidth();
	var height = getScreenHeight();			
	return width <= 640 && height <= 640;
}
// Returns if the splash screen should be the medium one
function isMediumSplash() {
	var width = getScreenWidth();
	var height = getScreenHeight();
	return width <= 1024 && height <= 1024;
}
// Returns if the splash screen should be the large one
function isLargeSplash() {
	var width = getScreenWidth();
	var height = getScreenHeight();	
	return width > 1024 || height > 1024;
}
// Initializes the application splash screen and adjust it
function splashScreen() { 		
	var splash = getLocalStorage().getItem('splash');
	if(splash) {						
		var image = new Image();		
		image.onload = function() {							
			var loading = document.getElementById('loading');
			var loadingImage = document.getElementById('loadingImage');								
			resizeImage(loadingImage, loading, splash);				
			
			// If screen rotates when displaying the splash screen resize it
			window.addEventListener('resize', function() {	
				loading = document.getElementById('loading');
				if(loading) {
					resizeImage(loadingImage, loading, splash);
				}
			}, false);
		};
		image.src = splash;
	}	
}
// Resizes an image to fit the screen
function resizeImage(container, divContainer, imageSrc) {
	
	var width = getScreenWidth();	
	var height = getScreenHeight();
		
	if(width != 0 && height != 0) {
		if(width >= height) {
			width = parseInt(getLocalStorage().getItem('high'));
			height = parseInt(getLocalStorage().getItem('low'));
		} else {
			width = parseInt(getLocalStorage().getItem('low'));
			height = parseInt(getLocalStorage().getItem('high'));
		}					
					
		// Resize div container
		divContainer.style.width = width + 'px';
		divContainer.style.height = height + 'px';						
		
		var top;			
		if (width >= height) { // horizontal					
			container.style.width = width + 'px';
			container.style.height = width + 'px';
			top = -((width-height)/2);
			container.style.top = top + 'px';
			container.style.left = 0 + 'px';			
		} else { // vertical
			container.style.width = height + 'px';
			container.style.height = height + 'px';
			top = -((height-width)/2);
			container.style.left = top + 'px';
			container.style.top = 0 + 'px';	
		}			
		container.src = imageSrc;
		// Ensure the container is visible
		container.style.display = 'block';
	}
}
// Truncate the given element with the given maximum size
function truncate(element, max) {
	if(element && element.value) {
		if(element.value.length > max) {
			element.value = element.value.substring(0, max);
		}
	}
}
// Cleans the given number
function sanitizeNumber(element) {
	if(element && element.value) {
		element.value = replaceAll(element.value, '-', '');
	}
}
// Replaces all occurrences in the given string variable
function replaceAll(string, find, replace) {
    return String(string).split(find).join(replace);
}
// Returns if the device screen has high density pixel (HDPI)
function isHdpi() {
	return getDevicePixelRatio() == 1.5;
}
// Returns if the device screen has extra high density pixel (XHDPI)
function isXhdpi() {
	return getDevicePixelRatio() > 1.5;
}
// Returns the device screen pixel ratio 
function getDevicePixelRatio() { 
	if(isUndefined(window.devicePixelRatio)) {		
		return 1; // Assume 1:1
	}
	return window.devicePixelRatio; 
}
// Returns an array with the keys of the given map variable
function getMapKeys(stack) {
	 var f = cleanObject(stack);
     if(f) {  
    	 var a = new Array();    
         for (var p in f) { 
        	 a.push(p); 
         }    
         return a;         
     }
     return null;
}
// Returns the given key value in the given map
function getMapValue(stack, key) {
	var f = cleanObject(stack);	
    if(f) {            
    	return cleanString(f[key]);
    }    
    return null;
}
// Returns whether the given variable is numeric
function isNumeric(string) {
	var numericExpression = /^[0-9]+$/;
	return string.match(numericExpression);			
}
// Configures the viewport for specific devices
function setupViewport() {
	if(isNextGenerationIos()) {
		// Newer iphones / ipods / ipads
		var viewport = document.querySelector('meta[name=viewport]');
		viewport.setAttribute('content', 'width=device-width,height=device-height,minimum-scale=.5,maximum-scale=.5,user-scalable=no');
	} 
}
// Detects if device is a next generation IOS
function isNextGenerationIos() {
	var ua = navigator.userAgent.toLowerCase();
	var ratio = getDevicePixelRatio() > 1;
	return (ua.indexOf('iphone') > -1 || ua.indexOf('ipod') > -1 || ua.indexOf('ipad') > -1) && ratio;
}
// Detects if device is a next generation BB
function isNextGenerationBB() {
	try {
		if(isBlackBerry()) {
			var ua = navigator.userAgent;
			if (ua.indexOf("Version/") >= 0) { 
				var pos = ua.indexOf("Version/") + 8;				
				if(parseInt(ua.substring(pos, pos + 1)) >= 7) {
					return true;
				}			
			}	
		}
	} catch (e) {}
	return false;
}
// Sets GWT language tags
function setupLanguage() {
	var userLanguage = getLocalStorage().getItem('language');
	var language = userLanguage ? userLanguage : getBrowserLanguage();	
	if(language && typeof(language) == 'string') {
		var locale = language.substring(0, 2);
		var meta = document.createElement('meta');
		meta.setAttribute('name', 'gwt:property');
		meta.setAttribute('content', 'locale=' + locale);
		var body = document.getElementsByTagName("head")[0];
	    body.appendChild(meta);
	}
}
// Preloads the given image
function preloadImage(imageSrc) {
	var image = new Image();
	image.src = imageSrc;
}
// Returns the screen width
function getScreenWidth() {
	var width = window.screen.availWidth;
	// Safari issue always tells the same width
	// ignoring the device orientation
	if(isIos() && isLandscape()) { 
		return getScreenHeight();
	}
	if(isNextGenerationIos()) {
		return width * 2; // Retina
	}
	return width;
}
// Returns the screen height
function getScreenHeight() {
	var height = window.screen.availHeight;
	// Safari issue always tells the same height
	// ignoring the device orientation
	if(isIos() && isLandscape()) { 
		return getScreenWidth();
	}
	if(isNextGenerationIos()) {
		return height * 2; // Retina
	}
	return height;
}
// Debugs the given message in the console
function debug(message) {
	console.log(message);
}
// Returns true if the device is landscape, otherwise is portrait
function isLandscape() {
	var orientation = window.orientation;
	if(orientation) {		 
		 return orientation == 90 || orientation == -90;
	}
	return false;
}
// Reloads the application
function reloadApp() {
	if(isAndroid()) {
		navigator.app.loadUrl('file:///android_asset/www/index.html');
    } else {
    	window.location.reload(false);
    } 
}
// Returns local storage
function getLocalStorage() {
	if(supportsLocal()) {
		return localStorage;
	} else {
		if(cStorage) {
			return cStorage;
		} else {
			cStorage = new cookieStorage();
			return cStorage;
		}
	}
}
// Gets a cookie by name
function getCookie(name) {
	var i,x,y,ARRcookies = document.cookie.split(";");
	for (i = 0; i < ARRcookies.length; i++) {
		x = ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
		y = ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
		x = x.replace(/^\s+|\s+$/g,"");
		if (x == name) {
			return unescape(y);
		}
	}
}
// Sets a cookie by name
function setCookie(name,value) {
	var exdate = new Date();
	exdate.setDate(exdate.getDate() + 1825);
	var c_value = value + "; expires=" + exdate.toUTCString();
	document.cookie = name + "=" + c_value;
}
// Creates a cookie storage object
function cookieStorage() {
	this.getItem = get;
	this.setItem = set;
	this.clear = clearCookies;
	this.removeItem = remove;
	function get(item) {
		return getCookie(item);
	}
	function set(item, value) {
		setCookie(item, value);
	}
	function clearCookies() {		
		var cookies = document.cookie.split(";");
	    for (var i = 0; i < cookies.length; i++) {
	        var cookie = cookies[i];
	        var eqPos = cookie.indexOf("=");
	        var item = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
	        document.cookie = item + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT";
	    }
	}
	function remove(item) {
		document.cookie = item + '=; expires=Thu, 01 Jan 1970 00:00:00 GMT;';
	}
}
// Scrolls the given element up
function scrollUp(id) {
	var element = document.getElementById(id);
	stopScroll();
	scrollInterval = setInterval(function() {
		element.scrollTop = element.scrollTop - 10;
		if(element.scrollTop <= 0) {				
			stopScroll();
		}
	},10); 
}
// Stops scrolling
function stopScroll() {
	clearInterval(scrollInterval);	
}
// Scrolls the given element down
function scrollDown(id) {
	var element = document.getElementById(id);
	stopScroll();
	scrollInterval = setInterval(function() {		
		element.scrollTop = element.scrollTop + 10;
		if(element.scrollTop >= element.scrollHeight) {
			stopScroll();
		}
	},10); 
}
// Returns if the device supports touch events
function isTouchDevice() {
	if(isBlackBerry() && !isNextGenerationBB()) {
		return false;
	}
	return !!('ontouchstart' in window);
}
// Returns if the device can handle complex UI widgets
function supportsComplexUI() {
	if(isBlackBerry() && !isNextGenerationBB()) {
		return false;
	}
	return true;
}
// Returns if the given argument is undefined
function isUndefined(arg) {
	return (typeof arg === 'undefined');		 	
}
// Returns if the given argument is not undefined
function isNotUndefined(arg) {
	return !isUndefined(arg);
}
var scrollInterval;
var spinnerInterval;
var cStorage;