<?php

namespace Sprint\Migration;

use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Order;
use FourPaws\App\Application;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\PersonalBundle\Entity\Address;
use FourPaws\PersonalBundle\Entity\OrderSubscribe;
use FourPaws\PersonalBundle\Entity\OrderSubscribeItem;
use FourPaws\PersonalBundle\Service\AddressService;
use FourPaws\PersonalBundle\Service\OrderSubscribeService;

class OrderSubscribeProcessOld20190415174353 extends \Adv\Bitrixtools\Migration\SprintMigrationBase {

    protected $description = "Обрабатывает старые подписки на заказ";


    public function up(){
        /**
         * @var AddressService $addressService
         * @var OrderSubscribeService $orderSubscribeService
         * @var DeliveryService $deliveryService
         * @var OrderSubscribe $orderSubscribe
         */
        $updateSubscribes = [];

        $orderSubscribeService = Application::getInstance()->getContainer()->get('order_subscribe.service');
        $deliveryService = Application::getInstance()->getContainer()->get('delivery.service');
        $addressService = Application::getInstance()->getContainer()->get('address.service');

        $subscribeCollection = $orderSubscribeService->getOrderSubscribeRepository()->findBy([]);

        foreach($subscribeCollection as $orderSubscribe){
            $orderId = $orderSubscribe->getOrderId();

            $dbres = Order::getList(['filter' => ['ID' => $orderId]]);
            if(!$order = $dbres->fetch()){
                $this->log()->error('Order not found', ['id' => $orderId]);
                continue;
            }

            $params = [
                'deliveryDay' => $order['DATE_INSERT']->format('N'),
                'deliveryId'  => $order['DELIVERY_ID'],
            ];

            $delCode = $deliveryService->getDeliveryCodeById($params['deliveryId']);
            $isDelivery = $deliveryService->isDeliveryCode($delCode);

            $dbres = \Bitrix\Sale\Internals\OrderPropsValueTable::getList(['filter' => ['ORDER_ID' => $order['ID'], 'CODE' => ['STREET', 'HOUSE', 'DELIVERY_PLACE_CODE']]]);
            $propCollection = [];
            while($property = $dbres->fetch()){
                $propCollection[$property['CODE']] = $property['VALUE'];
            }

            if($isDelivery){
                $addresses = $addressService->getAddressesByUser($order['USER_ID']);
                $addressId = null;
                $street = $propCollection['STREET'];
                $house = $propCollection['HOUSE'];

                /** @var Address $address */
                foreach($addresses as $address){
                    if($address->getStreet() == $street && $address->getHouse() == $house){
                        $addressId = $address->getId();
                    }
                }

                if(!$addressId){
                    $this->log()->error('Address not found', ['orderId' => $order->getId()]);
                    continue;
                }

                $params['deliveryPlace'] = $addressId;
            } else{
                $deliveryPlace = $propCollection['DELIVERY_PLACE_CODE'];
                if(!$deliveryPlace){
                    $this->log()->error('Address not found', ['orderId' => $order->getId()]);
                    continue;
                }

                $params['deliveryPlace'] = $deliveryPlace;
            }

            $orderSubscribe->setDeliveryDay($params['deliveryDay'])
                ->setDeliveryPlace($params['deliveryPlace'])
                ->setDeliveryId($params['deliveryId']);

            //$orderSubscribeService->update($orderSubscribe);
            /*if($orderSubscribeService->update($orderSubscribe)){
                $this->log()->info(sprintf('Подписка обновлена: %s', $orderSubscribe->getId()));
            } else {
                $this->log()->info(sprintf('Не удалось обновить подписку: %s', $orderSubscribe->getId()));
            }*/

            $dbres = Basket::getList(['filter' => ['ORDER_ID' => $orderId]]);
            $basket = [];
            while($row = $dbres->fetch()){
                $basket[] = [
                    'productId' => $row['PRODUCT_ID'],
                    'quantity' => $row['QUANTITY'],
                ];
            }

            /** @var BasketItem $basketItem */
            foreach ($basket as $basketItem){
                try {
                    $subscribeItem = (new OrderSubscribeItem())
                        ->setOfferId($basketItem['productId'])
                        ->setQuantity($basketItem['quantity']);
                } catch (\Exception $e) {
                    $this->log()->error(sprintf('Cannot create SubscribeItem: %s', $e->getMessage()), ['productId' => $basketItem['productId']]);
                    continue;
                }

                /*if($orderSubscribeService->addSubscribeItem($orderSubscribe, $subscribeItem)){
                    $this->log()->info(sprintf('Товар добавлен в подписку: %s subId: %s', $subscribeItem->getOfferId(), $orderSubscribe->getId()));
                } else {
                    $this->log()->error(sprintf('Ошибка добавления товара в подписку: %s subId: %s', $subscribeItem->getOfferId(), $orderSubscribe->getId()));
                }*/
            }

            break;
        }

        return true;
    }

    public function down(){
        return true;
    }

}
