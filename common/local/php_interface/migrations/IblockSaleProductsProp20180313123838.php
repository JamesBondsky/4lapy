<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class IblockSaleProductsProp20180313123838 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = "добавление свойства товаров для инфоблока с акциями";

    public function up(){
        $helper = new HelperManager();

        $helper->Iblock()->addPropertyIfNotExists(
            IblockUtils::getIblockId(
                IblockType::PUBLICATION,
                IblockCode::SHARES
            ),
            [
                'NAME'               => 'Связанные товары',
                'ACTIVE'             => 'Y',
                'SORT'               => '200',
                'CODE'               => 'PRODUCTS',
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
                'LINK_IBLOCK_ID'     => '3',
                'WITH_DESCRIPTION'   => 'N',
                'SEARCHABLE'         => 'N',
                'FILTRABLE'          => 'N',
                'IS_REQUIRED'        => 'N',
                'VERSION'            => '2',
                'USER_TYPE'          => 'ElementXmlID',
                'USER_TYPE_SETTINGS' => null,
                'HINT'               => '',
            ]
        );

    }
}
