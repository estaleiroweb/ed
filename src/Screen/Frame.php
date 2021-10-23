<?php
class Frame {
	public $opened=true;

	function __construct($title='',$html='',$id=''){
		$this->title=$title;
		$this->html=$html;
		$this->id=$id;
		$this->OutHtml=OutHtml::singleton();
	}
	function __toString() {
		$this->OutHtml->style('Frame','easyData');
		$this->OutHtml->script('Frame','easyData');
		$class=$this->opened?"":" class='closed'";
		$content=$this->html;
		//$content=Screen::mount($this->html);
		$id=$this->id?" id='$this->id'":'';
		$ret="
<div id='FrameMain'$class$id>
	<div id='FrameTitleBar'>
		<div id='FrameButtom' onclick='FrameOpenClose(this)'></div>
		<div id='FrameTitle'>$this->title</div>
	</div>
	<div id='FrameContent'>$content</div>
</div>";
		return $ret;
	}
}