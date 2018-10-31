<?php

namespace Components\Refactoring;


use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Entity\Base;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockProperty;
use FourPaws\Enum\IblockType;

class BrandsListComponent extends \CBitrixComponent {

    private $brandsIblockId;

    public function executeComponent()
    {
        if ($this->startResultCache(60)) {

            $this->brandsIblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::BRANDS);

            $this->fetchBrands();
            $this->includeComponentTemplate();
        }
    }

    private function formatFile($fieldName, $entity) {
        $formatted = [];

        if ($entity[$fieldName.'FILE_NAME']) {
            $formatted = [
                'ID' => $entity[$fieldName.'ID'],
                'HEIGHT' => $entity[$fieldName.'HEIGHT'],
                'WIDTH' => $entity[$fieldName.'WIDTH'],
                'FILE_SIZE' => $entity[$fieldName.'FILE_SIZE'],
                'CONTENT_TYPE' => $entity[$fieldName.'CONTENT_TYPE'],
                'SUBDIR' => $entity[$fieldName.'SUBDIR'],
                'FILE_NAME' => $entity[$fieldName.'FILE_NAME'],
                'ORIGINAL_NAME' => $entity[$fieldName.'ORIGINAL_NAME'],
                'SRC' => '/upload/'.$entity[$fieldName.'SUBDIR'].'/'.$entity[$fieldName.'FILE_NAME'],
            ];
        }

        return $formatted;
    }

    private function fetchBrands() {

        $popularProperty = PropertyTable::getList([
            'filter' => [
                'IBLOCK_ID' => $this->brandsIblockId,
                'CODE' => IblockProperty::BRANDS_POPULAR,
            ],
            'select' => ['ID'],
        ])->fetch();

        $propertiesSingle = Base::compileEntity(
            'PROPERTIES_SINGLE', [
                'IBLOCK_ELEMENT_ID' => ['data_type' => 'integer'],
                'PROPERTY_'.$popularProperty['ID'] => ['data_type' => 'integer'],
            ], ['table_name' => 'b_iblock_element_prop_s'.$this->brandsIblockId]
        );

        $rsBrands = ElementTable::getList([
            'order' => [
                'SORT' => 'ASC',
                'NAME' => 'ASC',
            ],
            'filter' => [
                'IBLOCK_ID' => $this->brandsIblockId,
                'ACTIVE' => 'Y',
            ],
            'select' => [
                'ID',
                'IBLOCK_ID',
                'SORT',
                'NAME',
                'CODE',
                'DETAIL_TEXT',
                'PREVIEW_TEXT',
                'DETAIL_PICTURE_' => 'DETAIL_PICTURE_ENTITY',
                'PREVIEW_PICTURE_' => 'PREVIEW_PICTURE_ENTITY',
                'PROPERTY_POPULAR_VALUE' => 'PROPERTIES_SINGLE.PROPERTY_'.$popularProperty['ID'],
            ],
            'runtime' => [
                'DETAIL_PICTURE_ENTITY' => [
                    'data_type' => 'Bitrix\Main\FileTable',
                    'reference' => ['=this.DETAIL_PICTURE' => 'ref.ID'],
                    'join_type' => 'left'
                ],
                'PREVIEW_PICTURE_ENTITY' => [
                    'data_type' => 'Bitrix\Main\FileTable',
                    'reference' => ['=this.PREVIEW_PICTURE' => 'ref.ID'],
                    'join_type' => 'left'
                ],
                'PROPERTIES_SINGLE' => [
                    'data_type' => $propertiesSingle->getDataClass(),
                    'reference' => ['=this.ID' => 'ref.IBLOCK_ELEMENT_ID'],
                    'join_type' => 'inner'
                ],
            ],
        ]);

        $arBrands = [];
        while ($arBrand = $rsBrands->fetch()) {
            $arBrands[] = [
                'ID' => $arBrand['ID'],
                'IBLOCK_ID' => $arBrand['IBLOCK_ID'],
                'NAME' => $arBrand['NAME'],
                'SORT' => $arBrand['SORT'],
                'CODE' => $arBrand['CODE'],
                'DETAIL_TEXT' => $arBrand['DETAIL_TEXT'],
                'PREVIEW_TEXT' => $arBrand['PREVIEW_TEXT'],

                'DETAIL_PAGE_URL' => '/brand/'.$arBrand['CODE'].'/',
                'LIST_PAGE_URL' => '/brand/',

                'DETAIL_PICTURE' => $this->formatFile('DETAIL_PICTURE_', $arBrand),
                'PREVIEW_PICTURE' => $this->formatFile('PREVIEW_PICTURE_', $arBrand),

                'PROPERTY_POPULAR_VALUE' => (string)(int)$arBrand['PROPERTY_POPULAR_VALUE'],
            ];
        }

        $this->arResult['ITEMS'] = $arBrands;
    }


}