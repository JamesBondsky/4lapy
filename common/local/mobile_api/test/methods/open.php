<?php

$conf = Config::get();

$email = 'ilicherv.am@gmail.com';
$delivery_id = 348;
$aMethods = [
	[
		'request_method' => 'get',
		'h' => [
			'email' => $email,
			'delivery_id' => $delivery_id,
			'hsh' => getOpenHsh($email, $delivery_id),

			'TEST_URL(no_send)' => $conf['server']['api_protocol'].'://'.$conf['server']['api_domain']
				.'/open.gif?email='.$email.'&delivery_id='.$delivery_id.'&hsh='
				.getOpenHsh($email, $delivery_id)
		],
	],
];
