<?php
$aMethods = [
	[
		'request_method' => 'put',
		'h' => [
			'name' => 'Алексей Ильичёв',
		],
	],
	[
		'request_method' => 'put',
		'h' => [
			'old_password' => '1',
			'new_password' => '1',
		],
	],
	[
		'request_method' => 'put',
		'h' => [
			'name' => 'Алексей Ильичёв',
			'old_password' => '1',
			'new_password' => '1',
		],
	],
	[
		'request_method' => 'put',
		'h' => [
			'old_password' => 'fake_pass',
			'new_password' => '1',
		],
	],
	[
		'request_method' => 'put',
		'h' => [
			'name' => 'Алексей Ильичёв',
			'old_password' => 'fake_pass',
			'new_password' => '1',
		],
	],
	[
		'request_method' => 'put',
		'h' => [
			'name' => '',
			'old_password' => '1',
			'new_password' => '',
		],
	],
];
