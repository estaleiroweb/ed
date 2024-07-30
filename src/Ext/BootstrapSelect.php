<?php

namespace EstaleiroWeb\ED\Ext;

class BootstrapSelect extends Once {
	protected $versions = [
	];
	public function dependences($version) {
		new Bootstrap();
	}
}
