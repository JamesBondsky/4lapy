<?php
$aMethods = [
	[
		'request_method' => 'get',
		'h' => [],
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
			'id' => '2',
		],
	],
	[
		'request_method' => 'post',
		'h' => [
			'name' => 'Тестовый проект',
			'domain' => 'ya.ru',
		],
	],
	[
		'request_method' => 'put',
		'h' => [
			'id' => '3',
			'name' => 'Тестовый проект - обновленное название',
		],
	],
	[
		'request_method' => 'delete',
		'h' => [
			'id' => '3',
		],
	],
];
