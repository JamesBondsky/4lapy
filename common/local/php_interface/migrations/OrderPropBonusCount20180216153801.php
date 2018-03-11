<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Sale\Internals\OrderPropsTable;

class OrderPropBonusCount20180216153801 extends SprintMigrationBase
{
    protected $description = 'Создание свойства заказа "Начислено бонусов"';

    const PROP_CODE = 'BONUS_COUNT';

    public function up()
    {
        if ($prop = OrderPropsTable::getList(
            [
                'filter' => ['CODE' => self::PROP_CODE],
            ]
        )->fetch()) {
            $this->log()->warning('Свойство заказа ' . self::PROP_CODE . ' уже существует');

            return true;
        }

        $addResult = OrderPropsTable::add(
            [
                'CODE'           => self::PROP_CODE,
                'NAME'           => 'Начислено бонусов',
                'TYPE'           => 'STRING',
                'REQUIRED'       => 'N',
                'USER_PROPS'     => 'N',
                'DESCRIPTION'    => 'Кол-во бонусов, которые были начислены на карту за этот заказ',
                'PERSON_TYPE_ID' => 1,
                'PROPS_GROUP_ID' => 4
            ]
        );
        if (!$addResult->isSuccess()) {
            $this->log()->error('Ошибка при добавлении свойства заказа ' . self::PROP_CODE);

            return false;
        }

        return true;
    }

    public function down()
    {
        if (!$prop = OrderPropsTable::getList(
            [
                'filter' => ['CODE' => self::PROP_CODE],
            ]
        )->fetch()) {
            $this->log()->error('Свойство заказа ' . self::PROP_CODE . ' не найдено');

            return false;
        }

        $deleteResult = OrderPropsTable::delete($prop['ID']);
        if (!$deleteResult->isSuccess()) {
            $this->log()->error('Ошибка при удалении свойства заказа ' . self::PROP_CODE);

            return false;
        }

        return true;
    }
}
