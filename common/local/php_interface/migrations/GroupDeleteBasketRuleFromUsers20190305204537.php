<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Exception\MigrationFailureException;
use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\UserTable;
use Bitrix\Main\GroupTable;
use Bitrix\Main\UserGroupTable;
use Bitrix\Sale\Internals\DiscountTable;
use Bitrix\Sale\Internals\DiscountGroupTable;
use FourPaws\Enum\UserGroup;
use CGroup;

class GroupDeleteBasketRuleFromUsers20190305204537 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = "Удаляет группу \"Правила работы с корзиной\" для магазинов";

    public function up()
    {
        $userIdsNot = [];
        $rsUser = UserGroupTable::getList([
            'filter' => [
                'GROUP_ID' => [28,29],
                'USER.ACTIVE' => 'Y',
            ],
            'select' => [
                'USER_ID'
            ],
            'group' => [
                'USER_ID'
            ],
            'runtime' => [
                'USER' => [
                    'data_type' => 'Bitrix\Main\UserTable',
                    'reference' => ['=this.USER_ID' => 'ref.ID'],
                    'join_type' => 'inner'
                ],
            ]
        ]);
        while ($arUser = $rsUser->fetch()) {
            $groups = \CUser::GetUserGroup($arUser['USER_ID']);

            if(count($groups) != 3){
                continue;
            }

            unset($groups[array_search(42, $groups)]);
            \CUser::SetUserGroup($arUser['USER_ID'], $groups);

            print_r($arUser['USER_ID']." обновлён ");

            break;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function down()
    {
        return true;
    }

}
