<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\GroupTable;
use Bitrix\Main\UserGroupTable;
use FourPaws\Enum\UserGroup;

class UserGroup20181006173141 extends SprintMigrationBase
{
    public function up()
    {
        $group = GroupTable::query()->setSelect(['ID'])
                           ->setFilter(['STRING_ID' => UserGroup::NOT_AUTH_CODE])
                           ->exec()
                           ->fetch();

        if (!$group) {
            $this->log()->error(\sprintf('User group with code %s not found', UserGroup::OPT_CODE));

            return false;
        }

        $result = UserGroupTable::add([
            'USER_ID'  => 0,
            'GROUP_ID' => $group['ID'],
        ]);
        if (!$result->isSuccess()) {
            $this->log()
                 ->error(\sprintf('Failed to update UserGroup table: %s', implode(', ', $result->getErrorMessages())));

            return false;
        }

        return true;
    }

    public function down()
    {

    }
}
