<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SaleBundle\Discount;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Order;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application as App;
use FourPaws\External\Exception\ManzanaPromocodeUnavailableException;
use FourPaws\External\Manzana\Dto\ChequePosition;
use FourPaws\External\Manzana\Dto\Coupon;
use FourPaws\External\Manzana\Dto\ExtendedAttribute;
use FourPaws\External\Manzana\Dto\SoftChequeResponse;
use FourPaws\External\Manzana\Exception\ExecuteException;
use FourPaws\External\ManzanaPosService;
use FourPaws\Helpers\BxCollection;
use FourPaws\PersonalBundle\Exception\CouponIsNotAvailableForUseException;
use FourPaws\PersonalBundle\Service\PersonalOffersService;
use FourPaws\PersonalBundle\Service\PiggyBankService;
use FourPaws\PersonalBundle\Service\StampService;
use FourPaws\SaleBundle\Helper\PriceHelper;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\UserService;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;

/**
 * Class Manzana
 *
 * @package FourPaws\SaleBundle\Discount
 */
class Manzana implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var BasketService
     */
    private $basketService;
    /**
     * @var ManzanaPosService
     */
    private $manzanaPosService;
    /**
     * @var PersonalOffersService
     */
    private $personalOffersService;
    /**
     * @var string
     */
    private $promocode = '';
    /**
     * @var UserService
     */
    private $userService;
    /**
     * @var float
     */
    private $discount = 0.0;
    /**
     * @var float
     */
    private $stampsToBeAdded = 0.0;
    /**
     * @var StampService
     */
    private $stampService;

    /**
     * Manzana constructor.
     *
     * @param BasketService $basketService
     * @param ManzanaPosService $manzanaPosService
     * @param UserService $userService
     * @param StampService $stampService
     */
    public function __construct(BasketService $basketService, ManzanaPosService $manzanaPosService, UserService $userService, StampService $stampService)
    {
        $this->basketService = $basketService;
        $this->manzanaPosService = $manzanaPosService;
        $this->userService = $userService;
        $this->stampService = $stampService;
    }

    /**
     * @param string $promocode
     */
    public function setPromocode(string $promocode): void
    {
        $this->promocode = trim($promocode);
    }

    /**
     * @param Order|null $order
     * @param Basket|null $basket
     *
     * @throws ArgumentOutOfRangeException
     * @throws ManzanaPromocodeUnavailableException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function calculate(?Order $order = null, ?Basket $basket = null): void
    {
        if ($order) {
            $basket = $order->getBasket();
        } elseif (!$basket) {
            $basket = $this->basketService->getBasket();
        }
        /** @var Basket $basket */
        $basket = $basket->getOrderableItems();

        if (!$basket->count()) {
            /**
             * Empty basket
             */
            return;
        }

        $price = $basket->getPrice();

        try {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $user = $this->userService->getCurrentUser();
            $card = $user->getDiscountCardNumber();
        } catch (NotAuthorizedException $e) {
            $card = '';
        }

        $request = $this->manzanaPosService->buildRequestFromBasket($basket, $card, $this->basketService);

        try {
            if ($this->promocode) {
                /** @var PiggyBankService $piggyBankService */
                $piggyBankService = App::getInstance()->getContainer()->get('piggy_bank.service');
                $piggyBankService->checkPiggyBankCoupon($this->promocode);

                $response = $this->manzanaPosService->processChequeWithCoupons($request, $this->promocode);

                $this->checkPromocodeByResponse($response, $this->promocode);

                /**
                 * @todo переделать костыль
                 */
                $this->saveCouponDiscount($response);
            } else {
                $response = $this->manzanaPosService->processCheque($request);
            }


            $this->recalculateBasketFromResponse($basket, $response);
            $this->discount = $price - $basket->getPrice();
        } catch (ExecuteException|CouponIsNotAvailableForUseException $e) {
            /** @var BasketItem $item */
            foreach ($basket as $item) {
                // Закомментировано, т.к. потребовалось сохранять скидку за марки при отваливающемся по таймауту запросе к Manzana
//                $offerXmlId = explode('#', $item->getField('PRODUCT_XML_ID'))[1];
//                if ($offerXmlId && isset($this->stampService::EXCHANGE_RULES[$offerXmlId])) {
//                    $price = PriceHelper::roundPrice($item->getBasePrice());
//                } else {
//                    $price = PriceHelper::roundPrice($item->getPrice());
//                }
                $price = PriceHelper::roundPrice($item->getPrice());
                /** @noinspection PhpInternalEntityUsedInspection */
                $item->setFieldsNoDemand([
                    'PRICE' => $price,
                    'DISCOUNT_PRICE' => $item->getBasePrice() - $price,
                    'CUSTOM_PRICE' => 'Y'
                ]);
            }

            if ($e instanceof ExecuteException) {
                $this->log()->error(
                    \sprintf(
                        'Manzana recalculate error: %s',
                        $e->getMessage()
                    )
                );
            } else if ($e instanceof CouponIsNotAvailableForUseException) {
                $this->log()->error(
                    \sprintf(
                        'Coupon checking error: %s',
                        $e->getMessage()
                    )
                );
            }
        }
    }

    /**
     * @param array      $promocodes
     * @param Order|null $order
     *
     * @return array
     *
     * @throws \Bitrix\Main\ObjectException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getAllowPromocodes(array $promocodes, ?Order $order = null): array
    {
        if ($order) {
            $basket = $order->getBasket();
        } else {
            $basket = $this->basketService->getBasket();
        }
        /** @var Basket $basket */
        $basket = $basket->getOrderableItems();

        if (!$basket->count()) {
            /**
             * Empty basket
             */
            return [];
        }

        try {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $user = $this->userService->getCurrentUser();
            $card = $user->getDiscountCardNumber();
        } catch (NotAuthorizedException $e) {
            $card = '';
        }

        $request = $this->manzanaPosService->buildRequestFromBasket($basket, $card, $this->basketService);

        foreach ($promocodes as $key => $promocode) {
            try {
                if ($promocode) {
                    $promocode = \htmlspecialchars($promocode);
                    $personalOfferService = $this->getPersonalOffersService();
                    $personalOfferService->checkCoupon($promocode);
                    $this->setPromocode($promocode);
                    $request->setCoupons(new ArrayCollection([(new Coupon())->setNumber($promocode)]));
                    $response = $this->manzanaPosService->execute($request, true);
                    $apply = false;
                    foreach ($response->getCoupons() as $coupon) {
                        if ($coupon->isApplied()) {
                            $apply = true;
                            break;
                        }
                    }
                    if (!$apply) {
                        unset($promocodes[$key]);
                    }
                } else {
                    unset($promocodes[$key]);
                }
            } catch (ExecuteException|CouponIsNotAvailableForUseException|ManzanaPromocodeUnavailableException $e) {
                unset($promocodes[$key]);
            }
        }

        return $promocodes;
    }

    /**
     * @param Basket             $basket
     * @param SoftChequeResponse $response
     *
     * @throws ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ArgumentNullException
     */
    public function recalculateBasketFromResponse(Basket $basket, SoftChequeResponse $response): void
    {
        $manzanaItems = $response->getItems();
        $this->setStampsToBeAdded($response->getChargedStatusBonus());

        try {
            $activeStampsCount = $this->stampService->getActiveStampsCount();
        } catch (\Exception $e) {
            $this->log()->error(__METHOD__ . '. getActiveStampsCount exception: ' . $e->getMessage());
            $activeStampsCount = 0;
        }

        /**
         * @var BasketItem $item
         */
        foreach ($basket as $item) {
            $basketCode = (int)str_replace('n', '', $item->getBasketCode());

            $manzanaItems->map(function (ChequePosition $position) use ($basketCode, $item, $activeStampsCount) {
                if ($position->getChequeItemNumber() === $basketCode) {
                    $price = PriceHelper::roundPrice($position->getSummDiscounted() / $position->getQuantity());

                    /** @noinspection PhpInternalEntityUsedInspection */
                    $item->setFieldsNoDemand([
                        'BASE_PRICE' => $item->getBasePrice(),
                        'PRICE' => $price,
                        'DISCOUNT_PRICE' => $item->getBasePrice() - $price,
                        'CUSTOM_PRICE' => 'Y',
                    ]);

                    if ($this->stampService::IS_STAMPS_OFFER_ACTIVE) {
                        $basketPropertyCollection = $item->getPropertyCollection();
                        $maxStampsLevelProperty = BxCollection::getBasketItemPropertyByCode($basketPropertyCollection, 'MAX_STAMPS_LEVEL');
                        $stampLevelsProperty = BxCollection::getBasketItemPropertyByCode($basketPropertyCollection, 'STAMP_LEVELS');
                        $extendedAttributeCollection = $position->getExtendedAttribute();
                        if ($extendedAttributeCollection->isEmpty()) { // Если атрибут пустой, значит, обмен марок невозможен
                            $this->clearBasketItemStampsProperties($item);
                        } else {
                            // указание, что можно применить марки для скидки на этот товар

                            $extendedAttributeArray = $extendedAttributeCollection->map(static function (ExtendedAttribute $level) {
                                return [
                                    'key' => $level->getKey(),
                                    'value' => $level->getValue(),
                                ];
                            })->getValues();
                            $stampLevelsSerialized = $extendedAttributeArray ? serialize($extendedAttributeArray): false;
                            if ($stampLevelsProperty) {
                                $stampLevelsProperty->setField('VALUE', $stampLevelsSerialized);
                            } else {
                                $stampLevelsProperty = $basketPropertyCollection->createItem();
                                $stampLevelsProperty->setFields([
                                    'NAME' => 'STAMP_LEVELS',
                                    'CODE' => 'STAMP_LEVELS',
                                    'VALUE' => $stampLevelsSerialized,
                                ]);
                                $stampLevelsProperty->save();
                            }

                            $maxAvailableLevel = $this->stampService->getMaxAvailableLevel($extendedAttributeCollection, $activeStampsCount);
                            if ($maxAvailableLevel['key']) {
                                $maxAvailableLevelArray = $this->stampService->parseLevelKey($maxAvailableLevel['key']);
                            }

                            $usedStampsLevel = $this->basketService->getBasketItemPropertyValue($item, 'USED_STAMPS_LEVEL');
                            $usedStampsLevel = $usedStampsLevel ? unserialize($usedStampsLevel) : false;

                            if (!$maxAvailableLevelArray || $usedStampsLevel['stampsUsed'] > $maxAvailableLevelArray['discountStamps'] * $maxAvailableLevel['value']) {
                                $maxAvailableLevelSerialized = $maxAvailableLevel ? serialize($maxAvailableLevel): false;
                                //$this->basketService->setBasketItemPropertyValue($item, 'MAX_STAMPS_LEVEL', $maxAvailableLevelSerialized);
                                if ($maxStampsLevelProperty) {
                                    $maxStampsLevelProperty->setField('VALUE', $maxAvailableLevelSerialized);
                                } else {
                                    $maxStampsLevelProperty = $basketPropertyCollection->createItem();
                                    $maxStampsLevelProperty->setFields([
                                        'NAME' => 'MAX_STAMPS_LEVEL',
                                        'CODE' => 'MAX_STAMPS_LEVEL',
                                        'VALUE' => $maxAvailableLevelSerialized,
                                    ]);
                                    $maxStampsLevelProperty->save();
                                }
                            }
                           // $basketPropertyCollection->save(); при сохранении всех свойств, сохраняется свойство SUBSCRIBE_PRICE

                            //TODO set USE_STAMPS=false & MAX_STAMPS_LEVEL=false instead (или заменить на максимальный из тех уровней, на который хватит марок), если пользователь уже выбрал обмен марок у других товаров и на этот обмен марок не хватит
                        }

                        $item->setPropertyCollection($basketPropertyCollection);
                    }
                }
            });
        }

        if ($this->stampService::IS_STAMPS_OFFER_ACTIVE) {
            // Расчет, сколько марок уже выбрано для обмена в $item в текущей корзине
            $availableStamps = $activeStampsCount;
            foreach ($basket as $item) {
                $offerXmlId = explode('#', $item->getField('PRODUCT_XML_ID'))[1];
                if (!$offerXmlId || !($this->stampService->getExchangeRules($offerXmlId))) {
                    continue;
                }
                $basketPropertyCollection = $item->getPropertyCollection();
                $usedStampsLevelProperty = BxCollection::getBasketItemPropertyByCode($basketPropertyCollection, 'USED_STAMPS_LEVEL');
                //$maxStampsLevelProperty = BxCollection::getBasketItemPropertyByCode($basketPropertyCollection, 'MAX_STAMPS_LEVEL');
                if ($usedStampsLevelProperty && $usedStamps = unserialize($usedStampsLevelProperty->getField('VALUE'), null)['stampsUsed']) {
                    $availableStamps -= $usedStamps;
                }

    //            if ($useStampsProperty && $useStamps = $useStampsProperty->getField('VALUE')) {
    //                if ($maxStampsLevelProperty && $maxStampsLevel = unserialize($maxStampsLevelProperty->getField('VALUE'))) {
    //                    $availableStamps -= $this->stampService->parseLevelKey($maxStampsLevel['key'])['discountStamps'] * $maxStampsLevel['value'];
    //                } else {
    //                    $this->basketService->setBasketItemPropertyValue($item, 'USE_STAMPS', false); //TODO будет ли работать без $basketPropertyCollection->save() ?
    //                }
    //            }

            }

            // для отладки марок
            //dump('остается марок: ' . $availableStamps);

            foreach ($basket as $item) {
                $offerXmlId = explode('#', $item->getField('PRODUCT_XML_ID'))[1];
                if (!$offerXmlId || !($this->stampService->getExchangeRules($offerXmlId))) {
                    continue;
                }
                $basketCode = (int)str_replace('n', '', $item->getBasketCode());

                $basketPropertyCollection = $item->getPropertyCollection();
                $useStampsProperty = BxCollection::getBasketItemPropertyByCode($basketPropertyCollection, 'USE_STAMPS');
                $maxStampsLevelProperty = BxCollection::getBasketItemPropertyByCode($basketPropertyCollection, 'MAX_STAMPS_LEVEL');

                if (
                    //$maxStampsLevelProperty && ($maxStampsLevel = unserialize($maxStampsLevelProperty->getField('VALUE')))
                    //&&
                    (!$useStampsProperty || !$useStamps = $useStampsProperty->getField('VALUE'))
                ) {
                    $manzanaItems->map(function (ChequePosition $position) use ($basketCode, $item, $availableStamps, $basketPropertyCollection, $maxStampsLevelProperty) {
                        if ($position->getChequeItemNumber() === $basketCode) {
                            $extendedAttributeCollection = $position->getExtendedAttribute();
                            $maxAvailableLevel = $this->stampService->getMaxAvailableLevel($extendedAttributeCollection, $availableStamps);

                            $maxAvailableLevelSerialized = $maxAvailableLevel ? serialize($maxAvailableLevel): false;
                            //$this->basketService->setBasketItemPropertyValue($item, 'MAX_STAMPS_LEVEL', $maxAvailableLevelSerialized);
                            if ($maxStampsLevelProperty) {
                                $maxStampsLevelProperty->setField('VALUE', $maxAvailableLevelSerialized);
                            } else {
                                $maxStampsLevelProperty = $basketPropertyCollection->createItem();
                                $maxStampsLevelProperty->setFields([
                                    'NAME' => 'MAX_STAMPS_LEVEL',
                                    'CODE' => 'MAX_STAMPS_LEVEL',
                                    'VALUE' => $maxAvailableLevelSerialized,
                                ]);
                                $maxStampsLevelProperty->save();
                            }
                            // $basketPropertyCollection->save();   see line 355
                            $item->setPropertyCollection($basketPropertyCollection);
                        }
                    });
                }
                unset($maxStampsLevel, $useStamps);
            }

            // для отладки марок
    //        foreach ($basket as $item) {
    //            $basketPropertyCollection = $item->getPropertyCollection();
    //            $useStampsProperty = BxCollection::getBasketItemPropertyByCode($basketPropertyCollection, 'USE_STAMPS');
    //            $maxStampsLevelProperty = BxCollection::getBasketItemPropertyByCode($basketPropertyCollection, 'MAX_STAMPS_LEVEL');
    //            dump($item->getField('PRODUCT_XML_ID')
    //                . PHP_EOL . $maxStampsLevelProperty->getField('VALUE')
    //                . PHP_EOL . 'использовать марки: ' . ($useStampsProperty && $useStampsProperty->getField('VALUE') ? 'true' : 'false'));
    //        }

            // если не делать здесь сохранение корзины, то надо использовать $maxStampsLevelProperty->setField и $basketPropertyCollection->save() (см.выше)
            // upd: если делать здесь сохранение всей корзины, то начинается дублирование товаров (из-за разделения товаров при применении скидок, которое создает временные дубли с internalId=n1,n2 и т.д.).
            //      Поэтому использован вариант с сохранением propertyCollection
            //$basket->save();
        }
    }

    /**
     * @param SoftChequeResponse $response
     * @param string             $promocode
     *
     * @throws ManzanaPromocodeUnavailableException
     */
    public function checkPromocodeByResponse(SoftChequeResponse $response, string $promocode): void
    {
        $applied = false;

        if ($response->getCoupons()) {
            $applied = $response->getCoupons()->filter(function (Coupon $coupon) use ($promocode) {
                return $coupon->isApplied() && $coupon->getNumber() === $promocode;
            })->count() > 0;
        }

        if (!$applied) {
            throw new ManzanaPromocodeUnavailableException(
                \sprintf(
                    'Promocode %s is not found or unavailable in current context',
                    $this->promocode
                )
            );
        }
    }

    /**
     * @param SoftChequeResponse $response
     */
    private function saveCouponDiscount(SoftChequeResponse $response): void
    {
        $this->basketService->setPromocodeDiscount($response->getSumm() - $response->getSummDiscounted());
    }

    /**
     * @return float
     */
    public function getDiscount(): float
    {
        return $this->discount;
    }

    /**
     * @param int $discount
     *
     * @return Manzana
     */
    public function setDiscount(int $discount): Manzana
    {
        $this->discount = $discount;

        return $this;
    }

    /**
     * @return PersonalOffersService|object
     */
    protected function getPersonalOffersService()
    {
        if ($this->personalOffersService)
        {
            return $this->personalOffersService;
        }

        $this->personalOffersService = App::getInstance()->getContainer()->get('personal_offers.service');

        return $this->personalOffersService;
    }

    /**
     * @return float
     */
    public function getStampsToBeAdded(): float
    {
        return $this->stampsToBeAdded;
    }

    /**
     * @param float $stampsToBeAdded
     * @return Manzana
     */
    public function setStampsToBeAdded(float $stampsToBeAdded): Manzana
    {
        $this->stampsToBeAdded = $stampsToBeAdded;
        return $this;
    }

    /**
     * Очищает значения всех свойств, связанных с марками
     *
     * @param BasketItem $item
     */
    public function clearBasketItemStampsProperties(BasketItem $item): void
    {
        $this->basketService->setBasketItemPropertyValue($item, 'USE_STAMPS', false);
        $this->basketService->setBasketItemPropertyValue($item, 'USED_STAMPS_LEVEL', false);
        $this->basketService->setBasketItemPropertyValue($item, 'MAX_STAMPS_LEVEL', false);
        $this->basketService->setBasketItemPropertyValue($item, 'STAMP_LEVELS', false);
        $this->basketService->setBasketItemPropertyValue($item, 'CAN_USE_STAMPS', false);
    }
}
