<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use CUser;

class UpdateUsersPhonesFromOldSite20190604152000 extends SprintMigrationBase
{
    const USER_PROP_CODE = 'UF_FROM_OLD_SITE';
    protected $description = 'Обновление телефона из логинов мигрированных пользователей';

    public function up()
    {
        $dbUsers = CUser::getList(
            $by = 'id',
            $order = 'asc',
            [
                static::USER_PROP_CODE => true,
                'PERSONAL_PHONE'       => '~_%'
            ]
        );

        $cUser = new CUser;
        while ($user = $dbUsers->Fetch()) {
            if (($user['PERSONAL_PHONE'] == '' || $user['PERSONAL_PHONE'] == null) && intval($user['LOGIN']) == $user['LOGIN']) {
                $cUser->Update($user['ID'], ['PERSONAL_PHONE' => $user['LOGIN']]);
            }
        }
    }

    public function down()
    {

    }
}
