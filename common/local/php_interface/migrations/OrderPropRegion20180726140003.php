<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Internals\OrderPropsTable;
use Exception;

class OrderPropRegion20180726140003 extends SprintMigrationBase
{
    protected $description = 'Создание свойства заказа "Регион"';

    protected const PROP_CODE = 'REGION';

    /**
     * @return bool
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     */
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
                'NAME'           => 'Регион',
                'TYPE'           => 'STRING',
                'REQUIRED'       => 'N',
                'USER_PROPS'     => 'N',
                'DESCRIPTION'    => 'Регион адреса доставки',
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

    /**
     * @return bool
     * @throws ArgumentException
     * @throws Exception
     * @throws ObjectPropertyException
     * @throws SystemException
     */
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
