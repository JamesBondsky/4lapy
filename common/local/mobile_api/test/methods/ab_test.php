<?php

$aMethods = [
	[
		'request_method' => 'get',
		'h' => [
			'id' => '17',
		],
	],
	[
		'request_method' => 'get',
		'h' => [
			'project_id' => '19',
			'limit' => '5',
			'offset' => '1',
		],
	],
	[
		'request_method' => 'post',
		'h' => [
			'name' => 'Название АБ теста',
			'project_id' => '19',
			'group_id' => '121,149',
			'cnt_subscriber' => '100',
			'deliveries' => [
				[
					'letter_id' => '60',
					'from_address' => '34',
					'from_address_caption' => '',
					'dt_start' => '22.04.2014 10:00',
					'dt_format' => 'd.m.Y G:i',
					'is_inline_images' => '0',
					'is_ad' => '0',
					'percent_subscribers' => '33',
				],
				[
					'letter_id' => '62',
					'from_address' => '34',
					'from_address_caption' => 'Новый капшион',
					'dt_start' => '22.04.2014 11:00',
					'dt_format' => 'd.m.Y G:i',
					'is_inline_images' => '1',
					'is_ad' => '0',
					'percent_subscribers' => '64',
				],
			],
		],
	],
	[
		'request_method' => 'put',
		'h' => [
			'ab_test_id' => '17',
			'is_finish_ab' => '1',
			'cnt_subscriber' => '',
			'letter_id' => '434',
			'from_address' => '17',
			'dt_start' => '14.05.2015 18:00',
			'dt_format' => 'd.m.Y G:i',
			'is_inline_images' => '0',
			'is_ad' => '0',
		],
	],
	[
		'request_method' => 'put',
		'h' => [
			'ab_test_id' => '17',
			'is_finish_ab' => '1',
			'delivery_id' => '345',
		],
	],
	[
		'request_method' => 'put',
		'h' => [
			'ab_test_id' => '17',
			'is_paused' => '1',
		],
	],
	[
		'request_method' => 'put',
		'h' => [
			'ab_test_id' => '17',
			'is_stopped' => '1',
		],
	],
];
