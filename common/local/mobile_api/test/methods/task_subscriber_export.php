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
			'project_id' => '10',
		],
	],
	[
		'request_method' => 'post',
		'h' => [
			'project_id' => '10',
			'group_id' => '1,3',
			'email' => 'slin',
			'unsubscribe' => '0',
		],
	],
	[
		'request_method' => 'post',
		'h' => [
			'project_id' => '10',
			'group_id' => '1000',
			'email' => 'slin',
			'unsubscribe' => '0',
		],
	],
	[
		'request_method' => 'post',
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
	// subscriber_stat export
	// принимает параметры аналогичные subscriber_stat get кроме следующих: limit, offset
	[
		'request_method' => 'post',
		'h' => [
			'export_type' => 'subscriber_stat',
			'delivery_id' => 166,
			'tab' => 'success',
			'is_open' => 1,
			'sort' => 'email',
			'sort_order' => 'ASC',
			'email' => '.ru',
		],
	],

	[
		'request_method' => 'post',
		'h' => [
			'export_type' => 'subscriber_stat_full',
			'delivery_id' => 166,
		],
	],
];
