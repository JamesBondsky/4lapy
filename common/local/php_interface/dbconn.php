<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/php_interface/local.env.php';

define('BX_USE_MYSQLI', true);
define('DBPersistent', false);
$DBType = 'mysql';
$DBHost = getenv('DB_HOST');
$DBLogin = getenv('DB_LOGIN');
$DBPassword = getenv('DB_PASSWORD');
$DBName = getenv('DB_NAME');
$DBDebug = false;
$DBDebugToFile = false;
define('MYSQL_TABLE_TYPE', 'INNODB');

define('DELAY_DB_CONNECT', true);

define('CACHED_b_file', 3600);
define('CACHED_b_file_bucket_size', 10);
define('CACHED_b_lang', 3600);
define('CACHED_b_option', 3600);
define('CACHED_b_lang_domain', 3600);
define('CACHED_b_site_template', 3600);
define('CACHED_b_event', 3600);
define('CACHED_b_agent', 3660);
define('CACHED_menu', 3600);

define('BX_COMP_MANAGED_CACHE', true);

define('BX_UTF', true);
define('BX_FILE_PERMISSIONS', 0664);
define('BX_DIR_PERMISSIONS', 0775);
@umask(~BX_DIR_PERMISSIONS);
define('BX_DISABLE_INDEX_PAGE', true);

if (!(defined('CHK_EVENT') && CHK_EVENT === true)) {
    define('BX_CRONTAB_SUPPORT', true);
}