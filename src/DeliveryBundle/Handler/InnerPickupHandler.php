<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\DeliveryBundle\Handler;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Error;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Sale\PropertyValue;
use Bitrix\Sale\Shipment;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Model\Offer;
use FourPaws\DeliveryBundle\Collection\IntervalCollection;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException;
use FourPaws\StoreBundle\Service\StoreService;

class InnerPickupHandler extends DeliveryHandlerBase
{
    protected const ORDER_DELIVERY_PLACE_CODE_PROP = 'DELIVERY_PLACE_CODE';

    protected $code = '4lapy_pickup';

    /**
     * InnerPickupHandler constructor.
     *
     * @param array $initParams
     *
     * @throws ArgumentNullException
     * @throws ArgumentTypeException
     * @throws SystemException
     * @throws ApplicationCreateException
     */
    public function __construct(array $initParams)
    {
        parent::__construct($initParams);
    }

    public static function getClassTitle(): string
    {
        return 'Самовывоз из магазина "Четыре лапы"';
    }

    public static function getClassDescription(): string
    {
        return 'Обработчик самовывоза "Четыре лапы"';
    }

    /**
     * @param Shipment $shipment
     *
     * @throws ObjectNotFoundException
     * @return bool
     */
    public function isCompatible(Shipment $shipment): bool
    {
        if (!parent::isCompatible($shipment)) {
            return false;
        }

        return true;
    }

    public function getIntervals(Shipment $shipment): IntervalCollection
    {
        return new IntervalCollection();
    }

    /**
     * @param Shipment $shipment
     *
     * @throws ArgumentException
     * @throws ObjectNotFoundException
     * @throws ApplicationCreateException
     * @throws NotFoundException
     * @return CalculationResult
     */
    protected function calculateConcrete(Shipment $shipment): CalculationResult
    {
        $result = new CalculationResult();

        $deliveryLocation = $this->deliveryService->getDeliveryLocation($shipment);
        $shops = $this->storeService->getStoresByLocation($deliveryLocation, StoreService::TYPE_SHOP);
        if ($shops->isEmpty()) {
            $result->addError(new Error('Нет доступных магазинов'));
            return $result;
        }

        /** @noinspection PhpInternalEntityUsedInspection */
        $basket = $shipment->getParentOrder()->getBasket()->getOrderableItems();

        $shopCode = null;
        /** @noinspection PhpInternalEntityUsedInspection */
        /* @var PropertyValue $prop */
        foreach ($shipment->getParentOrder()->getPropertyCollection() as $prop) {
            if ($prop->getField('CODE') === self::ORDER_DELIVERY_PLACE_CODE_PROP) {
                $shopCode = $prop->getValue();
                break;
            }
        }

        if ($shopCode) {
            /** @var StoreCollection $selectedShop */
            $shops = $shops->filter(
                function ($shop) use ($shopCode) {
                    /** @var Store $shop */
                    return $shop->getXmlId() === $shopCode;
                }
            );

            if ($shops->isEmpty()) {
                $result->addError(new Error('Выбран неверный пункт самовывоза'));

                return $result;
            }
        }

        if (!$offers = static::getOffers($deliveryLocation, $basket)) {
            /**
             * Нужно для отображения списка доставок в хедере и на странице доставок
             */
            return $result;
        }

        $stockResult = static::getStocks($basket, $offers, $shops);
        if ($stockResult->getAvailable()->isEmpty() && $stockResult->getDelayed()->isEmpty()) {
            $result->addError(new Error('Товары не в наличии'));

            return $result;
        }

        $data = [
            'STOCK_RESULT' => $stockResult,
            'INTERVALS' => $this->getIntervals($shipment)
        ];
        $result->setData($data);

        if ($shopCode && $stockResult->getOrderable()->isEmpty()) {
            $result->addError(new Error('Отсутствуют товары в наличии'));

            return $result;
        }

        return $result;
    }
}
