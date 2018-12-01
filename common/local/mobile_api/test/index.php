<?php

// init
require_once(dirname(dirname(dirname(__FILE__))).'/data/classes/Bootstrap.php');

define('FILEPATH', HTDOCSPATH.'/test');

$conf = init();

if($conf['test']['enable']) {
	echo '
<style>
	html, body {
		padding: 0;
		margin: 0;
	}
	table {
		width: 100%;
		height: 100%;
		border-spacing: 0;
		border: 0;
	}
	tr, th, td {
		vertical-align: top;
		padding: 20px;
	}
	a, a:hover, a:visited {
		color: blue;
	}
</style>
';
	require_once CLASSESPATH.'/APIClient.php';

	$oAPIClient = new APIClient([
		'protocol' => $conf['server']['api_protocol'],
		'server' => $conf['server']['api_domain'],
		'salt' => $conf['security']['salt'],
		'is_debug' => true,
	]);

	$method = trim(@$_GET['method'], '/');

	$aMethodsList = [];
	if($handle = opendir(FILEPATH.'/methods/')) {
		while(false !== ($file = readdir($handle))) {
			if($file != "." && $file != "..") {
				$dotPos = strripos($file, '.');
				if(substr($file, $dotPos + 1) == 'php') {
					$aMethodsList[] = substr($file, 0, $dotPos);
				}
			}
		}
		closedir($handle);
	}
	sort($aMethodsList);
	echo '<table><tr><td style="border-right: 1px solid #000000">';
	foreach($aMethodsList as $methodName) {
		echo '<div><a href="/test/'.$methodName.'/"'.($method == $methodName ? ' style="font-weight: bold;"' : '').'>'.$methodName.'</a></div>';
	}
	echo '</td><td style="background: #f7faff; width: 100%;"><pre>';
	if($method) {
		echo '<h3>Method: '.$method.'</h3>';
	}

	$path = FILEPATH.'/methods/'.$method.'.php';
	if(file_exists($path)) {
		include_once($path);
	} else {
		$aMethods = [];
	}


	if($method) {
		echo '<hr>';
		foreach($aMethods as $test) {
			$h = $test['h'];
			$h['method'] = $method;
			$oAPIClient->$test['request_method']($h);
		}
	}
	echo '</pre></td></tr></table>';
} else {
	// Rest 404
	$aRes = ['errors' => []];
	$request_method = $_SERVER['REQUEST_METHOD'];

	$_PUT_DELETE_GET = [];
	if($_SERVER['REQUEST_METHOD'] == 'PUT' || $_SERVER['REQUEST_METHOD'] == 'DELETE' || $_SERVER['REQUEST_METHOD'] == 'GET') {
		$putdata = file_get_contents('php://input');
		$exploded = explode('&', $putdata);

		foreach($exploded as $pair) {
			$item = explode('=', $pair);
			if(count($item) == 2) {
				$_PUT_DELETE_GET[urldecode($item[0])] = urldecode($item[1]);
			}
		}
	}

	$aParam = array_merge($_GET, $_POST, $_PUT_DELETE_GET, $_FILES);
	$aRes['method'] = 'test';
	$aRes['request_method'] = $request_method;
	$aRes['errors'] += ['method_not_found' => ['msg' => 'Метод не найден', 'msg_en' => 'Method not found']];

	header('Content-Type: application/json');
	echo json_encode($aRes);
}
