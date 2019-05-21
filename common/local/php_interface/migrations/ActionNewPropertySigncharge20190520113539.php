<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;

class ActionNewPropertySigncharge20190520113539 extends SprintMigrationBase
{

    protected $description = 'Добавление нового свойства для акций - псевдоакция';

    private $property = [
        'PROPERTY_TYPE'    => 'L',
        'USER_TYPE'        => '',
        'LIST_TYPE'        => 'C',
        'MULTIPLE'         => 'N',
        'NAME'             => 'Псевдоакция',
        'SORT'             => '900',
        'CODE'             => 'SIGNCHARGE',
        'FILE_TYPE'        => '',
        'IS_REQUIRED'      => 'N',
        'HINT'             => '',
        'WITH_DESCRIPTION' => '',
        'MULTIPLE_CNT'     => '5',
        'VALUES'           => [
            [
                'XML_ID' => 'Y',
                'VALUE'  => 'Да'
            ]
        ]
    ];

    public function up()
    {
        $helper = new HelperManager();

        $iblockId = $helper->Iblock()->addIblockIfNotExists([
            'IBLOCK_TYPE_ID' => 'publications',
            'CODE'           => 'shares'
        ]);


        $helper->Iblock()->addPropertyIfNotExists($iblockId, array_merge($this->property, [
            'ACTIVE'             => 'Y',
            'DEFAULT_VALUE'      => false,
            'ROW_COUNT'          => '1',
            'COL_COUNT'          => '30',
            'XML_ID'             => '',
            'TMP_ID'             => null,
            'SEARCHABLE'         => 'N',
            'FILTRABLE'          => 'N',
            'VERSION'            => '2',
            'USER_TYPE_SETTINGS' => null
        ]));

    }

    public function down()
    {
        $helper = new HelperManager();

        $iblockId = $helper->Iblock()->addIblockIfNotExists([
            'IBLOCK_TYPE_ID' => 'publications',
            'CODE'           => 'shares'
        ]);

        $helper->Iblock()->deletePropertyIfExists($iblockId, $this->property['CODE']);

    }

}
