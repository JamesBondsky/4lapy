<?php

namespace Sprint\Migration;


use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class AddBannerShareProperty20200123134931 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    protected $code = 'shares';
    protected $description = 'Добавляем свойства для баннеров для акций';
    
    public function up()
    {
        $helper = new HelperManager();
        $iblockId = $helper->Iblock()->getIblockId($this->code);
        
        $helper->Iblock()->addPropertyIfNotExists($iblockId, [
            'NAME'               => 'Баннер для десктопа (1280*300)',
            'ACTIVE'             => 'Y',
            'SORT'               => '500',
            'CODE'               => 'BANNER_DESKTOP',
            'DEFAULT_VALUE'      => '',
            'PROPERTY_TYPE'      => 'F',
            'ROW_COUNT'          => '1',
            'COL_COUNT'          => '30',
            'LIST_TYPE'          => 'L',
            'MULTIPLE'           => 'N',
            'XML_ID'             => '',
            'FILE_TYPE'          => 'jpg, gif, bmp, png, jpeg',
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
            'NAME'               => 'Баннер для планшета (940*250)',
            'ACTIVE'             => 'Y',
            'SORT'               => '500',
            'CODE'               => 'BANNER_TABLET',
            'DEFAULT_VALUE'      => '',
            'PROPERTY_TYPE'      => 'F',
            'ROW_COUNT'          => '1',
            'COL_COUNT'          => '30',
            'LIST_TYPE'          => 'L',
            'MULTIPLE'           => 'N',
            'XML_ID'             => '',
            'FILE_TYPE'          => 'jpg, gif, bmp, png, jpeg',
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
            'NAME'               => 'Баннер для мобильного (767*160)',
            'ACTIVE'             => 'Y',
            'SORT'               => '500',
            'CODE'               => 'BANNER_MOBILE',
            'DEFAULT_VALUE'      => '',
            'PROPERTY_TYPE'      => 'F',
            'ROW_COUNT'          => '1',
            'COL_COUNT'          => '30',
            'LIST_TYPE'          => 'L',
            'MULTIPLE'           => 'N',
            'XML_ID'             => '',
            'FILE_TYPE'          => 'jpg, gif, bmp, png, jpeg',
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
    }
    
    public function down()
    {
        $helper = new HelperManager();
        $iblockId = $helper->Iblock()->getIblockId($this->code);
        
        $helper->Iblock()->deletePropertyIfExists($iblockId, 'BANNER_DESKTOP');
        $helper->Iblock()->deletePropertyIfExists($iblockId, 'BANNER_TABLET');
        $helper->Iblock()->deletePropertyIfExists($iblockId, 'BANNER_MOBILE');
    }
}
