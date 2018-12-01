<?php

class Config
{
	static $_config = null;

	protected static function _init() {
		self::$_config = ArrayHelper::merge(
			self::_readIni("mobile-app.ini"),
			self::_readIni("mobile-app.local.ini")
		);
	}

	protected static function _readIni($file) {
		$file = CONFPATH."/$file";
		if(file_exists($file)) {
			return parse_ini_file($file, true);
		} else {
			return array();
		}
	}

	/**
	 * @param string|null $section
	 * @param string|null $param
	 * @return array
	 */
	static function get($section = null, $param = null) {
		if(!self::$_config) self::_init();
		return isset($section)
			? (isset($param) ? self::$_config[$section][$param] : self::$_config[$section])
			: self::$_config;
	}

}

function mergeArray(array $array1, array $array2 = null, array $_ = null) {
	$args = func_get_args();
	$res = array_shift($args);
	while(!empty($args)) {
		$next = array_shift($args);
		foreach($next as $k => $v) {
			if(is_integer($k))
				isset($res[$k]) ? $res[] = $v : $res[$k] = $v;
			elseif(is_array($v) && isset($res[$k]) && is_array($res[$k]))
				$res[$k] = mergeArray($res[$k], $v);
			else
				$res[$k] = $v;
		}
	}
	return $res;
}

function init() {
	return Config::get();
}
