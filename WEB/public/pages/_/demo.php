<?php

use _\Navigation as _NV;

_NV\View::retrieve (
	'_/demo/main',
	[
		'text' => [
			'title' => 'Demo',
			'paragraph' => 'Lorem ipsum dolor sit amet.'
		],
		'list' => [
			'First' => 'lorem',
			'ipsum',
			'Other' => 'dolor'
		],
		'multiList' => [
			['A', 'B', 'third' => 'C'],
			['D', 'E', 'third' => 'F']
		]
	]
);

?>
