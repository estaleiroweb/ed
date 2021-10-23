(function ($, window, document, undefined) {
	$.fn.DataGraphChart = function (chart,options) {
		var $this=$(this);
		if(chart===undefined) chart=new CanvasJS.Chart(this[0]);
		if(options) {
			chart.options=options;
			chart.render();
		}
		return chart;
	}
	$.fn.DataGraph = function (param) {
		return this.each(function () {
			//console.log('loading...')
			//<div ed-element="canvasjs" source="dados.php" refresh="15"></div>
			var $this=$(this);
			var chart;
			var showDataGraph=function (data){
				//if(data===undefined) data=
				
				//if(typeof data!=='object') 
				data=$.json_decode(data);
				//console.log(typeof data);
				//data['aaaa']='xxx';
				
				console.log(data);
				//$this.data('canvasjsChartRef',data);
				
				chart=$this.DataGraphChart(chart,data);
				chart.raw=data;
				
				$this.css('height',data.height || $($this.find('canvas')[0]).outerHeight(true));
				if(data.width) $this.css('width',data.width);
				//console.log($.json_decode({ "name": "John", "a": [1,2,3], "fn": 'function(e\n\t){alert(e);\n\n\t}' }))
				//console.log(chart.formatDate);
				//chart.add_dropdownMenu('test',function(){alert('Um test de sistema');});
				//data.legend.itemclick();
			};
			//var showDataGraphEvent=function(e,obj,data,onEvent){ return showDataGraph(data); };
			
			var url=null,dt={};
			//if((url=$this.attr('source')) || (url=$this.attr('path'))) $.ajax({url: url}).done(showDataGraph); else {
			if(!(url=$this.attr('source')) && !(url=$this.attr('path'))) {
				var e='$g=new DataGraph(\''+$this.attr('id')+'\',\''+$this.attr('idFile')+'\');return $g();';
				var dataPost=param?param:$.parseParams(window.location.search.slice(1));
				
				//console.log(dataPost);
				dt={cmd:'eval','__eval':e,data:dataPost};
				//print_r($g());
				//path=window.easyData['fn']+'/execCmd.php';
				//return;
			}
			var $ed=new $.Ed();
			$ed.ajax_on.beforeSend=function(args){ 
				$this.append('<img src="/easyData/skin/default/img/gifs/loding_blue_02.gif" style="position: absolute;display: inline-block;z-index:200;" />');
				return true; 
			};
			$ed.ajax_on.error=function(args){ 
				$this.html('<pre class="bg-danger"><b style="font-size: 150%;">'+args.textStatus+'</b>: '+args.errorThrown+'</pre>'+args.jqXHR.responseText);
				console.error(args.jqXHR.responseText);
			};
			$ed.ajax_on.success=function(args){ return showDataGraph(args.data); };
			$ed.ajax.async=true;
			$ed.execCmd(dt,url);
					
			var refresh=$this.attr('refresh');
			refresh=refresh===undefined?0:Number(refresh);
			if(refresh) setTimeout(function (){ $this.DataGraph(dataPost);}, refresh*1000);
			//if(refresh) setTimeout(function (){ $ed.execCmd(dt,url); }, refresh*1000);
		});
	}
}(jQuery, window, document));
$(document).ready(function(){
	$('[ed-element="DataGraph"]').DataGraph();
});

