<?php

use Adv\Bitrixtools\IBlockPropertyType\YesNoPropertyType;
use Bitrix\Main\EventManager;
use Bitrix\Main\Page\Asset;
use FourPaws\App\EventInitializer;
use WebArch\BitrixNeverInclude\BitrixNeverInclude;

require_once $_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php';

BitrixNeverInclude::registerModuleAutoload();

YesNoPropertyType::init();

/**
 * Регистрируем события
 */
(new EventInitializer())(EventManager::getInstance());

/**
 * Инициализируем скрипты ядра, использующиеся для стандартных js-методов
 */
CUtil::InitJSCore(['core', 'popup', 'fx', 'ui']);

/**
 * Устанавливаем cookie из ENV - для того, чтобы отфильтровать
 */
$cookieEnv = explode(':', getenv('ADDITIONAL_COOKIE'));

if ($cookieEnv) {
    $cookieScript = <<<SCR
    <script data-skip-moving="true">
        window.configDefence = {
            cName: '{$cookieEnv[0]}',
            cValue: '{$cookieEnv[1]}'
        }
    </script>
SCR;

    Asset::getInstance()->addString($cookieScript);
}
