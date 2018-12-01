<?php

$conf = Config::get();

$aMethods = [
	[
		'request_method' => 'get',
		'h' => [
			'email' => 'ilicherv.am@gmail.com',
			'delivery_id' => '166',
			'link' => urlencode('https://www.google.ru/?gws_rd=ssl'),
			'hsh' => getClickHsh('ilicherv.am@gmail.com', '166', 'https://www.google.ru/?gws_rd=ssl'),

			'TEST_URL(no_send)' => $conf['server']['api_protocol'].'://'.$conf['server']['api_domain']
				.'/click?email=ilicherv.am@gmail.com&delivery_id=166&link='.urlencode('https://www.google.ru/?gws_rd=ssl').'&hsh='
				.getClickHsh('ilicherv.am@gmail.com', '166', 'https://www.google.ru/?gws_rd=ssl')
		],
	],
	[
		'request_method' => 'get',
		'h' => [
			'email' => 'ilicherv.am@gmail.com',
			'delivery_id' => '1',
			'link' => urlencode('https://www.google.ru/?gws_rd=ssl'.$conf['subscriber']['unsubscribe_link_hash']),
			'hsh' => getClickHsh('ilicherv.am@gmail.com', '1', 'https://www.google.ru/?gws_rd=ssl'.$conf['subscriber']['unsubscribe_link_hash']),

			'TEST_URL(no_send)' => $conf['server']['api_protocol'].'://'.$conf['server']['api_domain']
				.'/click?email=ilicherv.am@gmail.com&delivery_id=1&link='.urlencode('https://www.google.ru/?gws_rd=ssl'.$conf['subscriber']['unsubscribe_link_hash']).'&hsh='
				.getClickHsh('ilicherv.am@gmail.com', '1', 'https://www.google.ru/?gws_rd=ssl'.$conf['subscriber']['unsubscribe_link_hash'])
		],
	],
	[
		'request_method' => 'get',
		'h' => [
			'email' => '_squirrel_ne@mail.ru',
			'delivery_id' => '1',
			'link' => urlencode($conf['subscriber']['unsubscribe_link_hash']),
			'hsh' => getClickHsh('_squirrel_ne@mail.ru', '1', $conf['subscriber']['unsubscribe_link_hash']),

			'TEST_URL(no_send)' => $conf['server']['api_protocol'].'://'.$conf['server']['api_domain']
				.'/click?email=_squirrel_ne@mail.ru&delivery_id=1&link='.urlencode($conf['subscriber']['unsubscribe_link_hash']).'&hsh='
				.getClickHsh('_squirrel_ne@mail.ru', '1', $conf['subscriber']['unsubscribe_link_hash'])
		],
	],
];
