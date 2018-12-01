<?php

class App
{

	protected static $_logger = null;
	protected static $_db = null;
	protected static $_mongo = null;

	public static function init() {
		self::_initLogger();
		\Monolog\ErrorHandler::register(self::$_logger);
	}

	protected static function _initLogger() {
		$logger = new \Monolog\Logger('app');
		$logger->pushHandler(
			new \Monolog\Handler\StreamHandler(ROOTPATH.'/tmp/eh-warning.log', \Monolog\Logger::NOTICE)
		);
		$logger->pushHandler(
			new \Monolog\Handler\StreamHandler(ROOTPATH.'/tmp/eh-error.log', \Monolog\Logger::ERROR)
		);

		self::$_logger = $logger;
	}

	/**
	 * @return MysqlDb
	 */
	public static function db() {
		if(!self::$_db) {
			$conf = Config::get('database');
			$_connectionString = "mysql:host=".$conf['host'].";port=".$conf['port'].";dbname=".$conf['dbname'].";charset=".$conf['charset'];
			$_dbUser = $conf['dbuser'];
			$_dbPassword = $conf['dbpwd'];
			self::$_db = MysqlDb::getInstance($_connectionString, $_dbUser, $_dbPassword);
		}
		return self::$_db;
	}

	/**
	 * @return MyMongoDb
	 */
	public static function mongo() {
		if(!self::$_mongo) {
			$conf = Config::get('mongo');
			self::$_mongo = MyMongoDb::getInstance($conf);
		}

		return self::$_mongo;
	}

	/**
	 * @return \Monolog\Logger
	 * @link https://github.com/Seldaek/monolog
	 */
	public static function logger() {
		if(!self::$_logger) {
			self::_initLogger();
		}
		return self::$_logger;
	}
} 