<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Sale\Internals\OrderPropsTable;

class OrderPropIsExportedToDostavista20190121165700 extends SprintMigrationBase
{
    protected const PROP_CODES = [
        'IS_EXPORTED_TO_DOSTAVISTA' => [
            'NAME' => 'Экспортировано в Достависту',
            'TYPE' => 'Y/N'
        ],
        'ORDER_ID_DOSTAVISTA' => [
            'NAME' => '№ заказа в Достависте',
            'TYPE' => 'NUMBER'
        ]
    ];

    protected $description = 'Создание свойства заказа "Экспортировано в Достависту"';

    public function up()
    {
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
                        'SORT' => '5000'
                    ]
                );
                if (!$addResult->isSuccess()) {
                    $this->log()->error('Ошибка при добавлении свойства заказа ' . $propCode);

                    return false;
                }
            } else {
                $this->log()->warning('Свойство заказа ' . $propCode . ' уже существует');
            }
        }

        return true;
    }

    public function down()
    {

    }
}
