<?php
$aMethods = [
	[
		'request_method' => 'get',
		'h' => [],
	],
	[
		'request_method' => 'get',
		'h' => [
			'name' => '.ru',
		],
	],
	[
		'request_method' => 'get',
		'h' => [
			'id' => '3',
		],
	],
	[
		'request_method' => 'post',
		'h' => [
			'name' => 'market-soft.ru',
			'abuse_username' => 'login@nickname.ru',
			'abuse_password' => 'pAssw0rd',
		],
	],
	[
		'request_method' => 'put',
		'h' => [
			'id' => '3',
			'is_active' => '1',
			'abuse_username' => 'login@nickname.ru',
			'abuse_password' => 'pAssw0rd',
		],
	],
	[
		'request_method' => 'put',
		'h' => [
			'id' => '3',
			'is_active' => '1',
			'abuse_username' => 'login@nickname.ru',
			'abuse_password' => 'pAssw0rd',
			'try_to_guess_abuse_mailbox' => '1',
		],
	],
	[
		'request_method' => 'put',
		'h' => [
			'id' => '3',
			'is_active' => '1',
			'abuse_username' => 'login@nickname.ru',
			'abuse_password' => 'pAssw0rd',
			'abuse_protocol' => 'IMAP',
			'abuse_host' => 'imap.test.ru',
			'abuse_port' => '993',
			'abuse_is_ssl' => '1',
		],
	],
	[
		'request_method' => 'delete',
		'h' => [
			'id' => '1',
		],
	],
];
