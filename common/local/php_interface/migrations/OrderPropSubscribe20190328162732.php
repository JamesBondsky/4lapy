<?php

namespace Sprint\Migration;


use Bitrix\Sale\Internals\OrderPropsTable;

class OrderPropSubscribe20190328162732 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = "Свойство \"Заказ по подписке\"";

    const PROP_CODE = 'SUBSCRIBE';

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
                'NAME'           => 'Подписка на доставку',
                'SORT'           => '9200',
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
