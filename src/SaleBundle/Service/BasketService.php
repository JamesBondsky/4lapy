<?php
declare(strict_types=1);

namespace FourPaws\SaleBundle\Service;

use Adv\Bitrixtools\Tools\BitrixUtils;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Catalog\Product\CatalogProvider;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\Result as MainResult;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\BasketItemCollection;
use Bitrix\Sale\BasketPropertyItem;
use Bitrix\Sale\Compatible\DiscountCompatibility;
use Bitrix\Sale\Internals\BasketTable;
use Bitrix\Sale\Order;
use Bitrix\Sale\Result;
use Exception;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\External\Manzana\Exception\ExecuteException;
use FourPaws\External\ManzanaPosService;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\LocationBundle\LocationService;
use FourPaws\SaleBundle\Discount\Gift;
use FourPaws\SaleBundle\Discount\Utils;
use FourPaws\SaleBundle\Discount\Utils\AdderInterface;
use FourPaws\SaleBundle\Discount\Utils\CleanerInterface;
use FourPaws\SaleBundle\Exception\BitrixProxyException;
use FourPaws\SaleBundle\Exception\InvalidArgumentException;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use WebArch\BitrixCache\BitrixCache;

/** @noinspection EfferentObjectCouplingInspection */

/**
 * Class BasketService
 * @package FourPaws\SaleBundle\Service
 */
