<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace Sprint\Migration;

use Adv\Bitrixtools\Exception\IblockNotFoundException;
use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class IblockSliderAddPropBackground20180106094700 extends SprintMigrationBase
{
    protected $description = 'Добавление свойства фона в инфоблок слайдера';
    
    /** @var HelperManager $helper */
    private $helper;
    
    public function up()
    {
        $helper       = new HelperManager();
        $this->helper = $helper;
        
        try {
            $iblockId = IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::BANNERS);
            $helper->Iblock()->addPropertyIfNotExists(
                $iblockId,
                [
                    'NAME'               => 'Фон',
                    'ACTIVE'             => 'Y',
                    'SORT'               => '500',
                    'CODE'               => 'BACKGROUND',
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
                ]
            );
        } catch (IblockNotFoundException $e) {
        }
    }
    
    public function down()
    {
    }
}
