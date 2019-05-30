<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use FourPaws\Enum\IblockCode;

class IBlockPersonalOffersAddNewProp20190530134500 extends SprintMigrationBase
{
    protected $description = 'Создает новое свойство для инфоблока "Персональные предложения"';
    protected $iblockType = 'publications';
    protected $propAddCode = 'DISCOUNT_CURRENCY';
    protected $propUpdateCode = 'DISCOUNT';
    protected $propActiveTo = 'ACTIVE_TO';

    public function up()
    {
        $helper = new HelperManager();
        $iblockId = $helper->Iblock()->getIblockId(IblockCode::PERSONAL_OFFERS, $this->iblockType);

        $helper->Iblock()->addPropertyIfNotExists($iblockId, [
            'NAME'               => 'Размер скидки в рублях',
            'ACTIVE'             => 'Y',
            'SORT'               => '150',
            'CODE'               => $this->propAddCode,
            'DEFAULT_VALUE'      => '',
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
            'FILTRABLE'          => 'Y',
            'IS_REQUIRED'        => 'Y',
            'VERSION'            => '2',
            'USER_TYPE'          => null,
            'USER_TYPE_SETTINGS' => null,
            'HINT'               => 'в рублях (число)',
            'IS_REQUIRED'        => 'N'
        ]);

        $helper->Iblock()->addPropertyIfNotExists($iblockId, [
            'NAME'               => 'Дата действия персонального предложения',
            'ACTIVE'             => 'Y',
            'SORT'               => '170',
            'CODE'               => $this->propActiveTo,
            'DEFAULT_VALUE'      => '',
            'PROPERTY_TYPE'      => 'S',
            'USER_TYPE'          => 'Date',
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
            'FILTRABLE'          => 'Y',
            'IS_REQUIRED'        => 'Y',
            'VERSION'            => '2',
            'USER_TYPE_SETTINGS' => null,
            'HINT'               => '',
            'IS_REQUIRED'        => 'N'
        ]);

        $helper->Iblock()->updatePropertyIfExists($iblockId, $this->propUpdateCode, [
            'NAME'               => 'Размер скидки в процентах',
            'ACTIVE'             => 'Y',
            'SORT'               => '100',
            'CODE'               => $this->propUpdateCode,
            'DEFAULT_VALUE'      => '',
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
            'FILTRABLE'          => 'Y',
            'IS_REQUIRED'        => 'Y',
            'VERSION'            => '2',
            'USER_TYPE'          => null,
            'USER_TYPE_SETTINGS' => null,
            'HINT'               => 'в процентах (число)',
            'IS_REQUIRED'        => 'N'
        ]);
    }

    public function down()
    {
    }
}
