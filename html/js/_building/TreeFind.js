function showHide(obj,className,id,method){
	var oItem=$(obj).parent();
	var oDetails=$(oItem).find('div#details').first();

	$(oItem).toggleClass('closed');
	if($(oItem).hasClass('closed') || $(oDetails).text() || typeof(className)=='undefined') return;
	//if($(oItem).hasClass('closed') || typeof(className)=='undefined') return;

	$(oDetails).text('Carregando...');
	$.post('/bdc2/tools/fastFind.php',{'className':className,'id':id,'method':method},function(data,status){
		$(oDetails).html(status=='success'?data:status);
	});
}