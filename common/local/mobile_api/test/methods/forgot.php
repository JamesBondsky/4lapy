<?php
$captchaUrl = 'http://'.$_SERVER['HTTP_HOST'].'/captcha?width=130&height=70&key=test_static_captcha_key';
echo 'Code:<br><form method="post" action="">'
	.'<img src="'.$captchaUrl.'" onclick="this.setAttribute(\'src\', \''.$captchaUrl.'\')">'
	.'<br><br><input type="text" name="code" value="">'
	.'<input type="submit"></form>';
$aMethods = [
	[
		'request_method' => 'post',
		'h' => [
			'email' => 'ilicherv.am@gmail.com',
			'key' => 'test_static_captcha_key',
			'code' => isset($_POST['code']) ? $_POST['code'] : '',
		],
	],
	[
		'request_method' => 'post',
		'h' => [
			'email' => 'ilicherv.am@gmail.com123',
			'key' => 'test_static_captcha_key',
			'code' => isset($_GET['code']) ? $_GET['code'] : '',
		],
	],
];
