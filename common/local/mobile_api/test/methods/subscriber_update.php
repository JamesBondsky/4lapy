<?php

$conf = Config::get();

$time = time();
$hsh = getSubscriberUpdateHsh('ilicherv.am@gmail.com', '166', $time);
$aMethods = [
	[
		'request_method' => 'get',
		'h' => [
			'email' => 'ilicherv.am@gmail.com',
			'project_id' => '166',
			'time' => $time,
			'hsh' => $hsh,
			'is_unsubscribe' => 1,
			'unsubscribe_reasons' => [1, 4],
		],
	],
	[
		'request_method' => 'get',
		'h' => [
			'email' => 'ilicherv.am@gmail.com',
			'project_id' => '166',
			'time' => $time,
			'hsh' => $hsh,
			'is_unsubscribe' => 1,
			'unsubscribe_reasons' => '1,2,3',
		],
	],
	[
		'request_method' => 'get',
		'h' => [
			'email' => 'ilicherv.am@gmail.com',
			'project_id' => '166',
			'time' => $time,
			'hsh' => $hsh,
			'is_unsubscribe' => 0,
		],
	],
];
