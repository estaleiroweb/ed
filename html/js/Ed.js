function getType(obj) {
	try {
		return Object.prototype.toString.call(obj).match(/^\[object\s(.*)\]$/)[1].toLowerCase();
	} catch (e) {
		if (obj === null) return 'null';
		if (obj === undefined) return 'undefined';
	}
	return typeof obj;
}
function clone(obj) {
	if (obj === null) return null;
	if (typeof (obj) != 'object') return obj;
	var copy = obj.constructor();
	for (var attr in obj) {
		if (obj.hasOwnProperty(attr)) copy[attr] = obj[attr];
	}
	return copy;
}
if (!Array.from) {
	Array.from = (function () {
		var toStr = Object.prototype.toString;
		var isCallable = function (fn) {
			return typeof fn === 'function' || toStr.call(fn) === '[object Function]';
		};
		var toInteger = function (value) {
			var number = Number(value);
			if (isNaN(number)) { return 0; }
			if (number === 0 || !isFinite(number)) { return number; }
			return (number > 0 ? 1 : -1) * Math.floor(Math.abs(number));
		};
		var maxSafeInteger = Math.pow(2, 53) - 1;
		var toLength = function (value) {
			var len = toInteger(value);
			return Math.min(Math.max(len, 0), maxSafeInteger);
		};

		// The length property of the from method is 1.
		return function from(arrayLike/*, mapFn, thisArg */) {
			// 1. Let C be the this value.
			var C = this;

			// 2. Let items be ToObject(arrayLike).
			var items = Object(arrayLike);

			// 3. ReturnIfAbrupt(items).
			if (arrayLike == null) {
				throw new TypeError("Array.from requires an array-like object - not null or undefined");
			}

			// 4. If mapfn is undefined, then let mapping be false.
			var mapFn = arguments.length > 1 ? arguments[1] : void undefined;
			var T;
			if (typeof mapFn !== 'undefined') {
				// 5. else
				// 5. a If IsCallable(mapfn) is false, throw a TypeError exception.
				if (!isCallable(mapFn)) {
					throw new TypeError('Array.from: when provided, the second argument must be a function');
				}

				// 5. b. If thisArg was supplied, let T be thisArg; else let T be undefined.
				if (arguments.length > 2) {
					T = arguments[2];
				}
			}

			// 10. Let lenValue be Get(items, "length").
			// 11. Let len be ToLength(lenValue).
			var len = toLength(items.length);

			// 13. If IsConstructor(C) is true, then
			// 13. a. Let A be the result of calling the [[Construct]] internal method of C with an argument list containing the single item len.
			// 14. a. Else, Let A be ArrayCreate(len).
			var A = isCallable(C) ? Object(new C(len)) : new Array(len);

			// 16. Let k be 0.
			var k = 0;
			// 17. Repeat, while k < len� (also steps a - h)
			var kValue;
			while (k < len) {
				kValue = items[k];
				if (mapFn) {
					A[k] = typeof T === 'undefined' ? mapFn(kValue, k) : mapFn.call(T, kValue, k);
				} else {
					A[k] = kValue;
				}
				k += 1;
			}
			// 18. Let putStatus be Put(A, "length", len, true).
			A.length = len;
			// 20. Return A.
			return A;
		};
	}());
}
String.prototype.cast = function (c) {
	var t = getType(c);
	if (t == 'undefined' || t == 'null') {
		if ((/^(0|-?1|fals[eo]|true|verdade(iro)?|off|on|(des)?ligad[oa]|no?|y(es)?|n[a�]o|s(im)?)$/i).test(this)) t = 'boolean';
		else if ((/^([-+]?)(\d*)((?:\.\d*)?)((?:E[-+]?)?)(\d*)$/i).test(this)) return Number(this);
		else if ((/^\s*(\[(.|\s)*\]|\{(.|\s)*\})\s*$/).test(this)) {
			try {
				return eval(this);
			} catch (e) {
				return this;
			}
		}
		else if ((/^$/).test(this)) return null;
		else return String(this);
	}
	if (t == 'boolean') return !(/^(|0|fals[eo]|off|desligad[oa]|no?|n[a�]o)$/i).test(this);
	else if (t == 'number') return Number(this);
	else if (t == 'array') {
		try {
			return eval(this);
		} catch (e) {
			return this.trim().split(/\s*[,;]\s*/);
		}
	}
	else if (t == 'object') {
		try {
			return eval(this);
		} catch (e) {
			return null;
		}
	}
	return String(this);
}
Number.prototype.cast = function (c) {
	var t = getType(c);
	if (t == 'undefined' || t == 'null') {
		if ((/^-?[01]$/i).test(this)) t = 'boolean';
		return String(this);
	}
	if (t == 'boolean') return !(/^0$/i).test(this);
	else if (t == 'number') return Number(this);
	else if (t == 'array') return Array(this);
	else if (t == 'object') return null;
	return String(this);
}
Boolean.prototype.cast = function (c) {
	var t = getType(c);
	var v = this.valueOf();
	if (t == 'undefined' || t == 'null' || t == 'number') return v ? 1 : 0;
	else if (t == 'boolean') return v ? true : false;
	else if (t == 'string') return v ? 'on' : 'off';
	return null;
}
Number.prototype.INET_numIps = function () { return Math.pow(2, 32 - this); }
Number.prototype.INET_m2maskDec = function (m) { return (Math.pow(2, this) - 1) * this.INET_numIps(); }
Number.prototype.INET_ipNet = function (m) { return this & m.INET_m2maskDec(); }
Number.prototype.INET_ipBCast = function (m) { return this.INET_ipNet(m) + m.INET_numIps() - 1; }
Number.prototype.INET_NTOA = function () {
	var v = this;
	var out = [];
	for (var k = 3; k >= 0; k--) {
		var m = Math.pow(256, k);
		var ip = Math.floor(v / m);
		out.push(ip);
		v -= ip * m;
	}
	return out.join('.');
}
String.prototype.INET_ATON = function () {
	var ret = this.match(/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})\/(\d{1,2})$/);
	var ipDec = 0;
	for (var i = 1; i < 5; i++) ipDec += Number(ret[i]) * Math.pow(256, 4 - i);
	return ipDec;
}
String.prototype.addslashes = function (str) { return (str + '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0'); }

