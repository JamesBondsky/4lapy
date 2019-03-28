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

class OrderPropComWayDostavista20192703113741 extends SprintMigrationBase
{
    protected $description = 'Изменение свойства "Способ коммуникации" с учетом экспресс-доставки';

    protected const PROP_CODE = 'COM_WAY';

    protected $values = [
        '08' => 'Заказ не удалось выгрузить в Экспресс-доставку',
        '09' => 'Заказ не удалось выгрузить в Экспресс-доставку и Анализ оплата',
    ];

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

        $variants = OrderPropsVariantTable::query()
            ->setSelect(['*'])
            ->setFilter(
                [
                    'ORDER_PROPS_ID' => $prop['ID']
                ]
            )
            ->exec();

        foreach ($this->values as $value => $name) {
            $addResult = OrderPropsVariantTable::add([
                'NAME' => $name,
                'VALUE' => $value,
                'ORDER_PROPS_ID' => $prop['ID']
            ]);
            if (!$addResult->isSuccess()) {
                $this->log()->error(
                    sprintf(
                        'Ошибка при добавлении %s значения свойства %s: %s',
                        $value,
                        static::PROP_CODE,
                        implode(', ', $addResult->getErrorMessages())
                    )
                );
                return false;
            }
        }

        return true;
    }
}
