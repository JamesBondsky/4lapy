<?php

use Adv\Bitrixtools\Tools\Log\ExceptionLogger;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\DB\MysqliConnection;
use FourPaws\App\Env;

/*
 * Обязательно нужно подключить vendor/autoload.php ,
 * чтобы автозагрузка классов позволила настроить перехват логирования
 */
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

require_once dirname(__DIR__) . '/bitrix/php_interface/local.env.php';

return [
    'utf_mode' =>
        [
            'value' => true,
            'readonly' => true,
        ],

    'cache' => [
        'value' => [
            'type' => [
                'class_name' => 'CustomCacheEngineMemcache',
                'required_remote_file' => realpath($_SERVER['DOCUMENT_ROOT']) . '/../src/Bitrix/Cache/CustomCacheEngineMemcache.php'
            ],
            'memcache' => [
                'host' => getenv('BX_MEMCACHE_HOST'),
                'port' => getenv('BX_MEMCACHE_PORT'),
            ],
            'sid' => realpath($_SERVER['DOCUMENT_ROOT']) . '#01',
            'use_lock' => true,
        ],
    ],

/*    'cache' => [
        'value' => [
            'sid' => realpath($_SERVER['DOCUMENT_ROOT']) . '#01',
            'type' => [
                'required_remote_file' => realpath($_SERVER["DOCUMENT_ROOT"]) . '/local/php_interface/cache_memcache_hl.php',
                'class_name' => 'CPHPCacheMemcacheHL',
            ],
        ],
        'readonly' => false,
    ],*/
    'cache_flags' =>
        [
            'value' =>
                [
                    'config_options' => 3600,
                    'site_domain' => 3600,
                ],
            'readonly' => false,
        ],
    'cookies' =>
        [
            'value' =>
                [
                    'secure' => false,
                    'http_only' => true,
                ],
            'readonly' => false,
        ],
    'exception_handling' =>
        [
            'value' =>
                [
                    'debug' => (bool)getenv('EXCEPTION_HANDLING_DEBUG'),
                    'handled_errors_types' => (E_ERROR
                            | E_PARSE
                            | E_CORE_ERROR
                            | E_COMPILE_ERROR
                            | E_USER_ERROR | E_RECOVERABLE_ERROR) & ~E_NOTICE,
                    'exception_errors_types' => (E_ERROR
                            | E_PARSE
                            | E_CORE_ERROR
                            | E_COMPILE_ERROR
                            | E_USER_ERROR | E_RECOVERABLE_ERROR) & ~E_NOTICE,
                    'ignore_silence' => false,
                    'assertion_throws_exception' => true,
                    'assertion_error_type' => E_USER_ERROR,
                    'log' => [
                        'class_name' => ExceptionLogger::class,
                        'settings' => [
                            'logger' => LoggerFactory::create('BX_EX_HNDLR'),
                        ],
                    ],
                ],
            'readonly' => true,
        ],
    'connections' =>
        [
            'value' =>
                [
                    'default' =>
                        [
                            'className' => MysqliConnection::class,
                            'host' => getenv('DB_HOST'),
                            'database' => getenv('DB_NAME'),
                            'login' => getenv('DB_LOGIN'),
                            'password' => getenv('DB_PASSWORD'),
                            'options' => 2,
                        ],
                ],
            'readonly' => true,
        ],
    'https_request' => [
        'value' => Env::getServerType() !== Env::DEV,
    ],
];