/*
	var stack=Stack.get();
	stack.shift();
	console.log(Stack.toString());
*/
function Stack() { }
Stack.backtrace = [];
Stack.get = function () {
	var origPrepareStackTrace = Error.prepareStackTrace;
	Error.prepareStackTrace = function (_, stack) { return stack; }
	var err = new Error();
	this.backtrace = err.stack;
	Error.prepareStackTrace = origPrepareStackTrace;
	this.backtrace.shift();
	return this.backtrace;
}
Stack.explain = function (stack) {
	if (!stack) stack = this.backtrace;
	if (getType(stack) == 'array') {
		var out = [];
		for (var i in stack) out.push(this.explain(stack[i]));
		return out;
	}
	var fn = [
		'getColumnNumber',
		'getFileName',
		'getFunctionName',
		'getMethodName',
		'getLineNumber',
		'getPosition',
		'getFunction',
		'getEvalOrigin',
		'getScriptNameOrSourceURL',
		'getThis',
		'getTypeName',
		'isConstructor',
		'isEval',
		'isNative',
		'isToplevel',
		'isAsync',
		'isPromiseAll',
		'getPromiseIndex',
		'toString',
	];
	var att = [
		'pos',
		'receiver',
	];
	var out = {};
	for (var f in att) out[att[f]] = stack[att[f]];
	for (var f in fn) out[fn[f]] = stack[fn[f]]();
	return out;
}
Stack.resume = function (stack) {
	if (!stack) stack = this.backtrace;
	if (getType(stack) == 'array') {
		var out = [];
		for (var i in stack) out.push(this.resume(stack[i]));
		return out;
	}
	var out = stack.getFileName() + '[' + stack.getLineNumber() + ',' + stack.getColumnNumber() + ']';
	var f = stack.getFunctionName();
	if (f) out += '->' + f;
	return out;
}
Stack.toString = function (stack) {
	if (!stack) stack = this.backtrace;
	if (getType(stack) == 'array') {
		var out = [];
		for (var i in stack) out.push(this.toString(stack[i]));
		return out;
	}
	return stack.toString();
}
var parent = new Proxy({}, {
	get: function get(target, method) {
		return function wrapper() {
			var args = Array.prototype.slice.call(arguments);
			var stack = Stack.get();
			var oSource = stack.shift();

			var oTarget = stack.shift();
			target = oTarget.getThis();
			var fn = oTarget.getFunctionName();
			var clName = fn.replace(/\.\w+$/, '');
			var cl = window[clName];
			var p = cl.prototype.parent_class
			if (method == 'this' && p[method] == undefined) {
				//if(method=='constructor') return ;
				//method=stack.getMethodName();
				method = fn.replace(/^.*\./, '');
				if (method == fn) method = 'constructor';
			}
			//console.log([p.constructor.name,method,p[method],args]);

			//,target:target,dad:p,stack:Stack.explain(stack)});
			//console.log(p.constructor.name,method,args);
			//console.log([target.constructor.name,method,args]);

			if (typeof p[method] == 'function') return p[method].apply(target, args);
			p[method] = args.length > 1 ? args : args[0];
			return p[method];
		}
	}
});

