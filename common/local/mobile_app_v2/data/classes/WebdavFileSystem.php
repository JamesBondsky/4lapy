<?php

class WebdavFileSystem
{
	private static $_serverUrl = null;
	private static $_host = null;
	private static $_user = null;
	private static $_password = null;

	public function __construct($host, $user, $password, $ext_url) {
		self::$_host = $host;
		self::$_user = $user;
		self::$_password = $password;
		self::$_serverUrl = $ext_url;
	}

	public static function save($sContent, $sPath) {
		$ch = curl_init(self::$_host.$sPath);

		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		curl_setopt($ch, CURLOPT_USERPWD, self::$_user.':'.self::$_password);

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');

		curl_setopt($ch, CURLOPT_POSTFIELDS, $sContent);

		curl_exec($ch);

		return self::$_serverUrl.$sPath;
	}

	public static function exists($sPath) {
		/*$sContent = null;
		$sContent = @webdav_get($sPath);
		return $sContent ? true : false;*/
	}

	public static function delete($sPath) {
		$ch = curl_init(self::$_host.$sPath);

		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		curl_setopt($ch, CURLOPT_USERPWD, self::$_user.':'.self::$_password);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

		curl_exec($ch);
	}

	public static function rename($sPathOld, $sPathNew) {
		//webdav_rename($sPathOld, $sPathNew);
	}

	public static function getServerUrl() {
		return self::$_serverUrl;
	}

	public function getExtUrl() {
		return self::$_serverUrl;
	}

}
