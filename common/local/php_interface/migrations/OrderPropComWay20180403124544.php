<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Internals\OrderPropsTable;
use Bitrix\Sale\Internals\OrderPropsVariantTable;
use Exception;
use FourPaws\SaleBundle\Service\OrderPropertyService;
use FourPaws\SaleBundle\Service\OrderService;

class OrderPropComWay20180403124544 extends SprintMigrationBase
{
    protected $description = 'Изменение свойства "Способ коммуникации"';

    protected const PROP_CODE = 'COM_WAY';

    protected const PROP_VARIANT_NAME = 'Анализ оплаты';
    protected const PROP_VARIANT_VALUE = '06';

    /**
     * @return bool
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws Exception
     */
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

        if ($variant = OrderPropsVariantTable::query()->setSelect(['*'])
            ->setFilter([
                'ORDER_PROPS_ID' => $prop['ID'],
                'VALUE' => static::PROP_VARIANT_VALUE
            ])->exec()->fetch()
        ) {
            $this->log()->info(sprintf('Значение %s свойства %s уже существует', static::PROP_VARIANT_VALUE, static::PROP_CODE));
            return true;
        }

        $addResult = OrderPropsVariantTable::add([
            'ORDER_PROPS_ID' => $prop['ID'],
            'NAME' => static::PROP_VARIANT_NAME,
            'VALUE' => static::PROP_VARIANT_VALUE,
            'SORT' => 100
        ]);

        if (!$addResult->isSuccess()) {
            $this->log()->error('Ошибка при добавлении варианта свойства заказа ' . self::PROP_CODE);

            return false;
        }

        return true;
    }
}
