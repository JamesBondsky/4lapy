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

        $this->log()->info('создание свойства в акциях');
        $iblockHelper->addPropertyIfNotExists($publicationIblockId, [
            'NAME'               => 'Шильдик',
            'ACTIVE'             => 'Y',
            'SORT'               => '20',
            'CODE'               => 'LABEL',
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
            'VERSION'            => '1',
            'USER_TYPE'          => null,
            'USER_TYPE_SETTINGS' => null,
            'HINT'               => '',
        ]);
        $this->log()->info('свойство в акциях создано');

        $this->log()->info('удаление свойства в товарах');
        $iblockHelper->deletePropertyIfExists($catalogIblockId,'LABEL');
        $this->log()->info('свойство в товарах удалено');

        $this->log()->info('удаление hl блока Label');
        $helper->Hlblock()->deleteHlblockIfExists('Label');
        $this->log()->info('hl блок Label удален');
    }
}