function click_link(e){
	//window.open(e.dataSeries.link,e.dataSeries.target || '_blank');
	
	var l=e.dataSeries.link;
	if(!l) return;
	switch(getType(l)) {
		case('string'):
			$.goURL(l);
			break;
		case('object'):
			if(event.ctrlKey || event.shiftKey) l.gdet=null;
			$.uriChange(l);
			break;
		case('function'):
			l(e);
			break;
		default:
			console.warn(l);
	}
}
function click_legend(e){
	var d=e.chart.options.data;
	var t=d.length;
	if(event.ctrlKey) {
		e.dataSeries.visible=e.dataSeries.visible===false?true:false;
	}
	else if(event.shiftKey) {
		if(e.chart.lastLegendClick===undefined) {
			e.chart.lastLegendClick=e.dataSeriesIndex;
			return;
		}
		if(e.chart.lastLegendClick==e.dataSeriesIndex) return;
		if(e.chart.lastLegendClick>e.dataSeriesIndex) var a=e.dataSeriesIndex,b=e.chart.lastLegendClick;
		else var a=e.chart.lastLegendClick,b=e.dataSeriesIndex;
		for(var i=0;i<a;i++)   d[i].visible=false;
		for(var i=b+1;i<t;i++) d[i].visible=false;
		for(var i=a;i<=b;i++)  d[i].visible=true;
	}
	else {
		e.chart.lastLegendClick=e.dataSeriesIndex;
		for(var i=0;i<t;i++) d[i].visible=(e.dataSeriesIndex==i);
	}
	e.chart.render();
}
function mouseover_dataSeries(e){
	//console.log(e);
	if(e.dataSeries.link) {
		e.dataSeries.lineThicknessOld=(e.dataSeries.lineThickness || 2);
		e.dataSeries.lineThickness=e.dataSeries.lineThicknessOld*2;
	}
	e.chart.raw.indexData=e.dataSeriesIndex;
	//console.log(e.dataSeriesIndex);
	e.chart.render();
}
function mouseout_dataSeries(e){
	//console.log(e);
	if(e.dataSeries.link) {
		e.dataSeries.lineThickness=e.dataSeries.lineThicknessOld;
	}
	e.chart.raw.indexData=null;
	e.chart.render();
}
function toolTip_shared(e){
	var content=[];
	
	var date=true;
	for (var i=0; i < e.entries.length; i++) {
		var et=e.entries[i];
		
		if(date) {
			content.push(toolTip_get_x(et));
			date=false;
		}
		var tag=e.chart.raw.indexData===i?'b':'span';
		content.push(toolTip_get_y(et,toolTip_get_label(et,'span')+': ',tag));
	}
	return content.join('<br/>');
}
function toolTip(e){
	//console.log(e);
	if(e.entries.length>1) return toolTip_shared(e);
	var et=e.entries[0];
	var content=[];
	var lbl=toolTip_get_label(et);
	if(lbl) content.push(lbl);
	content.push(toolTip_get_x(et));
	content.push(toolTip_get_y(et,'<b>Valor</b>: '));
	return content.join('<br/>');
}
function toolTip_get_label(et,tag){
	if(!tag) tag='b';
	//var title=et.dataSeries.label || et.dataSeries.legendText || et.dataSeries.indexLabel || et.dataSeries.name;
	var title=et.dataSeries.label || et.dataSeries.legendText;
	
	return title?'<'+tag+' style="color:'+et.dataSeries.color+';">'+title+'</'+tag+'>':false;
}
function toolTip_get_x(et){
	var chart=et.dataSeries.chart;
	var out='<b>';
	if(et.dataSeries.xValueType=='dateTime') {
		out+='Data';
		var fn='formatDate';
	} else {
		out+='X';
		var fn='formatNumber';
	}
	out+=': </b>';
	out+=chart.fn[fn](et.dataPoint.x,et.dataSeries.xValueFormatString || et.dataSeries.axisX.valueFormatString || et.dataSeries.axisX.autoValueFormatString,chart.culture);
	return out;
}
function toolTip_get_y(et,out,tag){
	if(!tag) tag='span';
	out+='<'+tag+'>';
	var chart=et.dataSeries.chart;
	var dt=chart.raw.data[et.dataSeries.index];
	var yFormat=et.dataSeries.yValueFormatString || et.dataSeries.axisY.valueFormatString || et.dataSeries.axisY.autoValueFormatString;
	var addY=dt.CounterUnit?' '+dt.CounterUnit:'';
	if(dt.Aggr) addY+='['+dt.Aggr+']';
	if(typeof et.dataPoint.y=='object') {
		out+='<ul>';
		for(var i in et.dataPoint.y) out+='<li>'+chart.fn.formatNumber(et.dataPoint.y[i],yFormat,chart.culture)+addY+'</li>';
		out+='</ul>';
	}
	else out+=chart.fn.formatNumber(et.dataPoint.y,yFormat,chart.culture)+addY;
	out+='</'+tag+'>';
	return out;
}
