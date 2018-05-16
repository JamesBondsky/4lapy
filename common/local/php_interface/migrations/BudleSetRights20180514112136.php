<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Highloadblock\HighloadBlockRightsTable;
use Bitrix\Main\GroupTable;
use Bitrix\Main\TaskTable;

class BudleSetRights20180514112136 extends SprintMigrationBase
{

    protected $description = 'Установка прав для наборов';

    public function up()
    {
        $helper = new HelperManager();

        $bundleId = $helper->Hlblock()->getHlblockId('Bundle');
        $bundleItemsId = $helper->Hlblock()->getHlblockId('BundleItems');

        $groupTechId = (int)GroupTable::query()->where('STRING_ID',
            'tech-users')->setSelect(['ID'])->exec()->fetch()['ID'];
        if ($groupTechId > 0) {
            $accessCode = 'G' . $groupTechId;

            $taskId = (int)TaskTable::query()->where('MODULE_ID', 'highloadblock')->where('NAME',
                'hblock_write')->setSelect(['ID'])->exec()->fetch()['ID'];

            if ($taskId > 0) {
                HighloadBlockRightsTable::add(
                    [
                        'HL_ID'       => $bundleId,
                        'ACCESS_CODE' => $accessCode,
                        'TASK_ID'     => $taskId,
                    ]
                );

                HighloadBlockRightsTable::add(
                    [
                        'HL_ID'       => $bundleItemsId,
                        'ACCESS_CODE' => $accessCode,
                        'TASK_ID'     => $taskId,
                    ]
                );
            }
        }
    }
}
