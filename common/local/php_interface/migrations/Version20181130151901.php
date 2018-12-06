<?php

namespace Sprint\Migration;


class Version20181130151901 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{

    protected $description = "Новое свойство - транслиты названия бренда";

    private $properties = [
        0 => [
            'PROPERTY_TYPE' => 'S',
            'LIST_TYPE' => 'C',
            'MULTIPLE' => 'N',
            'NAME' => 'Транслиты названия бренда',
            'SORT' => '90',
            'CODE' => 'TRANSLITS',
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
            'CODE' => 'brands'
        ]);

        foreach ($this->properties as $property) {
            $helper->Iblock()->addPropertyIfNotExists($iblockId, array_merge($property, [
                'ACTIVE' => 'Y',
                'DEFAULT_VALUE' => false,
                'ROW_COUNT' => '1',
                'COL_COUNT' => '30',
                'XML_ID' => '',
                'TMP_ID' => null,
                'SEARCHABLE' => 'N',
                'FILTRABLE' => 'N',
                'VERSION' => '2',
                'USER_TYPE_SETTINGS' => null
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
