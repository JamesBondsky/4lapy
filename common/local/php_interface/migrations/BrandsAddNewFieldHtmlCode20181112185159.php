<?php

namespace Sprint\Migration;


class BrandsAddNewFieldHtmlCode20181112185159 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{

    protected $description = 'Добавление нового свойства для брендов (детальная страница редизайн) - Баннер под каталогом';

    private $properties = [
        8 => [
            'PROPERTY_TYPE' => 'S',
            'USER_TYPE' => 'HTML',
            'LIST_TYPE' => 'C',
            'MULTIPLE' => 'N',
            'NAME' => 'Баннер под каталогом',
            'SORT' => '18',
            'CODE' => 'CATALOG_UNDER_BANNER',
            'FILE_TYPE' => '',
            'IS_REQUIRED' => 'N',
            'HINT' => '',
            'WITH_DESCRIPTION' => '',
            'MULTIPLE_CNT' => '5'
        ]
    ];

    public function up()
    {
        $helper = new HelperManager();

        $iblockId = $helper->Iblock()->addIblockIfNotExists([
            'IBLOCK_TYPE_ID' => 'catalog',
            'CODE' => 'brands'
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
