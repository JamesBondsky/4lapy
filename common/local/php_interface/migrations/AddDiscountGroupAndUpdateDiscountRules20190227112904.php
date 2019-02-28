<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Exception\MigrationFailureException;
use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\UserTable;
use Bitrix\Main\GroupTable;
use Bitrix\Main\UserGroupTable;
use Bitrix\Sale\Internals\DiscountTable;
use Bitrix\Sale\Internals\DiscountGroupTable;
use CGroup;

class AddDiscountGroupAndUpdateDiscountRules20190227112904 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = "Добавляет новую группу для правил работы с корзиной и помещает туда не \"Избранных\" пользователей";

    public function up()
    {
        $result = GroupTable::getList([
            'filter' => ['STRING_ID' => 'BASKET_RULES'],
        ]);

        if (!$group = $result->fetch()) {
            $rsGroup = GroupTable::getList([
                'filter' => ['STRING_ID' => 'VIP'],
            ]);
            if(!$groupVip = $rsGroup->fetch()){
                throw new MigrationFailureException('Группа Избранные не найдена');
            }

            $userIdsNot = [];
            $rsUser = UserGroupTable::getList([
                'filter'  => [
                    'GROUP_ID' => 32,
                    'USER.ACTIVE' => 'Y',
                ],
                'select'  => [
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
            while($arUser = $rsUser->fetch()){
                $userIdsNot[] = $arUser['USER_ID'];
            }

            $userIds = [];
            $rsUser = UserTable::getList([
                'filter'  => [
                    '!=ID' => $userIdsNot,
                    'ACTIVE' => 'Y',
                ],
                'select'  => [
                    'ID'
                ],
            ]);
            while($arUser = $rsUser->fetch()){
                $userIds[] = $arUser['ID'];
            }

            $cGroup = new CGroup();
            $groupId = $cGroup->Add([
                'ACTIVE' => 'Y',
                'C_SORT' => 100,
                'NAME' => 'Применяются правила работы с корзиной',
                'DESCRIPTION' => 'К пользователям этой группы применяются правила работы с корзиной',
                'STRING_ID' => 'BASKET_RULES',
                'USER_ID' => $userIds,
            ]);
            if ($cGroup->LAST_ERROR) {
                throw new MigrationFailureException($cGroup->LAST_ERROR);
            }

            $optionStr = \COption::GetOptionString('main', 'new_user_registration_def_group');
            $optionStr .= ",".$groupId;
            \COption::SetOptionString('main', 'new_user_registration_def_group', $optionStr);
        }
        else{
            $groupId = $group['ID'];
        }

        return true;
    }

    /**
     * @return bool
     */
    public function down()
    {
        $result = GroupTable::getList([
            'filter' => ['STRING_ID' => 'BASKET_RULES'],
        ]);
        if ($group = $result->fetch()) {
            $cGroup = new CGroup();
            $cGroup->Delete($group['ID']);
            if ($cGroup->LAST_ERROR) {
                throw new MigrationFailureException($cGroup->LAST_ERROR);
            }

            $optionStr = \COption::GetOptionString('main', 'new_user_registration_def_group');
            $optionStr = str_replace(",".$group['ID'], "", $optionStr);
            \COption::SetOptionString('main', 'new_user_registration_def_group', $optionStr);
        }

        return true;
    }

}
