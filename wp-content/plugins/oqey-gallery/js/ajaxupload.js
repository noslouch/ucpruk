/**
 * Ajax upload
 * Project page - http://valums.com/ajax-upload/
 * Copyright (c) 2008 Andris Valums, http://valums.com
 * Licensed under the MIT license (http://valums.com/mit-license/)
 * Version 3.5 (23.06.2009)
 */

/**
 * Changes from the previous version:
 * 1. Added better JSON handling that allows to use 'application/javascript' as a response
 * 2. Added demo for usage with jQuery UI dialog
 * 3. Fixed IE "mixed content" issue when used with secure connections
 * 
 * For the full changelog please visit: 
 * http://valums.com/ajax-upload-changelog/
 */

(function(){
	
var d = document, w = window;

/**
 * Get element by id
 */	
function get(element){
	if (typeof element == "string")
		element = d.getElementById(element);
	return element;
}

/**
 * Attaches event to a dom element
 */
function addEvent(el, type, fn){
	if (w.addEventListener){
		el.addEventListener(type, fn, false);
	} else if (w.attachEvent){
		var f = function(){
		  fn.call(el, w.event);
		};			
		el.attachEvent('on' + type, f)
	}
}


/**
 * Creates and returns element from html chunk
 */
var toElement = function(){
	var div = d.createElement('div');
	return function(html){
		div.innerHTML = html;
		var el = div.childNodes[0];
		div.removeChild(el);
		return el;
	}
}();

function hasClass(ele,cls){
	return ele.className.match(new RegExp('(\\s|^)'+cls+'(\\s|$)'));
}
function addClass(ele,cls) {
	if (!hasClass(ele,cls)) ele.className += " "+cls;
}
function removeClass(ele,cls) {
	var reg = new RegExp('(\\s|^)'+cls+'(\\s|$)');
	ele.className=ele.className.replace(reg,' ');
}

// getOffset function copied from jQuery lib (http://jquery.com/)
if (document.documentElement["getBoundingClientRect"]){
	// Get Offset using getBoundingClientRect
	// http://ejohn.org/blog/getboundingclientrect-is-awesome/
	var getOffset = function(el){
		var box = el.getBoundingClientRect(),
		doc = el.ownerDocument,
		body = doc.body,
		docElem = doc.documentElement,
		
		// for ie 
		clientTop = docElem.clientTop || body.clientTop || 0,
		clientLeft = docElem.clientLeft || body.clientLeft || 0,
		
		// In Internet Explorer 7 getBoundingClientRect property is treated as physical,
		// while others are logical. Make all logical, like in IE8.
		
		
		zoom = 1;
		if (body.getBoundingClientRect) {
			var bound = body.getBoundingClientRect();
			zoom = (bound.right - bound.left)/body.clientWidth;
		}
		if (zoom > 1){
			clientTop = 0;
			clientLeft = 0;
		}
		var top = box.top/zoom + (window.pageYOffset || docElem && docElem.scrollTop/zoom || body.scrollTop/zoom) - clientTop,
		left = box.left/zoom + (window.pageXOffset|| docElem && docElem.scrollLeft/zoom || body.scrollLeft/zoom) - clientLeft;
				
		return {
			top: top,
			left: left
		};
	}
	
} else {
	// Get offset adding all offsets 
	var getOffset = function(el){
		if (w.jQuery){
			return jQuery(el).offset();
		}		
			
		var top = 0, left = 0;
		do {
			top += el.offsetTop || 0;
			left += el.offsetLeft || 0;
		}
		while (el = el.offsetParent);
		
		return {
			left: left,
			top: top
		};
	}
}

function getBox(el){
	var left, right, top, bottom;	
	var offset = getOffset(el);
	left = offset.left;
	top = offset.top;
						
	right = left + el.offsetWidth;
	bottom = top + el.offsetHeight;		
		
	return {
		left: left,
		right: right,
		top: top,
		bottom: bottom
	};
}

/**
 * Crossbrowser mouse coordinates
 */
function getMouseCoords(e){		
	// pageX/Y is not supported in IE
	// http://www.quirksmode.org/dom/w3c_cssom.html			
	if (!e.pageX && e.clientX){
		// In Internet Explorer 7 some properties (mouse coordinates) are treated as physical,
		// while others are logical (offset).
		var zoom = 1;	
		var body = document.body;
		
		if (body.getBoundingClientRect) {
			var bound = body.getBoundingClientRect();
			zoom = (bound.right - bound.left)/body.clientWidth;
		}

		return {
			x: e.clientX / zoom + d.body.scrollLeft + d.documentElement.scrollLeft,
			y: e.clientY / zoom + d.body.scrollTop + d.documentElement.scrollTop
		};
	}
	
	return {
		x: e.pageX,
		y: e.pageY
	};		

}
/**
 * Function generates unique id
 */		
var getUID = function(){
	var id = 0;
	return function(){
		return 'ValumsAjaxUpload' + id++;
	}
}();

function fileFromPath(file){
	return file.replace(/.*(\/|\\)/, "");			
}

function getExt(file){
	return (/[.]/.exec(file)) ? /[^.]+$/.exec(file.toLowerCase()) : '';
}			

// Please use AjaxUpload , Ajax_upload will be removed in the next version
Ajax_upload = AjaxUpload = function(button, options){
	if (button.jquery){
		// jquery object was passed
		button = button[0];
	} else if (typeof button == "string" && /^#.*/.test(button)){					
		button = button.slice(1);				
	}
	button = get(button);	
	
	this._input = null;
	this._button = button;
	this._disabled = false;
	this._submitting = false;
	// Variable changes to true if the button was clicked
	// 3 seconds ago (requred to fix Safari on Mac error)
	this._justClicked = false;
	this._parentDialog = d.body;
	
	if (window.jQuery && jQuery.ui && jQuery.ui.dialog){
		var parentDialog = jQuery(this._button).parents('.ui-dialog');
		if (parentDialog.length){
			this._parentDialog = parentDialog[0];
		}
	}			
					
	this._settings = {
		// Location of the server-side upload script
		action: 'upload.php',			
		// File upload name
		name: 'userfile',
		// Additional data to send
		data: {},
		// Submit file as soon as it's selected
		autoSubmit: true,
		// The type of data that you're expecting back from the server.
		// Html and xml are detected automatically.
		// Only useful when you are using json data as a response.
		// Set to "json" in that case. 
		responseType: false,
		// When user selects a file, useful with autoSubmit disabled			
		onChange: function(file, extension){},					
		// Callback to fire before file is uploaded
		// You can return false to cancel upload
		onSubmit: function(file, extension){},
		// Fired when file upload is completed
		// WARNING! DO NOT USE "FALSE" STRING AS A RESPONSE!
		onComplete: function(file, response) {}
	};

	// Merge the users options with our defaults
	for (var i in options) {
		this._settings[i] = options[i];
	}
	
	this._createInput();
	this._rerouteClicks();
}
			
// assigning methods to our class
AjaxUpload.prototype = {
	setData : function(data){
		this._settings.data = data;
	},
	disable : function(){
		this._disabled = true;
	},
	enable : function(){
		this._disabled = false;
	},
	// removes ajaxupload
	destroy : function(){
		if(this._input){
			if(this._input.parentNode){
				this._input.parentNode.removeChild(this._input);
			}
			this._input = null;
		}
	},				
	/**
	 * Creates invisible file input above the button 
	 */
	_createInput : function(){
		var self = this;
		var input = d.createElement("input");
		input.setAttribute('type', 'file');
		input.setAttribute('name', this._settings.name);
		var styles = {
			'position' : 'absolute'
			,'margin': '-5px 0 0 -175px'
			,'padding': 0
			,'width': '220px'
			,'height': '30px'
			,'fontSize': '14px'								
			,'opacity': 0
			,'cursor': 'pointer'
			,'display' : 'none'
			,'zIndex' :  2147483583 //Max zIndex supported by Opera 9.0-9.2x 
			// Strange, I expected 2147483647					
		};
		for (var i in styles){
			input.style[i] = styles[i];
		}
		
		// Make sure that element opacity exists
		// (IE uses filter instead)
		if ( ! (input.style.opacity === "0")){
			input.style.filter = "alpha(opacity=0)";
		}
							
		this._parentDialog.appendChild(input);

		addEvent(input, 'change', function(){
			// get filename from input
			var file = fileFromPath(this.value);	
			if(self._settings.onChange.call(self, file, getExt(file)) == false ){
				return;				
			}														
			// Submit form when value is changed
			if (self._settings.autoSubmit){
				self.submit();						
			}						
		});
		
		// Fixing problem with Safari
		// The problem is that if you leave input before the file select dialog opens
		// it does not upload the file.
		// As dialog opens slowly (it is a sheet dialog which takes some time to open)
		// there is some time while you can leave the button.
		// So we should not change display to none immediately
		addEvent(input, 'click', function(){
			self.justClicked = true;
			setTimeout(function(){
				// we will wait 3 seconds for dialog to open
				self.justClicked = false;
			}, 3000);			
		});		
		
		this._input = input;
	},
	_rerouteClicks : function (){
		var self = this;
	
		// IE displays 'access denied' error when using this method
		// other browsers just ignore click()
		// addEvent(this._button, 'click', function(e){
		//   self._input.click();
		// });
				
		var box, dialogOffset = {top:0, left:0}, over = false;							
		addEvent(self._button, 'mouseover', function(e){
			if (!self._input || over) return;
			over = true;
			box = getBox(self._button);
					
			if (self._parentDialog != d.body){
				dialogOffset = getOffset(self._parentDialog);
			}	
		});
		
	
		// we can't use mouseout on the button,
		// because invisible input is over it
		addEvent(document, 'mousemove', function(e){
			var input = self._input;			
			if (!input || !over) return;
			
			if (self._disabled){
				removeClass(self._button, 'hover');
				input.style.display = 'none';
				return;
			}	
										
			var c = getMouseCoords(e);

			if ((c.x >= box.left) && (c.x <= box.right) && 
			(c.y >= box.top) && (c.y <= box.bottom)){			
				input.style.top = c.y - dialogOffset.top + 'px';
				input.style.left = c.x - dialogOffset.left + 'px';
				input.style.display = 'block';
				addClass(self._button, 'hover');				
			} else {		
				// mouse left the button
				over = false;
				if (!self.justClicked){
					input.style.display = 'none';
				}
				removeClass(self._button, 'hover');
			}			
		});			
			
	},
	/**
	 * Creates iframe with unique name
	 */
	_createIframe : function(){
		// unique name
		// We cannot use getTime, because it sometimes return
		// same value in safari :(
		var id = getUID();
		
		// Remove ie6 "This page contains both secure and nonsecure items" prompt 
		// http://tinyurl.com/77w9wh
		var iframe = toElement('<iframe src="javascript:false;" name="' + id + '" />');
		iframe.id = id;
		iframe.style.display = 'none';
		d.body.appendChild(iframe);			
		return iframe;						
	},
	/**
	 * Upload file without refreshing the page
	 */
	submit : function(){
		var self = this, settings = this._settings;	
					
		if (this._input.value === ''){
			// there is no file
			return;
		}
										
		// get filename from input
		var file = fileFromPath(this._input.value);			

		// execute user event
		if (! (settings.onSubmit.call(this, file, getExt(file)) == false)) {
			// Create new iframe for this submission
			var iframe = this._createIframe();
			
			// Do not submit if user function returns false										
			var form = this._createForm(iframe);
			form.appendChild(this._input);
			
			form.submit();
			
			d.body.removeChild(form);				
			form = null;
			this._input = null;
			
			// create new input
			this._createInput();
			
			var toDeleteFlag = false;
			
			addEvent(iframe, 'load', function(e){
					
				if (// For Safari
					iframe.src == "javascript:'%3Chtml%3E%3C/html%3E';" ||
					// For FF, IE
					iframe.src == "javascript:'<html></html>';"){						
					
					// First time around, do not delete.
					if( toDeleteFlag ){
						// Fix busy state in FF3
						setTimeout( function() {
							d.body.removeChild(iframe);
						}, 0);
					}
					return;
				}				
				
				var doc = iframe.contentDocument ? iframe.contentDocument : frames[iframe.id].document;

				// fixing Opera 9.26
				if (doc.readyState && doc.readyState != 'complete'){
					// Opera fires load event multiple times
					// Even when the DOM is not ready yet
					// this fix should not affect other browsers
					return;
				}
				
				// fixing Opera 9.64
				if (doc.body && doc.body.innerHTML == "false"){
					// In Opera 9.64 event was fired second time
					// when body.innerHTML changed from false 
					// to server response approx. after 1 sec
					return;				
				}
				
				var response;
									
				if (doc.XMLDocument){
					// response is a xml document IE property
					response = doc.XMLDocument;
				} else if (doc.body){
					// response is html document or plain text
					response = doc.body.innerHTML;
					if (settings.responseType && settings.responseType.toLowerCase() == 'json'){
						// If the document was sent as 'application/javascript' or
						// 'text/javascript', then the browser wraps the text in a <pre>
						// tag and performs html encoding on the contents.  In this case,
						// we need to pull the original text content from the text node's
						// nodeValue property to retrieve the unmangled content.
						// Note that IE6 only understands text/html
						if (doc.body.firstChild && doc.body.firstChild.nodeName.toUpperCase() == 'PRE'){
							response = doc.body.firstChild.firstChild.nodeValue;
						}
						if (response) {
							response = window["eval"]("(" + response + ")");
						} else {
							response = {};
						}
					}
				} else {
					// response is a xml document
					var response = doc;
				}
																			
				settings.onComplete.call(self, file, response);
						
				// Reload blank page, so that reloading main page
				// does not re-submit the post. Also, remember to
				// delete the frame
				toDeleteFlag = true;
				
				// Fix IE mixed content issue
				iframe.src = "javascript:'<html></html>';";		 								
			});
	
		} else {
			// clear input to allow user to select same file
			// Doesn't work in IE6
			// this._input.value = '';
			d.body.removeChild(this._input);				
			this._input = null;
			
			// create new input
			this._createInput();						
		}
	},		
	/**
	 * Creates form, that will be submitted to iframe
	 */
	_createForm : function(iframe){
		var settings = this._settings;
		
		// method, enctype must be specified here
		// because changing this attr on the fly is not allowed in IE 6/7		
		var form = toElement('<form method="post" enctype="multipart/form-data"></form>');
		form.style.display = 'none';
		form.action = settings.action;
		form.target = iframe.name;
		d.body.appendChild(form);
		
		// Create hidden input element for each data key
		for (var prop in settings.data){
			var el = d.createElement("input");
			el.type = 'hidden';
			el.name = prop;
			el.value = settings.data[prop];
			form.appendChild(el);
		}			
		return form;
	}	
};
})();
		
		
/*
 * jQuery UI Checkbox 0.1
 *
 * Copyright (c) 2009 Jeremy Lea <reg@openpave.org>
 * Dual licensed under the MIT and GPL licenses.
 *
 * http://docs.jquery.com/Licensing
 *
 * Based loosely on plugin by alexander.farkas.
 * http://www.protofunc.com/scripts/jquery/checkbox-radiobutton/
 *
 * Label, id and title handling by Bela Gabor Zakar <zbacsi@gmail.com>
 */

