<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;


/**
 * Class PublicationActionBasketRuleProperty20180402203814
 * @package Sprint\Migration
 */
class PublicationActionBasketRuleProperty20180402203814 extends SprintMigrationBase
{

    protected $description = 'Свойство привязки к правилам корзины';

    /**
     *
     *
     * @throws \RuntimeException
     *
     * @return void
     */
    public function up(): void
    {
        $arFields = [
            'IBLOCK_ID' => '10',
            'NAME' => 'Правило работы с корзиной',
            'ACTIVE' => 'Y',
            'SORT' => '503',
            'CODE' => 'BASKET_RULES',
            'DEFAULT_VALUE' => '',
            'PROPERTY_TYPE' => 'S',
            'ROW_COUNT' => '1',
            'COL_COUNT' => '30',
            'LIST_TYPE' => 'L',
            'MULTIPLE' => 'Y',
            'XML_ID' => '',
            'FILE_TYPE' => '',
            'MULTIPLE_CNT' => '1',
            'TMP_ID' => null,
            'LINK_IBLOCK_ID' => '0',
            'WITH_DESCRIPTION' => 'N',
            'SEARCHABLE' => 'N',
            'FILTRABLE' => 'N',
            'IS_REQUIRED' => 'N',
            'VERSION' => '2',
            'USER_TYPE' => 'LinkToBasketRules',
            'USER_TYPE_SETTINGS' =>
                [
                    'size' => 1,
                    'width' => 0,
                ],
            'HINT' => '',
        ];

        $this->getHelper()->Iblock()->addPropertyIfNotExists($this->getIblockId(), $arFields);
    }

    /**
     *
     *
     * @return int
     * @throws \RuntimeException
     */
    protected function getIblockId(): int
    {
        $id = $this->getHelper()->Iblock()->getIblockId(IblockCode::SHARES, IblockType::PUBLICATION);
        if ($id) {
            return $id;
        }
        throw new \RuntimeException('No such iblock');
    }

    /**
     *
     *
     * @return bool|void
     */
    public function down()
    {
        // нет необходимости
    }
}
