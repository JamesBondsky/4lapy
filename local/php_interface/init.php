<?php

use Adv\Bitrixtools\IBlockPropertyType\YesNoPropertyType;
use WebArch\BitrixNeverInclude\BitrixNeverInclude;

require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/vendor/autoload.php';

BitrixNeverInclude::registerModuleAutoload();

YesNoPropertyType::init();
