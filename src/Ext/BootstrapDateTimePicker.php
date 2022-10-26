<?php

namespace EstaleiroWeb\ED\Ext;

class BootstrapDateTimePicker extends Once {
	protected $versions = [
	];
	public function dependences($version) {
		new Bootstrap();
	}
}
