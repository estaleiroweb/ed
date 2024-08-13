<?php

namespace EstaleiroWeb\ED\Ext;

class Ed extends Once {
	protected $versions = [
		'1.0.0' => [
			'<script src="{$c->ed["js"]}/Ed.js"></script>',
		],
	];
	public function dependences($version) {
		new JQuery();
	}
}
