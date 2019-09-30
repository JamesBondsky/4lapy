<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class IBlockCatalogSectionSlider20190926110633 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = "Модернизирует инфоблок Одежда: категории товаров";

    public function up(){
        $helper = new HelperManager();

        $iblockId = IblockUtils::getIblockId(IblockType::GRANDIN, IblockCode::CATALOG_SLIDER_PRODUCTS);
        $fields = [
            "NAME" => "Слайдер категорий товаров",
        ];

        $obIblock = new \CIBlock();
        $result = $obIblock->Update($iblockId, $fields);
        $this->log()->error('Обновление инфоблока: '.($result ? 'Да' : 'Нет'));

        $obSection = new \CIBlockSection();
        $arSection = [
            "IBLOCK_ID" => $iblockId,
            "IBLOCK_SECTION_ID" => null,
            "ACTIVE" => "Y",
            "GLOBAL_ACTIVE" => "Y",
            "SORT" => "500",
            "NAME" => "LP Одежда: фильтр",
            "PICTURE" => null,
            "DESCRIPTION" => "",
            "DESCRIPTION_TYPE" => "text",
            "CODE" => "fashion",
            "XML_ID" => "",
        ];
        $iblockSectionId = $obSection->Add($arSection);
        $this->log()->error('ID Секции: '.$iblockSectionId);

        if($iblockSectionId > 0){
            $dbres = \CIBlockElement::GetList([],['IBLOCK_ID' => $iblockId, 'IBLOCK_SECTION_ID' => false]);
            while($item = $dbres->Fetch()){
                $obElement = new \CIBlockElement();
                $result = $obElement->Update($item['ID'], ['IBLOCK_SECTION_ID' => $iblockSectionId]);
                $this->log()->error('Element update: '.($result ? 'Да' : 'Нет'));
            }
        }

        return true;

    }

    public function down(){
        $helper = new HelperManager();
        return true;
    }

}
