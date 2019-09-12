<?php

/**
 * Created by PhpStorm.
 * Date: 14.03.2018
 * Time: 23:00
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Discount\Utils\Detach;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\App\Application as App;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Sale\BasketItem;
use Exception;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\Helpers\BxCollection;
use FourPaws\LocationBundle\LocationService;
use FourPaws\PersonalBundle\Service\OrderSubscribeService;
use FourPaws\PersonalBundle\Service\StampService;
use FourPaws\SaleBundle\Discount\Utils\AdderInterface;
use FourPaws\SaleBundle\Discount\Utils\BaseDiscountPostHandler;
use FourPaws\SaleBundle\Exception\BitrixProxyException;
use FourPaws\SaleBundle\Exception\InvalidArgumentException;
use FourPaws\SaleBundle\Exception\RuntimeException;
use FourPaws\SaleBundle\Helper\PriceHelper;
use FourPaws\SaleBundle\Service\BasketRulesService;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\SaleBundle\Service\OrderStorageService;
use WebArch\BitrixCache\BitrixCache;

/**
 * Class Adder
 * @package FourPaws\SaleBundle\Discount\Utils\Detach
 */
class Adder extends BaseDiscountPostHandler implements AdderInterface
{
    public static $skippedDiscountsFakeIds = [];

    private static $excludedDiscountsFakeIds = [];
    private static $excludedDiscountsIds = null;

    public static $regionalDiscounts = [];

