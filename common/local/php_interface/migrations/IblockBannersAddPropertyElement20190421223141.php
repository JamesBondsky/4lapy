<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class IblockBannersAddPropertyElement20190421223141 extends SprintMigrationBase
{

    protected $description = 'Добавление свойства "Привязка к элементу" с привязкой в инфоблок "Баннеры"';

    protected $propCode = 'ELEMENT';

    public function up()
    {
        $helper = new HelperManager();
        $iblockId = IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::BANNERS);

        $helper->Iblock()->addPropertyIfNotExists($iblockId, array(
            'NAME' => 'Привязка к элементу',
            'ACTIVE' => 'Y',
            'SORT' => '100',
            'CODE' => $this->propCode,
            'DEFAULT_VALUE' => '',
            'PROPERTY_TYPE' => 'E',
            'ROW_COUNT' => '1',
            'COL_COUNT' => '30',
            'LIST_TYPE' => 'L',
            'MULTIPLE' => 'N',
            'XML_ID' => '',
            'FILE_TYPE' => '',
            'MULTIPLE_CNT' => '5',
            'TMP_ID' => NULL,
            'LINK_IBLOCK_ID' => '0',
            'WITH_DESCRIPTION' => 'N',
            'SEARCHABLE' => 'N',
            'FILTRABLE' => 'Y',
            'IS_REQUIRED' => 'N',
            'VERSION' => '2',
            'USER_TYPE' => NULL,
            'USER_TYPE_SETTINGS' => NULL,
            'HINT' => '',
        ));
    }

    public function down()
    {
        $helper = new HelperManager();
        $iblockId = IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::BANNERS);

        $helper->Iblock()->deletePropertyIfExists($iblockId, $this->propCode);
    }

}
