<?php

namespace Sprint\Migration;


use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Sale\Internals\StatusTable;

class ResortOrderStatus20180424234221 extends SprintMigrationBase
{

    protected $description = 'Пересортировка статусов заказа';

    public function up()
    {
        $res = StatusTable::query()->setSelect(['ID','SORT'])->setFilter(['>=SORT' => 1000])->exec();
        while ($item = $res->fetch()){
            $sort = 0;
            switch($item['ID']){
                case 'Q':
                    $sort=10;
                    break;
                case 'J':
                    $sort=110;
                    break;
                case 'W':
                    $sort=30;
                    break;
                case 'E':
                    $sort=40;
                    break;
                case 'R':
                    $sort=50;
                    break;
                case 'T':
                    $sort=120;
                    break;
                case 'Y':
                    $sort=80;
                    break;
                case 'I':
                    $sort=90;
                    break;
                case 'A':
                    $sort=60;
                    break;
            }
            if($sort > 0){
                StatusTable::update($item['ID'], ['SORT'=>$sort]);
            }
        }
    }
}
