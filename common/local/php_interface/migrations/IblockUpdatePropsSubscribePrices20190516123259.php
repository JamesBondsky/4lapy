<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class IblockUpdatePropsSubscribePrices20190516123259 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = "Обновление свойств у инфоблока \"Скидка по подписке\"";

    protected $properties = [
        0 => [
            "NAME" => "Код региона",
            "ACTIVE" => "Y",
            "SORT" => "500",
            "CODE" => "REGION_CODE",
            "DEFAULT_VALUE" => "",
            "PROPERTY_TYPE" => "S",
            "ROW_COUNT" => "1",
            "COL_COUNT" => "30",
            "LIST_TYPE" => "L",
            "MULTIPLE" => "Y",
            "XML_ID" => "",
            "FILE_TYPE" => "",
            "MULTIPLE_CNT" => "5",
            "TMP_ID" => null,
            "LINK_IBLOCK_ID" => "0",
            "WITH_DESCRIPTION" => "N",
            "SEARCHABLE" => "N",
            "FILTRABLE" => "N",
            "IS_REQUIRED" => "Y",
            "VERSION" => "1",
            "USER_TYPE" => null,
            "USER_TYPE_SETTINGS" => null,
            "HINT" => "Код региона можно узнать в типах цен, все коды начинаются с IR. \"ALL\" - для всех регионов",
        ],
        [
            "NAME" => "Привязка к бренду",
            "ACTIVE" => "Y",
            "SORT" => "100",
            "CODE" => "BRAND",
            "DEFAULT_VALUE" => "",
            "PROPERTY_TYPE" => "E",
            "ROW_COUNT" => "1",
            "COL_COUNT" => "30",
            "LIST_TYPE" => "L",
            "MULTIPLE" => "Y",
            "XML_ID" => "",
            "FILE_TYPE" => "",
            "MULTIPLE_CNT" => "5",
            "TMP_ID" => null,
            "LINK_IBLOCK_ID" => "4",
            "WITH_DESCRIPTION" => "N",
            "SEARCHABLE" => "N",
            "FILTRABLE" => "N",
            "IS_REQUIRED" => "Y",
            "VERSION" => "1",
            "USER_TYPE" => null,
            "USER_TYPE_SETTINGS" => null,
            "HINT" => "",
        ],
    ];

    public function up(){
        $helper = new HelperManager();
        $iblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::SUBSCRIBE_PRICES);
        foreach ($this->properties as $property) {
            $helper->Iblock()->updatePropertyIfExists($iblockId, $property['CODE'], $property);
        }
        return true;
    }

    public function down(){
        return true;
    }
}
