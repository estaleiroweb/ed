function getDomDocumentPrefix() {
	if (getDomDocumentPrefix.prefix)
		return getDomDocumentPrefix.prefix;
	return getDomDocumentPrefix.prefix = getXmlPrefix('DomDocument');
}
function getXmlHttpPrefix() {
	if (getXmlHttpPrefix.prefix)
		return getXmlHttpPrefix.prefix;
	return getXmlHttpPrefix.prefix = getXmlPrefix('XmlHttp');
}
function getXmlPrefix(sufix) {
	var prefixes = ["MSXML2", "Microsoft", "MSXML", "MSXML3"];
	var o;
	for (var i = 0; i < prefixes.length; i++) {
		try {
			// try to create the objects
			o = new ActiveXObject(prefixes[i] + "." + sufix);
			return prefixes[i];
		}
		catch (ex) {};
	}
	throw new Error("Could not find an installed XML parser");
}

// XmlHttp factory
function XmlHttp() {}

XmlHttp.create = function () {
	try {
		if (window.XMLHttpRequest) {
			var req = new XMLHttpRequest();
			// some versions of Moz do not support the readyState property
			// and the onreadystate event so we patch it!
			if (req.readyState == null) {
				req.readyState = 1;
				req.addEventListener("load", function () {
					req.readyState = 4;
					if (typeof req.onreadystatechange == "function")
						req.onreadystatechange();
				}, false);
			}
			
			return req;
		}
		if (window.ActiveXObject) {
			return new ActiveXObject(getXmlHttpPrefix() + ".XmlHttp");
		}
	}
	catch (ex) {}
	// fell through
	throw new Error("Seu browser não suporta objetos XmlHttp");
};

// XmlDocument factory
function XmlDocument() {}

XmlDocument.create = function () {
	try {
		// DOM2
		if (document.implementation && document.implementation.createDocument) {
			var doc = document.implementation.createDocument("", "", null);
			
			// some versions of Moz do not support the readyState property
			// and the onreadystate event so we patch it!
			if (doc.readyState == null) {
				doc.readyState = 1;
				doc.addEventListener("load", function () {
					doc.readyState = 4;
					if (typeof doc.onreadystatechange == "function")
						doc.onreadystatechange();
				}, false);
			}
			
			return doc;
		}
		if (window.ActiveXObject)
			return new ActiveXObject(getDomDocumentPrefix() + ".DomDocument");
	}
	catch (ex) {}
	throw new Error("Seu browser não suporta objetos XmlDocument");
};

// Create the loadXML method and xml getter for Mozilla
if (window.DOMParser &&
	window.XMLSerializer &&
	window.Node && Node.prototype && Node.prototype.__defineGetter__) {

	// XMLDocument did not extend the Document interface in some versions
	// of Mozilla. Extend both!
	//XMLDocument.prototype.loadXML = 
	Document.prototype.loadXML = function (s) {
		
		// parse the string to a new doc	
		var doc2 = (new DOMParser()).parseFromString(s, "text/xml");
		
		// remove all initial children
		while (this.hasChildNodes())
			this.removeChild(this.lastChild);
			
		// insert and import nodes
		for (var i = 0; i < doc2.childNodes.length; i++) {
			this.appendChild(this.importNode(doc2.childNodes[i], true));
		}
	};
	
	
	/*
	 * xml getter
	 *
	 * This serializes the DOM tree to an XML String
	 *
	 * Usage: var sXml = oNode.xml
	 *
	 */
	// XMLDocument did not extend the Document interface in some versions
	// of Mozilla. Extend both!
	/*
	XMLDocument.prototype.__defineGetter__("xml", function () {
		return (new XMLSerializer()).serializeToString(this);
	});
	*/
	Document.prototype.__defineGetter__("xml", function () {
		return (new XMLSerializer()).serializeToString(this);
	});
}

///////////////////// XML-HTTP ///////////////////////////
// Class xmlHttpRequest ([strUrl],[strParametros],[boolAsync],[strHeads])

// Propriedades:
//	obj.src
//	obj.method=GET | POST | PUT | PROPFIND
//	obj.async
//	obj.head
//	obj.XmlHttp.responseText
//	obj.XmlHttp.responseXML
//	obj.XmlHttp.responseBody
//	obj.XmlHttp.responseStream
//	obj.XmlHttp.status
//	obj.XmlHttp.statusText
//	obj.XmlHttp.readyState

// Metodos:
//	obj.load([url],[parametros])
//	obj.reset()
//	obj.XmlHttp.abort()
//	obj.XmlHttp.getAllResponseHeaders()
//	obj.XmlHttp.getResponseHeader(strHeader)
//	obj.XmlHttp.open(strMethod, strUrl, [boolAsync], [strUser], [strPassword]) [obsoleta]
//	obj.XmlHttp.send(strBody) [obsoleta]
//	obj.XmlHttp.setRequestHeader(strHeader,strValue) [obsoleta]

