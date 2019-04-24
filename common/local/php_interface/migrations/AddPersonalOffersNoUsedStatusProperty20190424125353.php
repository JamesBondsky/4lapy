<?php

namespace Sprint\Migration;


use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class AddPersonalOffersNoUsedStatusProperty20190424125353 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{

    protected $description = 'Добавление свойства "Не гасить купоны" в инфоблок "Персональные предложения"';

    protected $propCode = 'NO_USED_STATUS';

    public function up()
    {
        $helper = new HelperManager();

        //'ID' => 'publications',personal_offers
        $iblockId = $helper->Iblock()->getIblockId(IblockCode::PERSONAL_OFFERS, IblockType::PUBLICATION);
        $helper->Iblock()->addPropertyIfNotExists($iblockId, array(
            'NAME' => 'Не гасить купоны',
            'ACTIVE' => 'Y',
            'SORT' => '300',
            'CODE' => $this->propCode,
            'DEFAULT_VALUE' => false,
            'PROPERTY_TYPE' => 'N',
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
            'USER_TYPE' => 'WebArch\\BitrixIblockPropertyType\\YesNoType',
            'USER_TYPE_SETTINGS' => NULL,
            'HINT' => '',
        ));


    }

    public function down()
    {
        $helper = new HelperManager();

        $iblockId = $helper->Iblock()->getIblockId(IblockCode::PERSONAL_OFFERS, IblockType::PUBLICATION);
        $helper->Iblock()->deletePropertyIfExists($iblockId, $this->propCode);
    }

}
