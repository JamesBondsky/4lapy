<?php

$conf = Config::get();
echo '<hr><b>Request method:</b> post<br><form method="post" action="http://'.$conf['server']['api_domain'].'/attachment/" enctype="multipart/form-data">'
	.'<br>file: <input type="file" name="file" value="">'
	.'<br>letter_id: <input type="text" name="letter_id" value="1">'
	.'<br>type: <select name="type"><option value="FILE">FILE</option><option value="IMAGE">IMAGE</option></select>'
	.'<br>token: <input type="text" name="token" value="">'
	.'<br><input type="submit"></form>';


$aMethods = [
	[
		'request_method' => 'get',
		'h' => [
			'id' => '1',
			'letter_id' => '1',
		],
	],
	[
		'request_method' => 'get',
		'h' => [
			'letter_id' => '1',
			'type' => 'FILE',
		],
	],
	[
		'request_method' => 'get',
		'h' => [
			'letter_id' => '1',
			'type' => 'IMAGE',
		],
	],
	[
		'request_method' => 'put',
		'h' => [
			'id' => '20',
			'letter_id' => '1',
			'is_shared' => '1',
		],
	],
	[
		'request_method' => 'delete',
		'h' => [
			'id' => '9',
			'letter_id' => '10',
		],
	],
	[
		'request_method' => 'delete',
		'h' => [
			'id' => '55,56,57',
			'letter_id' => '161',
			'type' => 'IMAGE',
		],
	]
];
