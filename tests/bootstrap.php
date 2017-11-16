<?php
/**
 * Bootstrap-файл для запуска phpUnit
 */
$_SERVER['DOCUMENT_ROOT'] = realpath(dirname(__DIR__) . '/web');
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);

if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include.php')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include.php';
}
set_time_limit(0);