(function($){

// Set up IE for VML if we have not done so already...
if ($.browser.msie) {
	// IE6 background flicker fix
	try	{
		document.execCommand('BackgroundImageCache', false, true);
	} catch (e) {}

	if (!document.namespaces["v"]) {
		$("head").prepend("<xml:namespace ns='urn:schemas-microsoft-com:vml' prefix='v' />");
		$("head").prepend("<?import namespace='v' implementation='#default#VML' ?>");
	}
}

$.widget("ui.checkbox", {
	_init: function() {
		// XXX: UI widget will not actually fail...
		if (!this.element.is(":radio,:checkbox")) {
			return false;
		}
		// _radio stores the members of the radio group (if there is one).
		if (this.element.is(":radio")) {
			this._radio = $(this.element[0].form).find("input:radio")
				.filter('[name="'+this.element[0].name+'"]');
		} else {
			this._radio = false;
		}

		var self = this, o = this.options; // closures for callbacks.
		// Set the ARIA properties on the native input
		this.element
			.attr({
				role: (this._radio ? "radio" : "checkbox"),
				"aria-checked": !!this.element[0].checked
			});
		// Create the main wrapper element (which gives the background box)
		this._wrapper = this.element.wrap($("<span />")).parent()
			.addClass((this._radio ? "ui-radio" : "ui-checkbox") +
				" ui-state-default").attr({'title':$(this.element).attr('title')});
		// Create the icon element
		this._wrapper.prepend($("<span/>")
			.addClass("ui-icon " + this._icon(false))
			.click(function(event) {
				// The icon covers the entire box, but is not in a bubbling
				// path, so use it to trigger the native event, and let it
				// take care of the rest.  Gobble up this fake event.
				self.element[0].click();
				event.preventDefault();
				event.stopImmediatePropagation();
				return false;
			}));
		// handle label click
		$('label[for='+this.element.attr('id')+']').click(function(event){				
			self.element[0].click();
			event.preventDefault();
			event.stopImmediatePropagation();
			return false;
		});
		if ($.browser.msie) {
			// IE does not support rounded corners...  We should check
			// something to see if it does.   But anyway, we make another
			// element which is a VML roundrect, and hide the normal wrapper.
			//
			// XXX: Check if we can use this in place of the span.
			// XXX: Implement background images.
			// XXX: Tidy this up to be more jQuery'ish
			//
			// Play tricks to get around arcsize bugs...
			this._wrapper[0].insertAdjacentHTML("afterBegin",
				"<v:roundrect arcsize='" + (this._radio ? "1" : "0.1") +
				"'><v:stroke /><v:fill /></v:roundrect>");
			this._vml = this._wrapper[0].childNodes[0];
			var ss = this._wrapper[0].currentStyle;
			this._vml.style.top = "-1px";
			this._vml.style.left = "-1px";
			this._vml.style.width = parseInt(ss.width)+1+"px";
			this._vml.style.height = parseInt(ss.height)+1+"px";
			this._doVML();
			this._vml.style.visibility = "visible";
			this._wrapper.css('visibility','hidden');
			// Listen for class or other changes to recreate the elements.
			this._wrapper[0].onpropertychange = function() {
				switch (event.propertyName) {
				case 'className':
				case 'style.borderTopWidth':
				case 'style.borderTopColor':
				case 'style.backgroundColor':
				case 'style.filter':
					self._doVML();
					break;
				}
			}
			// Listen for the custom event from the theme switcher.
			$().bind('ui-theme-switch', function() {
				setTimeout(function() {
					self._doVML();
				}, 500);
				return false;
			});
		}
		if ($.browser.opera) {
			// Opera also does not support rounded corners...  Use an SVG
			// element instead.  Same as above, but a little simpler.
			//
			// XXX: Check if we can use this in place of the span.
			// XXX: Implement background images.
			// XXX: Tidy this up to be more jQuery'ish
			var svg = document.createElementNS("http://www.w3.org/2000/svg","svg");
			var rect = document.createElementNS("http://www.w3.org/2000/svg","rect");
			var ss = this._wrapper[0].currentStyle;
			rect.setAttributeNS(null, "x", "1px");
			rect.setAttributeNS(null, "y", "1px");
			rect.setAttributeNS(null, "width", ss.width);
			rect.setAttributeNS(null, "height", ss.height);
			rect.setAttributeNS(null, "rx", (this._radio ? "6px" : "2px"));
			svg.appendChild(rect);
			this._wrapper.prepend(svg);
			this._svg = this._wrapper[0].firstChild;
			this._svg.style.width = parseInt(ss.width)+2+"px";
			this._svg.style.height = parseInt(ss.height)+2+"px";
			this._doSVG();
			this._svg.style.visibility = "visible";
			this._wrapper.css('visibility','hidden');
			// Listen for class changes.
			this._wrapper.bind("DOMAttrModified", function(event) {
				if (event.attrName === 'class') {
					self._doSVG();
				}
			});
			// Listen for the custom event from the theme switcher.
			$().bind("ui-theme-switch", function() {
				self._doSVG();
				return false;
			});
		}

		// Set up events...
		this._wrapper
			.hover(function(event) {
				if (!o.disabled) {
					$(this).addClass("ui-state-hover");
				}
			}, function(event) {
				if (!o.disabled) {
					$(this).removeClass("ui-state-hover");
				}
			})
			.bind("mousedown", function(event) {
				if (!o.disabled) {
					$(this).addClass("ui-state-active");
				}
			})
			.bind("mouseup", function(event) {
				if (!o.disabled) {
					$(this).removeClass("ui-state-active");
				}
			})
			.bind(this.widgetEventPrefix + "focus", function(event) {
				if (!o.disabled) {
					if (self._radio) {
						self._radio.not(self.element)
							.removeClass("ui-state-focus");
					}
					$(this).addClass("ui-state-focus");
				}
			})
			.bind(this.widgetEventPrefix + "blur", function(event) {
				if (!o.disabled) {
					$(this).removeClass("ui-state-focus");
				}
			})
			.bind(this.widgetEventPrefix + "click", function(event) {
				if (!o.disabled) {
					if (self._radio) {
						self._radio.not(self.element).checkbox("uncheck");
						self.check();
					} else {
						self.toggle();
					}
				}
			});
		this.element
			.bind("focus." + this.widgetName, function(event) {
				self._trigger("focus", event); // Actually checkboxfocus
			})
			.bind("blur." + this.widgetName, function(event) {
				self._trigger("blur", event); // Actually checkboxblur
			})
			.bind("click." + this.widgetName, function(event) {
				self._trigger("click", event); // Actually checkboxclick
			});

		// Capture the initial value
		this._setData("checked", !!this.element[0].checked);
	},
	destroy: function() {
		this._wrapper.replaceWith(this.element);
		this.element.removeAttr("role")
			.removeAttr("aria-checked")
			.unbind("."+this.widgetName);

		$.widget.prototype.destroy.apply(this, arguments);
	},

	// Most of the work is done here.
	_setData: function(key, value) {
		$.widget.prototype._setData.apply(this, arguments);

		if (key == "disabled") {
			if (value) {
				this.element.attr("disabled","disabled");
				this._wrapper.removeClass("ui-state-focus ui-state-hover ui-state-active");
			} else {
				this.element.removeAttr("disabled");
			}
			this._wrapper
				[value ? "addClass" : "removeClass"](
					this.widgetName + "-disabled " +
					this.namespace + "-state-disabled");
		} else if (key == "checked") {
			this.element[0].checked = !!value;
			this.element.attr("aria-checked", !!value);
			this._wrapper.find(".ui-icon")
				.addClass(this._icon(!!value))
				.removeClass(this._icon(!value));
		}
	},

	check: function() {
		this._setData("checked", true);
	},
	uncheck: function() {
		this._setData("checked", false);
	},
	toggle: function() {
		this._setData("checked", !this._getData("checked"));
	},


	_icon: function(state) {
		if (this._radio) {
			return "ui-icon-"
				+ this.options[state?"radioChecked":"radioUnchecked"];
		} else {
			return "ui-icon-"
				+ this.options[state?"checkboxChecked":"checkboxUnchecked"];
		}
	},

	_opacityFixed: false,
	_inFixup: false,
	_fixStyle: function(jq, re) {
		var s = jq.attr("style").replace(re,"");
		if (s !== "") {
			jq.attr("style",s);
		} else {
			jq.removeAttr("style");
		}
	},
	// Only called for IE
	_doVML: function() {
		if (!this._vml || this._inFixup) {
			return;
		}
		this._inFixup = true;
		var ss, op;
		if (this._opacityFixed) {
			this._vml.childNodes[0].opacity = '1';
			this._vml.childNodes[1].opacity = '1';
			this._fixStyle(this._wrapper.find(".ui-icon"),/filter[^;]*\;?/i);
			this._fixStyle(this._wrapper,/filter[^;]*\;?/i);
			this._opacityFixed = false;
		}
		ss = this._wrapper[0].currentStyle;
		// IE6 needs both of these...
		this._vml.strokecolor = ss.borderTopColor;
		this._vml.strokeweight = ss.borderTopWidth;
		this._vml.fillcolor = ss.backgroundColor;
		this._vml.childNodes[0].color = ss.borderTopColor;
		this._vml.childNodes[0].weight = ss.borderTopWidth;
		this._vml.childNodes[1].color = ss.backgroundColor;
		if (ss.filter && ss.filter.search(/Alpha/i) !== -1) {
			op = /(\d+)/.exec(ss.filter);
			this._wrapper.find(".ui-icon").css("filter",ss.filter);
			this._vml.childNodes[0].opacity = op[1]/100;
			this._vml.childNodes[1].opacity = op[1]/100;
			this._wrapper.css("filter","");
			this._opacityFixed = true;
		}
		this._inFixup = false;
	},
	// Only called for Opera
	_doSVG: function() {
		if (!this._svg || this._inFixup) {
			return;
		}
		this._inFixup = true;
		var ss, op;
		// Opera doesn't carry over opacity from the hidden container...
		if (this._opacityFixed) {
			this._fixStyle(this._wrapper.find(".ui-icon"),/opacity[^;]*\;?/i);
			this._fixStyle(this._wrapper.find("rect"),/opacity[^;]*\;?/i);
			this._fixStyle(this._wrapper,/opacity[^;]*\;?/i);
			this._opacityFixed = false;
		}
		ss = this._wrapper[0].currentStyle;
		this._svg.firstChild.style.stroke = ss.borderTopColor;
		this._svg.firstChild.style.strokeWidth = ss.borderTopWidth;
		this._svg.firstChild.style.fill = ss.backgroundColor;
		if (ss.opacity && ss.opacity !== 1) {
			op = ss.opacity;
			this._wrapper.find(".ui-icon").css("opacity",op);
			this._wrapper.find("rect").css("opacity",op);
			this._wrapper[0].style.opacity = "1";
			this._opacityFixed = true;
		}
		this._inFixup = false;
	}

});
$.ui.checkbox.defaults = {
	checkboxChecked: "check",
	checkboxUnchecked: "empty",
	radioChecked: "bullet",
	radioUnchecked: "empty"
};

})(jQuery);
