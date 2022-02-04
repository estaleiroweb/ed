<?php
if(strlen($lnk=key($_GET))<15 && $_GET[$lnk]==''){
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	require_once 'get_autoload.php';
	
	$s=Secure::singleton(true);
	//$o=OutHtml::singleton();
	//new JQuery_Cookie;
	//$o->script('Ed','easyData');
	
	$conn=$s->connect();
	$conn->select_db($s::$ini['main']['db']);
	$lnk=$conn->addQuote($lnk);
	$line=$conn->fastLine("SELECT * FROM tb_URLs u WHERE u.lnk=$lnk");
	$conn->query("UPDATE tb_URLs u SET u.DtLastVisit=NULL WHERE u.lnk=$lnk");
	
	$opts=$_GET;array_shift($opts);
	$line['QString']=array_replace_recursive($line['QString']?json_decode($line['QString'],true):array(),$opts);
	//print_r($line);
	
	print "<html><body onload='document.getElementById(\"frm\").submit()'>\n";
	print "<form id='frm' method='post' action='{$line['URL']}'>\n";
	$bq=explode('&',http_build_query($line['QString']));
	foreach($bq as $l){
		$l=explode('=',$l);
		$k=urldecode($l[0]);
		$v=@$l[1];
		$v=urldecode($l[1]);
		print "	<input type='hidden' name='$k' value='$v' />\n";
	}
	print "</form>\n";
	print "</body></html>\n";
	exit;
}
?>
<html><head>
	<meta http-equiv='expires' content='Mon, 26 Jul 1990 05:00:00 GMT' />
	<meta http-equiv='cache-control' content='private' />
	<meta http-equiv='pragma' content='no-cache' />
	</head>
<body><form id='frm' method='post'></form></body>
<script>
	function createInputs(obj,name){
		if(name) var bracketI='[', bracketF=']';
		else var bracketI='', bracketF='',name='';
		switch(Object.prototype.toString.call(obj)){
			case '[object Object]': case '[object Array]': for(var i in obj) createInputs(obj[i],name+bracketI+i+bracketF); break;
			case '[object Function]': obj=obj.toString();
			/*case '[object String]':case '[object Number]':case '[object Boolean]':case '[object Null]':*/
			default: 
				var o=document.createElement("INPUT");
				o.name=name;
				o.type='hidden';
				o.value=obj;
				oForm.appendChild(o);
		}
	}
	var oForm=document.getElementById('frm')
	//var query=location.search.replace(/#/g,'');
	var query=location.href.replace(/^.*?\?/,'');
	if(query.match(/%[a-f0-9]{2}/i)) query=decodeURIComponent(query).replace(/^\?/,'');
	var data=JSON.parse(query); //var str=JSON.stringify(obj); JSON.parse(query);
	//console.log(data); 
	oForm.action=data.URL;
	delete data.URL;
	createInputs(data);
	oForm.submit();
</script></html>

<!--
	http://bdconfig.intelig23/shared/easyData/fn/urlRedir.php?http%3A//bdconfig.intelig23/bdc2/eqto/&tblData%5BEquipamentos%5D%5BshowTable%5D=1&tblData%5BEquipamentos%5D%5Bvalues%5D%5BEqto%5D=dir*&tblData%5BEquipamentos%5D%5Bvalues%5D%5BError%5D=0&tblData%5BEquipamentos%5D%5BshowFilter%5D=1&tblData%5BEquipamentos%5D%5BshowRecCount%5D=1&tblData%5BEquipamentos%5D%5BshowNavBars%5D=1&tblData%5BEquipamentos%5D%5Bpage%5D=1&tblData%5BEquipamentos%5D%5Border%5D=idEqtoTipo%2CEqto&tblData%5BEquipamentos%5D%5Bgroup%5D=&tblData%5BEquipamentos%5D%5BlstFields%5D=Eqto%2CDns%2CidEqtoTipo%2CFabricante%2CSeries%2CModelo%2CsysIp%2CsysCommunity%2CsysVersion%2CActive%2CEnable%2CColetarConfig%2CColetarPerformance%2CError%2CTempoColeta%2CCnl%2CSite&tblData%5BEquipamentos%5D%5BwidthField%5D=33&tblData%5BEquipamentos%5D%5Blines%5D=50&rawk=556
	{
		"URL":"http://portalfsc/",
		"DataTab":{"SBC Acme Inventory":{"tabActived":"System Config"}},
		"DataList":{
			"Host Routes":{
				"showTable":"1",
				"page":"1",
				"order":"",
				"lstFields":"Device,idDevice,DtCol,dest_network,netmask,gateway,description,last_modified_date",
				"lines":"10",
				"values":{"Device":"*","idDevice":"*","DtCol":"*","dest_network":"*","netmask":"*","gateway":"*","description":"*","last_modified_date":"*"}
			}
		}
	}
-->