<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\GroupTable;
use Bitrix\Sale\Internals\DiscountGroupTable;
use Bitrix\Sale\Internals\DiscountTable;
use FourPaws\Enum\UserGroup;


/**
 * Class UpdateSimpleDiscountsGroups20180928204813
 * @package Sprint\Migration
 */
class UpdateSimpleDiscountsGroups20180928204813 extends SprintMigrationBase {

    protected $description = 'Обновление групп у пресетов "рекламная цена" и "простая скидка"';

    /**
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     *
     * @return bool|void
     */
    public function up(){
        $result = DiscountTable::getList([
            'select' => ['ID'],
            'filter' => ['=XML_ID' => ['SimpleDiscountPreset', 'PromoPriceDiscountPreset']]
        ]);
        $ids = array_column($result->fetchAll(), 'ID');

        $result = GroupTable::getList([
            'filter' => ['!=STRING_ID' => [UserGroup::OPT_CODE, UserGroup::ALL_USERS_CODE]],
            'select' => ['ID'],
        ]);
        $groups = array_column($result->fetchAll(), 'ID');

        foreach ($ids as $id) {
            DiscountGroupTable::updateByDiscount($id, $groups, 'Y', true);
        }
    }

    /**
     * @return bool|void
     */
    public function down(){

    }

}
