<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class MigrationPropLabel20180322171039 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = 'Миграция свойства Шильдики в инфоблок акций';

    public function up(){
        $helper = new HelperManager();

        $iblockHelper = $helper->Iblock();
        $catalogIblockId = IblockUtils::getIblockId(IblockType::CATALOG,IblockCode::PRODUCTS);
        $publicationIblockId = IblockUtils::getIblockId(IblockType::PUBLICATION,IblockCode::SHARES);

        $iblockHelper->addPropertyIfNotExists($publicationIblockId, [
            'NAME'               => 'Шильдики',
            'ACTIVE'             => 'Y',
            'SORT'               => '20',
            'CODE'               => 'LABEL',
            'DEFAULT_VALUE'      => '',
            'PROPERTY_TYPE'      => 'S',
            'ROW_COUNT'          => '1',
            'COL_COUNT'          => '30',
            'LIST_TYPE'          => 'L',
            'MULTIPLE'           => 'Y',
            'XML_ID'             => '',
            'FILE_TYPE'          => '',
            'MULTIPLE_CNT'       => '5',
            'TMP_ID'             => null,
            'LINK_IBLOCK_ID'     => '0',
            'WITH_DESCRIPTION'   => 'N',
            'SEARCHABLE'         => 'N',
            'FILTRABLE'          => 'N',
            'IS_REQUIRED'        => 'N',
            'VERSION'            => '1',
            'USER_TYPE'          => 'directory',
            'USER_TYPE_SETTINGS' => [
                'size'       => 1,
                'width'      => 0,
                'group'      => 'N',
                'multiple'   => 'N',
                'TABLE_NAME' => 'b_hlbd_label',
            ],
            'HINT'               => '',
        ]);
        $this->log()->info('свойство в акциях создано создано');
        $iblockHelper->deletePropertyIfExists($catalogIblockId,'LABEL');
        $this->log()->info('свойство в товарах удалено');
    }
}
