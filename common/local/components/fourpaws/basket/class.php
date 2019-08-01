<?php

declare(strict_types=1);

namespace FourPaws\Components;

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\BasketItemCollection;
use Bitrix\Sale\Order;
use Bitrix\Sale\PriceMaths;
use CBitrixComponent;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Entity\BaseEntity;
use FourPaws\BitrixOrm\Model\ResizeImageDecorator;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\EcommerceBundle\Preset\Bitrix\SalePreset;
use FourPaws\EcommerceBundle\Service\GoogleEcommerceService;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\Helpers\DateHelper;
use FourPaws\PersonalBundle\Service\OrderSubscribeService;
use FourPaws\KioskBundle\Service\KioskService;
use FourPaws\SaleBundle\Discount\Gift;
use FourPaws\SaleBundle\Discount\Utils\Detach\Adder;
use FourPaws\SaleBundle\Discount\Utils\Manager;
use FourPaws\SaleBundle\Exception\InvalidArgumentException;
use FourPaws\SaleBundle\Helper\PriceHelper;
use FourPaws\SaleBundle\Repository\CouponStorage\CouponSessionStorage;
use FourPaws\SaleBundle\Repository\CouponStorage\CouponStorageInterface;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\SaleBundle\Service\OrderStorageService;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserService;
use RuntimeException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use FourPaws\SaleBundle\Enum\OrderStorage as OrderStorageEnum;

/** @noinspection AutoloadingIssuesInspection */
/** @noinspection EfferentObjectCouplingInspection */

/**
 * Class BasketComponent
 * @package FourPaws\Components
 */
class BasketComponent extends CBitrixComponent
{
    const GIFT_DOBROLAP_XML_ID = '3006635'; //FIXME вынести в SaleBundle

    /**
     * @var BasketService
     */
    public $basketService;
    /** @var array */
    public $offers;
    /**
     * @var DeliveryService
     */
    private $deliveryService;
    /**
     * @var UserService
     */
    private $currentUserService;
    /** @var array $images */
    private $images;
    /**
     * @var CouponSessionStorage
     */
    private $couponsStorage;
    /**
     * @var GoogleEcommerceService
     */
    private $ecommerceService;
    /**
     * @var OrderStorageService
     */
    private $orderStorageService;
    /**
     * @var SalePreset
     */
    private $ecommerceSalePreset;
    private $promoDescriptions = [];
    private $offer2promoMap = [];

    /**
     * BasketComponent constructor.
     *
     * @param CBitrixComponent|null $component
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     */
    public function __construct(CBitrixComponent $component = null)
    {
        parent::__construct($component);
        $container = Application::getInstance()->getContainer();

        $this->basketService = $container->get(BasketService::class);
        $this->currentUserService = $container->get(CurrentUserProviderInterface::class);
        $this->couponsStorage = $container->get(CouponStorageInterface::class);
        $this->deliveryService = $container->get(DeliveryService::class);
        $this->ecommerceService = $container->get(GoogleEcommerceService::class);
        $this->ecommerceSalePreset = $container->get(SalePreset::class);
        $this->orderStorageService = $container->get(OrderStorageService::class);
    }

