<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Iblock\PropertyIndex\Manager;
use Bitrix\Main\Loader;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class FacetDelete20180515163325 extends SprintMigrationBase {

    protected $description = 'удаление и блокировка фасеты';

    public function up(){
        Loader::includeModule('iblock');

        $catalogIblockId = IblockUtils::getIblockId(IblockType::CATALOG,IblockCode::PRODUCTS);
        Manager::deleteIndex($catalogIblockId);
        Manager::markAsInvalid($catalogIblockId);

        $offerIblockId = IblockUtils::getIblockId(IblockType::CATALOG,IblockCode::OFFERS);
        Manager::deleteIndex($offerIblockId);
        Manager::markAsInvalid($offerIblockId);
    }
}