; (function ($, window, document, undefined) {
	/*	@Example setSelection
		$('#elem').setSelection(3,5); // select a range of text
		$('#elem').setSelection(3);   // set cursor position
	*/
	$.fn.setSelection = function (start, tam) {
		if (tam === undefined) tam = 0;
		var end = start + tam;
		return this.each(function () {
			if ('selectionStart' in this) {
				this.selectionStart = start;
				this.selectionEnd = end;
			}
			else if (this.setSelectionRange) {
				this.setSelectionRange(start, end);
			}
			else if (this.createTextRange) {
				var range = this.createTextRange();
				range.collapse(true);
				range.moveEnd('character', end);
				range.moveStart('character', start);
				range.select();
			}
		});
	}
	/*  @Example getSelection: 
		$('#elem').getSelection() //{ start:0, end:0, length: 0, text:'' }
	*/
	$.fn.getSelection = function () {
		var obj = this.get(0);
		var out = { start: 0, end: 0, length: 0, text: '' };
		var oRange;
		if ('selectionStart' in obj) {
			out.start = obj.selectionStart;
			out.end = obj.selectionEnd;
			out.length = out.end - out.start;
			out.text = window.getSelection().toString(); // obj.value.substring(obj.selectionStart,obj.selectionEnd)
		}
		else if ((document.selection && (oRange = document.selection.createRange)) || (oRange = obj.createTextRange)) {
			var range = oRange();
			out.text = range.text;
			out.length = out.text.length;
			range.moveStart('character', -obj.value.length);
			out.end = range.text.length;
			out.start = out.end - out.length;
			range.moveStart('character', out.start);
		};
		return out;
	}
	$.fn.getPluginName = function (name) {
		return name.substr(0, 1).toLowerCase() + name.substr(1);
	}
	$.uriChange = function (o, window_top) {
		if (o === undefined) o = {};
		var old = $.uri2array(location.search.substr(1));
		//console.log(old);
		old = $.extend(old, o);
		//var n=Object.assign(old, o);

		//console.log($.array2uri(old));
		$.goURL('?' + $.array2uri(old), window_top);
	}
	$.goURL = function (url, window_top) {
		if (event.ctrlKey) window.open(url);
		else if (event.shiftKey) window.open(url);
		//else if(event.altKey) window.open(url);
		else {
			if (window_top === undefined) location = url;
			else window.open(url, '_top');
		}
	}
	$.uri2array = function (uri) {
		var obj = {}
		uri = uri.split(/&/g);
		//console.log(uri);
		for (var i in uri) {
			if (uri[i].length == 0) continue;
			var item = uri[i].split(/=/);
			item[0] = decodeURIComponent(item[0]);
			if (item[1] != '') {
				item[1] = decodeURIComponent(item[1]);
				//console.log(item);
				var n = Number(item[1]);
				if (!isNaN(n)) item[1] = n;
				else if (item[1].match(/true|false/i)) item[1] = Boolean(item[1].match(/true/i));
			}
			$.pushObj(obj, item[0], item[1]);
		}
		return obj;
	}
	$.array2uri = function (obj, pre) {
		if (pre == undefined) pre = '';
		uri = '';
		if (typeof obj == 'object') for (var i in obj) {
			if (getType(obj[i]) != 'null') {
				//if(getType(obj[i])!='null')
				//console.log(getType(obj[i]));
				var k = encodeURIComponent(i);
				uri += uri ? '&' : '';
				uri += $.array2uri(obj[i], pre ? pre + '[' + k + ']' : k);
			}
		}
		else {
			uri += (pre ? pre + '=' : '') + encodeURIComponent(obj);
		}
		return uri;
	}
	$.copy = function (value, tipo) {
		console.log(value);
		if (tipo === undefined) tipo = 'text';
		else tipo = tipo.toLowerCase();
		if (typeof window.clipboardData != 'undefined') window.clipboardData.setData(tipo, value);
		else if (typeof unsafeWindow != 'undefined') {
			unsafeWindow.netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
			const clipboardHelper = Components.classes["@mozilla.org/widget/clipboardhelper;1"].getService(Components.interfaces.nsIClipboardHelper);
			clipboardHelper.copyString(value);
		}
		else {
			if (tipo == 'text') {
				var $o = $('<textarea>', { 'val': value, 'css': 'display:none;' });
				$('body').append($o);
				$o.select();
				document.execCommand('copy');
			}
			else {
				//var $o = $('<div>', { 'html': value, 'css': 'display:none;' });
				var $o = $(value);
				//var $o=$('<div>'+value+'</div>');
				//var $o=$('<div></div>');
				//$('body').append($o);
				var div = $o[0];

				$o.focus();
				window.getSelection().removeAllRanges();
				var range = document.createRange();
				// not using range.selectNode(div) as that makes chrome add an extra <br>
				range.setStartBefore(div.firstChild);
				range.setEndAfter(div.lastChild);
				//range.selectNode(div); 
				window.getSelection().addRange(range);
				
				document.execCommand('copy');
				window.getSelection().removeAllRanges();
			}
			//$o.remove();
		}
	}
	$.pushObj = function (obj, fullname, value) { //URI Query name   name[subname][][other]=value
		var m = fullname.match(/^\[?([^\[\]]*)\]?(.*)?$/);
		var name = m[1];
		var fullname = m[2];
		if (name == '') var name = Object.keys(obj).length;
		if (m[2]) {
			if (!obj[name]) obj[name] = {}
			$.pushObj(obj[name], m[2], value);
		}
		else obj[name] = value;
	}
	$.sessControl = function (id, idFile, data, async) { //r=$.sessControl(this.elements.id,this.elements.idFile,{lstFields:flds});
		var e = new $.Ed();
		if (async == undefined) async = false;
		return e.execCmd({ cmd: 'sessControl', id: id, idFile: idFile, data: data }, false, async);
	}
	$.copyURL = function (e) {
		var $ed = new $.Ed();
		var out = {};
		$('form').each(function () {
			for (var i in this) if (this[i] && this[i].nodeName && (/^Data/).test(this[i].name)) {
				//console.log([this[i].type,jQuery.inArray(this[i].type,['submit','button','reset'])])
				if (
					this[i].nodeName != 'BUTTON' &&
					jQuery.inArray(this[i].type, ['submit', 'button', 'reset']) == -1 &&
					(this[i].type != 'checkbox' || this[i].checked)
				) $.pushObj(out, this[i].name, this[i].value);
			}
		});

		if (e.ctrlKey) { // N�o Codifica URL, apenas copia JSON
			//$ed.dataType='text';
			var lnk = $ed.execCmd({ cmd: 'eval', "__eval": 'return json_encode($_REQUEST);', data: out }, false, false);
			//var txtCopy=lnk.data;
			var txtCopy = lnk;
		} else {
			var lnk = $ed.execCmd({ cmd: 'makeLink', url: location.href, data: out }, false, false);
			var url = window.easyData.url ? window.easyData.url : '/easyData/fn/url';
			var txtCopy = location.origin + url + '?' + lnk['data'];
		}

		console.log(txtCopy);
		$.copy(txtCopy);

		var fnOrg = function (obj, name, id) {
			if (typeof (obj) == 'object') {
				var out = '';
				if (name) out += '<li><a data-toggle="collapse" href="#' + id + '" aria-expanded="false"><b>' + name + '</b></a><ul class="collapse" id="' + id + '">';
				for (var i in obj) out += fnOrg(obj[i], i, id + '_' + i.replace(/\W/g, '_'));
				out += '</ul></li>';
				return out;
			}
			else return '<li><b>' + name + ': </b>' + (obj.replace(/, */g, ', ')) + '</li>';
		}
		var $modal = $(
			'<div class="modal fade" role="dialog">' +
			'	<div class="modal-dialog">' +
			'		<div class="modal-content">' +
			'			<div class="modal-header">' +
			'				<button type="button" class="close" data-dismiss="modal">&times;</button>' +
			'				<h4 class="modal-title">Copied URL to Clipboard</h4>' +
			'			</div>' +
			'			<div class="modal-body">' +
			'				<ul>' + fnOrg(out) + '</ul>' +
			'			</div>' +
			'			<div class="modal-footer">' +
			'				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>' +
			'			</div>' +
			'		</div>' +
			'	</div>' +
			'</div>'
		);
		//$('body').append($modal);
		$modal.on('hidden.bs.modal', function () {
			$modal.remove();
		}).modal('show');
	}
	$.json_decode = function (obj) {
		//JSON.parse()
		//jQuery.parseJSON()
		var ty = getType(obj)
		switch (ty) {
			case 'string':
				//obj=$.json_decode(jQuery.parseJSON(obj));
				if ((/^\s*function\s*\([^\)]*\)\s*{/i).test(obj) && (/\}\s*/).test(obj)) obj = new Function('return ' + obj)();
				else {
					var er = obj.match(/^\s*(?:js|javascript):(?:\/{2})?((?:.|\s)*)$/i);
					if (er) obj = new Function('return ' + er[1])();
				}
				break;
			case 'array':
			case 'object':
				for (var i in obj) obj[i] = $.json_decode(obj[i]);
				break;
		}
		return obj;
	}
	$.urldecode = function (str) { return str === undefined ? '' : decodeURIComponent(str.replace(/\+/g, ' ')); }
	$.parseParams = function (query, obj, key, value) {
		if (obj != undefined) {
			var sItem;
			if (query && (sItem = query.match(/^\[([^\]])*\](.*)/))) {
				if (sItem[1]) {
					if (obj[key] === undefined) obj[key] = {}
					$.parseParams(sItem[2], obj[key], sItem[1], value);
				}
				else {
					if (obj[key] === undefined) obj[key] = []
					if (sItem[2]) value = $.parseParams(sItem[2], {}, '_', value)['_'];
					obj[key].push(value);
				}

			}
			else obj[key] = value;
			return obj;
		}
		var re = /([^#&=\[]+)(\[[^#&=]*\])?=?([^&#]*)/g;
		var params = {}, e;
		while (e = re.exec(query)) {
			var k = $.urldecode(e[1]), q = $.urldecode(e[2]), v = $.urldecode(e[3]);
			//if(!params[k]) params[k]=null;
			//console.log([k,q,v]);
			$.parseParams(q, params, k, v);
			continue;

			if (k.substring(k.length - 2) === '[]') {
				k = k.substring(0, k.length - 2);
				(params[k] || (params[k] = [])).push(v);
			}
			else params[k] = v;
		}
		return params;
	};

	Function.prototype.extends = function (parent_class, extend_static) {
		var p = Object.create(parent_class.prototype).__proto__;
		if (extend_static !== false) for (var i in parent_class) {
			this[i] = parent_class[i];
			p[i] = parent_class[i];
		}
		this.prototype = $.extend({}, this.prototype, p);
		this.prototype.constructor = this;
		this.prototype.parent_class = p;
		if (!this.prototype.parents) this.prototype.parents = {};
		this.prototype.parents[parent_class.name] = p;

		//if(!window.xxxx) window.xxxx={};
		window[this.name] = this;
		//console.log(window.xxxx)
		return this;
	}

	Function.prototype.jQuery = function () {
		var name = this.name === undefined ? String(this).replace(/^\s*function\s+([^ \(]+)(.|\s)+/i, '$1') : this.name;
		var pluginName = name.substr(0, 1).toLowerCase() + name.substr(1);
		this.pluginName = pluginName;
		var data_obj = 'ed';
		var self = this;
		/*var self=window[name];*/
		/*var data_obj='plugin_' + pluginName;*/
		$.fn[pluginName] = function (options) {
			var typeOptions = typeof options;

			if (options === undefined || typeOptions === 'object') {
				//console.log(this)
				return this.each(function () {
					if (!$.data(this, data_obj)) {
						$.data(this, data_obj, new self(this, options));
					}
				});
			}
			else if (typeOptions === 'string' && options[0] !== '_' && options !== 'init') {
				//console.log(this)
				var args = arguments;
				var returns;
				this.each(function () {
					var instance = $.data(this, data_obj);
					if (instance instanceof self) {
						var subOptions = Array.prototype.slice.call(args, 1);
						if (typeof instance[options] === 'function') returns = instance[options].apply(instance, subOptions);
						else instance[options] = subOptions;
					}
				});
				return returns !== undefined ? returns : this;
			}
		}
		$[name] = this;
		return this;
	}
	function Ed(element, options) {
		//console.log(this);
		Ed.children.push(this);
		this.name = this.constructor.name;
		if (element != undefined) {
			this.element = $(element);
			//console.log(this.element);
			//this.element.edElement=123;
		}
		this.settings = $.extend({}, this.constructor.defaults, options);
		this.init_attr();
		Ed.ids[this.id] = this;
		this.init();
		this.init_elements();
		this.init_events();
		this.init_done();
	}
	Ed.children = [];
	Ed.ids = {};
	Ed.defaults = {};
	Ed.keyDefalts = null;

	Ed.prototype.element = null;
	Ed.prototype.elements = {};
	Ed.prototype.settings = {};
	Ed.prototype.ajax = {
		url: null,
		type: "POST",
		dataType: 'json', //xml, json, script, html, text
		async: true
	};
	Ed.prototype.ajax_on = {
		beforeSend: null,
		success: null,
		error: null,
		complete: null
	};
	Ed.prototype.ajax_response = {
		type: null,
		data: null,
		ret: null
	};
	Ed.prototype.dad = function (fn) {//depreciado para parent.method(arg1,...) | parent.this(arg1,...)
		var args = Array.from(arguments);
		args.shift();
		if (typeof this.parent_class[fn] == 'function') return this.parent_class[fn].apply(this, args);
		if (args.length != 1) this.parent_class[fn] = args.length > 1 ? args : args[0];
		return this.parent_class[fn];
	}
	Ed.prototype.dads = function (fn, obj) {
		var args = Array(arguments);
		args.shift(); args.shift();
		if (typeof obj[fn] == 'function') return obj[fn].apply(this, args);
		if (args.length != 1) obj[fn] = args.length > 1 ? args : args[0];
		return obj[fn];
	}

	Ed.prototype.init_keyDefalts = function () {
		if (this.constructor.keyDefalts === null) {
			this.constructor.keyDefalts = {};
			for (var i in this.constructor.defaults) this.constructor.keyDefalts[i.toLowerCase()] = i;
		}
		return this.constructor.keyDefalts;
	}
	Ed.prototype.addElements = function (selector, element_name, base) {
		if (!base) base = this.element;
		var e = base.find(selector);
		if (element_name) {
			this.elements[element_name] = e;
			return this;
		}
		return e;
	}

	Ed.prototype.init_attr = function () {/*copy tag attributes to settings*/
		if (this.element && this.element[0]) {
			this.init_keyDefalts();
			var _this = this;
			$.each(this.element[0].attributes, function (index, obj) {
				if (this.name == 'id') _this.id = this.value;
				else if (this.specified && this.name in _this.constructor.keyDefalts) {
					//console.log([this.name,this.specified,_this])
					var name = _this.constructor.keyDefalts[this.name];
					_this.settings[name] = this.value.cast(_this.settings[name]);
				}
			});
			//console.log(_this)
		}
		return this;
	}
	Ed.prototype.init = function () {/*start aditional containers override*/
		//console.log(this.element);
		//console.log(this.parent_class);
		//console.log(this.name);
		//this.elements.xpto=$('<div></div>').insertBefore(this.element);
		//this.elements.xpto=this.element.append('<div>div>');
		return this;
	}
	Ed.prototype.init_elements = function () {/*find Elements override*/
		return this;
		/*{//find Elements
			this.elements={
			form:$(this.element.find('input')[0].form),
			clearFilter: this.element.find('[ed-item="uploadlist-clear-filter"]'),
			}
		}*/
		//this.addElements=function(selector,element_name,base)
	}
	Ed.prototype.init_events = function () {/*set events override*/
		return this;
		/*{//Set Events
			this.elements.form.submit(function(e){
			var ch=_this.element.find('[ed-item="uploadlist-remove-check"]:checked');
			var len=ch.length;
			if(len!=0 && (
			!confirm('Voce est� prestes a excluir '+len+' arquivo(s).\nDeseja realmente fazer isto?') ||
			!confirm('Tem certeza desta opea��o?')
			)) e.preventDefault();
			});
		}*/
	}
	Ed.prototype.init_done = function () { return this; }
	Ed.prototype.on_event = function (args) {
		//by this.ajax_on / this.defaults
		//this.on_event({on:'beforeXXXXX','event':event,jqXHR:jqXHR,settings:settings});
		//this.on_event({on:'evento',arg1:val1, ....});
		var fn = this.ajax_on[args.on] || this.settings[args.on] || this.settings[args];
		if (fn === null || undefined) return null;
		var t = getType(fn);
		//if(t=='string') return {ret:this.on_event(eval(fn))};
		if (t == 'string') {
			args = this;
			if ((t = getType(this[fn])) == 'function') fn = this[fn];
			else if ((t = getType(window[fn])) == 'function') fn = window[fn];
			else return { ret: eval(fn) };
		}
		if (t == 'function') return { ret: fn(args) };
		return null;
	}
	Ed.prototype.execCmd = function (data, url, async) {
		if (async == undefined) async = this.ajax.async;
		if (!url) url = window.easyData['fn'] + '/execCmd.php';
		this.ajax.url = url;
		if (typeof data != 'object') data = {};
		if (data.PHPSESSID == undefined) data.PHPSESSID = $.cookie('PHPSESSID');
		if (data.ajax == undefined) data.ajax = this.ajax;
		var _this = this;
		$.ajax({
			data: data, url: url,
			beforeSend: function (jqXHR, settings) {//A pre-request callback function that can be used to modify the jqXHR
				var o = _this.on_event({ on: 'beforeSend', 'event': event, jqXHR: jqXHR, settings: settings });
				if (!o) return true;
				_this.ajax_response.ret = o.ret;
				return o.ret;
			},
			success: function (data, textStatus, jqXHR) {
				_this.ajax_response.type = 'success';
				_this.ajax_response.data = data;
				var o = _this.on_event({ on: 'success', 'event': event, jqXHR: jqXHR, textStatus: textStatus, data: data });
				_this.ajax_response.ret = o ? o.ret : null;
			},
			error: function (jqXHR, textStatus, errorThrown) {
				//jqXHR=jQuery 1.4.x XMLHttpRequest
				//textStatus=null, "timeout", "error", "abort", and "parsererror"
				//errorThrown=textual portion of the HTTP status
				_this.ajax_response.type = 'error';
				_this.ajax_response.data = data;
				var o = _this.on_event({ on: 'error', 'event': event, jqXHR: jqXHR, textStatus: textStatus, errorThrown: errorThrown });
				_this.ajax_response.ret = o ? o.ret : null;
			},
			complete: function (jqXHR, textStatus) { //after success and error 
				//textStatus="success", "notmodified", "nocontent", "error", "timeout", "abort", or "parsererror"
				var o = _this.on_event({ on: 'complete', 'event': event, jqXHR: jqXHR, textStatus: textStatus });
			},
			type: _this.ajax.type,
			dataType: _this.ajax.dataType,
			async: async
		});
		return this.ajax_response;
	}
	Ed.prototype.getElement = function (mixed) { //id/obj
		var tp = getType(mixed); //string/object
		var id;
		if (tp == 'string') id = mixed;
		else if (tp == 'object' || tp.match(/^html/)) id = mixed.id || (mixed[0] && mixed[0].id);
		//else if(tp.match(/^html/)) id=mixed.getAttribute('id');
		if (id == undefined) return false;

		if (id in $.Ed.ids) return $.Ed.ids[id];
		//console.log(id);
		//console.log($.Ed.ids);
	}
	Ed.prototype.validate = function (fields, showAlert) {
		var ret = true;
		var er = '';
		//console.log($.Ed.ids);
		for (var i in fields) {
			var f = fields[i];
			var o = this.getElement(f);
			if (o && o.valid) {
				//console.log([o.settings.label,o.name]);
				//var $f=$(f);console.log([getType(f),f,$f,required]);
				var r = o.valid();
				ret &= r;
				if (!r) {
					var label = o.element.attr('label') || o.getLabel().replace(/\s*\*?:\s*$/, '');
					er += label + ":\n";
					for (var erro in o.erros) er += '    - ' + (o.erros[erro]) + "\n";
					//console.log(o.erros);
				}
			} else if (getType(f).match(/^html(input|select|textarea|radio)element$/)) {
				var $f = $(f);
				var required = $f.attr('required') || false;
				if (typeof required === 'string') required = required.match(/^(1|on|true)$/i) ? true : false;
				//ret=false
				//console.log([getType(f),f,$f,required]);
				if (required && $f.val() === '') {
					var n = $f.attr('name') || $f[0].id;
					if (n != '') er += n + ': ';
					er += "Valor Requerido\n";
				}
			}
			//console.log(o);
		}
		//alert(111)
		//console.log(ret);
		if (!ret && showAlert !== false) {
			$box = $("<div title='Erros de Preenchimento'><pre>\n" + (er.replace(/(.+:)/, '<b>$1</b>')) + "</pre></div>");
			$("body").append($box);
			$box.dialog({
				modal: true,
				width: 'auto'
			});
			//console.log($box)
			//alert("*** EXISTEM ERROS ***\n"+er);
			//$box.remove();
		}
		return ret;
	}
	//$.prototype.edElement=null;
	//$.prototype.getElementEd=function(){
	//	console.log(11111)
	//}
	Ed.jQuery();
})(jQuery, window, document);
$(document).ready(function () {
	$('[ed-class]').each(function (idx, obj) {
		var $this = $(this);
		var c = $this.getPluginName($this.attr('ed-class'));
		if (typeof $this[c] == 'function') $this[c]();
		//else console.log({'Error':'There isn\'t class','id':$this.attr('id'),'label':$this.attr('label'),'class':c});
	});
	$('body').keyup(function (e) {
		var c = e.which ? e.which : e.keyCode;
		if (c == 76 && e.shiftKey && e.altKey) $.copyURL(e); //Shift+ALT+L
	});
});

/*
	keyMap={
	3:'Break', //Ctrl
	8:'BS',9:'Tab',13:'Enter',16:'Shift',17:'Ctrl',18:'Alt',
	19:'Pause',20:'CapsLock',
	27:'Esc',
	33:'PgUp',34:'PgDn',35:'End',36:'Home',
	37:'Left',38:'Up',39:'Right',40:'Down',
	45:'Ins',46:'Del',
	48:'0',49:'1',50:'2',51:'3',52:'4',53:'5',54:'6',55:'7',56:'8',57:'9',
	65:'A',66:'B',67:'C',68:'D',69:'E',70:'F',71:'G',72:'H',73:'I',74:'J',
	75:'K',76:'L',77:'M',78:'N',79:'O',80:'P',81:'Q',82:'R',83:'S',84:'T',
	85:'U',86:'V',87:'W',88:'X',89:'Y',90:'Z',
	91:'LeftWin',92:'RightWin',
	93:'Command',
	96:0,97:1,98:2,99:3,100:4,101:5,102:6,103:7,104:8,105:9, //Num
	106:'*',
	107:'+',109:'-',110:',',111:'/', //Num
	112:'F1',113:'F2',114:'F3',115:'F4',116:'F5',117:'F6',
	118:'F7',119:'F8',120:'F9',121:'F10',122:'F11',123:'F12',
	144:'NumLock', //Num
	145:'ScrollLock',
	186:'�',
	187:'=',188:',',189:'-',190:'.',191:';',192:'\'',193:'/',
	194:'.', //Num
	219:'�',220:']',221:'[',222:'~',226:'\\'
}*/