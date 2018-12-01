<?php
$aMethods = [
	[
		'request_method' => 'get',
		'h' => [
			'project_id' => '4',
		],
	],
	[
		'request_method' => 'get',
		'h' => [
			'letter_id' => '9',
		],
	],
	[
		'request_method' => 'get',
		'h' => [
			'id' => '1',
		],
	],
	[
		'request_method' => 'get',
		'h' => [
			'id' => '90,88,130,86',
			'exclude_id' => '122',
			'project_id' => '6',
			'is_only_subscribers_count' => 1,
		],
	],
	[
		'request_method' => 'get',
		'h' => [
			'project_id' => '6',
			'is_only_subscribers_count' => 1,
		],
	],
	[
		'request_method' => 'post',
		'h' => [
			'project_id' => '10',
			'name' => 'TestGroup',
		],
	],
	[
		'request_method' => 'put',
		'h' => [
			'id' => '6',
			'name' => 'TestGroup2',
		],
	],
	[
		'request_method' => 'delete',
		'h' => [
			'id' => '3',
		],
	],
	// Только удаляем подписчиков из группы, сама группа остается
	[
		'request_method' => 'delete',
		'h' => [
			'id' => '3',
			'only_clear_group' => '1',
		],
	],
	[
		'request_method' => 'delete',
		'h' => [
			'id' => '3,4,5,6',
			'only_clear_group' => '1',
		],
	],
];