    /**
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentException
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws BitrixProxyException
     * @throws ObjectNotFoundException
     * @throws LoaderException
     * @throws ArgumentOutOfRangeException
     * @throws Exception
     */
    public function processOrder(): void
    {
        /** @var StampService $stampService */
        $stampService = App::getInstance()->getContainer()->get(StampService::class);

        /**
         * 1. Региональные скидки
         * 2. количества и свойства
         * 3. PRICE и DISCOUNT_PRICE
         */
        //todo Вероятно стоит сначала целиком разобрать резалт, а потом действовать
        if (!$discountBase = $this->order->getDiscount()) {
            return;
        }

        $this->applySimpleDiscounts();

        $applyResult = $discountBase->getApplyResult(true);
        $skippedDiscounts = $this->getLowDiscounts($applyResult['RESULT']['BASKET']);
        $skippedDiscounts = array_merge($skippedDiscounts, $this->getExcludedDiscounts($applyResult['DISCOUNT_LIST']));

        if ($stampService::IS_STAMPS_OFFER_ACTIVE) {
            $basket = $this->order->getBasket();
            /** @var BasketItem $basketItem */
            foreach ($basket as $basketItem) {
                $useStamps = $this->basketService->getBasketItemPropertyValue($basketItem, 'USE_STAMPS');
                $usedStampsLevel = unserialize($this->basketService->getBasketItemPropertyValue($basketItem, 'USED_STAMPS_LEVEL'));
                if ($useStamps && $usedStampsLevel) {
                    // Костыли, чтобы скидка не применялась повторно. Лучше вынести флаг о том, что скидка за марки применена, например, в отдельное свойство товара в корзине
                    $basketItem->setPrice($basketItem->getBasePrice());
                    $applyResult['PRICES']['BASKET'][$basketItem->getId()]['PRICE'] = $basketItem->getBasePrice();

                    $params = [
                        'discountType' => "DETACH",
                        'params' => [
                            'apply_count' => $usedStampsLevel['productQuantity'],
                            'discount_value' => $usedStampsLevel['discountValue'],
                            'percent' => $usedStampsLevel['discountType'] === 'P',
                        ]
                    ];
                    $applyResult['RESULT']['BASKET'][$basketItem->getId()] = [[ //FIXME сейчас заменяем все остальные скидки, которые доступны по этому товару. После релиза в перспективе логика должна быть переделана с возможностью применения разных скидок одновременно
                        //'DISCOUNT_ID' => 0,
                        'COUPON_ID' => '',
                        'APPLY' => 'Y',
                        'DESCR' => json_encode($params)
                    ]];
                } elseif (
                    ($xmlId = explode('#', $basketItem->getField('PRODUCT_XML_ID'))[1])
                    && array_key_exists($xmlId, $stampService::EXCHANGE_RULES)
                ) {
                    // Костыли, для отмены скидки. Лучше вынести флаг о том, что скидка за марки применена, например, в отдельное свойство товара в корзине
                    $basketItem->setPrice($basketItem->getBasePrice());
                    $applyResult['PRICES']['BASKET'][$basketItem->getId()]['PRICE'] = $basketItem->getBasePrice();
                }
            }
            unset ($params);
        }

        if (is_iterable($applyResult['RESULT']['BASKET'])) {
            foreach ($applyResult['RESULT']['BASKET'] as $basketCode => $discounts) {
                if (is_iterable($discounts)) {
                    foreach ($discounts as $discount) {
                        if (
                            ($params = json_decode($discount['DESCR'], true))
                            && \is_array($params)
                            && isset($params['discountType'])
                            && $params['discountType'] === 'DETACH'
                        ) {
                            if (\in_array((int)$discount['DISCOUNT_ID'], $skippedDiscounts, true)) {
                                continue;
                            }

                            $applyCount = (int)$params['params']['apply_count'];
                            $discountValue = (int)$params['params']['discount_value'];
                            $isPercent = (bool)$params['params']['percent'];

                            /** @var BasketItem $basketItem */
                            if (
                                $applyCount
                                && null !== $basketItem = $this->order->getBasket()->getItemByBasketCode($basketCode)
                            ) {
                                if ((int)$basketItem->getQuantity() > $applyCount) {
                                    //Детачим
                                    $initialPrice = $basketItem->getPrice();
                                    if ($isPercent) {
                                        $price = (100 - $discountValue) * $basketItem->getPrice() / 100;
                                    } else {
                                        $price = $basketItem->getPrice() - $discountValue;
                                        if ($price < 0) {
                                            $price = 0;
                                        }
                                    }
                                    $price = PriceHelper::roundPrice($price);
                                    $oldQuantity = $basketItem->getQuantity();
                                    $basketItem->setField('QUANTITY', $applyCount);
                                    $basketItem->setField('PRICE', $price);
                                    $basketItem->setField('DISCOUNT_PRICE', $basketItem->getBasePrice() - $price);
                                    $basketItem->setField('CUSTOM_PRICE', 'Y');

                                    $fields = [
                                        'PROPS' => [
                                            [
                                                'NAME' => 'Отделено от элемента корзины',
                                                'CODE' => 'DETACH_FROM',
                                                'VALUE' => $basketCode,
                                                'SORT' => 100,
                                            ],
                                            [
                                                'NAME' => 'Название скидки',
                                                'CODE' => 'SHARE_NAME',
                                                'VALUE' => $applyResult['DISCOUNT_LIST'][$discount['DISCOUNT_ID']]['NAME'],
                                                'SORT' => 200,
                                            ],
                                        ]
                                    ];
                                    /**
                                     * вызывает то же событие, но у нас обработчик заблокирован пока выполняется
                                     */
                                    $newBasketItem = $this->basketService->addOfferToBasket(
                                        $basketItem->getProductId(),
                                        $oldQuantity - $applyCount,
                                        $fields,
                                        false,
                                        $this->order->getBasket()
                                    );
                                    $newBasketItem->setField('PRICE', $initialPrice);
                                    $newBasketItem->setField(
                                        'DISCOUNT_PRICE',
                                        $basketItem->getBasePrice() - $initialPrice
                                    );
                                    $newBasketItem->setFieldNoDemand('FUSER_ID', $basketItem->getFUserId());

                                } elseif ((int)$basketItem->getQuantity() === (int)$params['params']['apply_count']) {
                                    //Просто проставляем поля
                                    if ($isPercent) {
                                        $price = (100 - $discountValue) * $basketItem->getPrice() / 100;
                                    } else {
                                        $price = $basketItem->getPrice() - $discountValue;
                                        if ($price < 0) {
                                            $price = 0;
                                        }
                                    }
                                    /** @noinspection PhpInternalEntityUsedInspection */
                                    $basketItem->setFieldsNoDemand([
                                        'PRICE' => $price,
                                        'DISCOUNT_PRICE' => $basketItem->getBasePrice() - $price,
                                        'CUSTOM_PRICE' => 'Y'
                                    ]);
                                } else {
                                    throw new RuntimeException('Impossible exception');
                                }
                            }
                        }
                    }
                }
            }
        }

        $this->applySubscribeDiscounts();
    }

