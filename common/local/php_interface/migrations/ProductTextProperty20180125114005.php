<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

class ProductTextProperty20180125114005 extends SprintMigrationBase
{
    protected $properties = [
        [
            'NAME'               => 'Состав',
            'ACTIVE'             => 'Y',
            'SORT'               => '90',
            'CODE'               => 'COMPOSITION',
            'DEFAULT_VALUE'      => [
                'TYPE' => 'HTML',
                'TEXT' => '',
            ],
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
            'USER_TYPE'          => 'HTML',
            'USER_TYPE_SETTINGS' =>
                [
                    'height' => 200,
                ],
            'HINT'               => '',
        ],
        [
            'NAME'               => 'Нормы использования',
            'ACTIVE'             => 'Y',
            'SORT'               => '90',
            'CODE'               => 'NORMS_OF_USE',
            'DEFAULT_VALUE'      => [
                'TYPE' => 'HTML',
                'TEXT' => '',
            ],
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
            'USER_TYPE'          => 'HTML',
            'USER_TYPE_SETTINGS' =>
                [
                    'height' => 200,
                ],
            'HINT'               => '',
        ],
    ];

    public function __construct()
    {
        parent::__construct();
        $this->description = 'Добавление текстовых свойств товара';
    }

    public function up()
    {
        $iblockId = $this->getIblockId();
        foreach ($this->properties as $property) {
            $this->getHelper()->Iblock()->addPropertyIfNotExists($iblockId, $property);
        }
    }

    public function down()
    {
        $iblockId = $this->getIblockId();
        foreach ($this->properties as $property) {
            $this->getHelper()->Iblock()->deletePropertyIfExists($iblockId, $property['CODE']);
        }
    }

    protected function getIblockId()
    {
        $id = $this->getHelper()->Iblock()->getIblockId(IblockCode::PRODUCTS, IblockType::CATALOG);
        if ($id) {
            return $id;
        }
        throw new \RuntimeException('No such iblock');
    }
}