// Eventos:
//	obj.onstart
//	obj.onloading
//	obj.onloaded
//	obj.onreadystatechange
function xmlHttpRequest (url,parametros,bAsync,heads) {
	var method,p,i;
	this.reset=function() {
		if (this.onstart) eval(this.onstart)
		if (this.src) {
			method=(this.method)?this.method:((this.parameteres)?'POST':'GET') //Escolhe o método
			this.XmlHttp.open(method, this.src, this.async); // Conecta ao arquivo
			if (this.head==='') {
				this.XmlHttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=ISO-8859-1') //text/html   text/xml
			} else if (this.head!==false)  this.XmlHttp.setRequestHeader('Content-Type',this.head)
			if (this.async) {
				var xmlThis=this
				//this.XmlHttp.onreadystatechange = this.runing
				this.XmlHttp.onreadystatechange =function () { 
					if (xmlThis.onloading) eval(xmlThis.onloading)
					if (xmlThis.XmlHttp.readyState == 4){
						xmlThis.showTarget(xmlThis)
						if(xmlThis.onloaded) eval(xmlThis.onloaded)
					}
				}
			}
			this.XmlHttp.send(this.getParametros())
			if (!this.async && this.onloaded) eval(this.onloaded)
		} 
		return this.src?true:false
	}
	this.runing=function(){
		if (this.onloading) eval(this.onloading)
		if (this.XmlHttp.readyState == 4 && this.onloaded) {
			this.showTarget(this)
			eval(this.onloaded)
		}
	}
	this.load=function(url,parametros) {
		if (url) this.src=url
		if (parametros) this.parameteres=parametros
		var ret=this.reset()

		if(ret && !this.async) {
//			this.showTarget(this)
			var xml=this.XmlHttp.responseXML
			return (xml)?xml:this.XmlHttp.responseText
		}
	}
	this.getParametros=function(){
	   switch (typeof(this.parameteres)){
	      case 'string': return urlencode(this.parameteres)
	      case 'object': case 'array': return http_build_query(this.parameteres)
		}
	}
	this.showTarget=function(obj){
		if (typeof(obj.htmlTarget)=='object') obj.htmlTarget.innerHTML=obj.XmlHttp.responseText
		if (typeof(obj.valueTarget)=='object') obj.valueTarget.value=obj.XmlHttp.responseText
	}
	this.getText=function(){
		return this.XmlHttp.responseText
	}
	this.XmlHttp=XmlHttp.create()
	this.src=''
	this.method=''
	this.head=(typeof(heads)=='undefined')?'':heads
	this.async=(bAsync)?bAsync:false
	this.parameteres=''
	this.onstart=''
	this.onloaded=''
	this.onloading=''
	this.htmlTarget=''
	this.valueTarget=''
	this.load(url,parametros)
}
//////////////////////// fim XML-HTTP //////////////////////////////

function http_build_query(arrayVar,predec) { //similar ao PHP
/*
	var keyVar=ret='',tp=typeof(arrayVar)
	if (typeof(predec)=='undefined') predec='';
	for (var i in arrayVar) {
	   ret+=ret?"&":""
	   keyVar=predec?predec+"["+escape(i)+']':escape(i)
	   if (typeof(arrayVar[i])=='object' || typeof(arrayVar[i])=='array') ret+=http_build_query(arrayVar[i],keyVar)
	   else ret+=keyVar+"="+escape(arrayVar[i])
	}
*/
	var keyVar=ret=''

	var tp=(String(arrayVar.constructor).match(/\s*(?:function)?\s*(\w+)/i))[1]
	if (typeof(predec)=='undefined') predec='';
	if (tp=='Object') for (var i in arrayVar) {
	   ret+=ret?"&":""
	   keyVar=predec?predec+"["+urlencode(i)+']':urlencode(i)
	   if (typeof(arrayVar[i])=='object') ret+=http_build_query(arrayVar[i],keyVar)
	   else ret+=keyVar+"="+urlencode(arrayVar[i])
	} else if (tp=='Array') for (var i=0;i<arrayVar.length;i++) {
	   ret+=ret?"&":""
	   keyVar=predec?predec+"["+urlencode(i)+']':urlencode(i)
	   if (typeof(arrayVar[i])=='object' || typeof(arrayVar[i])=='array') ret+=http_build_query(arrayVar[i],keyVar)
	   else ret+=keyVar+"="+urlencode(arrayVar[i])
	} else ret=urlencode(arrayVar)
	return ret
}
function urlencode(value){//similar ao PHP
	if ((/[\x80-\xFF]/ ).test(value)) {
		var er=/[\x00-\x24\x26-\x2C\x2F\x3A-\x40\x5B-\x5E\x60\x7B-\xFF]/
		value=value.replace(/%/g,'%25')
	} else {
		var er=/[!\'-\*~]/
		value=encodeURIComponent(value)
	}
	var c
	while (r=value.match(er)) {
		c=r[0].charCodeAt().toString(16).toUpperCase()
		value=value.replace(r[0],'%'+(c.length==1?'0':'')+c)
	}
	return value 
}
function urldecode(value){
	var r,er
	value=value.replace(/\+/g ,' ').replace(/%25/g ,'%')
	while (r=value.match(/%([89A-F].)/)) {
		er=new RegExp(r[0],"g")
		value=value.replace(er,String.fromCharCode(parseInt(r[1],16)))
	}
	return decodeURIComponent(value)
}