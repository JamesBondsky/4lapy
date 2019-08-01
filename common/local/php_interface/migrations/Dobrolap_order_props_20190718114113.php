<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Sale\Internals\OrderPropsTable;

class Dobrolap_order_props_20190718114113 extends SprintMigrationBase
{
    protected $description = 'Создание свойств заказа "ID подарочного купона купона Добролап" и "Штрих-код питомника Добролап"';

    protected const PROP_CODES = [
        'DOBROLAP_COUPON_ID' => [
            'NAME' => 'ID подарочного купона купона Добролап',
            'TYPE' => 'NUMBER'
        ],
        'DOBROLAP_SHELTER' => [
            'NAME' => 'Штрих-код питомника Добролап',
            'TYPE' => 'STRING'
        ]
    ];

    public function up()
    {
        $i = 0;
        foreach (self::PROP_CODES as $propCode => $propValues) {
            $prop = OrderPropsTable::getList(
                [
                    'filter' => [
                        'CODE' => $propCode
                    ],
                ]
            )->fetch();
            if (!$prop) {
                $addResult = OrderPropsTable::add(
                    [
                        'CODE' => $propCode,
                        'NAME' => $propValues['NAME'],
                        'TYPE' => $propValues['TYPE'],
                        'REQUIRED' => 'N',
                        'USER_PROPS' => 'N',
                        'DESCRIPTION' => '',
                        'PERSON_TYPE_ID' => 1,
                        'PROPS_GROUP_ID' => 4,
                        'UTIL' => 'Y',
                        'IS_FILTERED' => 'Y',
                        'SORT' => 10000 + $i,
                        'ENTITY_REGISTRY_TYPE' => 'ORDER'
                    ]
                );
                if (!$addResult->isSuccess()) {
                    $this->log()->error('Ошибка при добавлении свойства заказа ' . $propCode);

                    return false;
                }
            } else {
                $this->log()->warning('Свойство заказа ' . $propCode . ' уже существует');
            }
            $i+=100;
        }

        return true;
    }

    public function down()
    {

        return true;
    }
}
