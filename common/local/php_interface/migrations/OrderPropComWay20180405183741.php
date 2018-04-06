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

class OrderPropComWay20180405183741 extends SprintMigrationBase
{
    protected $description = 'Изменение свойства "Способ коммуникации"';

    protected const PROP_CODE = 'COM_WAY';

    protected $values = [
        '01' => 'SMS',
        '02' => 'Телефонный звонок',
        '03' => 'Анализ самовывоз',
        '04' => 'Заказ в 1 клик',
        '05' => 'Анализ адрес',
        '06' => 'Анализ оплата',
        '07' => 'Подписка'
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

        while ($variant = $variants->fetch()) {
            $name = $this->values[$variant['VALUE']];
            if (null === $name) {
                //delete
                $deleteResult = OrderPropsVariantTable::delete($variant['ID']);
                if (!$deleteResult->isSuccess()) {
                    $this->log()->error(
                        sprintf(
                            'Ошибка при удалении %s значения свойства %s: %s',
                            $variant['VALUE'],
                            static::PROP_CODE,
                            implode(', ', $deleteResult->getErrorMessages())
                        )
                    );
                    return false;
                }
                continue;
            }

            if ($name !== $variant['NAME']) {
                $updateResult = OrderPropsVariantTable::update($variant['ID'], ['NAME' => $name]);
                if (!$updateResult->isSuccess()) {
                    $this->log()->error(
                        sprintf(
                            'Ошибка при обновлении %s значения свойства %s: %s',
                            $variant['VALUE'],
                            static::PROP_CODE,
                            implode(', ', $updateResult->getErrorMessages())
                        )
                    );
                    return false;
                }
            }

            unset($this->values[$variant['VALUE']]);
        }

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
