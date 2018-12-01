<?php

define ('ROOTPATH', dirname(dirname(dirname(dirname(__FILE__)))));
define ('DOC_ROOT', ROOTPATH.'/mobile_app_v2');
define ('DATAPATH', ROOTPATH.'/mobile_app_v2/data');
define ('PHANTOMPATH', ROOTPATH.'/mobile_app_v2/PhantomJs');
define ('TMPPATH', ROOTPATH.'/mobile_app_v2/tmp');
define ('BACKUPPATH', ROOTPATH.'/mobile_app_v2/tmp/backup');
define ('CONFPATH', realpath(dirname(ROOTPATH).'/..') . '/app/config');
define ('HTDOCSPATH', ROOTPATH.'/mobile_app_v2/htdocs');
define ('CLASSESPATH', DATAPATH.'/classes');
define ('METHODPATH', CLASSESPATH.'/methods');
define ('HELPERSPATH', CLASSESPATH.'/helpers');

spl_autoload_register(
	function ($class_name) {
		if(file_exists(CLASSESPATH.'/'.$class_name.'.php')) {
			require_once CLASSESPATH.'/'.$class_name.'.php';
		} elseif(file_exists(METHODPATH.'/'.$class_name.'.php')) {
			require_once METHODPATH.'/'.$class_name.'.php';
		} elseif(file_exists(METHODPATH.'/base/'.$class_name.'.php')) {
			require_once METHODPATH.'/base/'.$class_name.'.php';
		} elseif(file_exists(HELPERSPATH.'/class.'.strtolower($class_name).'.php')) {
			require_once HELPERSPATH.'/class.'.strtolower($class_name).'.php';
		}
	}
);

require_once CLASSESPATH.'/Lib.php';

date_default_timezone_set(Config::get('server', 'timezone'));

if(Config::get('debug', 'enable')) {
	error_reporting(E_ALL);
	ini_set('display_errors', true);
}