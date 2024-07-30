$(document).ready(function(){
	$('fieldset.ed  legend').click(function(e){
		var $dad=$(this).parent()
		$dad.children('div').toggle('slow');
		if($dad.hasClass('closed')) $dad.removeClass('closed');
		else $dad.addClass('closed');
	});
});
