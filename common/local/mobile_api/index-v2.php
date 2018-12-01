<?php
//error_reporting(E_ERROR);
//ini_set('display_errors', true);

// Allow from any origin
if (isset($_SERVER['HTTP_ORIGIN'])) {
	header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
	header('Access-Control-Allow-Credentials: true');
	header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
	if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
		header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
	}

	if(isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
		header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
	}
}

function mf()
{
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

$start = mf(); // если $conf['debug']['save_slow_log']=0 то переменная нигде не используется

require_once(realpath(dirname(__FILE__) . '/..').'/mobile_app_v2/data/classes/Bootstrap.php');

$aRes=array('error'=>array(), 'data'=>array());

$conf = Config::get();

$request_method = $_SERVER['REQUEST_METHOD'];

$_PUT_DELETE_GET = array();

if ($_SERVER['REQUEST_METHOD'] == 'PUT' || $_SERVER['REQUEST_METHOD'] == 'DELETE' || $_SERVER['REQUEST_METHOD'] == 'GET') {
	$putdata = file_get_contents('php://input');
	$exploded = explode('&', $putdata);

	foreach ($exploded as $pair) {
		$item = explode('=', $pair);
		if(count($item) == 2) {
			$_PUT_DELETE_GET[urldecode($item[0])] = urldecode($item[1]);
		}
	}
}

$aParam = array_merge($_GET, $_POST, $_PUT_DELETE_GET, $_FILES);
$method = isset($aParam['method']) ? trim($aParam['method'], "/") : null;

if (count($aRes['error']) == 0) {
	if (isset($method) && file_exists(CLASSESPATH.'/methods/'.$method.'.php')) {
		unset($aParam['method']);

		$oAPI = new $method(array(
			'conf' => $conf,
			'salt' => $conf['security']['salt'],
			'request_method' => $request_method,
			'param' => $aParam
		));

		$aResTmp = $oAPI->runMethod();
		$aRes['error'] += $aResTmp['errors'];
		
		if (count($aRes['errors']) == 0) {
			$aRes['data']+=$aResTmp['result'];
		}
	} else {
		$aRes['error']+=array(array('code' => '0', 'title' => 'Метод не найден'));
	}
}

if (count($aRes['error']) == 0) {
	unset($aRes['error']);
}

header('Content-Type: application/json');
echo json_encode($aRes);