class BasketService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /** Оплата бонусами до 90% заказа */
    public const MAX_BONUS_PAYMENT = 0.9;
    /** @var Basket */
    private $basket;
    private $basketProductIds = [];
    /** @var CurrentUserProviderInterface */
    private $currentUserProvider;
    /** @var OfferCollection */
    private $offerCollection;
    /** @var ManzanaPosService */
    private $manzanaPosService;
    /** @todo КОСТЫЛЬ! УБРАТЬ В КУПОНЫ */
    private $promocodeDiscount = 0.0;
    private $fUserId;

    /**
     * BasketService constructor.
     *
     * @param CurrentUserProviderInterface $currentUserProvider
     * @param ManzanaPosService            $manzanaPosService
     */
    public function __construct(
        CurrentUserProviderInterface $currentUserProvider,
        ManzanaPosService $manzanaPosService
    ) {
        $this->currentUserProvider = $currentUserProvider;
        $this->manzanaPosService = $manzanaPosService;
    }

    /**
     * @param int         $offerId
     * @param int|null    $quantity
     * @param array       $rewriteFields
     * @param bool        $save
     * @param Basket|null $basket
     *
     * @throws ArgumentNullException
     * @throws ArgumentException
     * @throws InvalidArgumentException
     * @throws BitrixProxyException
     * @throws ObjectNotFoundException
     * @throws LoaderException
     * @throws Exception
     *
     * @return BasketItem
     */
    public function addOfferToBasket(
        int $offerId,
        int $quantity = 1,
        array $rewriteFields = [],
        bool $save = true,
        ?Basket $basket = null
    ): BasketItem {
        if ($offerId < 1) {
            throw new InvalidArgumentException('Неверный ID товара');
        }
        if ($quantity < 1) {
            $quantity = 1;
        }
        $fields = [
            'PRODUCT_ID'             => $offerId,
            'QUANTITY'               => $quantity,
            'MODULE'                 => 'catalog',
            'PRODUCT_PROVIDER_CLASS' => CatalogProvider::class,
        ];
        if ($rewriteFields) {
            /** @noinspection AdditionOperationOnArraysInspection */
            $fields = $rewriteFields + $fields;
        }

        $basket = $basket instanceof Basket ? $basket : $this->getBasket();

        $oldBasketCodes = [];
        /** @var BasketItem $basketItem */
        foreach ($basket->getBasketItems() as $basketItem) {
            $oldBasketCodes[] = $basketItem->getBasketCode();
        }

        $result = \Bitrix\Catalog\Product\Basket::addProductToBasketWithPermissions(
            $basket,
            $fields,
            $this->getContext()
        );

        if ($result->isSuccess()) {
            $basketItem = $result->getData()['BASKET_ITEM'];
        } else {
            // проверяем не специально ли было запорото
            $basketItem = $this->checkErrorActual($result, $basket, $oldBasketCodes);
            if($basketItem === null){
                throw new BitrixProxyException($result);
            }
        }
        if ($save) {
            $basketItem->save();
            //всегда перегружаем из-за подарков
            $this->setBasketIds();
            if (!\in_array($basketItem->getProductId(), $this->basketProductIds, true)) {
                $this->basketProductIds[] = $basketItem->getProductId();
            }
        }

        return $basketItem;
    }

    /**
     * @param int         $offerId
     * @param int|null    $quantity
     * @param array       $rewriteFields
     * @param bool        $save
     * @param Basket|null $basket
     *
     * @throws ArgumentNullException
     * @throws ArgumentException
     * @throws InvalidArgumentException
     * @throws BitrixProxyException
     * @throws ObjectNotFoundException
     * @throws LoaderException
     * @throws Exception
     *
     * @return BasketItem
     */
    public function addOfferToBasketWithoutPermission(
        int $offerId,
        int $quantity = 1,
        array $rewriteFields = [],
        bool $save = true,
        ?Basket $basket = null
    ): BasketItem {
        if ($offerId < 1) {
            throw new InvalidArgumentException('Неверный ID товара');
        }
        if ($quantity < 1) {
            $quantity = 1;
        }
        $fields = [
            'PRODUCT_ID'             => $offerId,
            'QUANTITY'               => $quantity,
            'MODULE'                 => 'catalog',
            'PRODUCT_PROVIDER_CLASS' => CatalogProvider::class,
            'USE_MERGE'              => 'Y',
        ];
        if ($rewriteFields) {
            /** @noinspection AdditionOperationOnArraysInspection */
            $fields = $rewriteFields + $fields;
        }

        $basket = $basket instanceof Basket ? $basket : $this->getBasket();

        $oldBasketCodes = [];
        /** @var BasketItem $basketItem */
        foreach ($basket->getBasketItems() as $basketItem) {
            $oldBasketCodes[] = $basketItem->getBasketCode();
        }

        $result = \Bitrix\Catalog\Product\Basket::addProductToBasket(
            $basket,
            $fields,
            $this->getContext()
        );

        if ($result->isSuccess()) {
            $basketItem = $result->getData()['BASKET_ITEM'];
        } else {
            // проверяем не специально ли было запорото
            $basketItem = $this->checkErrorActual($result, $basket, $oldBasketCodes);
            if($basketItem === null){
                throw new BitrixProxyException($result);
            }
        }
        if ($save) {
            $basketItem->save();
            //всегда перегружаем из-за подарков
            $this->setBasketIds();
            if (!\in_array($basketItem->getProductId(), $this->basketProductIds, true)) {
                $this->basketProductIds[] = $basketItem->getProductId();
            }
        }

        return $basketItem;
    }

    /**
     * @param int         $offerId
     * @param int|null    $quantity
     * @param array       $rewriteFields
     * @param bool        $save
     * @param Basket|null $basket
     *
     * @return bool
     * @throws ArgumentTypeException
     * @throws ArgumentOutOfRangeException
     * @throws NotSupportedException
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws \Exception
     */
    public function addOfferToBasketCustom(
        int $offerId,
        int $quantity = 1,
        array $rewriteFields = [],
        bool $save = true,
        ?Basket $basket = null
    ): bool {
        if ($basket === null) {
            $basket = $this->getBasket();
        }
        if ($quantity < 1) {
            $quantity = 1;
        }
        $returnRes = true;
        /** @var BasketItem $basketItem */
        $props = [];
        if (!empty($rewriteFields['PROPS'])) {
            $props = $rewriteFields['PROPS'];
            unset($rewriteFields['PROPS']);
        }

        $oldBasketCodes = [];
        /** @var BasketItem $basketItem */
        foreach ($basket->getBasketItems() as $basketItem) {
            $oldBasketCodes[] = $basketItem->getBasketCode();
        }

        $basketItem = $basket->getExistsItem('catalog', $offerId, $props);
        if ($basketItem === null) {
            $basketItem = $basket->createItem('catalog', $offerId);
        }
        $result = $basketItem->setField('QUANTITY', $quantity);
        $continue = true;
        if (!$result->isSuccess()) {
            // проверяем не специально ли было запорото
//            $basketItem = $this->checkErrorActual($result, $basket, $oldBasketCodes);
//            if($basketItem === null){
                return false;
//            }
            $continue = false;
        }
        if($continue) {
            if (!empty($rewriteFields)) {
                $oldVals = $basketItem->getFields()->getValues();
                if(array_key_exists('QUANTITY', $oldVals)){
                    unset($oldVals['QUANTITY']);
                }
                $res = $basketItem->setFields(array_merge($oldVals, $rewriteFields));
                if (!$res->isSuccess()) {
                    $returnRes = false;
                }
            }
            if ($returnRes && !empty($props)) {
                $propertyCollection = $basketItem->getPropertyCollection();
                foreach ($props as $prop) {
                    $value = $propertyCollection->createItem();
                    $value->setFields($prop);
                    $propertyCollection->addItem($value);
                }
            }
        }
        if ($returnRes && $save) {
            $res = $basketItem->save();
            return $res->isSuccess();
        }
        return $returnRes;
    }

    /**
     * @param int $basketId
     *
     * @throws ArgumentNullException
     * @throws ArgumentException
     * @throws ObjectNotFoundException
     * @throws BitrixProxyException
     * @throws NotFoundException
     * @throws InvalidArgumentException
     * @throws Exception
     *
     * @return bool
     */
    public function deleteOfferFromBasket(int $basketId): bool
    {
        if ($basketId < 1) {
            throw new InvalidArgumentException('Wrong $basketId');
        }
        /** @var BasketItem $basketItem */
        $basketItem = $this->getBasket()->getItemById($basketId);
        if (null === $basketItem) {
            throw new NotFoundException('Не найден элемент корзины');
        }
        $result = $basketItem->delete();
        if (!$result->isSuccess()) {
            // проверяем не специально ли было запорото
            $found = false;
            foreach ($result->getErrors() as $error) {
                if ($error->getCode() === 'SALE_EVENT_ON_BEFORE_SALEORDER_FINAL_ACTION_ERROR') {
                    $found = true;
                }
            }
            if (!$found) {
                throw new BitrixProxyException($result);
            }
        }
        $res = BasketTable::deleteWithItems($basketItem->getId())->isSuccess();
        if ($res) {
            //всегда перегружаем из-за подарков
            $this->setBasketIds();
        }
        return $res;
    }

    /**
     * @param int      $basketId
     * @param int|null $quantity
     *
     * @throws Exception
     * @throws BitrixProxyException
     * @throws NotFoundException
     * @throws InvalidArgumentException
     * @throws ArgumentOutOfRangeException
     *
     * @return bool
     */
    public function updateBasketQuantity(int $basketId, ?int $quantity = null): bool
    {
        if ($quantity < 1) {
            throw new InvalidArgumentException('Wrong $quantity');
        }

        if ($basketId < 1) {
            throw new InvalidArgumentException('Wrong $basketId');
        }

        $basketItem = $this->getBasket()->getItemById($basketId);
        if (null === $basketItem) {
            throw new NotFoundException('BasketItem');
        }

        $result = $basketItem->setField('QUANTITY', $quantity);
        if (!$result->isSuccess()) {
            throw new BitrixProxyException($result);
        }
        if ($this->getBasket()->getOrder()) {
            $updateResult = BasketTable::update($basketItem->getId(), ['QUANTITY' => $quantity]);
            if (!$updateResult->isSuccess()) {
                throw new BitrixProxyException($updateResult);
            }
        } else {
            $this->getBasket()->save();
        }

        //всегда перегружаем из-за подарков
        $this->setBasketIds();

        return true;
    }

    /**
     * @param int|null $discountId
     *
     * @throws Exception
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     *
     * @return array
     */
    public function getGiftGroupOfferCollection(?int $discountId = null): array
    {
        //todo эээ так можно или нельзя? определись, чувак)
        if (!$discountId || $discountId < 0) {
            throw new InvalidArgumentException('Отсутствует идентификатор скидки');
        }

        $basket = $this->getBasket();
        if (null === $order = $basket->getOrder()) {
            $order = Order::create(SITE_ID);
            $order->setBasket($basket);
        }

        $giftGroups = Gift::getPossibleGiftGroups($order, $discountId);
        if ($giftGroups) {
            if (\count($giftGroups[$discountId]) === 1) {
                $giftGroup = \current($giftGroups[$discountId]);
            } else {
                throw new RuntimeException('todo');
            }
        } else {
            throw new NotFoundException('Товары по акции не найдены');
        }

        $giftIds = $giftGroup['list'];
        if (!\is_array($giftIds) || !($giftIds = \array_flip(\array_flip(\array_filter($giftIds))))) {
            throw new NotFoundException('Товары по акции не найдены');
        }
        /** @todo сортировка будет работать только пока 1 цена у нас(ибо запрос базовой тут) */
        $priceType = (int)\CCatalogGroup::GetBaseGroup()['ID'];
        $offerQuery = new OfferQuery();
        $offerQuery->withFilterParameter('=ID', $giftIds);
        if ($priceType > 0) {
            $offerQuery->withOrder(['catalog_PRICE_' . $priceType => 'asc']);
        }
        $giftGroup['list'] = $offerQuery->exec();
        return $giftGroup;
    }

    /**
     * @param bool|null $reload
     * @param int       $fUserId
     *
     * @return Basket
     */
    public function getBasket(bool $reload = null, int $fUserId = 0): Basket
    {
        if (null === $this->basket || $reload) {
            /** @var Basket $basket */
            /** @noinspection PhpInternalEntityUsedInspection */
            DiscountCompatibility::stopUsageCompatible();

            $this->setFuserId($fUserId);

            $this->basket = Basket::loadItemsForFUser($this->fUserId, SITE_ID);
            //всегда перегружаем из-за подарков
            $this->setBasketIds();
            try {
                $this->refreshAvailability($this->basket);
            } catch (\Exception $e) {
                $this->log()->error(sprintf('failed to update basket availability: %s', $e->getMessage()), [
                    'fuserId' => $this->fUserId,
                ]);
            }
        }

        return $this->basket;
    }

    /**
     * @return array
     */
    public function getContext(): array
    {
        try {
            $userId = $this->currentUserProvider->getCurrentUserId();
        } /** @noinspection BadExceptionsProcessingInspection */
        catch (NotAuthorizedException $e) {
            $userId = 0;
        }
        return [
            'SITE_ID' => SITE_ID,
            'USER_ID' => $userId,
        ];
    }

    /**
     * Возвращает OfferCollection содержащих товары корзины и возможные подарки
     *
     * @param bool $renew
     *
     * @throws InvalidArgumentException
     *
     * @return OfferCollection
     *
     */
    public function getOfferCollection(bool $renew = false): OfferCollection
    {
        return null === $this->offerCollection || $renew ? $this->loadOfferCollection() : $this->offerCollection;
    }

    /**
     * @param Basket $basket
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ObjectNotFoundException
     * @return Basket
     */
    public function refreshAvailability(Basket $basket): Basket
    {
        $updateIds = false;
        $isTemporary = function (BasketItem $basketItem): bool {
            $property = $basketItem->getPropertyCollection()->getPropertyValues()['IS_TEMPORARY'];
            return $property && $property['VALUE'] === BitrixUtils::BX_BOOL_TRUE;
        };

        $normalItems = [];
        $temporaryItems = [];
        /** @var BasketItem $basketItem */
        foreach ($basket as $basketItem) {
            if ($isTemporary($basketItem)) {
                $temporaryItems[$basketItem->getProductId()] = $basketItem;
            } else {
                $normalItems[$basketItem->getProductId()] = $basketItem;
            }
        }

        $offerCollection = $this->getOfferCollection();
        /** @var BasketItem $basketItem */
        foreach ($basket as $basketItem) {
            $isTemporaryItem = $isTemporary($basketItem);
            if ($isTemporaryItem) {
                if (isset($normalItems[$basketItem->getProductId()])) {
                    $basketItem->delete();
                    $updateIds = true;
                    continue;
                }

                $this->setBasketItemPropertyValue($basketItem, 'IS_TEMPORARY', BitrixUtils::BX_BOOL_FALSE);
            }
            /** @var Offer $offer */
            foreach ($offerCollection as $offer) {
                if ($offer->getId() !== (int)$basketItem->getProductId()) {
                    continue;
                }

                $temporaryItem = null;
                $quantity = (int)$basketItem->getQuantity();

                if (!$offer->isAvailable()) {
                    if (!$basketItem->isDelay()) {
                        $toUpdate['DELAY'] = BitrixUtils::BX_BOOL_TRUE;
                    }
                } else {
                    $toUpdate['DELAY'] = BitrixUtils::BX_BOOL_FALSE;
                    $maxAmount = $offer->getQuantity();
                    if (($quantity - $maxAmount) > 0) {
                        $toUpdate['QUANTITY'] = $maxAmount;
                    }
                }

                if (!empty($toUpdate)) {
                    $updateIds = true;
                    $basketItem->setFields($toUpdate);
                }

                break;
            }
        }

        if ($updateIds) {
            //всегда перегружаем из-за подарков
            $this->setBasketIds();
        }

        return $basket;
    }

    /**
     * @param string $type
     * @param bool   $renew
     *
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @return AdderInterface
     */
    public function getAdder(string $type, bool $renew = false): AdderInterface
    {
        static $storage;
        if (null === $storage || $renew) {
            $storage = [
                'gift'   => null,
                'detach' => null,
            ];
        }
        if (null === $order = $this->getBasket()->getOrder()) {
            $order = Order::create(SITE_ID);
            $order->setBasket($this->getBasket());
        }

        if ($type === 'gift') {
            if (null === $storage[$type]) {
                $adder = new Utils\Gift\Adder($order, $this);
                $storage[$type] = $adder;
            } else {
                $adder = $storage[$type];
            }
        } elseif ($type === 'detach') {
            if (null === $storage[$type]) {
                $adder = new Utils\Detach\Adder($order, $this);
                $storage[$type] = $adder;
            } else {
                $adder = $storage[$type];
            }
        } else {
            throw new InvalidArgumentException('Передан неверный тип');
        }

        return $adder;
    }

    /**
     * @param string $type
     * @param bool   $renew
     *
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @return CleanerInterface
     */
    public function getCleaner(string $type, bool $renew = false): CleanerInterface
    {
        static $storage;
        if (null === $storage || $renew) {
            $storage = [
                'gift'   => null,
                'detach' => null,
            ];
        }
        if (null === $order = $this->getBasket()->getOrder()) {
            $order = Order::create(SITE_ID);
            $order->setBasket($this->getBasket());
        }
        if ($type === 'gift') {
            if (null === $storage[$type]) {
                $cleaner = new Utils\Gift\Cleaner($order, $this);
                $storage[$type] = $cleaner;
            } else {
                $cleaner = $storage[$type];
            }
        } elseif ($type === 'detach') {
            if (null === $storage[$type]) {
                $cleaner = new Utils\Detach\Cleaner($order, $this);
                $storage[$type] = $cleaner;
            } else {
                $cleaner = $storage[$type];
            }
        } else {
            throw new InvalidArgumentException('Передан неверный тип');
        }

        return $cleaner;
    }

    /**
     * @param int|User $userId
     *
     * @return float
     */
    public function getBasketBonus($userId = null): float
    {
        try {
            if ($userId !== null) {
                if ($userId instanceof User) {
                    $user = $userId;
                } else {
                    $user = $this->currentUserProvider->getUserRepository()->find($userId);
                }
                $cardNumber = $user === null ? '' : $user->getDiscountCardNumber();
            } else {
                try {
                    $user = $this->currentUserProvider->getCurrentUser();
                    $cardNumber = $user === null ? '' : $user->getDiscountCardNumber();
                } catch (NotAuthorizedException $e) {
                    /** запрашиваем без карты */
                } catch (InvalidIdentifierException | ConstraintDefinitionException $e) {
                    $logger = LoggerFactory::create('params');
                    $logger->error($e->getMessage());
                    /** запрашиваем без карты */
                }
            }

            $basketRequest = $this->manzanaPosService->buildRequestFromBasket(
                $this->getBasket(),
                $cardNumber ?? '',
                $this
            );
            if (!$basketRequest->getItems()->isEmpty()) {
                $cheque = $this->manzanaPosService->processChequeWithoutBonus(
                    $basketRequest
                );
                $result = $cheque->getChargedBonus();
            } else {
                $result = 0.0;
            }
        } catch (ExecuteException $e) {
            $result = 0.0;
        }
        return $result;
    }

    /**
     * @todo КОСТЫЛЬ! УБРАТЬ В КУПОНЫ
     *
     * @return float
     */
    public function getPromocodeDiscount(): float
    {
        return $this->promocodeDiscount;
    }

    /**
     * @todo КОСТЫЛЬ! УБРАТЬ В КУПОНЫ
     *
     * @param float $promocodeDiscount
     */
    public function setPromocodeDiscount(float $promocodeDiscount): void
    {
        $this->promocodeDiscount = $promocodeDiscount;
    }

    /**
     * Получение максимального кол-ва бонусов, которыми можно оплатить корзину
     *
     * @param Basket|null $basket
     *
     * @return float
     */
    public function getMaxBonusesForPayment(?Basket $basket = null): float
    {
        $result = 0;
        if (!$basket) {
            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
            $basket = $this->getBasket()->getOrderableItems();
        }

        if (!$basket->isEmpty()) {
            try {
                $user = $this->currentUserProvider->getCurrentUser();
                if ($user && $user->getDiscountCardNumber()) {
                    $chequeRequest = $this->manzanaPosService->buildRequestFromBasket(
                        $basket,
                        $user->getDiscountCardNumber(),
                        $this
                    );
                    $chequeRequest->setPaidByBonus($basket->getPrice());

                    $cheque = $this->manzanaPosService->processCheque($chequeRequest);
                    $result = $cheque->getAvailablePayment();
                }
            } catch (ExecuteException $e) {
                $this->log()->error(sprintf('failed to get bonuses for payment: %s', $e->getMessage()));
            } catch (NotAuthorizedException $e) {
                // обработка не требуется
            }
        }

        return floor(min($basket->getPrice() * static::MAX_BONUS_PAYMENT, $result));
    }

    /**
     * @param BasketItem $basketItem
     * @param string     $code
     *
     * @return null|string
     */
    public function getBasketItemPropertyValue(BasketItem $basketItem, string $code): ?string
    {
        $result = null;
        /** @var BasketPropertyItem $property */
        foreach ($basketItem->getPropertyCollection() as $property) {
            if ($property->getField('CODE') === $code) {
                $result = $property->getField('VALUE');
            }
        }

        return $result;
    }

    /**
     *
     *
     * @param BasketItem $basketItem
     *
     * @throws InvalidArgumentException
     *
     * @return bool
     */
    public function isItemWithBonusAwarding(BasketItem $basketItem, ?Order $order = null): bool
    {
        /**
         * @var BasketItemCollection $basketItemCollection
         * @var Order                $order
         * @var Basket               $basket
         */

        if (
            !$order
            &&
            (
                !($basketItemCollection = $basketItem->getCollection())
                ||
                !($basket = $basketItemCollection->getBasket())
                ||
                !($order = $basket->getOrder())
            )
        ) {
            throw new InvalidArgumentException('У элемента корзины не установлен заказ');
        }

        if (
            !($discount = $order->getDiscount())
            ||
            !($applyResult = $discount->getApplyResult(true))
        ) {
            throw new InvalidArgumentException('У элемента корзины не расчитаны скидки');
        }
        $basketDiscounts = $applyResult['RESULT']['BASKET'][$basketItem->getBasketCode()];

        if (!$basketDiscounts) {
            /** @var BasketPropertyItem $basketPropertyItem */
            foreach ($basketItem->getPropertyCollection() as $basketPropertyItem) {
                $propCode = $basketPropertyItem->getField('CODE');
                if ($propCode === 'DETACH_FROM') {
                    $basketDiscounts = $applyResult['RESULT']['BASKET'][$basketPropertyItem->getField('VALUE')];
                } elseif ($propCode === 'IS_GIFT') {
                    $discountId = $basketPropertyItem->getField('VALUE');
                    if (\is_iterable($applyResult['DISCOUNT_LIST'])) {
                        foreach ($applyResult['DISCOUNT_LIST'] as $appliedDiscount) {
                            if ((int)$appliedDiscount['REAL_DISCOUNT_ID'] === (int)$discountId) {
                                $basketDiscounts = [
                                    'DISCOUNT_ID' => $appliedDiscount['ID'],
                                    'COUPON_ID'   => '',
                                    'APPLY'       => 'Y',
                                    'DESCR'       => $appliedDiscount['ACTIONS_DESCR']['BASKET'],
                                ];
                            }
                        }
                    }
                }
            }
        }

        if (\is_array($basketDiscounts) && !empty($basketDiscounts)) {
            $basketDiscounts = $this->purifyAppliedDiscounts($applyResult, $basketDiscounts);
        }

        return (bool)$basketDiscounts;
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     *
     * @param BasketItem $basketItem
     * @param string     $code
     * @param string     $value
     * @param string     $name
     */
    public function setBasketItemPropertyValue(
        BasketItem $basketItem,
        string $code,
        string $value,
        string $name = ''
    ): void {
        try {
            $found = false;
            /** @var BasketPropertyItem $property */
            foreach ($basketItem->getPropertyCollection() as $property) {
                if ($property->getField('CODE') === $code) {
                    $property->setField('VALUE', $value);
                    $found = true;
                }
            }

            if (!$found) {
                $property = $basketItem->getPropertyCollection()->createItem();
                $property->setFields([
                    'NAME'  => $name ?: $code,
                    'CODE'  => $code,
                    'VALUE' => $value,
                ]);
            }
        } catch (\Exception $e) {
            $this->log()->error(sprintf('failed to update basket item property: %s', $e->getMessage()), [
                'itemId'  => $basketItem->getId(),
                'offerId' => $basketItem->getProductId(),
                'code'    => $code,
                'value'   => $value,
            ]);
        }
    }

    /**
     * @param BasketItem $basketItem
     *
     * @return string
     */
    public function getBasketItemXmlId(BasketItem $basketItem): string
    {
        if (!$xmlId = $basketItem->getField('PRODUCT_XML_ID')) {
            $xmlId = $basketItem->getPropertyCollection()->getPropertyValues()['PRODUCT.XML_ID']['VALUE'] ?? '';
        }

        if (\strpos($xmlId, '#')) {
            /** @noinspection ShortListSyntaxCanBeUsedInspection */
            list(, $xmlId) = \explode('#', $xmlId);
        }

        return $xmlId;
    }

    public function resetCustomPrices()
    {
        $basket = $this->getBasket(true);

        try {
            /** @var BasketItem $basketItem */
            foreach ($basket as $basketItem) {
                $basketItem->setField('CUSTOM_PRICE', 'N');
            }
        } catch (\Exception $e) {
            $this->log()->error('failed to disable custom price', [
                'fuserId' => $basket->getFUserId(),
                'id'      => $basketItem->getId(),
            ]);
        }
        $basket->save();
    }

    /**
     * @param MainResult $res
     * @param Basket     $basket
     * @param array      $oldBasketCodes
     *
     * @return BasketItem|null
     */
    protected function checkErrorActual(MainResult $res, Basket $basket, array $oldBasketCodes = []): ?BasketItem
    {
        // проверяем не специально ли было запорото
        $found = false;
        foreach ($res->getErrors() as $error) {
            if ($error->getCode() === 'SALE_EVENT_ON_BEFORE_SALEORDER_FINAL_ACTION_ERROR') {
                $found = true;
            }
        }
        if (!$found) {
            return null;
        }
        // и если специально ищем баскет айтем
        // todo проверить еще и количества
        $basketItem = null;
        /** @var BasketItem $basketItem */
        foreach ($basket->getBasketItems() as $basketItem) {
            if (!\in_array($basketItem->getBasketCode(), $oldBasketCodes, true)) {
                break;
            }
        }

        return $basketItem;
    }

    /**
     * ad
     *
     * @param array $applyResult
     * @param array $appliedDiscounts
     *
     * @return array
     */
    protected function purifyAppliedDiscounts(array $applyResult, array $appliedDiscounts): array
    {
        foreach ($appliedDiscounts as $k => $appliedDiscount) {
            $id = $applyResult['DISCOUNT_LIST'][$appliedDiscount['DISCOUNT_ID']]['REAL_DISCOUNT_ID'];
            $settings = $applyResult['FULL_DISCOUNT_LIST'][$id]['ACTIONS']['CHILDREN'];

            if (
                \count($settings) === 1
                &&
                ($settings = \current($settings))
                &&
                $settings['CLASS_ID'] === 'ActSaleBsktGrp'
                &&
                ($settings = \array_values($settings['CHILDREN']))
                &&
                \count($settings) === 2
                &&
                (
                    (
                        0 === \strpos($settings[0]['CLASS_ID'], 'BasketQuantity')
                        &&
                        0 === \strpos($settings[1]['CLASS_ID'], 'CondIBProp')
                    )
                    ||
                    (
                        0 === \strpos($settings[1]['CLASS_ID'], 'BasketQuantity')
                        &&
                        0 === \strpos($settings[0]['CLASS_ID'], 'CondIBProp')
                    )
                )
            ) {
                unset($appliedDiscounts[$k]);
            }
        }

        return $appliedDiscounts;
    }

    /**
     * @param int $fUserId
     */
    private function setFuserId(int $fUserId = 0): void
    {
        if ($fUserId === 0) {
            $this->fUserId = $this->currentUserProvider->getCurrentFUserId();
        } else {
            $this->fUserId = $fUserId;
        }
    }

    /**
     * @return bool
     */
    private function setBasketIds(): bool
    {
        $hasGifts = false;
        /** @var BasketItem $basketItem */
        $this->basketProductIds = [];
        $basket = $this->getBasket();
        foreach ($basket->getBasketItems() as $basketItem) {
            $this->basketProductIds[] = $basketItem->getProductId();
        }

        if (null !== $order = $basket->getOrder()) {
            /** @noinspection AdditionOperationOnArraysInspection */
            $gifts = Gift::getPossibleGifts($order);
            $this->basketProductIds += $gifts;
            if (!empty($gifts)) {
                $hasGifts = true;
            }
        }

        if (!empty($this->basketProductIds)) {
            $this->basketProductIds = \array_flip(\array_flip(\array_filter($this->basketProductIds)));
            $this->basketProductIds = array_unique($this->basketProductIds);
            sort($this->basketProductIds);
        }
        return $hasGifts;
    }

    /**
     * @throws InvalidArgumentException
     *
     * @return OfferCollection
     */
    private function loadOfferCollection(): OfferCollection
    {
        /**
         * @var Basket          $basket
         * @var OfferCollection $offerCollection
         */
        if (empty($this->basketProductIds)) {
            $this->setBasketIds();
        }

        if (!empty($this->basketProductIds)) {
            $offerCollection = (new OfferQuery())->withFilter(['=ID' => $this->basketProductIds])->exec();
        } else {
            $offerCollection = new OfferCollection(new \CDBResult());
        }

        return $this->offerCollection = $offerCollection;
    }
}
