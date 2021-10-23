$(document).ready(function(){
	$('ul.tblDataTab li a').click (function(e){
		var $this=$(this);
		var $tab=$($this.parents('.tblDataTab')[0]);
		var $input=$tab.find('input');
		var id=$this.attr('id');
		if($input.val()!=id) {
			$input.val($this.attr('id'));
			$tab.parents('form')[0].submit();
		}
	});
});
