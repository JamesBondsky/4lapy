<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Service;

use Adv\Bitrixtools\Tools\BitrixUtils;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\UserMessageException;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\DeliveryBundle\Entity\StockResult;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\SaleBundle\Collection\BasketSplitItemCollection;
use FourPaws\SaleBundle\Discount\Utils\Manager;
use FourPaws\SaleBundle\Entity\BasketSplitItem;
use FourPaws\SaleBundle\Entity\OrderSplitResult;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Exception\BitrixProxyException;
use FourPaws\SaleBundle\Exception\DeliveryNotAvailableException;
use FourPaws\SaleBundle\Exception\OrderCreateException;
use FourPaws\SaleBundle\Exception\OrderSplitException;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;
use Psr\Log\LoggerAwareInterface;

class OrderSplitService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var BasketService
     */
    protected $basketService;

    /**
     * @var DeliveryService
     */
    protected $deliveryService;

    /**
     * @var OrderStorageService
     */
    protected $orderStorageService;


    /**
     * OrderSplitService constructor.
     * @param BasketService       $basketService
     * @param DeliveryService     $deliveryService
     * @param OrderStorageService $orderStorageService
     */
    public function __construct(
        BasketService $basketService,
        DeliveryService $deliveryService,
        OrderStorageService $orderStorageService
    )
    {
        $this->deliveryService = $deliveryService;
        $this->orderStorageService = $orderStorageService;
        $this->basketService = $basketService;
    }

    /**
     * @param OrderStorage $storage
     *
     * @throws ApplicationCreateException
     * @throws OrderSplitException
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws LoaderException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws UserMessageException
     * @throws NotFoundException
     * @throws BitrixProxyException
     * @throws DeliveryNotAvailableException
     * @throws OrderCreateException
     * @throws StoreNotFoundException
     * @return OrderSplitResult[]
     */
    public function splitOrder(OrderStorage $storage): array
    {
        $delivery = clone $this->orderStorageService->getSelectedDelivery($storage);
        $canSplit = $this->canSplitOrder($delivery);
        $canGetPartial = $this->canGetPartial($delivery);

        if (!($canSplit || $canGetPartial)) {
            throw new OrderSplitException('Cannot split order');
        }

        $splitResult1 = null;
        $splitResult2 = null;

        $basket = $this->basketService->getBasket();
        if ($isDiscountEnabled = Manager::isExtendDiscountEnabled()) {
            Manager::disableExtendsDiscount();
        }

        $storage1 = clone $storage;

        [$available, $delayed] = $this->splitStockResult($delivery);
        [$availableItems, $delayedItems] = $this->splitBasket($basket, $available, $delayed);

        $basket1 = $this->generateBasket($availableItems, $canGetPartial);

        $storage1->setBonus($this->getMaxBonusPayment($basket1, $storage));

        /**
         * Требуется пересчет стоимости доставки для первого заказа
         */
        $tmpDeliveries = $this->deliveryService->getByBasket(
            $basket1,
            '',
            [$delivery->getDeliveryCode()],
            $storage1->getCurrentDate()
        );
        if (!$delivery1 = reset($tmpDeliveries)) {
            throw new OrderSplitException('Delivery for order1 is unavailable');
        }

        if (!$canGetPartial) {
            $storage2 = clone $storage;
            $basket2 = $this->generateBasket($delayedItems, false);
            $storage2->setDeliveryInterval($storage->getSecondDeliveryInterval());
            $storage2->setDeliveryDate($storage->getSecondDeliveryDate());
            $storage2->setComment($storage->getSecondComment());
            $storage2->setBonus($storage->getBonus() - $storage1->getBonus());

            $delivery2 = (clone $delivery)->setStockResult($delayed);
            if (!$delivery2->isSuccess()) {
                throw new OrderSplitException('Delivery for order2 is unavailable');
            }
            /**
             * У второго заказа (содержащего товары под заказ) доставка бесплатная
             */
            $delivery2->setDeliveryPrice(0);

            $order2 = $this->getOrderService()->initOrder($storage2, $basket2, $delivery2);
            $splitResult2 = (new OrderSplitResult())->setOrderStorage($storage2)
                ->setOrder($order2)
                ->setDelivery($delivery2);
        } else {
            $maxBonusesForOrder1 = floor(
                min($storage1->getBonus(), $basket1->getPrice() * BasketService::MAX_BONUS_PAYMENT)
            );
            if ($storage1->getBonus() > $maxBonusesForOrder1) {
                $storage1->setBonus($maxBonusesForOrder1);
            }
        }
        $order1 = $this->getOrderService()->initOrder($storage1, $basket1, $delivery1, $canGetPartial);
        $splitResult1 = (new OrderSplitResult())->setOrderStorage($storage1)
            ->setOrder($order1)
            ->setDelivery($delivery1);

        if ($isDiscountEnabled) {
            Manager::enableExtendsDiscount();
        }

        return [$splitResult1, $splitResult2];
    }


    /**
     * @param Basket                $basket
     * @param StockResultCollection $available
     * @param StockResultCollection $delayed
     *
     * @return BasketSplitItemCollection[]
     */
    protected function splitBasket(Basket $basket, StockResultCollection $available, StockResultCollection $delayed): array
    {
        /** @var BasketSplitItemCollection $availableItems */
        $availableItems = new BasketSplitItemCollection();
        /** @var BasketSplitItemCollection $availableItems */
        $delayedItems = new BasketSplitItemCollection();
        /** @var BasketItem $basketItem */
        foreach ($basket as $basketItem) {
            if ($availableResult = $available->filterByOfferId($basketItem->getProductId())->first()) {
                /** @var StockResult $availableResult */
                if (($priceForAmount = $availableResult->getPriceForAmountByBasketCode($basketItem->getBasketCode())) &&
                    $priceForAmount->getAmount()
                ) {
                    $availableItems->add((new BasketSplitItem())
                        ->setAmount($priceForAmount->getAmount())
                        ->setPrice($basketItem->getPrice())
                        ->setBasePrice($basketItem->getBasePrice())
                        ->setDiscount($basketItem->getDiscountPrice())
                        ->setProperties($basketItem->getPropertyCollection()->getPropertyValues())
                    );
                }
            }
            if ($delayedResult = $delayed->filterByOfferId($basketItem->getProductId())->first()) {
                /** @var StockResult $delayedResult */
                /** @var StockResult $availableResult */
                if (($priceForAmount = $delayedResult->getPriceForAmountByBasketCode($basketItem->getBasketCode())) &&
                    $priceForAmount->getAmount()
                ) {
                    $delayedItems->add((new BasketSplitItem())
                        ->setAmount($priceForAmount->getAmount())
                        ->setPrice($basketItem->getPrice())
                        ->setBasePrice($basketItem->getBasePrice())
                        ->setDiscount($basketItem->getDiscountPrice())
                        ->setProperties($basketItem->getPropertyCollection()->getPropertyValues())
                    );
                }
            }
        }

        return [$availableItems, $delayedItems];
    }


    /**
     * Можно ли разделить заказ
     *
     * @param CalculationResultInterface $delivery
     *
     * @return bool
     */
    public function canSplitOrder(CalculationResultInterface $delivery): bool
    {
        $result = false;

        if (!$this->deliveryService->isDpdPickup($delivery) &&
            \in_array($delivery->getDeliveryZone(), [DeliveryService::ZONE_1, DeliveryService::ZONE_2], true) &&
            !$delivery->getStockResult()->getOrderable()->getByRequest(true)->isEmpty()
        ) {
            [$available, $delayed] = $this->splitStockResult($delivery);

            $result = !$available->isEmpty() && !$delayed->isEmpty();
        }

        return $result;
    }

    /**
     * Возможно ли частичное получение заказа
     *
     * @param CalculationResultInterface $delivery
     *
     * @return bool
     */
    public function canGetPartial(CalculationResultInterface $delivery): bool
    {
        $result = false;
        if ($this->deliveryService->isInnerPickup($delivery) &&
            $delivery->getStockResult()->getByRequest(true)->isEmpty() &&
            !$delivery->getStockResult()->getAvailable()->isEmpty() &&
            !$delivery->getStockResult()->getDelayed()->isEmpty()
        ) {
            $result = true;
        }
        return $result;
    }

    /**
     * @param CalculationResultInterface $delivery
     *
     * @return StockResultCollection[]
     */
    public function splitStockResult(CalculationResultInterface $delivery): array
    {
        $stockResultCollection = $delivery->getStockResult();
        if ($delivery->getStockResult()->getByRequest(true)->isEmpty()) {
            $available = $stockResultCollection->getAvailable();
            $delayed = $stockResultCollection->getDelayed();
        } else {
            $available = $stockResultCollection->getRegular();
            $delayed = $stockResultCollection->getByRequest();
        }

        /**
         * Не позволяем разделять так, чтобы один из наборов становился с нулевой ценой
         * (например, содержал одни только подарки)
         */
        if ((!$available->isEmpty() && !$available->getPrice()) ||
            (!$delayed->isEmpty() && !$delayed->getPrice())
        ) {
            $delayed = $stockResultCollection;
            $available = new StockResultCollection();
        }

        return [$available, $delayed];
    }

    /**
     * @param BasketSplitItemCollection $items
     * @param bool                      $canGetPartial
     *
     * @return Basket
     */
    protected function generateBasket(BasketSplitItemCollection $items, $canGetPartial = false): Basket
    {
        /** @var Basket $basket */
        $basket = Basket::create(SITE_ID);

        try {
            /** @var BasketSplitItem $item */
            foreach ($items as $item) {
                if ($canGetPartial) {
                    $rewriteFields = [];
                } else {
                    $rewriteFields = [
                        'PRICE'        => $item->getPrice(),
                        'BASE_PRICE'   => $item->getBasePrice(),
                        'DISCOUNT'     => $item->getDiscount(),
                        'PROPS'        => $item->getProperties(),
                        'CUSTOM_PRICE' => BitrixUtils::BX_BOOL_TRUE,
                    ];
                }

                $this->basketService->addOfferToBasket(
                    $item->getProductId(),
                    $item->getAmount(),
                    $rewriteFields,
                    false,
                    $basket
                );
            }
        } catch (\Exception $e) {
            $this->log()->error(
                sprintf(
                    'failed to add offer to basket: %s: %s',
                    \get_class($e),
                    $e->getMessage()
                )
            );
        }

        return $basket;
    }

    /**
     * @param Basket       $basket
     * @param OrderStorage $storage
     *
     * @return int
     */
    protected function getMaxBonusPayment(Basket $basket, OrderStorage $storage): int
    {
        return floor(
            min($storage->getBonus(), $basket->getPrice() * BasketService::MAX_BONUS_PAYMENT)
        );
    }

    /**
     * @throws ApplicationCreateException
     * @return OrderService
     */
    protected function getOrderService(): OrderService
    {
        return Application::getInstance()->getContainer()->get(OrderService::class);
    }
}