<?php

namespace FourPaws;

use RuntimeException;

class DeployScripts
{
    public static function installBitrixStubs()
    {
        $DOC_ROOT = realpath(__DIR__ . '/../../..');

        $copyActions = [
            [
                'from' => '/local/php_interface/migration_sources/bitrix-stub/.settings.php',
                'to'   => '/bitrix/.settings.php',
            ],
            [
                'from' => '/local/php_interface/migration_sources/bitrix-stub/dbconn.php',
                'to'   => '/bitrix/php_interface/dbconn.php',
            ],
            [
                'from' => '/local/php_interface/migration_sources/bitrix-stub/after_connect.php',
                'to'   => '/bitrix/php_interface/after_connect.php',
            ],
            [
                'from' => '/local/php_interface/migration_sources/bitrix-stub/after_connect_d7.php',
                'to'   => '/bitrix/php_interface/after_connect_d7.php',
            ],
        ];

        foreach ($copyActions as $action) {

            if (true !== copy($DOC_ROOT . $action['from'], $DOC_ROOT . $action['to'])) {
                throw new RuntimeException(
                    sprintf(
                        'Error installing stub %s',
                        $action['to']
                    )
                );
            }

        }

    }
}
