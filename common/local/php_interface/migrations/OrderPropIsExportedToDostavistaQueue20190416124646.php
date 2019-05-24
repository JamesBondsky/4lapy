<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Sale\Internals\OrderPropsTable;

class OrderPropIsExportedToDostavistaQueue20190416124646 extends SprintMigrationBase
{
    protected const PROP_CODES = [
        'IS_EXPORTED_TO_DOSTAVISTA_QUEUE' => [
            'NAME' => 'Помещен в очередь Достависты',
            'TYPE' => 'Y/N'
        ],
    ];

    protected $description = 'Создание свойства заказа "Экспортировано в Достависту"';

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
                        'SORT' => 8400 + $i,
                        'ENTITY_REGISTRY_TYPE' => 'ORDER',
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

    }
}
