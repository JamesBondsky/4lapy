<?php

namespace Sprint\Migration;


class AddIConFieldForChampions20200124183916 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    protected $description = "Новые поля для Победителей";
    
    public function up()
    {
        $helper   = new HelperManager();
        $iblockId = $helper->Iblock()->getIblockId('action_winners');
        
        $helper->Iblock()->addPropertyIfNotExists($iblockId, [
            'NAME'               => 'Иконка победителя',
            'ACTIVE'             => 'Y',
            'SORT'               => '500',
            'CODE'               => 'ICON',
            'DEFAULT_VALUE'      => '',
            'PROPERTY_TYPE'      => 'S',
            'ROW_COUNT'          => '1',
            'COL_COUNT'          => '30',
            'LIST_TYPE'          => 'L',
            'MULTIPLE'           => 'N',
            'XML_ID'             => '',
            'FILE_TYPE'          => '',
            'MULTIPLE_CNT'       => '5',
            'TMP_ID'             => null,
            'LINK_IBLOCK_ID'     => '0',
            'WITH_DESCRIPTION'   => 'N',
            'SEARCHABLE'         => 'N',
            'FILTRABLE'          => 'N',
            'IS_REQUIRED'        => 'N',
            'VERSION'            => '2',
            'USER_TYPE'          => null,
            'USER_TYPE_SETTINGS' => null,
            'HINT'               => '',
        ]);
        
        $helper->Iblock()->addPropertyIfNotExists($iblockId, [
            'NAME'               => 'Показывать в слайдере',
            'ACTIVE'             => 'Y',
            'SORT'               => '500',
            'CODE'               => 'SLIDER',
            'DEFAULT_VALUE'      => false,
            'PROPERTY_TYPE'      => 'N',
            'ROW_COUNT'          => '1',
            'COL_COUNT'          => '30',
            'LIST_TYPE'          => 'L',
            'MULTIPLE'           => 'N',
            'XML_ID'             => '',
            'FILE_TYPE'          => '',
            'MULTIPLE_CNT'       => '5',
            'TMP_ID'             => null,
            'LINK_IBLOCK_ID'     => '0',
            'WITH_DESCRIPTION'   => 'N',
            'SEARCHABLE'         => 'N',
            'FILTRABLE'          => 'N',
            'IS_REQUIRED'        => 'N',
            'VERSION'            => '2',
            'USER_TYPE'          => 'WebArch\\BitrixIblockPropertyType\\YesNoType',
            'USER_TYPE_SETTINGS' => null,
            'HINT'               => '',
        ]);
    }
    
    public function down()
    {
        $helper   = new HelperManager();
        $iblockId = $helper->Iblock()->getIblockId('action_winners');
        
        $helper->Iblock()->deletePropertyIfExists($iblockId, 'ICON');
    }
}
