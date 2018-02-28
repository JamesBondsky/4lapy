<?php

namespace Sprint\Migration;


class SetUserGroupCode20180214112504 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{

    protected $description = 'Установка символьного кода для групп пользователей';

    public function up()
    {
        $codeExists = [];
        $forceTranslit = [
            35 => 'HOT_LINE',
            9  => 'GUEST',
            25 => 'SALE_MANAGER',
            31 => 'SUBSCRIBER',
            2  => 'ALL_USERS',
            1  => 'ADMIN',
            36 => 'CONTENT_ORDER',
            23 => 'GUEST',
            26 => 'CONTENT_MARKUP',
        ];

        $tranclitParams = [
            'max_len'               => '50',
            'change_case'           => 'U',
            'replace_space'         => '_',
            'replace_other'         => '_',
            'delete_repeat_replace' => true,
        ];

        $sortBy = 'ID';
        $sortOrder = 'desc';

        $groupObj = new \CGroup();
        $rsGroups = $groupObj::GetList($sortBy, $sortOrder);
        while ($group = $rsGroups->Fetch()) {
            if (!$group['STRING_ID']) {
                $code = $forceTranslit[$group['ID']] ?: \Cutil::translit($group['NAME'], LANGUAGE_ID, $tranclitParams);

                if (\in_array($code, $codeExists, true)) {
                    $code .= '_' . $group['ID'];
                }

                $groupObj->Update(
                    $group['ID'],
                    [
                        'STRING_ID' => $code,
                    ]
                );

                $codeExists[] = $code;
            }
        }
    }

    public function down()
    {
        //your code ...
    }
}
