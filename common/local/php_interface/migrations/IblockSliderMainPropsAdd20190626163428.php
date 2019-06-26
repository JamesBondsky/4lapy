<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class IblockSliderMainPropsAdd20190626163428 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = "Добавляет свойства \"Текст кнопки\" и \"Цвет кнопки\" для изменения кнопки \"Подробнее\" в слайдерах на главной";

    public function up(){
        $helper = new HelperManager();

        $iblockId = IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::SLIDER_CONTROLLED);

        $props = [
            [
                "IBLOCK_ID" => $iblockId,
                "NAME" => 'Текст кнопки "Подробнее"',
                "ACTIVE" => "Y",
                "SORT" => "500",
                "CODE" => "BUTTON_TEXT",
                "DEFAULT_VALUE" => "",
                "PROPERTY_TYPE" => "S",
                "ROW_COUNT" => "1",
                "COL_COUNT" => "30",
                "LIST_TYPE" => "L",
                "MULTIPLE" => "N",
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
            ],
            [
                "IBLOCK_ID" => $iblockId,
                "NAME" => 'Цвет кнопки "Подробнее"',
                "ACTIVE" => "Y",
                "SORT" => "500",
                "CODE" => "BUTTON_COLOR",
                "DEFAULT_VALUE" => "",
                "PROPERTY_TYPE" => "S",
                "ROW_COUNT" => "1",
                "COL_COUNT" => "7",
                "LIST_TYPE" => "L",
                "MULTIPLE" => "N",
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
                "HINT" => "Код цвета в формате RGB, например: #00ffaa",
            ]
        ];

        foreach($props as $prop){
            $helper->Iblock()->addPropertyIfNotExists($iblockId, $prop);
        }
    }

    public function down(){
        $helper = new HelperManager();
        return true;
    }

}
