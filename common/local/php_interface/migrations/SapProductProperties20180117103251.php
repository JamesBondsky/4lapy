<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class SapProductProperties20180117103251 extends SprintMigrationBase
{
    protected $properties = [
        'TRANSPORT_ONLY_REFRIGERATOR' => [
            'NAME'               => 'Транспортировка при низкой температуре',
            'ACTIVE'             => 'Y',
            'SORT'               => '500',
            'CODE'               => 'TRANSPORT_ONLY_REFRIGERATOR',
            'DEFAULT_VALUE'      => 0,
            'PROPERTY_TYPE'      => 'N',
            'ROW_COUNT'          => '1',
            'COL_COUNT'          => '30',
            'LIST_TYPE'          => 'L',
            'MULTIPLE'           => 'N',
            'XML_ID'             => '23',
            'FILE_TYPE'          => '',
            'MULTIPLE_CNT'       => '5',
            'TMP_ID'             => null,
            'LINK_IBLOCK_ID'     => '0',
            'WITH_DESCRIPTION'   => 'N',
            'SEARCHABLE'         => 'N',
            'FILTRABLE'          => 'Y',
            'IS_REQUIRED'        => 'N',
            'VERSION'            => '2',
            'USER_TYPE'          => 'YesNoPropertyType',
            'USER_TYPE_SETTINGS' => null,
            'HINT'               => '',
        ],
        'DC_SPECIAL_AREA_STORAGE'     => [
            'NAME'               => 'Ограничения по области доставки',
            'ACTIVE'             => 'Y',
            'SORT'               => '500',
            'CODE'               => 'DC_SPECIAL_AREA_STORAGE',
            'DEFAULT_VALUE'      => 0,
            'PROPERTY_TYPE'      => 'N',
            'ROW_COUNT'          => '1',
            'COL_COUNT'          => '30',
            'LIST_TYPE'          => 'L',
            'MULTIPLE'           => 'N',
            'XML_ID'             => '23',
            'FILE_TYPE'          => '',
            'MULTIPLE_CNT'       => '5',
            'TMP_ID'             => null,
            'LINK_IBLOCK_ID'     => '0',
            'WITH_DESCRIPTION'   => 'N',
            'SEARCHABLE'         => 'N',
            'FILTRABLE'          => 'Y',
            'IS_REQUIRED'        => 'N',
            'VERSION'            => '2',
            'USER_TYPE'          => 'YesNoPropertyType',
            'USER_TYPE_SETTINGS' => null,
            'HINT'               => '',
        ],
    ];

    public function __construct()
    {
        parent::__construct();
        $this->description = 'Добавляем недостающие SAP поля';
    }

    public function up()
    {
        $iblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS);
        foreach ($this->properties as $property) {
            $this->getHelper()->Iblock()->addPropertyIfNotExists($iblockId, $property);
        }
    }

    public function down()
    {
        $iblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::PRODUCTS);
        foreach (array_keys($this->properties) as $propertyId) {
            $this->getHelper()->Iblock()->deletePropertyIfExists($iblockId, $propertyId);
        }
    }
}
