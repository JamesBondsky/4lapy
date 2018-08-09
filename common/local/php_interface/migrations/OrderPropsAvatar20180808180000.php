<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Sale\Internals\OrderPropsTable;

class OrderPropsAvatar20180808180000 extends SprintMigrationBase
{
    protected const PROP_CODE_1 = 'OPERATOR_EMAIL';
    protected const PROP_CODE_2 = 'OPERATOR_SHOP';

    protected $description = 'Создание свойств заказа "E-mail оператора" и "Магазин оператора"';
    
    public function up()
    {
        $prop = OrderPropsTable::getList(
            [
                'filter' => [
                    'CODE' => self::PROP_CODE_1
                ],
            ]
        )->fetch();
        if (!$prop) {
            $addResult = OrderPropsTable::add(
                [
                    'CODE' => self::PROP_CODE_1,
                    'NAME' => 'E-mail оператора',
                    'TYPE' => 'STRING',
                    'REQUIRED' => 'N',
                    'USER_PROPS' => 'N',
                    'DESCRIPTION' => '',
                    'PERSON_TYPE_ID' => 1,
                    'PROPS_GROUP_ID' => 4,
                    'UTIL' => 'Y',
                    'IS_FILTERED' => 'Y',
                    'SORT' => '7000'
                ]
            );
            if (!$addResult->isSuccess()) {
                $this->log()->error('Ошибка при добавлении свойства заказа ' . static::PROP_CODE_1);

                return false;
            }
        } else {
            $this->log()->warning('Свойство заказа ' . static::PROP_CODE_1 . ' уже существует');
        }

        $prop = OrderPropsTable::getList(
            [
                'filter' => [
                    'CODE' => self::PROP_CODE_2
                ],
            ]
        )->fetch();
        if (!$prop) {
            $addResult = OrderPropsTable::add(
                [
                    'CODE' => self::PROP_CODE_2,
                    'NAME' => 'Магазин оператора',
                    'TYPE' => 'STRING',
                    'REQUIRED' => 'N',
                    'USER_PROPS' => 'N',
                    'DESCRIPTION' => '',
                    'PERSON_TYPE_ID' => 1,
                    'PROPS_GROUP_ID' => 4,
                    'UTIL' => 'Y',
                    'IS_FILTERED' => 'Y',
                    'SORT' => '7100'
                ]
            );
            if (!$addResult->isSuccess()) {
                $this->log()->error('Ошибка при добавлении свойства заказа ' . static::PROP_CODE_2);

                return false;
            }
        } else {
            $this->log()->warning('Свойство заказа ' . static::PROP_CODE_2 . ' уже существует');
        }

        return true;
    }
    
    public function down()
    {

    }
}
