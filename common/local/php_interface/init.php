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
    <script>
        var date = new Date();
        date.setDate(date.getDate() + 30);
        document.cookie = "{$cookieEnv[0]}={$cookieEnv[1]}; path=/; expires=" + date;
    </script>
SCR;

    Asset::getInstance()->addString($cookieScript);
}
