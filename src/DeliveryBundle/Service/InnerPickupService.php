<?php

namespace FourPaws\DeliveryBundle\Service;

use Bitrix\Main\Error;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Sale\PropertyValue;
use Bitrix\Sale\Shipment;
use FourPaws\StoreBundle\Collection\StockCollection;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\Stock;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Service\StoreService;

class InnerPickupService extends DeliveryServiceHandlerBase
{
    const ORDER_DELIVERY_PLACE_CODE_PROP = 'DELIVERY_PLACE_CODE';

    protected $code = '4lapy_pickup';

    public function __construct(array $initParams)
    {
        parent::__construct($initParams);
    }

    public static function getClassTitle()
    {
        return 'Самовывоз из магазина "Четыре лапы"';
    }

    public static function getClassDescription()
    {
        return 'Обработчик самовывоза "Четыре лапы"';
    }

    public function isCompatible(Shipment $shipment)
    {
        if (!parent::isCompatible($shipment)) {
            return false;
        }

        $deliveryLocation = $this->deliveryService->getDeliveryLocation($shipment);
        if (!$deliveryLocation) {
            return false;
        }

        $shops = $this->storeService->getByLocation($deliveryLocation, StoreService::TYPE_SHOP);
        if ($shops->isEmpty()) {
            return false;
        }

        return true;
    }

    public function getIntervals(Shipment $shipment): array
    {
        return [];
    }

    protected function calculateConcrete(Shipment $shipment)
    {
        $result = parent::calculateConcrete($shipment);
        if (!$result->isSuccess()) {
            return $result;
        }
        $deliveryLocation = $this->deliveryService->getDeliveryLocation($shipment);

        /* @todo учитывать график поставок для товаров под заказ */
        $order = $shipment->getParentOrder();
        $propertyCollection = $order->getPropertyCollection();

        $offerData = [];
        $basketCollection = $order->getBasket();

        /** @var BasketItem $basketItem */
        foreach ($basketCollection as $basketItem) {
            if (!$basketItem->canBuy()) {
                continue;
            }
            $offerId = $basketItem->getProductId();
            $quantity = $basketItem->getQuantity();
            if (!$offerId || !$quantity) {
                continue;
            }
            $offerData[$offerId] = $quantity;
        }

        if (empty($offerData) || $basketCollection->isEmpty()) {
            $result->setPeriodFrom(1);
            $result->setPeriodType(CalculationResult::PERIOD_TYPE_HOUR);

            return $result;
        }
        $stores = $this->storeService->getByLocation($deliveryLocation, StoreService::TYPE_ALL);
        $shops = $stores->getShops();

        $shopCode = null;
        /* @var PropertyValue $prop */
        foreach ($propertyCollection as $prop) {
            if ($prop->getField('CODE') == self::ORDER_DELIVERY_PLACE_CODE_PROP) {
                $shopCode = $prop->getValue();
                break;
            }
        }

        if ($shopCode) {
            /** @var StoreCollection $selectedShop */
            $shops = $shops->filter(
                function ($shop) use ($shopCode) {
                    /** @var Store $shop */
                    return $shop->getXmlId() == $shopCode;
                }
            );

            if ($shops->isEmpty()) {
                $result->addError(new Error('Выбран неверный пункт самовывоза'));

                return $result;
            }
        }

        $stockData = self::getStocks($deliveryLocation, $offerData);
        /** @var StockCollection $stocks */
        $stocks = $stockData['STOCKS'];

        foreach ($offerData as $offerId => $quantity) {
            if ($stocks->filterByOfferId($offerId)->isEmpty()) {
                $result->addError(
                    new Error(
                        'Товар ' . $offerId . ' недоступен'
                    )
                );
            }
        }
        if (!$result->isSuccess()) {
            return $result;
        }

        /** @var bool $getFromShop флаг того, что товары будут получены из магазина */
        $getFromShop = true;
        $shopStocks = $stocks->filterByStores($shops);
        $availableIn = [];
        foreach ($offerData as $offerId => $quantity) {
            $offerStocks = $shopStocks->filterByOfferId($offerId);
            if ($offerStocks->isEmpty()) {
                $getFromShop = false;
                break;
            }
            /** @var Stock $offerStock */
            foreach ($offerStocks as $offerStock) {
                if ($offerStock->getAmount() < $quantity) {
                    continue;
                }
                $availableIn[$offerId][] = $offerStock->getStoreId();
            }
        }

        // находим магазины, в которых есть все товары в нужном кол-ве
        if ($getFromShop) {
            if (\count($offerData) == 1) {
                $availableShopIds = reset($availableIn);
            } else {
                $availableShopIds = array_intersect(...$availableIn);
            }

            if (empty($availableShopIds)) {
                $getFromShop = false;
            } else {
                $result->setData(
                    [
                        'AVAILABLE_IN' => $shops->filter(
                            function (Store $shop) use ($availableShopIds) {
                                return \in_array($shop->getId(), $availableShopIds);
                            }
                        ),
                    ]
                );
            }
        }

        if ($getFromShop) {
            $result->setPeriodFrom(1);
            $result->setPeriodType(CalculationResult::PERIOD_TYPE_HOUR);
        } else {
            /* @todo расчет сроков по графику поставок в магазин */
            $result->setPeriodFrom(1);
            $result->setPeriodType(CalculationResult::PERIOD_TYPE_HOUR);
        }

        return $result;
    }
}
