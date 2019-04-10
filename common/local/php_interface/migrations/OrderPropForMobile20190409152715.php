<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ORM\Data\AddResult;
use Bitrix\Main\ORM\Data\DeleteResult;
use Bitrix\Sale\Internals\OrderPropsTable;
use Bitrix\Sale\Internals\OrderPropsVariantTable;

class OrderPropForMobile20190409152715 extends SprintMigrationBase
{
    protected $description = 'Добавление нового свойства в заказ - сделано из мобильного приложения типа список с устройствами';

    protected const PROP_CODE = 'FROM_APP_DEVICE';
    protected const PROP_NAME = 'Тип устройства';

    protected $values = [
        'android' => 'Android',
        'ios' => 'iOS'
    ];

    /**
     * @return bool
     * @throws ArgumentException
     * @throws \Exception
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
        /**
         * Добавляем свойство
         * @var AddResult $addResult
         */
        $addResult = OrderPropsTable::add(
            [
                'CODE' => self::PROP_CODE,
                'NAME' => self::PROP_NAME,
                'TYPE' => 'ENUM',
                'REQUIRED' => 'N',
                'USER_PROPS' => 'N',
                'DESCRIPTION' => '',
                'PERSON_TYPE_ID' => 1,
                'PROPS_GROUP_ID' => 4,
                'UTIL' => 'Y',
                'IS_FILTERED' => 'Y',
                'ENTITY_REGISTRY_TYPE' => 'ORDER'
            ]
        );
        if (!$addResult->isSuccess()) {
            $this->log()->error('Ошибка при добавлении свойства заказа ' . self::PROP_CODE);
            return false;
        }
        /**
         * Добавляем значения свойств
         */
        $propId = $addResult->getId();
        $i = 0;
        foreach ($this->values as $code => $value) {
            $addResult = OrderPropsVariantTable::add([
                'ORDER_PROPS_ID' => $propId,
                'NAME' => $value,
                'VALUE' => $code,
                'SORT' => 100 + $i,
                'DESCRIPTION' => ''
            ]);
            if (!$addResult->isSuccess()) {
                $this->log()->error('Ошибка при добавлении значения свойства заказа ' . self::PROP_CODE);
                return false;
            }
            $i += 100;
        }

        return true;
    }

    /**
     * @return bool
     * @throws ArgumentException
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
        /**
         * Удаляем свойство
         * @var DeleteResult $deleteResult
         */
        $deleteResult = OrderPropsTable::delete($prop['ID']);
        if (!$deleteResult->isSuccess()) {
            $this->log()->error('Ошибка при удалении свойства заказа ' . self::PROP_CODE);
            return false;
        }

        return true;
    }
}
