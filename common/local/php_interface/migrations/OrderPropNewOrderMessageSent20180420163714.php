<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Sale\Internals\OrderPropsTable;

class OrderPropNewOrderMessageSent20180420163714 extends SprintMigrationBase
{
    protected $description = 'Создание свойства заказа "Письмо Новый Заказ отправлено"';

    const PROP_CODE = 'NEW_ORDER_MESSAGE_SENT';

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
                'NAME'           => 'Письмо "Новый Заказ" отправлено',
                'TYPE'           => 'Y/N',
                'REQUIRED'       => 'N',
                'USER_PROPS'     => 'N',
                'DESCRIPTION'    => '',
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
