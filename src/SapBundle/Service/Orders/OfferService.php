<?php

namespace FourPaws\SapBundle\Service\Orders;

use Adv\Bitrixtools\Tools\BitrixUtils;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\NotImplementedException;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Order;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\PersonalBundle\Service\StampService;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\SapBundle\Dto\Out\Orders\OrderOffer;
use FourPaws\SapBundle\Enum\SapOrder;
use Psr\Log\LoggerAwareInterface;

/**
 * Class OfferService
 * @package FourPaws\SapBundle\Service\Orders
 */
class OfferService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    protected const LEVEL_SHIPMENT = 1;
    protected const LEVEL_BONUS = 2;
    protected const LEVEL_STAMPS = 3;

    /**
     * @var BasketService
     */
    protected $basketService;
    /**
     * @var StampService
     */
    protected $stampService;
    /**
     * @var OrderService
     */
    protected $orderService;
    /**
     * @var bool
     */
    protected $isPseudoAction = false;

    /**
     * OrderService constructor.
     *
     * @param BasketService $basketService
     * @param StampService $stampService
     * @param OrderService $orderService
     */
    public function __construct(
        BasketService $basketService,
        StampService $stampService,
        OrderService $orderService
    )
    {
        $this->basketService = $basketService;
        $this->stampService = $stampService;
        $this->orderService = $orderService;
    }

    /**
     * @param ArrayCollection $collection
     * @param $position
     * @param Order $order
     *
     * @param BasketItem $basketItem
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws NotImplementedException
     */
    public function addOfferToCollection(ArrayCollection $collection, &$position, Order $order, BasketItem $basketItem): void
    {
        if ($basketItem->isDelay()) {
            return;
        }

        $this->setPseudoAction($basketItem);

        /* эти параметры будут одинаковые у всех элементов после разделения */
        $offer = (new OrderOffer())
            ->setOfferXmlId($this->basketService->getBasketItemXmlId($basketItem))
            ->setUnitPrice($basketItem->getPrice())
            ->setUnitOfMeasureCode(SapOrder::UNIT_PTC_CODE)
            ->setDeliveryFromPoint($this->orderService->getPropertyValueByCode($order, 'DELIVERY_PLACE_CODE'));

        /* Может измениться внутри разделения по местоположению, выставляем здесь, чтобы можно было запустить разделение в цикле */
        $offer->setDeliveryShipmentPoint($this->getShipmentPlaceCode($basketItem, $order));

        /* Переменные, от значения которых зависит разделение товара */
        $quantity = (int)$basketItem->getQuantity();
        $dc01Amount = (int)$this->basketService->getBasketPropertyValueByCode($basketItem, 'DC01_AMOUNT'); // количество товара на DC01, если заказ берется у поставщика
        $hasBonus = (int)$this->basketService->getBasketPropertyValueByCode($basketItem, 'HAS_BONUS');

        [$stampsProductAmount, $stampsLevelInfo] = $this->getStampsInfo($basketItem);

        /*
         * Три уровня разделения
         * 1-ое разделение может быть из-за того, что часть товара берется с DC01, а другая часть заказывается у постовщика
         * 2-ое может быть из-за того, что за часть товара начисляются бонусы, а за другую часть - нет (может быть внутри первого)
         * 3-е разделение происходит из-за того, что за за часть товара списны марки, а за другую часть - нет (может быть внутри двух предыдущих)
         */
        $detachLevels = [
            self::LEVEL_SHIPMENT => 'dc01Amount',
            self::LEVEL_BONUS => 'hasBonus',
            self::LEVEL_STAMPS => 'stampsProductAmount',
        ];

        /* Непосредственно разделение товара */
        foreach ($detachLevels as $detachLevel => $detachAmount) {
            if ($this->detachOfferByLevel($detachLevel, $$detachAmount, $collection, $offer, $position, $quantity, $hasBonus, $stampsProductAmount, $stampsLevelInfo)) {
                $quantity -= $dc01Amount;

                if ($quantity <= 0) {
                    return;
                }
            }
        }

        $offer
            ->setPosition($position)
            ->setQuantity($quantity)
            ->setChargeBonus($this->getChargeBonusValue($hasBonus));

        $collection->add($offer);
        $position++;
    }

    /**
     * Разделение товара
     *
     * @param int $level
     * @param int $detachAmount
     * @param ArrayCollection $collection
     * @param OrderOffer $originOffer
     * @param $position
     * @param $quantity
     * @param $hasBonus
     * @param $stampsProductAmount
     * @param $stampsLevelInfo
     *
     * @return bool
     */
    protected function detachOfferByLevel(int $level, int $detachAmount, ArrayCollection $collection, OrderOffer $originOffer, &$position, &$quantity, &$hasBonus, &$stampsProductAmount, $stampsLevelInfo): bool
    {
        if ($detachAmount && $detachAmount < $quantity) {
            $detachedOffer = clone $originOffer;

            if ($level === self::LEVEL_SHIPMENT) {
                $detachedOffer->setDeliveryShipmentPoint(OrderOffer::DEFAULT_PROVIDER_POINT);

                if ($this->detachOfferByLevel(self::LEVEL_BONUS, $hasBonus, $collection, $detachedOffer, $position, $detachAmount, $hasBonus, $stampsProductAmount, $stampsLevelInfo)) {
                    $detachAmount -= $hasBonus;
                }
            }

            if ($level === self::LEVEL_SHIPMENT || $level === self::LEVEL_BONUS) {
                if ($this->detachOfferByLevel(self::LEVEL_STAMPS, $stampsProductAmount, $collection, $originOffer, $position, $detachAmount, $hasBonus, $stampsProductAmount, $stampsLevelInfo)) {
                    $detachAmount -= $stampsProductAmount;
                }
            }

            $detachedOffer
                ->setPosition($position)
                ->setQuantity($detachAmount)
                ->setChargeBonus($this->getChargeBonusValue($hasBonus));

            if ($stampsProductAmount > 0) {
                $detachedOffer
                    ->setExchangeName($stampsLevelInfo['title'])
                    ->setStampsQuantity($detachAmount * $stampsLevelInfo['discountStamps']);
            }

            $hasBonus = ($hasBonus > $detachAmount) ? ($hasBonus - $detachAmount) : 0;
            $stampsProductAmount = ($stampsProductAmount > $detachAmount) ? ($stampsProductAmount - $detachAmount) : 0;

            $collection->add($detachedOffer);
            $position++;

            return true;
        }

        return false;
    }

    /**
     * @param BasketItem $basketItem
     *
     * @return array
     */
    protected function getStampsInfo(BasketItem $basketItem): array
    {
        $exchangeName = '';
        $stampsProductAmount = 0;
        $discountStamps = 0;

        if ($this->stampService::IS_STAMPS_OFFER_ACTIVE) {
            try {
                $useStamps = $this->basketService->getBasketPropertyValueByCode($basketItem, 'USE_STAMPS');
                if ($useStamps) {
                    $usedStampsLevelProp = $this->basketService->getBasketPropertyValueByCode($basketItem, 'USED_STAMPS_LEVEL');
                    if ($usedStampsLevelValue = unserialize($usedStampsLevelProp)) {
                        $stampsProductAmount = (int)$usedStampsLevelValue['productQuantity'];
                        $discountStamps = (int)($usedStampsLevelValue['stampsUsed'] / $stampsProductAmount);
                        $exchangeName = $usedStampsLevelValue['exchangeName'];
                    }
                }
            } catch (\Exception $e) {
            }
        }

        return [
            $stampsProductAmount,
            [
                'title' => $exchangeName,
                'discountStamps' => $discountStamps,
            ],
        ];
    }

    /**
     * @param $hasBonus
     *
     * @return bool
     */
    protected function getChargeBonusValue($hasBonus): bool
    {
        return ($this->isPseudoAction) ? true : (bool)$hasBonus;
    }

    /**
     * @param BasketItem $basketItem
     * @param Order $order
     * @return string
     * @throws ArgumentException
     * @throws NotImplementedException
     */
    protected function getShipmentPlaceCode(BasketItem $basketItem, Order $order): string
    {
        $shipmentPlaceCode = $this->basketService->getBasketPropertyValueByCode($basketItem, 'SHIPMENT_PLACE_CODE');
        if (!$shipmentPlaceCode || empty($shipmentPlaceCode)) {
            $shipmentPlaceCode = $this->orderService->getPropertyValueByCode($order, 'DELIVERY_PLACE_CODE');
        }

        return $shipmentPlaceCode;
    }

    /**
     * @param BasketItem $basketItem
     * @throws ArgumentException
     * @throws NotImplementedException
     */
    protected function setPseudoAction(BasketItem $basketItem): void
    {
        $isPseudoActionPropValue = $this->basketService->getBasketPropertyValueByCode($basketItem, 'IS_PSEUDO_ACTION');
        $this->isPseudoAction = BitrixUtils::BX_BOOL_TRUE === $isPseudoActionPropValue;
    }
}
