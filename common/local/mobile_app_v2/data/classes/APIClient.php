<?php

class APIClient
{
	protected $server;
	protected $protocol = 'https';
	protected $salt;

	protected $isDebug = false;

	protected $login;
	protected $password;
	protected $pathToCookie;

	function __construct($h) {
		if(empty($h['server'])) {
			throw new Exception('Parameter "server" not passed', 500);
		}
		$this->server = $h['server'];
		if(!empty($h['protocol'])) {
			$this->protocol = $h['protocol'];
		}
		$this->salt = isset($h['salt']) ? $h['salt'] : '';

		if(isset($h['is_debug'])) {
			$this->isDebug = ($h['is_debug'] ? true : false);
		}

		$this->login = isset($h['login']) ? $h['login'] : 'ilicherv.am@gmail.com';
		$this->password = isset($h['password']) ? $h['password'] : '1';

		$this->pathToCookie = isset($h['path_to_cookie']) ? $h['path_to_cookie'] : 'curl.cookie';
		$dirCookie = dirname($this->pathToCookie);
		if(!is_dir($dirCookie)) {
			mkdir($dirCookie, 0775, true);
		}
	}

	public function get($h) {
		return $this->exec('get', $h);
	}

	public function post($h) {
		return $this->exec('post', $h);
	}

	public function delete($h) {
		return $this->exec('delete', $h);
	}

	public function put($h) {
		return $this->exec('put', $h);
	}

	private function exec($request_method, $h) {
		$method = $h['method'];
		unset($h['method']);
		if(isset($h['sign_method'])) {
			unset($h['sign_method']);
			$h['sign_dt'] = time();
			$h['sign'] = $this->getSign($h, $this->salt);
		}

		if($this->isDebug) {
			echo '<b>Request method:</b> '.$request_method.'<br><br>';
			echo "<b>Data:</b><br>";
			print_r(['json' => json_encode($h), 'array' => $h]);
			echo "<br>";
		}

		$myCurl = curl_init();
		curl_setopt_array($myCurl, [
			CURLOPT_URL => $this->protocol.'://'.$this->server.'/'.$method.'/',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CUSTOMREQUEST => strtoupper($request_method),
			CURLOPT_POSTFIELDS => http_build_query($h),
			CURLOPT_COOKIEFILE => $this->pathToCookie,
			CURLOPT_COOKIEJAR => $this->pathToCookie,
		]);
		if(isset($_SERVER['HTTP_USER_AGENT'])) {
			curl_setopt($myCurl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		}
		$response = curl_exec($myCurl);
		curl_close($myCurl);

		$jsonArray = json_decode($response, true);
		if($this->isDebug) {
			echo "<b>Answer</b>:<br>";
			print_r(['json' => $response, 'array' => $jsonArray]);
			echo '<br><hr><br>';
		} else {
			if(isset($jsonArray['errors']) && isset($jsonArray['errors']['bad_token'])) {
				// try login
				$jsonArray = $this->post([
					'method' => 'login',
					'email' => $this->login,
					'password' => $this->password,
					'remember' => '1',
				]);
				if(isset($jsonArray['result']) && isset($jsonArray['result']['user'])) {
					$jsonArray = $this->$method($h);
				}
			}
			return $jsonArray;
		}
	}

	function getSign($h, $salt) {
		$sForSign = '';
		ksort($h);
		foreach($h as $key => $val) {
			$sForSign .= (is_array($val) && isset($val['tmp_name'])) ? md5_file($val['tmp_name']).'~' : $val.'~';
		}
		return md5($sForSign.$salt);
	}
}