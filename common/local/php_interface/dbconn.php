<?php

use Symfony\Component\Dotenv\Dotenv;

if (getenv('APP_ENV') === false) {
    require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

    $dotenv = new Dotenv();
    $dotenv->load(dirname(__DIR__, 3) . '/.env.local');
}

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

define('BX_CACHE_TYPE', 'memcache');
//define("BX_CACHE_TYPE", "CPHPCacheMemcacheHL");
//define("BX_CACHE_CLASS_FILE", realpath($_SERVER["DOCUMENT_ROOT"]) . '/local/php_interface/cache_memcache_hl.php');

define('BX_MEMCACHE_HOST', getenv('BX_MEMCACHE_HOST'));
define('BX_MEMCACHE_PORT', getenv('BX_MEMCACHE_PORT'));
define('BX_SECURITY_SESSION_MEMCACHE_HOST', getenv('BX_SESSION_MEMCACHE_HOST'));
define('BX_SECURITY_SESSION_MEMCACHE_PORT', getenv('BX_SESSION_MEMCACHE_PORT'));
define('BX_CACHE_SID', realpath($_SERVER['DOCUMENT_ROOT']) . '#01');

define('BX_UTF', true);
define('BX_FILE_PERMISSIONS', 0664);
define('BX_DIR_PERMISSIONS', 0775);
@umask(~BX_DIR_PERMISSIONS);
define('BX_DISABLE_INDEX_PAGE', true);

if (!(defined('CHK_EVENT') && CHK_EVENT === true)) {
    define('BX_CRONTAB_SUPPORT', true);
}

define('BX_COMPRESSION_DISABLED', true);
