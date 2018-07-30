<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Sale\Internals\OrderPropsValueTable;
use Bitrix\Sale\Order;
use Bitrix\Sale\Payment;

class DeleteManzanaOrders20180730125341 extends SprintMigrationBase
{
    protected $description = 'Удаление всех заказов из манзаны';

    /**
     * @return bool|void
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * @throws \Exception
     */
    public function up()
    {
        $orders = OrderPropsValueTable::query()
            ->setSelect(['ORDER_ID'])
            ->setOrder(['ORDER_ID' => 'DESC'])
            ->setFilter(
                [
                    'CODE' => 'MANZANA_NUMBER',
                ]
            )
            ->exec();

        while ($manzanaOrder = $orders->fetch()) {
            $order = Order::load($manzanaOrder['ORDER_ID']);
            if (!$order) {
                $this->log()->warning(sprintf('Заказ %s не найден', $manzanaOrder['ORDER_ID']));
                continue;
            }

            /** @var Payment $payment */
            foreach ($order->getPaymentCollection() as $payment) {
                $payment->setPaid('N');
            }

            $order->save();
            $result = Order::delete($order->getId());
            if (!$result->isSuccess()) {
                $this->log()->error(
                    sprintf(
                        'Ошибка при удалении заказа %s: %s',
                        $order->getId(),
                        \implode('. ', $result->getErrorMessages())
                    )
                );
            }
            $this->log()->debug('Удален заказ ' . $order->getId());
        }
    }

    public function down()
    {

    }
}