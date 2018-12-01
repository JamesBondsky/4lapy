<?php
$aMethods = [
	[
		'request_method' => 'post',
		'h' => [
			'letter_id' => '62',
			'from_address' => '34',
			'dt_start' => '09.04.2015 17:10',
			'is_inline_images' => '1',
			'group_id' => '149',
			'exclude_group_id' => '150',
		],
	],
	[
		'request_method' => 'get',
		'h' => [
			'id' => '77',
			'limit' => 10,
			'offset' => 0,
		],
	],
	[
		'request_method' => 'get',
		'h' => [
			'letter_id' => '195',
			'limit' => 10,
			'offset' => 0,
		],
	],
	[
		'request_method' => 'get',
		'h' => [
			'delivery_id' => '50,60',
		],
	],
	[
		'request_method' => 'get',
		'h' => [
			'project_id' => '4,10,11',
			'limit' => 10,
			'offset' => 0,
		],
	],
	[
		'request_method' => 'get',
		'h' => [
			'domain_id' => '1,2,3',
			'limit' => 10,
			'offset' => 0,
		],
	],
	[
		'request_method' => 'get',
		'h' => [
			'dt_start_from' => '2015-03-05 00:00:00',
			'dt_start_to' => '2015-03-05 23:59:59',
			'limit' => 10,
			'offset' => 0,
		],
	],
	[
		'request_method' => 'post',
		'h' => [
			'letter_id' => '1',
			'from_address' => '2',
			'dt_start' => '16.12.2014 12:07',
			'dt_format' => 'd.m.Y G:i',
			'is_inline_images' => '1',
			'group_id' => '5,6,9,10',
		],
	],
	[
		'request_method' => 'put',
		'h' => [
			'id' => '31',
			'is_paused' => '1',
		],
	],
	[
		'request_method' => 'put',
		'h' => [
			'id' => '31',
			'is_paused' => '0',
		],
	],
	[
		'request_method' => 'put',
		'h' => [
			'id' => '31',
			'is_paused' => '0',
			'is_ad' => '1',
		],
	],
	[
		'request_method' => 'delete',
		'h' => [
			'id' => '164',
		],
	],
	[
		'request_method' => 'post',
		'h' => [
			'letter_id' => '62',
			'from_address' => '34',
			'dt_start' => '09.04.2015 17:10',
			'is_inline_images' => '1',
			'group_id' => '',
			'exclude_group_id' => '',
			'email' => 'test1@mail.ru, test2@mail.ru, test3@mail.ru',
		],
	],
	[
		'request_method' => 'get',
		'h' => [
			'project_id' => '',
			'limit' => 10,
			'offset' => 0,
		],
	],

	[
		'request_method' => 'post',
		'h' => [
			'type' => 'TRIGGER',
			'key' => 'delivery_trigger_key',
			'letter_id' => '62',
			'from_address' => '34',
			'is_inline_images' => '1',
		],
	],

	[
		'request_method' => 'put',
		'h' => [
			'id' => '31',
			'is_stopped' => '1',
		],
	],
];
