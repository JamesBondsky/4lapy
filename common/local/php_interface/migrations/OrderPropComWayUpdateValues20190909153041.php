<?php

namespace Sprint\Migration;


use Bitrix\Main\ArgumentException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Internals\OrderPropsTable;
use Bitrix\Sale\Internals\OrderPropsVariantTable;
use Exception;

class OrderPropComWayUpdateValues20190909153041 extends \Adv\Bitrixtools\Migration\SprintMigrationBase
{
    protected $description = 'Изменение свойства "Способ коммуникации"';

    protected const PROP_CODE = 'COM_WAY';

    protected const PROP_VALUES = [
        [
            'name' => 'Первый заказ по подписке (SMS)',
            'value' => '09',
        ],
        [
            'name' => 'Первый заказ по подписке (телефон)',
            'value' => '10',
        ]
    ];

    /**
     * @return bool
     * @throws ArgumentException
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

        foreach (self::PROP_VALUES as $propValue) {
            if ($variant = OrderPropsVariantTable::query()
                ->setSelect(['*'])
                ->setFilter([
                    'ORDER_PROPS_ID' => $prop['ID'],
                    'VALUE' => $propValue['value'],
                ])->exec()->fetch()
            ) {
                $addResult = OrderPropsVariantTable::update(
                    $variant['ID'],
                    [
                        'NAME' => $propValue['name'],
                        'VALUE' => $propValue['value'],
                        'SORT' => 100
                    ]
                );
            } else {
                $addResult = OrderPropsVariantTable::add([
                    'ORDER_PROPS_ID' => $prop['ID'],
                    'NAME' => $propValue['name'],
                    'VALUE' => $propValue['value'],
                    'SORT' => 100
                ]);
            }

            if (!$addResult->isSuccess()) {
                $this->log()->error('Ошибка при добавлении варианта свойства заказа ' . self::PROP_CODE);
                return false;
            }
        }

        return true;
    }

    public function down()
    {
        $helper = new HelperManager();
        return true;
    }
}
