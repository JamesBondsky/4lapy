<?php

$conf = Config::get();

echo '<hr><b>Request method:</b> post<br><form method="post" action="http://'.$conf['server']['api_domain'].'/task_subscriber_import/" enctype="multipart/form-data">'
	.'<br>file: <input type="file" name="file" value="">'
	.'<br>project_id: <input type="text" name="project_id" value="10">'
	.'<br>group_id: <input type="text" name="group_id" value="5,10,15">'
	.'<br>token: <input type="text" name="token" value="">'
	.'<br><input type="submit"></form>';


$aMethods = [
	[
		'request_method' => 'get',
		'h' => [
		],
	],
	[
		'request_method' => 'get',
		'h' => [
			'project_id' => '10',
		],
	],
	[
		'request_method' => 'delete',
		'h' => [
			'id' => '2',
		],
	],
];
