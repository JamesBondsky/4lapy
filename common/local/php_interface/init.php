<?php

use Adv\Bitrixtools\IBlockPropertyType\YesNoPropertyType;
use Bitrix\Main\Application;
use Bitrix\Main\EventManager;
use Bitrix\Main\Web\Cookie;
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
CUtil::InitJSCore(['popup', 'fx']);

/**
 * Устанавливаем cookie из ENV - для того, чтобы отфильтровать
 */
$cookieEnv = explode(':', getenv('ADDITIONAL_COOKIE'));

if ($cookieEnv) {
    $cookie = new Cookie($cookieEnv[0], $cookieEnv[1]);
    Application::getInstance()->getContext()->getResponse()->addCookie($cookie);
}
