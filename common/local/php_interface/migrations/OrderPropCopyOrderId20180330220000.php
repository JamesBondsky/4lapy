<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Sale\Internals\OrderPropsTable;

class OrderPropCopyOrderId20180330220000 extends SprintMigrationBase
{
    protected const PROP_CODE = 'COPY_ORDER_ID';

    protected $description = 'Создание свойства заказа "ID скопированного заказа (источника)"';
    
    public function up()
    {
        $prop = OrderPropsTable::getList(
            [
                'filter' => [
                    'CODE' => self::PROP_CODE
                ],
            ]
        )->fetch();
        if (!$prop) {
            $addResult = OrderPropsTable::add(
                [
                    'CODE' => self::PROP_CODE,
                    'NAME' => 'ID скопированного заказа (источника)',
                    'TYPE' => 'NUMBER',
                    'REQUIRED' => 'N',
                    'USER_PROPS' => 'N',
                    'DESCRIPTION' => '',
                    'PERSON_TYPE_ID' => 1,
                    'PROPS_GROUP_ID' => 4,
                    'UTIL' => 'Y',
                    'IS_FILTERED' => 'Y',
                    'SORT' => '5100'
                ]
            );
            if (!$addResult->isSuccess()) {
                $this->log()->error('Ошибка при добавлении свойства заказа ' . self::PROP_CODE);

                return false;
            }
        } else {
            $this->log()->warning('Свойство заказа ' . self::PROP_CODE . ' уже существует');
        }

        return true;
    }
    
    public function down()
    {

    }
}
