<?php

use Adv\Bitrixtools\IBlockPropertyType\YesNoPropertyType;
use Bitrix\Main\EventManager;
use FourPaws\App\EventInitializer;
use FourPaws\Helpers\MailHelper;
use WebArch\BitrixNeverInclude\BitrixNeverInclude;

require_once $_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php';

BitrixNeverInclude::registerModuleAutoload();

YesNoPropertyType::init();

/**
 * Регистрируем события
 */
(new EventInitializer())(EventManager::getInstance());
