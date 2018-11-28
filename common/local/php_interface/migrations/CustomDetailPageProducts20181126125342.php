<?php

namespace Sprint\Migration;


class CustomDetailPageProducts20181126125342 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{

    protected $description = "3 новых html поля для детальной страницы продуктов";

    private $properties = [
        0 => [
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'HTML',
            'LIST_TYPE' => 'C',
            'MULTIPLE' => 'N',
            'NAME' => 'Верстка во вкладке "Описание"',
            'SORT' => '1000',
            'CODE' => 'LAYOUT_DESCRIPTION',
            'FILE_TYPE' => '',
            'IS_REQUIRED' => 'N',
            'HINT' => '',
            'WITH_DESCRIPTION' => '',
            'MULTIPLE_CNT' => '5'
        ],
        1 => [
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'HTML',
            'LIST_TYPE' => 'C',
            'MULTIPLE' => 'N',
            'NAME' => 'Верстка во вкладке "Состав"',
            'SORT' => '1100',
            'CODE' => 'LAYOUT_COMPOSITION',
            'FILE_TYPE' => '',
            'IS_REQUIRED' => 'N',
            'HINT' => '',
            'WITH_DESCRIPTION' => '',
            'MULTIPLE_CNT' => '5'
        ],
        2 => [
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'HTML',
            'LIST_TYPE' => 'C',
            'MULTIPLE' => 'N',
            'NAME' => 'Верстка во вкладке "Рекомендации по питанию"',
            'SORT' => '1200',
            'CODE' => 'LAYOUT_RECOMMENDATIONS',
            'FILE_TYPE' => '',
            'IS_REQUIRED' => 'N',
            'HINT' => '',
            'WITH_DESCRIPTION' => '',
            'MULTIPLE_CNT' => '5'
        ],
    ];

    public function up()
    {
        $helper = new HelperManager();

        $iblockId = $helper->Iblock()->addIblockIfNotExists([
            'IBLOCK_TYPE_ID' => 'catalog',
            'CODE' => 'products'
        ]);

        foreach ($this->properties as $property) {
            $helper->Iblock()->addPropertyIfNotExists($iblockId, array_merge($property, [
                'ACTIVE' => 'Y',
                'DEFAULT_VALUE' => false,
                'ROW_COUNT' => '1',
                'COL_COUNT' => '30',
                'XML_ID' => '',
                'TMP_ID' => NULL,
                'SEARCHABLE' => 'N',
                'FILTRABLE' => 'N',
                'VERSION' => '2',
                'USER_TYPE_SETTINGS' => NULL
            ]));
        }

    }

    public function down()
    {
        $helper = new HelperManager();

        $iblockId = $helper->Iblock()->addIblockIfNotExists([
            'IBLOCK_TYPE_ID' => 'catalog',
            'CODE' => 'brands'
        ]);
        foreach ($this->properties as $property) {
            $helper->Iblock()->deletePropertyIfExists($iblockId, $property['CODE']);
        }

    }

}