    /**
     * Возвращает массив фэйков айдишников скидок, которые не нужно применять,
     * т.к. они пересакаются с другими и менее выгодны
     *
     * @param array $basketApplyResult
     *
     * @return array
     */
    protected function getLowDiscounts(array $basketApplyResult): array
    {
        $result = [];
        // Найдем конфликтующие скидки
        $conflicts = [];
        /**
         * @var $basketId
         * @var array $discounts
         */
        foreach ($basketApplyResult as $basketId => $discounts) {
            if (\is_iterable($discounts)) {
                $currentStepIds = [];
                foreach ($discounts as $discount) {
                    if (
                        ($params = \json_decode($discount['DESCR'], true))
                        && \is_array($params)
                        && isset($params['discountType'])
                        && $params['discountType'] === 'DETACH'
                    ) {
                        $currentStepIds[$discount['DISCOUNT_ID']] = true;
                    }
                }
                if (\count($currentStepIds) > 1) {
                    foreach ($currentStepIds as $id => $v) {
                        $t = $currentStepIds;
                        unset($t[$id]);
                        if (isset($conflicts[$id])) {
                            /** @noinspection SlowArrayOperationsInLoopInspection */
                            $conflicts[$id] = \array_merge($conflicts[$id], \array_keys($t));
                        } else {
                            $conflicts[$id] = \array_keys($t);
                        }
                    }
                }
            }
        }

        if (!empty($conflicts)) {
            $conflicts = \array_map(
                function ($e) {
                    return \array_flip(\array_flip($e));
                },
                $conflicts
            );
        }

        // теперь узнаем какую скидку в рублях дает каждая из конфликтных акций
        $promoDiscounts = [];
        foreach ($conflicts as $currentId => $refuseIds) {
            foreach ($basketApplyResult as $basketId => $discounts) {
                if (\is_iterable($discounts)) {
                    foreach ($discounts as $discount) {
                        if (
                            ($params = json_decode($discount['DESCR'], true))
                            && \is_array($params)
                            && isset($params['discountType'])
                            && $params['discountType'] === 'DETACH'
                        ) {
                            if (\in_array((int)$discount['DISCOUNT_ID'], $refuseIds, true)) {
                                continue;
                            }
                            $applyCount = (int)$params['params']['apply_count'];
                            $percent = (int)$params['params']['discount_value'];
                            if ($applyCount && null !== $basketItem = $this->order->getBasket()->getItemById($basketId)) {
                                $promoDiscounts[$currentId] += $basketItem->getPrice() / 100 * $percent * $applyCount;
                            }
                        }
                    }
                }
            }
        }

        /**
         * @todo вероятны баги при множественном пересечении. Сейчас мы не применяем акции, которые мешают максимальной,
         * но могут быть же и другие пересекающиеся акции
         */

        if (!empty($promoDiscounts)) {
            $bestDiscount = \array_search(\max($promoDiscounts), $promoDiscounts, true);
            /** @var $bestDiscount int */
            $result = $conflicts[$bestDiscount];
        }
        self::setSkippedDiscountsFakeIds($result);
        return $result;
    }

    /**
     * @param array $discountList
     * @param array $skippedDiscounts
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    protected function getExcludedDiscounts(array $discountList)
    {
        $excludedDiscounts = [];

        /** @var LocationService $locationService */
        $locationService = App::getInstance()->getContainer()->get('location.service');
        /** @var BasketRulesService $BasketRulesService */
        $basketRulesService = App::getInstance()->getContainer()->get(BasketRulesService::class);

        $regionCode = $locationService->getCurrentRegionCode();
        $regionalDiscounts = $basketRulesService->getRegionalDiscounts();

