<?php
$aMethods = [
	[
		'request_method' => 'get',
		'h' => [
		],
	],
	[
		'request_method' => 'get',
		'h' => [
			'domain_id' => '1,2,3',
		],
	],
	[
		'request_method' => 'get',
		'h' => [
			'project_id' => 6,
		],
	],
	[
		'request_method' => 'get',
		'h' => [
			'project_id' => 6,
			'get_deliveries' => 1,
		],
	],
	[
		'request_method' => 'get',
		'h' => [
			'project_id' => 6,
			'ab_list' => 1,
			'limit' => 10,
			'offset' => 0,
		],
	],
	[
		'request_method' => 'get',
		'h' => [
			'project_id' => '10,55',
		],
	],
	[
		'request_method' => 'get',
		'h' => [
			'project_id' => 10,
			'limit' => 3,
			'offset' => 0,
		],
	],
	[
		'request_method' => 'get',
		'h' => [
			'id' => 1,
		],
	],

	[
		'request_method' => 'post',
		'h' => [
			'project_id' => 10,
			'subject' => 'Новый шаблон письма'
		],
	],
	[
		'request_method' => 'put',
		'h' => [
			'id' => 162,
			'subject' => 'Новый шаблон письма',
		],
	],
	[
		'request_method' => 'post',
		'h' => [
			'id' => 162,
			'project_id' => 10,
			'subject' => 'Новый шаблон письма',
		],
	],
	[
		'request_method' => 'post',
		'h' => [
			'id' => 2,
			'project_id' => 10,
			'subject' => 'Новый шаблон письма - test 2',
			'content_html' => 'HTML контент 1234567890 !!! for TEST <img src="https://s.yimg.com/rz/l/yahoo_en-US_f_p_142x37.png">',
		],
	],

	[
		'request_method' => 'delete',
		'h' => [
			'id' => 35,
		],
	],

	[
		'request_method' => 'put',
		'h' => [
			'id' => 35,
			'clone' => 1,
		],
	],
];
