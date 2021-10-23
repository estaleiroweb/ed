$(document).ready(function(){
	$('.dataTabFrame li.active a').each(function(){ 
		var $this=$(this);
		var idFrm=$this.attr('target');
		$('#'+idFrm).attr("src",$this.attr("href")); 
	});
	$('.dataTabFrame li a').click(function(){
		$(".dataTabFrame li.active").removeClass("active");
		$(this).parent().addClass("active");
	});
	var arr_tblDataFrame={};
	function resize_iframe(obj){
		//console.log([obj.height,obj.contentWindow.document.body.scrollHeight]);
		obj.height=(obj.contentWindow.document.body.scrollHeight) + 'px';
		if(arr_tblDataFrame[obj.id]===undefined) {
			//console.log(obj.id)
			arr_tblDataFrame[obj.id]=setInterval(function(){ resize_iframe(obj); }, 500);
		}
	}
	$('[ed-element="tblDataFrame"]').load(function(){resize_iframe(this);});

	//window.addEventListener("resize", myFunction);

});