        foreach($discountList as $discount){
            if(!empty($regionalDiscounts[$discount['REAL_DISCOUNT_ID']]) && !in_array($regionCode, $regionalDiscounts[$discount['REAL_DISCOUNT_ID']])){
                $excludedDiscounts[$discount['ID']] = $discount['REAL_DISCOUNT_ID'];
            }
        }
        self::setExcludedDiscountsFakeIds(array_keys($excludedDiscounts));
        return count($excludedDiscounts) > 0 ? array_keys($excludedDiscounts) : [];
    }

    /**
     * @return array
     */
    public static function getSkippedDiscountsFakeIds(): array
    {
        return self::$skippedDiscountsFakeIds;
    }

    /**
     * @param array $skippedDiscountsFakeIds
     */
    public static function setSkippedDiscountsFakeIds(array $skippedDiscountsFakeIds): void
    {
        self::$skippedDiscountsFakeIds = array_flip(array_flip(array_merge(
            $skippedDiscountsFakeIds, self::$skippedDiscountsFakeIds
        )));
    }

    /**
     * @return array
     */
    public static function getExcludedDiscountsFakeIds(): array
    {
        return self::$excludedDiscountsFakeIds;
    }

    /**
     * @param array $skippedDiscountsFakeIds
     */
    public static function setExcludedDiscountsFakeIds(array $skippedDiscountsFakeIds): void
    {
        self::$excludedDiscountsFakeIds = array_flip(array_flip(array_merge(
            $skippedDiscountsFakeIds, self::$excludedDiscountsFakeIds
        )));
    }

    /**
     * @return array
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     */
    public static function getExcludedDiscountsIds(): array
    {
        if(null === self::$excludedDiscountsIds) {
            $excludedDiscounts = [];

            /** @var LocationService $locationService */
            $locationService = App::getInstance()->getContainer()->get('location.service');
            /** @var BasketRulesService $basketRulesService */
            $basketRulesService = App::getInstance()->getContainer()->get(BasketRulesService::class);

            $regionCode = $locationService->getCurrentRegionCode();
            $regionalDiscounts = $basketRulesService->getRegionalDiscounts();

            foreach($regionalDiscounts as $discountId => $regions){
                if(!in_array($regionCode, $regions)){
                    $excludedDiscounts[] = $discountId;
                }
            }
            self::setExcludedDiscountsIds($excludedDiscounts);
        }

        return self::$excludedDiscountsIds;
    }

    /**
     * @param array $skippedDiscountsIds
     */
    public static function setExcludedDiscountsIds(array $skippedDiscountsIds): void
    {
        self::$excludedDiscountsIds = $skippedDiscountsIds;
    }

    /**
     * Применение региональных простых скидок
     *
     * @throws ArgumentOutOfRangeException
     */
    private function applySimpleDiscounts()
    {
        /** @var BasketItem $basketItem */
        foreach ($this->order->getBasket() as $basketItem){
            if($percent = $this->basketService->getBasketItemPropertyValue($basketItem, Offer::SIMPLE_SHARE_DISCOUNT_CODE)) {
                $price = $basketItem->getBasePrice() * ((100 - $percent)/100);
            } else {
                $price = $this->basketService->getBasketItemPropertyValue($basketItem, Offer::SIMPLE_SHARE_SALE_CODE);
            }

            if($price > 0 && $price != $basketItem->getPrice()){
                $basketItem->setFieldsNoDemand([
                    'PRICE' => $price,
                    'DISCOUNT_PRICE' => $basketItem->getBasePrice() - $price,
                    'CUSTOM_PRICE' => 'Y'
                ]);
            }
        }
    }

    /**
     * Установка скидки по подписке на доставку
     *
     * @throws ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \FourPaws\App\Exceptions\ApplicationCreateException
     * @throws \FourPaws\SaleBundle\Exception\OrderStorageSaveException
     */
    private function applySubscribeDiscounts()
    {
        /** @var OrderStorageService $storageService */
        $storageService = App::getInstance()->getContainer()->get(OrderStorageService::class);
        /** @var OrderSubscribeService $orderSubscribeService */
        $orderSubscribeService = App::getInstance()->getContainer()->get('order_subscribe.service');

        $storage = $storageService->getStorage();
        $offerCollection = $this->basketService->getOfferCollection();

        /** @var BasketItem $basketItem */
        foreach ($this->order->getBasket() as $basketItem){
            /** @var Offer $offer */
            $offer = $offerCollection->getById($basketItem->getProductId());
            if(!$offer){
                continue;
            }

            $percent = $offer->getSubscribeDiscount();
            if($percent <= 0){
                continue;
            }

            $priceSubscribe = $offer->getSubscribePrice() * $basketItem->getQuantity();
            $priceDefault = $basketItem->getPrice() * $basketItem->getQuantity();
            $isSubscribeActive = $this->basketService->getBasketItemPropertyValue($basketItem, "SUBSCRIBE_PRICE");

            if($storage->isSubscribe() && !$isSubscribeActive && $priceSubscribe <= $priceDefault){
                $basketItem->setFieldsNoDemand([
                    'PRICE' => $offer->getSubscribePrice(),
                    'DISCOUNT_PRICE' => $basketItem->getBasePrice() - $offer->getSubscribePrice(),
                    'CUSTOM_PRICE' => 'Y'
                ]);
                $this->basketService->setBasketItemPropertyValue($basketItem, "SUBSCRIBE_PRICE", true);
            }
            else if(!$storage->isSubscribe() && $isSubscribeActive){
                $price = $orderSubscribeService->countSubscribePrice($basketItem->getPrice(), $percent, true);
                $basketItem->setFieldsNoDemand([
                    'PRICE' => $price,
                    'DISCOUNT_PRICE' => $basketItem->getBasePrice() - $price,
                    'CUSTOM_PRICE' => 'Y'
                ]);
                $this->basketService->setBasketItemPropertyValue($basketItem, "SUBSCRIBE_PRICE", false);
            }
        }
    }
}
