<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Sale\Internals\OrderPropsTable;

class Order_location_prop_set_is_location20171212131004 extends SprintMigrationBase
{
    protected $description = 'Задание свойству заказа "LOCATION" атрибута IS_LOCATION = Y';

    const PROP_CODE = 'CITY_CODE';

    public function up()
    {
        if (!$prop = OrderPropsTable::getList(
            [
                'filter' => ['CODE' => self::PROP_CODE],
            ]
        )->fetch()) {
            $this->log()->error('Свойство заказа ' . self::PROP_CODE . ' не найдено');

            return false;
        }

        $updateResult = OrderPropsTable::update($prop['ID'], ['IS_LOCATION' => 'Y']);
        if (!$updateResult->isSuccess()) {
            $this->log()->error('Ошибка при обновлении свойства заказа ' . self::PROP_CODE);

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

        $updateResult = OrderPropsTable::update($prop['ID'], ['IS_LOCATION' => 'N']);
        if (!$updateResult->isSuccess()) {
            $this->log()->error('Ошибка при обновлении свойства заказа ' . self::PROP_CODE);

            return false;
        }

        return true;
    }
}
