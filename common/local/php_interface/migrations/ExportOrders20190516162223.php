<?php

namespace Sprint\Migration;

use Adv\Bitrixtools\Migration\SprintMigrationBase;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\NotImplementedException;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\BasketItemCollection;
use Bitrix\Sale\Order;
use CSaleOrder;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application as AppApplication;

/**
 * Class ExportOrders20190516162223
 * @package Sprint\Migration
 */
class ExportOrders20190516162223 extends SprintMigrationBase
{
    protected const FOLDER_CHMOD = 0775;
    protected const DELIMITER = "\t";
    protected const CSV_HEADER = [
        'id заказа',
        'сумма товаров НДС 10%',
        'сумма товаров НДС 20%',
        'статус заказа'
    ];

    protected $description = 'Экспорт заказов в csv файл';

    protected $ordersFile = '/upload/orders_export/orders_import.txt';
    protected $resultPath = '/upload/orders_export/';
    protected $resultFile = 'orders.csv';
    protected $resultDataToCsv = [];
    protected $statuses = [];

    /**
     * @return bool
     * @throws ArgumentNullException
     * @throws NotImplementedException
     */
    public function up()
    {
        $filePathAbs = AppApplication::getAbsolutePath($this->ordersFile);
        if (!file_exists($filePathAbs)) {
            echo 'Файл импорта не найден по пути ' . $this->ordersFile;
            return false;
        }
        $ordersIds = explode("\r\n", file_get_contents($filePathAbs));

        $existsOrders = [];
        $orderDb = CSaleOrder::GetList([], ['ACCOUNT_NUMBER' => $ordersIds]);
        while ($order = $orderDb->Fetch()) {
            $existsOrders[] = $order['ACCOUNT_NUMBER'];
        }

        $orders = new ArrayCollection();
        foreach ($existsOrders as $orderId) {
            $order = Order::loadByAccountNumber($orderId);
            $orders->set($order->getField('ACCOUNT_NUMBER'), $order);
        }
        unset($order);

        $dbStatuses = \CSaleStatus::GetList();
        while ($status = $dbStatuses->Fetch()) {
            $this->statuses[$status['ID']] = $status['NAME'];
        }


        $this->resultDataToCsv[] = static::CSV_HEADER;
        foreach ($ordersIds as $orderId) {
            $order = $orders->get($orderId);
            if ($order instanceof Order) {
                $statusId = $order->getField('STATUS_ID');
                $summ10 = 0;
                $summ20 = 0;
                $basket = $order->getBasket();
                /** @var BasketItemCollection $basketItems */
                $basketItems = $basket->getBasketItems();
                /** @var BasketItem $basketItem */
                foreach ($basketItems as $basketItem) {
                    switch ($basketItem->getVatRate()) {
                        case 0.1:
                            $summ10 += $basketItem->getFinalPrice();
                            break;
                        case 0.2:
                            $summ20 += $basketItem->getFinalPrice();
                            break;
                    }
                }
                /**
                 * 'id заказа',
                 * 'сумма товаров НДС 10%',
                 * 'сумма товаров НДС 20%',
                 * 'статус заказа'
                 */
                $this->resultDataToCsv[] = [
                    'ACCOUNT_NUMBER' => $orderId,
                    'SUMM_10'        => str_replace('.', ',', (string)$summ10),
                    'SUMM_20'        => str_replace('.', ',', (string)$summ20),
                    'STATUS'         => $this->statuses[$statusId] . ' [' . $statusId . ']',
                ];
            } else {
                $this->resultDataToCsv[] = [
                    'ACCOUNT_NUMBER' => $orderId,
                    'SUMM_10'        => '',
                    'SUMM_20'        => '',
                    'STATUS'         => 'Заказ не найден на сайте',
                ];
            }
            unset($order);
        }

        $resultPath = AppApplication::getAbsolutePath($this->resultPath);
        if (!is_dir($resultPath)) {
            mkdir($resultPath, static::FOLDER_CHMOD);
        }

        $resultPath .= $this->resultFile;
        if (file_exists($resultPath)) {
            unlink($resultPath);
        }
        $fp = fopen($resultPath, 'a');
        foreach ($this->resultDataToCsv as $order) {
            fputcsv($fp, $order, static::DELIMITER);
        }
        fclose($fp);

        return true;
    }

    /**
     * @return bool
     */
    public function down()
    {
        //empty
        return true;
    }
}