    /** @noinspection PhpMissingParentCallCommonInspection
     *
     * @return void
     *
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws ApplicationCreateException
     * @throws Exception
     * @throws SystemException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws \InvalidArgumentException
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     */
    public function executeComponent(): void
    {
        /** @var Basket $basket */
        $basket = $this->arParams['BASKET'];
        if (null === $basket || !\is_object($basket) || !($basket instanceof Basket)) {
            $basket = $this->basketService->getBasket();
        }


        $this->arResult['BASKET'] = $basket;

        $this->arResult['USER'] = $user = $userId = null;
        $this->arResult['USER_ACCOUNT'] = null; // Это зачем???
        try {
            $user = $this->currentUserService->getCurrentUser();
            $this->arResult['USER'] = $user;
            $userId = $user->getId();
        } /** @noinspection BadExceptionsProcessingInspection */
        catch (NotAuthorizedException $e) {
        }

        // привязывать к заказу нужно для расчета скидок
        if (null === $order = $basket->getOrder()) {
            // в корзине надо всегда сбрасывать состояние подписки для пересчёта цен
            $storage = $this->orderStorageService->getStorage();
            if($storage->isSubscribe()){
                $storage->setSubscribe(false);
                $this->orderStorageService->updateStorage($storage, OrderStorageEnum::NOVALIDATE_STEP);
            }
            $order = Order::create(SITE_ID, $userId);
            $order->setBasket($basket);
            // но иногда он так просто не запускается
            if (!Manager::isExtendCalculated()) {
                $order->doFinalAction(true);
            }
        }

        $this->setItems($basket);

        // необходимо подгрузить подарки
        $this->loadPromoDescriptions();
        $this->setCoupon();
        if ($user) {
            $this->arResult['MAX_BONUS_SUM'] = $this->basketService->getMaxBonusesForPayment();
        }
        $this->arResult['POSSIBLE_GIFT_GROUPS'] = Gift::getPossibleGiftGroups($order);
        $this->arResult['POSSIBLE_GIFTS'] = Gift::getPossibleGifts($order);
        $this->calcTemplateFields();
        $this->calcSubscribeFields();
        $this->checkSelectedGifts();
        $this->arResult['SHOW_FAST_ORDER'] = $this->deliveryService->getCurrentDeliveryZone() !== $this->deliveryService::ZONE_4 && !KioskService::isKioskMode();
        $this->arResult['ECOMMERCE_VIEW_BASKET'] = $this->ecommerceService->renderScript(
            $this->ecommerceSalePreset->createEcommerceToCheckoutFromBasket($basket, 1, 'Просмотр корзины'),
            true
        );

        /** если авторизирован добавляем магнит */
        if ($user) { // костыль, если магнитик не добавился сразу после оплаты исходного заказа)
            $needAddDobrolapMagnet = $user->getGiftDobrolap();
            /** Если пользователю должны магнит */
            if ($needAddDobrolapMagnet == BaseEntity::BITRIX_TRUE || $needAddDobrolapMagnet == true || $needAddDobrolapMagnet == 1) {
                $magnetID = ElementTable::getList([
                    'select' => ['ID', 'XML_ID'],
                    'filter' => ['XML_ID' => static::GIFT_DOBROLAP_XML_ID, 'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS)],
                    'limit'  => 1,
                ])->fetch()['ID'];
                /** если магнит найден как оффер */
                if ($magnetID) {
                    /** @var BasketService $basketService */
                    $basketService = Application::getInstance()->getContainer()->get(BasketService::class);
                    $basketItem = $basketService->addOfferToBasket(
                        (int)$magnetID,
                        1,
                        [],
                        true,
                        $basket
                    );
                    /** если магнит успешно добавлен в корзину */
                    if ($basketItem->getId()) {
                        $userDB = new \CUser;
                        $fields = [
                            'UF_GIFT_DOBROLAP' => false
                        ];
                        $userDB->Update($userId, $fields);
                    }
                }
            }
        }

        $this->includeComponentTemplate($this->getPage());
    }

    /**
     * @return UserService
     */
    public function getCurrentUserService(): UserService
    {
        return $this->currentUserService;
    }

    /**
     *
     * @param BasketItem $basketItem
     * @param bool $onlyApplied
     *
     * @return array
     */
    public function getPromoLink(BasketItem $basketItem, bool $onlyApplied = false): array
    {
        $result = [];
        /**
         * @var BasketItemCollection $basketItemCollection
         * @var Order $order
         */
        $applyResult = $this->arResult['DISCOUNT_RESULT'];
        $basketDiscounts = $applyResult['RESULT']['BASKET'][$basketItem->getBasketCode()];
        if (!$basketDiscounts) {
            /** @var \Bitrix\Sale\BasketPropertyItem $basketPropertyItem */
            foreach ($basketItem->getPropertyCollection() as $basketPropertyItem) {
                if ($basketPropertyItem->getField('CODE') === 'DETACH_FROM') {
                    $basketDiscounts = $applyResult['RESULT']['BASKET'][$basketPropertyItem->getField('VALUE')];
                }
            }
        }

        if ($basketDiscounts) {
            /** @noinspection ForeachSourceInspection */
            foreach (\array_column($basketDiscounts, 'DISCOUNT_ID') as $fakeId) {
                if ($onlyApplied && \in_array($fakeId, Adder::getSkippedDiscountsFakeIds(), true)) {
                    continue;
                }
                if ($this->promoDescriptions[$applyResult['DISCOUNT_LIST'][$fakeId]['REAL_DISCOUNT_ID']]) {
                    $k = $applyResult['DISCOUNT_LIST'][$fakeId]['REAL_DISCOUNT_ID'];
                    $result[$k] = $this->promoDescriptions[$k];
                }
            }
        }
        if (
            !$onlyApplied
            &&
            $discountIds = $this->offer2promoMap[$this->getOffer((int)$basketItem->getProductId())->getXmlId()]
        ) {
            foreach ($discountIds as $id) {
                if (!$result[$id]) {
                    $result[$id] = $this->promoDescriptions[$id];
                }
            }
        }
        return $result;
    }

    /**
     * @return DeliveryService
     */
    public function getDeliveryService(): DeliveryService
    {
        return $this->deliveryService;
    }

    /**
     * @param int $offerId
     *
     * @return Offer|null
     */
    public function getOffer(int $offerId): ?Offer
    {
        if ($offerId <= 0) {
            return null;
        }
        if (!isset($this->offers[$offerId])) {
            $this->offers[$offerId] = OfferQuery::getById($offerId);
        }
        return $this->offers[$offerId];
    }

    /**
     * @param int $offerId
     *
     * @return ResizeImageDecorator|null
     */
    public function getImage(int $offerId): ?ResizeImageDecorator
    {
        if ($offerId <= 0) {
            return null;
        }

        if (!isset($this->images[$offerId])) {
            $offer = $this->getOffer($offerId);
            $image = null;
            if ($offer !== null) {
                $images = $offer->getResizeImages(110, 110);
                $this->images[$offerId] = $images->first();
            }
        }
        return $this->images[$offerId];
    }

    /**
     * @param Basket $basket
     *
     * @return Basket|bool
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws RuntimeException
     * @throws ApplicationCreateException
     * @throws ObjectNotFoundException
     * @throws SystemException
     * @throws Exception
     */
    private function setItems($basket)
    {
        $notAllowedItems = new ArrayCollection();
        $fastOrderClass = null;
        $this->arResult['OFFER_MIN_DELIVERY'] = [];
        $this->arResult['ONLY_PICKUP'] = [];
        $haveOrder = $basket->getOrder() instanceof Order;
        $deliveries = $this->getDeliveryService()->getByLocation();

        $delivery = null;
        foreach ($deliveries as $calculationResult) {
            if ($this->getDeliveryService()->isDelivery($calculationResult)) {
                $delivery = $calculationResult;
                break;
            }
        }

        /** @var BasketItem $basketItem */
        foreach ($basket->getBasketItems() as $basketItem) {
            if ($basketItem->getProductId() === 0) {
                /** удаляет непонятно что в корзине */
                if (!$haveOrder) {
                    $basketItem->delete();
                }
                continue;
            }
            $offer = $this->getOffer((int)$basketItem->getProductId());
            $useOffer = $offer instanceof Offer && $offer->getId() > 0;
            if (!$useOffer) {
                /** если нет офера удаляем товар из корзины */
                if (!$haveOrder) {
                    $basketItem->delete();
                }
                continue;
            }

            if ($basketItem->isDelay()) {
                $notAllowedItems->add($basketItem);
            } else {
                if ($basketItem->getPrice() && (
                        (null === $delivery) ||
                        !(clone $delivery)->setStockResult(
                            $this->getDeliveryService()->getStockResultForOffer(
                                $offer,
                                $delivery,
                                (int)$basketItem->getQuantity(),
                                $basketItem->getPrice()
                            )
                        )->isSuccess()
                    )
                ) {
                    $this->arResult['ONLY_PICKUP'][] = $offer->getId();
                }
            }

            if ($offer->isByRequest()) {
                if (null !== $delivery) {
                    $res = (clone $delivery)->setStockResult(
                        $this->getDeliveryService()->getStockResultForOffer(
                            $offer,
                            $delivery,
                            (int)$basketItem->getQuantity(),
                            $basketItem->getPrice()
                        )
                    );
                    if ($res->isSuccess()) {
                        $this->arResult['OFFER_MIN_DELIVERY'][$basketItem->getProductId()] = DateHelper::formatDate(
                            'XX',
                            $res->getDeliveryDate()->getTimestamp()
                        );
                    }
                }

                if (!$notAllowedItems->contains($basketItem)) {
                    $notAllowedItems->add($basketItem);
                }
            }
        }
        $this->arResult['NOT_ALLOWED_ITEMS'] = $notAllowedItems;

        return true;
    }

    /**
     *
     *
     * @throws \FourPaws\SaleBundle\Exception\InvalidArgumentException
     * @throws RuntimeException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     */
    private function checkSelectedGifts(): void
    {
        $this->arResult['SELECTED_GIFTS'] = [];
        if (\is_array($this->arResult['POSSIBLE_GIFT_GROUPS']) && !empty($this->arResult['POSSIBLE_GIFT_GROUPS'])) {
            foreach ($this->arResult['POSSIBLE_GIFT_GROUPS'] as $group) {
                if (\count($group) === 1) {
                    $group = current($group);
                } else {
                    throw new RuntimeException('TODO');
                }

                /** @noinspection PhpUndefinedMethodInspection */
                $this->arResult['SELECTED_GIFTS'][$group['discountId']] = $this->basketService
                    ->getAdder('gift')->getExistGifts($group['discountId'], true);
            }
        }
    }

    /**
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws ApplicationCreateException
     */
    private function calcTemplateFields(): void
    {
        $weight = $quantity = $basePrice = $price = 0;
        /** @var Basket $basket */
        $basket = $this->arResult['BASKET'];
        /** @var BasketItem $basketItem */
        $orderableBasket = $basket->getOrderableItems();

        foreach ($orderableBasket as $basketItem) {
            $itemQuantity = (int)$basketItem->getQuantity();
            $weight += (float)$basketItem->getWeight() * $itemQuantity;
            $quantity += $itemQuantity;
            //если не подарок
            if (!isset($basketItem->getPropertyCollection()->getPropertyValues()['IS_GIFT'])) {
                $basePrice += (float)$basketItem->getBasePrice() * $itemQuantity;
                $price += (float)$basketItem->getPrice() * $itemQuantity;
                // Слияние строчек с одинаковыми sku
                $offer = $this->getOffer((int)$basketItem->getProductId());
                if (!$offer || $offer->isByRequest()) {
                    continue;
                }
                $this->arResult['PRODUCT_QUANTITIES'][$basketItem->getProductId()] += $basketItem->getQuantity();
                /** @var BasketItem $tItem */
                foreach ($orderableBasket as $tItem) {
                    if (
                        (int)$basketItem->getProductId() === (int)$tItem->getProductId()
                        &&
                        $basketItem->getBasketCode() !== $tItem->getBasketCode()
                        &&
                        !$this->arResult['ROWS_MAP'][$tItem->getBasketCode()]
                        &&
                        !isset($tItem->getPropertyCollection()->getPropertyValues()['IS_GIFT'])
                    ) {
                        $this->arResult['ROWS_MAP'][$basketItem->getBasketCode()]['ROWS'][] = $tItem->getBasketCode();
                        $this->arResult['SKIP_ROWS'][] = $tItem->getBasketCode();
                    }
                }
            }
        }

        //Количества и цены для слияния
        foreach ($this->arResult['ROWS_MAP'] as $code => &$values) {
            $tItem = $basket->getItemByBasketCode($code);
            $values['TOTAL_PRICE'] = $tItem->getQuantity() * $tItem->getPrice();
            $values['BASE_PRICE'] = $tItem->getQuantity() * $tItem->getBasePrice();

            foreach ($values['ROWS'] as $rCode) {
                $tItem = $basket->getItemByBasketCode($rCode);
                $values['TOTAL_PRICE'] += $tItem->getQuantity() * $tItem->getPrice();
                $values['BASE_PRICE'] += $tItem->getQuantity() * $tItem->getBasePrice();
            }
        }
        unset($values);


        $this->arResult['BASKET_WEIGHT'] = $weight;
        $this->arResult['TOTAL_QUANTITY'] = $quantity;
        $this->arResult['TOTAL_DISCOUNT'] = PriceMaths::roundPrecision($basePrice - $price);
        $this->arResult['TOTAL_PRICE'] = $price;
        $this->arResult['TOTAL_BASE_PRICE'] = $basePrice;
    }

    /**
     * @throws ApplicationCreateException
     */
    private function calcSubscribeFields()
    {
        $subscribePrice = 0;
        /** @var Basket $basket */
        $basket = $this->arResult['BASKET'];
        /** @var BasketItem $basketItem */
        $orderableBasket = $basket->getOrderableItems();

        foreach ($orderableBasket as $basketItem) {
            if (!isset($basketItem->getPropertyCollection()->getPropertyValues()['IS_GIFT'])) {
                $offer = $this->getOffer((int)$basketItem->getProductId());
                if (!$offer) {
                    continue;
                }

                $priceSubscribe = $offer->getSubscribePrice() * $basketItem->getQuantity();
                $priceDefault = $basketItem->getPrice() * $basketItem->getQuantity();
                $price = $priceDefault;
                if($priceSubscribe < $priceDefault){
                    $price = $priceSubscribe;
                }

                $subscribePrice += $price;
            }
        }

        $this->arResult['SUBSCRIBE_PRICE'] = $subscribePrice;
        $this->arResult['SUBSCRIBE_ALLOWED'] = true;
    }

    /**
     * @return string
     */
    private function getPage(): string
    {
        $page = '';
        /** @var Basket $basket */
        $basket = $this->arResult['BASKET'];
        /** @var Order $order */
        $order = $basket->getOrder();
        if (!Manager::isOrderNotEmpty($order)) {
            $page = 'empty';
        }
        return $page;
    }

    /**
     * Подгружает названия и ссылки на описания акций по XML_ID
     */
    private function loadPromoDescriptions(): void
    {
        /** @var Basket $basket */
        $basket = $this->arResult['BASKET'];
        /** @var Order $order */
        $order = $basket->getOrder();
        $applyResult = $order->getDiscount()->getApplyResult(true);
        $this->arResult['DISCOUNT_RESULT'] = $applyResult;

        $offerXmlIds = [];
        /** @var BasketItem $basketItem */
        foreach ($basket->getBasketItems() as $basketItem) {
            if ($offer = $this->getOffer((int)$basketItem->getProductId())) {
                $offerXmlIds[] = $offer->getXmlId();
            }
        }
        $offerXmlIds = array_flip(array_flip($offerXmlIds));

        if (\is_array($applyResult['DISCOUNT_LIST']) || $offerXmlIds) {
            $discountMap = \array_column($applyResult['DISCOUNT_LIST'], 'REAL_DISCOUNT_ID', 'ID');
            $res = \CIBlockElement::GetList(
                ['ID' => 'ASC'],
                [
                    [
                        'LOGIC' => 'OR',
                        'PROPERTY_PRODUCTS' => $offerXmlIds,
                        'PROPERTY_BASKET_RULES' => \array_values($discountMap),
                    ],
                    'ACTIVE' => 'Y',
                    'ACTIVE_DATE' => 'Y',
                    'IBLOCK_CODE' => IblockCode::SHARES,
                    'IBLOCK_TYPE' => IblockType::PUBLICATION,
                ],
                false,
                false,
                ['NAME', 'DETAIL_PAGE_URL', 'PROPERTY_BASKET_RULES', 'PROPERTY_PRODUCTS']
            );
            /** @noinspection PhpAssignmentInConditionInspection */
            while ($elem = $res->GetNext()) {
                if (\is_array($elem['PROPERTY_BASKET_RULES_VALUE'])) {
                    // описания акций
                    foreach ($elem['PROPERTY_BASKET_RULES_VALUE'] as $ruleId) {
                        $this->promoDescriptions[$ruleId] = [
                            'url' => $elem['DETAIL_PAGE_URL'],
                            'name' => $elem['NAME'],
                        ];
                    }
                    // связки товаров и акций
                    foreach ($elem['PROPERTY_PRODUCTS_VALUE'] as $offerXmlId) {
                        if ($this->offer2promoMap[$offerXmlId]) {
                            $this->offer2promoMap[$offerXmlId]
                                = array_merge($this->offer2promoMap[$offerXmlId], $elem['PROPERTY_BASKET_RULES_VALUE']);
                        } else {
                            $this->offer2promoMap[$offerXmlId] = $elem['PROPERTY_BASKET_RULES_VALUE'];
                        }
                    }
                }
            }
            //array unique
            foreach ($this->offer2promoMap as &$value) {
                $value = array_flip(array_flip($value));
            }
            unset($value);
        }
    }

    /**
     * Set coupon and coupon discount
     *
     * @return void
     */
    private function setCoupon(): void
    {
        $this->arResult['COUPON'] = $this->couponsStorage->getApplicableCoupon() ?? '';
        $this->arResult['COUPON_DISCOUNT'] = !empty($this->arResult['COUPON']) ? $this->basketService->getPromocodeDiscount() : 0;
    }
}
