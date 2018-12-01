<?php

$conf = Config::get();

$aMethods = [
	[
		'request_method' => 'post',
		'h' => [
			'email' => 'ilicherv.am@gmail.com',
			'password' => '1',
			'remember' => '1',
		],
	],
	[
		'request_method' => 'get',
		'h' => [
			'email' => 'ilicherv.am@gmail.com',
			'password' => '1',
			'remember' => '1',
			'is_iframe_login' => '1',
			'LINK_EXAMPLE' => $conf['server']['api_protocol'].'://'.$conf['server']['api_domain'].'/login?email=ilicherv.am@gmail.com'
				.'&password=1&remember=1&is_iframe_login=1',
		],
	],

	/*array(
		'request_method' => 'post',
		'h' => array(
			'email' => 'ilicherv.am@gmail.com',
			'password' => 'invalid_pass',
			'remember' => '0',
		),
	),
	array(
		'request_method' => 'post',
		'h' => array(
			'email' => 'ilicherv.am@gmail.com3423',
			'password' => '1',
			'remember' => '1',
		),
	),
	array(
		'request_method' => 'post',
		'h' => array(
			'email' => 'internet@europaplustv.com',
			'password' => '1',
			'remember' => '1',
		),
	),*/
];
