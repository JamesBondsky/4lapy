<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class IblockSharesAddPropertyRegion20190731180113 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = "Добавляет новое свойств \"Регион\" для ИБ \"Акции\"";

    public function up(){
        $helper = new HelperManager();
        $iblockId = IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::SHARES);
        $helper->Iblock()->addPropertyIfNotExists($iblockId, [
            "NAME" => "Регионы",
            "ACTIVE" => "Y",
            "SORT" => "500",
            "CODE" => "REGION",
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
            "IS_REQUIRED" => "N",
            "VERSION" => "2",
            "USER_TYPE" => null,
            "USER_TYPE_SETTINGS" => null,
            "HINT" => "",
        ]);
    }

    public function down(){
        $helper = new HelperManager();
        return true;
    }

}
