<?php
declare(strict_types=1);

namespace FourPaws\SaleBundle\Service;

use Adv\Bitrixtools\Tools\BitrixUtils;
use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Catalog\PriceTable;
use Bitrix\Catalog\Product\CatalogProvider;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\LoaderException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\Result as MainResult;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\BasketItemCollection;
use Bitrix\Sale\BasketPropertyItem;
use Bitrix\Sale\Compatible\DiscountCompatibility;
use Bitrix\Sale\Internals\BasketPropertyTable;
use Bitrix\Sale\Internals\BasketTable;
use Bitrix\Sale\Internals\OrderTable;
use Bitrix\Sale\Order;
use Exception;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\BitrixOrm\Collection\ShareCollection;
use FourPaws\BitrixOrm\Model\Share;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\Catalog\Query\PriceQuery;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\Enum\UserGroup;
use FourPaws\External\Manzana\Exception\ExecuteException;
use FourPaws\External\ManzanaPosService;
use FourPaws\LocationBundle\LocationService;
use FourPaws\PersonalBundle\Service\OrderService;
use FourPaws\PersonalBundle\Service\PiggyBankService;
use FourPaws\SaleBundle\Discount\Gift;
use FourPaws\SaleBundle\Discount\Utils;
use FourPaws\SaleBundle\Discount\Utils\AdderInterface;
use FourPaws\SaleBundle\Discount\Utils\CleanerInterface;
use FourPaws\SaleBundle\Exception\BitrixProxyException;
use FourPaws\SaleBundle\Exception\InvalidArgumentException;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\SapBundle\Repository\ShareRepository;
use FourPaws\UserBundle\Entity\Group;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

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
    /** @var OrderService */
    private $orderService;
    /** @todo КОСТЫЛЬ! УБРАТЬ В КУПОНЫ */
    private $promocodeDiscount = 0.0;
    private $fUserId;
    /**
     * @var ShareRepository
     */
    private $shareRepository;

	/**
	 * BasketService constructor.
	 *
	 * @param CurrentUserProviderInterface $currentUserProvider
	 * @param ManzanaPosService $manzanaPosService
	 * @param OrderService $orderService
	 * @param ShareRepository $shareRepository
	 */
    public function __construct(
        CurrentUserProviderInterface $currentUserProvider,
        ManzanaPosService $manzanaPosService,
        OrderService $orderService,
        ShareRepository $shareRepository
    ) {
        $this->currentUserProvider = $currentUserProvider;
        $this->manzanaPosService = $manzanaPosService;
        $this->orderService = $orderService;
        $this->shareRepository = $shareRepository;
    }

    /**
     * @param int $offerId
     * @param int|null $quantity
     * @param array $rewriteFields
     * @param bool $save
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
            'PRODUCT_ID' => $offerId,
            'QUANTITY' => $quantity,
            'MODULE' => 'catalog',
            'PRODUCT_PROVIDER_CLASS' => CatalogProvider::class,
        ];
        if ($rewriteFields) {
            /** @noinspection AdditionOperationOnArraysInspection */
            $fields = $rewriteFields + $fields;
        }

        $basket = $basket ?? $this->getBasket();

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
            if ($basketItem === null) {
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
     * @param int $basketId
     * @param array|null $excludeXmlIds
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
    public function deleteOfferFromBasket(int $basketId, ?array $excludeXmlIds = []): bool
    {
        if ($basketId < 1) {
            throw new InvalidArgumentException('Wrong $basketId');
        }
        /** @var BasketItem $basketItem */
        $basketItem = $this->getBasket()->getItemById($basketId);

        if (null === $basketItem) {
            throw new NotFoundException('Не найден элемент корзины');
        }

        if ($excludeXmlIds
            && ($skuXmlId = explode('#', $basketItem->getField('PRODUCT_XML_ID'))[1])
            && in_array($skuXmlId, $excludeXmlIds, true)) {
            return true;
        } else {
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
    }

    /**
     * @param int $basketId
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
     * @param int|null    $discountId
     * @param Basket|null $basket
     *
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     *
     * @return array
     */
    public function getGiftGroupOfferCollection(?int $discountId = null, ?Basket $basket = null): array
    {
        //todo эээ так можно или нельзя? определись, чувак)
        if (!$discountId || $discountId < 0) {
            throw new InvalidArgumentException('Отсутствует идентификатор скидки');
        }

        $basket = $basket ?? $this->getBasket();
        if (null === $order = $basket->getOrder()) {
            $userId = null;
            try {
                $userId = $this->currentUserProvider->getCurrentUserId();
            } catch (NotAuthorizedException $e) {
            }
            $order = Order::create(SITE_ID, $userId);
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

        /** @var BasketItem $basketItem */
        foreach ($basket->getBasketItems() as $basketItem) {
            /** @var Offer $offer */
            $offer = OfferQuery::getById((int)$basketItem->getProductId());
            $temporaryItem = null;
            $quantity = (int)$basketItem->getQuantity();
            $toUpdate = [];

            if ($offer != null) {
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
            } else {
                $basketItem->delete();
            }
        }

        if ($updateIds) {
            //всегда перегружаем из-за подарков
            $this->setBasketIds();
        }

        return $basket;
    }

    /**
     * @param string     $type
     * @param Order|null $order
     * @param bool       $renew
     *
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @return AdderInterface
     */
    public function getAdder(string $type, ?Order $order = null, bool $renew = false): AdderInterface
    {
        static $storage;
        if (null === $storage || $renew) {
            $storage = [
                'gift' => [],
                'detach' => [],
            ];
        }

        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        $order = $order instanceof Order ? $order : $this->getBasket()->getOrder();
        if (null === $order) {
            $userId = null;
            try {
                $userId = $this->currentUserProvider->getCurrentUserId();
            } catch (NotAuthorizedException $e) {
            }
            $order = Order::create(SITE_ID, $userId);
            $order->setBasket($this->getBasket());
        }

        $orderInternalId = $order->getInternalId();
        if ($type === 'gift') {
            if (null === $storage[$type][$orderInternalId]) {
                $adder = new Utils\Gift\Adder($order, $this);
                $storage[$type][$orderInternalId] = $adder;
            } else {
                $adder = $storage[$type][$orderInternalId];
            }
        } elseif ($type === 'detach') {
            if (null === $storage[$type][$orderInternalId]) {
                $adder = new Utils\Detach\Adder($order, $this);
                $storage[$type][$orderInternalId] = $adder;
            } else {
                $adder = $storage[$type][$orderInternalId];
            }
        } else {
            throw new InvalidArgumentException('Передан неверный тип');
        }

        return $adder;
    }

    /**
     * @param string     $type
     * @param Order|null $order
     * @param bool       $renew
     *
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @return CleanerInterface
     */
    public function getCleaner(string $type, ?Order $order = null, bool $renew = false): CleanerInterface
    {
        static $storage;
        if (null === $storage || $renew) {
            $storage = [
                'gift'   => [],
                'detach' => [],
            ];
        }

        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        $order = $order instanceof Order ? $order : $this->getBasket()->getOrder();
        if (null === $order) {
            $userId = null;
            try {
                $userId = $this->currentUserProvider->getCurrentUserId();
            } catch (NotAuthorizedException $e) {
            }
            $order = Order::create(SITE_ID, $userId);
            $order->setBasket($this->getBasket());
        }

        $orderInternalId = $order->getInternalId();
        if ($type === 'gift') {
            if (null === $storage[$type][$orderInternalId]) {
                $cleaner = new Utils\Gift\Cleaner($order, $this);
                $storage[$type][$orderInternalId] = $cleaner;
            } else {
                $cleaner = $storage[$type][$orderInternalId];
            }
        } elseif ($type === 'detach') {
            if (null === $storage[$type][$orderInternalId]) {
                $cleaner = new Utils\Detach\Cleaner($order, $this);
                $storage[$type][$orderInternalId] = $cleaner;
            } else {
                $cleaner = $storage[$type][$orderInternalId];
            }
        } else {
            throw new InvalidArgumentException('Передан неверный тип');
        }

        return $cleaner;
    }

    /**
     * Рассчитывает бонус, который будет начислен, если покупатель совершит заказ без оплаты бонусами
     * @param int|User $userId
     *
     * @throws \FourPaws\UserBundle\Exception\InvalidIdentifierException
     * @throws \FourPaws\UserBundle\Exception\ConstraintDefinitionException
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
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
        if ($basketItem->existsPropertyCollection()) {
            foreach ($basketItem->getPropertyCollection() as $property) {
                if ($property->getField('CODE') === $code) {
                    $result = $property->getField('VALUE');
                }
            }
        } else {
            $prop = $this->getBasketPropByCode($basketItem->getId(), $code);
            if ($prop !== null) {
                $result = $prop['VALUE'];
            }
        }

        return $result;
    }

    /**
     * Вернет true если пользователь имеет группу, которой нужно всегда начислять бонусы
     *
     * @param User $user
     *
     * @return bool
     */
    public function isUserGroupWithPermanentBonusRewarding(User $user): bool
    {
        return $user->getGroups()->exists(
            function (/** @noinspection PhpUnusedParameterInspection */ $k, Group $group) {
                return $group->getCode() === UserGroup::OPT_CODE;
            }
        );
    }

    /**
     * @param BasketItem $basketItem
     *
     * @throws InvalidArgumentException
     *
     * @return Order
     */
    public function extractOrderFromBasketItem(BasketItem $basketItem): Order
    {
        /**
         * @var $basketItemCollection BasketItemCollection
         * @var $order Order
         */
        if (
            !($basketItemCollection = $basketItem->getCollection())
            ||
            !($basket = $basketItemCollection->getBasket())
            ||
            !($order = $basket->getOrder())
        ) {
            throw new InvalidArgumentException('У элемента корзины не установлен заказ');
        }
        return $order;
    }

    /**
     *
     * @param BasketItem $basketItem
     * @param Order|null $order
     *
     * @throws \FourPaws\UserBundle\Exception\InvalidIdentifierException
     * @throws \FourPaws\UserBundle\Exception\ConstraintDefinitionException
     * @throws InvalidArgumentException
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     *
     * @return int
     */
    public function getBonusAwardingQuantity(BasketItem $basketItem, ?Order $order = null): int
    {
        if ($this->currentUserProvider->getCurrentFUserId() === (int) $basketItem->getFUserId()) {
            try {
                $user = $this->currentUserProvider->getCurrentUser();
            } catch (NotAuthorizedException $e) {
                // для неавторизованного не имеет смысла проверять
                $user = false;
            }
        } else {
            $user = $this->currentUserProvider->getUserRepository()->findByFUser(
                (int)$basketItem->getFUserId() ?: $this->currentUserProvider->getCurrentFUserId()
            );
        }

        $resultQuantity = 0;
        /** @var PiggyBankService $piggyBankService */
        $piggyBankService = App::getInstance()->getContainer()->get('piggy_bank.service');
        /** @var Offer|null $offer */
        $offer = $this->getOfferCollection(true)->getById($basketItem->getProductId());
        if (!$offer)
        {
            if($basketItem->getProductId() > 0){
                $offerCollection = (new OfferQuery())->withFilter(['ID' => $basketItem->getProductId()])->exec();
                if($offerCollection->isEmpty()){
                    $this->log()->error(\sprintf(
                        'empty offer for product id: %s',
                        $basketItem->getProductId()
                    ));
                    return 0;
                }
                $offer = $offerCollection->first();
            }
        }
        if (in_array((int)$offer->getXmlId(), $piggyBankService::getMarkXmlIds(), true))
        {
            return 0;
        }

        /** LP23-81 */
        if ($user && $this->isUserGroupWithPermanentBonusRewarding($user)) {
            $resultQuantity = (int)$basketItem->getQuantity();
            $basketDiscounts = false;
        } else {
            if (!$offer) {
                $offer = (new OfferQuery())
                    ->withFilter(['=ID' => $basketItem->getProductId()])
                    ->exec()
                    ->getById($basketItem->getProductId());
                if (!$offer) {
                    throw new InvalidArgumentException('Предложение не найдено');
                }
                $this->getOfferCollection()->add($offer);
            }
        }

        if ($resultQuantity === 0 && isset($offer) && ($offer->isBonusExclude() || $offer->isShare()) ) {
            $basketDiscounts = true;
        } elseif ($resultQuantity === 0) {
            if (!$order) {
                $order = $this->extractOrderFromBasketItem($basketItem);
            }

            if (
                !($discount = $order->getDiscount())
                ||
                !($applyResult = $discount->getApplyResult(true))
            ) {
                throw new InvalidArgumentException('У элемента корзины не расчитаны скидки');
            }

            $basketDiscounts = $applyResult['RESULT']['BASKET'][$basketItem->getBasketCode()];
            if (\is_array($basketDiscounts) && !empty($basketDiscounts)) {
                $basketDiscounts = $this->purifyAppliedDiscounts($applyResult, $basketDiscounts);
            }

            // Проверяем не подарок ли это
            if (!$basketDiscounts) {
                /** @var BasketPropertyItem $basketPropertyItem */
                foreach ($basketItem->getPropertyCollection() as $basketPropertyItem) {
                    $propCode = $basketPropertyItem->getField('CODE');
                    if ($propCode === 'IS_GIFT') {
                        $basketDiscounts = true;
                        break;
                    }
                }
            }

            if (!$basketDiscounts) {
                $resultQuantity = (int)$basketItem->getQuantity() - $this->getPremisesQuantity(
                        $applyResult, $basketItem, $order
                    );
            }
        }
        return (bool)$basketDiscounts ? 0 : $resultQuantity;
    }

    /**
     * Возвращает количество предпосылок на которое НЕ начисляются бонусы
     *
     * @param array $applyResult
     * @param BasketItem $basketItem
     * @param Order $order
     *
     * @return int
     */
    public function getPremisesQuantity(array $applyResult, BasketItem $basketItem, Order $order): int
    {
        $allPremises = [];
        foreach ($applyResult['DISCOUNT_LIST'] as $fakeId => $discountDesc) {
            if (
                $discountDesc['ACTIONS_DESCR']['BASKET']
                &&
                ($params = json_decode($discountDesc['ACTIONS_DESCR']['BASKET'], true))
                &&
                \is_array($params)
                &&
                !$this->isDiscountWithBonus($applyResult, (int)$discountDesc['REAL_DISCOUNT_ID'])
            ) {
                if ($params['discountType'] === 'DETACH') {
                    $premises = (array)$params['params']['premises'];
                } elseif ($params['discountType'] === 'GIFT') {
                    $premises = (array)$params['premises'];
                }
                foreach ($premises as $productId => $quantity) {
                    $allPremises[$productId] += $quantity;
                }
            }
        }
        $productId = (int)$basketItem->getProductId();
        $productPremiseQty = (int)($allPremises[$productId] ?? 0);
        $basketCode = $basketItem->getBasketCode();
        $result = 0;
        if ($productPremiseQty) {
            /** @var BasketItem $item */
            foreach ($order->getBasket()->getBasketItems() as $item) {
                foreach ($item->getPropertyCollection() as $basketPropertyItem) {
                    if ($basketPropertyItem->getField('CODE') === 'IS_GIFT') {
                        continue 2;
                    }
                }
                if ((int)$item->getProductId() !== $productId) {
                    continue;
                }
                if ($basketCode === $item->getBasketCode()) {
                    if ($productPremiseQty <= 0) {
                        $result = 0;
                    } elseif ($productPremiseQty >= (int)$item->getQuantity()) {
                        $result = (int)$item->getQuantity();
                    } else {
                        $result = $productPremiseQty;
                    }
                } else {
                    $productPremiseQty -= (int)$item->getQuantity();
                }
            }
        }
        return $result;
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
                    'NAME' => $name ?: $code,
                    'CODE' => $code,
                    'VALUE' => $value,
                ]);
            }
        } catch (\Exception $e) {
            $this->log()->error(sprintf('failed to update basket item property: %s', $e->getMessage()), [
                'itemId' => $basketItem->getId(),
                'offerId' => $basketItem->getProductId(),
                'code' => $code,
                'value' => $value,
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
            if ($basketItem->existsPropertyCollection()) {
                $xmlId = $basketItem->getPropertyCollection()->getPropertyValues()['PRODUCT.XML_ID']['VALUE'] ?? '';
            } else {
                $prop = $this->getBasketPropByCode($basketItem->getId(), 'PRODUCT.XML_ID');
                if ($prop !== null) {
                    $xmlId = $prop['VALUE'];
                }
            }
        }

        if (\strpos((string)$xmlId, '#')) {
            /** @noinspection ShortListSyntaxCanBeUsedInspection */
            list(, $xmlId) = \explode('#', $xmlId);
        }

        return (string)$xmlId;
    }

    /**
     * @param BasketItem $basketItem
     * @param bool|null $extendedCheck
     *
     * @return bool
     */
    public function isGiftProduct(BasketItem $basketItem, ?bool $extendedCheck = false): bool
    {
        $xmlId = $this->getBasketItemXmlId($basketItem);

        return $this->isGiftProductByXmlId($xmlId, $extendedCheck);
    }

    /**
     * @param string|null $xmlId
     * @param bool|null $extendedCheck
     * @return bool
     */
    public function isGiftProductByXmlId(?string $xmlId, ?bool $extendedCheck = false): bool
    {
        /**
         * @todo выпилить 1 октября 2018 года (коммент перенесен из метода isGiftProduct)
         */
        return (!\in_array($xmlId, ['3005425', '3005437', '3005424', '3005436'], true) && // @todo костыль для акции "добролап" (коммент перенесен из метода isGiftProduct)
            ($xmlId[0] === '3')) || ($extendedCheck && $xmlId[0] === '2');
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
            // Описания подарочных скидок нужно чистить потому что они вешаются на предпосылки.
            if (
                $appliedDiscount['DESCR']
                &&
                ($params = json_decode($appliedDiscount['DESCR'], true))
                &&
                $params['discountType'] === 'GIFT'
            ) {
                unset($appliedDiscounts[$k]);
                continue;
            }
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
            $gifts = Gift::getPossibleGifts($order);
            /** @noinspection AdditionOperationOnArraysInspection */
            $this->basketProductIds += $gifts;
            if (!empty($gifts)) {
                $hasGifts = true;
            }
        }

        if (!empty($this->basketProductIds)) {
            $this->basketProductIds = \array_flip(\array_flip(\array_filter($this->basketProductIds)));
            sort($this->basketProductIds); // зачем?
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
            $offerCollection = new OfferCollection(new \CIblockResult());
        }

        return $this->offerCollection = $offerCollection;
    }

    /**
     * @param int $basketItemId
     * @param string     $propCode
     *
     * @return array|null
     */
    public function getBasketPropByCode(int $basketItemId, string $propCode): ?array
    {
        try {
            $res = BasketPropertyTable::query()
                ->where('CODE', $propCode)
                ->where('BASKET_ID', $basketItemId)
                ->setSelect(['*'])
                ->exec();
            if ($res->getSelectedRowsCount() === 0) {
                return null;
            }
            return $res->fetch();
        } catch (ObjectPropertyException | ArgumentException | SystemException $e) {
            /** @todo залогировать */
        }

        return null;
    }

    /**
     * @param int    $basketItemId
     * @param string $propCode
     *
     * @return bool
     */
    public function isBasketPropEmpty(int $basketItemId, string $propCode): bool
    {
        $value = $this->getBasketPropByCode($basketItemId, $propCode);
        return $value === null || empty($value);
    }

    /**
     * @param Basket|null $basket
     *
     * @return array
     */
    public function getBasketProducts(?Basket $basket = null): array
    {
        $result = [];

        try {
            $basket = $basket instanceof Basket ? $basket : $this->getBasket();

            /** @var BasketItem $basketItem */
            foreach ($basket as $basketItem) {
                $result[$basketItem->getProductId()] += $basketItem->getQuantity();
            }
        } catch (\Exception $e) {
            $this->log()->error(
                \sprintf('Failed to get basket products: %s: %s', \get_class($e), $e->getMessage()),
                ['trace' => $e->getTrace()]
            );
        }

        return $result;
    }


    /**
     * @param Basket|null $basket
     *
     * @return OfferCollection
     */
    public function getBasketOffers(?Basket $basket = null): OfferCollection
    {
        $ids = array_keys($this->getBasketProducts($basket));

        if (empty($ids)) {
            return new OfferCollection(new \CIBlockResult());
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return (new OfferQuery())->withFilterParameter('=ID', $ids)->exec();
    }

    /**
     * @param int $basketItemId
     * @param Basket|null $basket
     * @return int
     */
    public function getProductIdByBasketItemId(int $basketItemId, ?Basket $basket = null)
    {
        $basket = $basket instanceof Basket ? $basket : $this->getBasket();
        /** @var BasketItem $basketItem */
        foreach ($basket as $basketItem) {
            if ($basketItem->getId() === $basketItemId) {
                return $basketItem->getProductId();
            }
        }
        return 0;
    }

    /**
     *
     * @param array $applyResult - только для того чтобы не генерировать много запросов
     * @param int $discountId - настоящий id правила
     *
     * @return bool
     */
    private function isDiscountWithBonus(array $applyResult, int $discountId): bool
    {
        static $shareCollection;
        static $discountIdsString = '';
        $discountIds = [];
        $result = false;
        foreach ($applyResult['DISCOUNT_LIST'] as $discount) {
            if ($discount['APPLY'] === 'Y') {
                $discountIds[] = (int)$discount['REAL_DISCOUNT_ID'];
            }
        }
        if ($discountIds) {
            if ($discountIdsString !== implode($discountIds)) {
                /** @todo закешировать как-нибудь получше */
                /** @var ShareCollection $shareCollection */
                $shareCollection = $this->shareRepository->findBy(['PROPERTY_BASKET_RULES' => $discountIds]);
                $discountIdsString = implode($discountIds);
            }

            /** @var Share $share */
            foreach ($shareCollection as $share) {
                if (\in_array($discountId, $share->getPropertyBasketRules())) {
                    $result = $share->isBonus();
                    break;
                }
            }
        }
        return $result;
    }

    /**
     * @param int $limit
     * @return int[]
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws ArgumentException
     */
    public function getPopularOfferIds(int $limit = 10): array
    {
        try {
            $userId = $this->currentUserProvider->getCurrentUserId();
        } catch (NotAuthorizedException $e) {
        }
        $offersIblockId = IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS);
        /**
         * Все элементы корзины из заказов, принадлежащих данному пользователю,
         * у которых цена > 0 и активен оффер, оффер не является подарком
         */
        $query = BasketTable::query()
            ->setSelect([
                'PRODUCT_ID',
            ])
            ->setFilter([
                '>CATALOG_PRICE.PRICE' => 0,
                'ORDER.USER_ID'        => $userId,
                '!ELEMENT.XML_ID' => '3%'
            ])
            ->setGroup(['PRODUCT_ID'])
            ->registerRuntimeField(
                new ExpressionField('CNT', 'COUNT(*)')
            )
            ->registerRuntimeField(
                new ReferenceField(
                    'ORDER',
                    OrderTable::class,
                    ['=this.ORDER_ID' => 'ref.ID'],
                    ['join_type' => 'INNER']
                )
            )
            ->registerRuntimeField(
                new ReferenceField(
                    'ELEMENT', ElementTable::class,
                    Query\Join::on('this.PRODUCT_ID', 'ref.ID')
                        ->where('ref.ACTIVE', BitrixUtils::BX_BOOL_TRUE)
                        ->where('ref.IBLOCK_ID', $offersIblockId),
                    ['join_type' => 'INNER']
                )
            )
            ->registerRuntimeField(
                new ReferenceField(
                    'CATALOG_PRICE', PriceTable::class,
                    Query\Join::on('this.PRODUCT_ID', 'ref.PRODUCT_ID')->where('ref.CATALOG_GROUP_ID', 2),
                    ['join_type' => 'INNER']
                )
            )
            ->setOrder(['CNT' => 'DESC'])
            ->setLimit($limit)
            ->exec();

        $result = [];
        while ($offerId = $query->fetch()) {
            $result[] = $offerId['PRODUCT_ID'];
        }

        return $result;
    }

	/**
	 * @return int
	 */
	public function getMarksQuantityFromUserBaskets(): int
    {
        $userId = $this->currentUserProvider->getCurrentUserId();


        try {
        	/** @var PiggyBankService $piggyBankService */
            $piggyBankService = App::getInstance()->getContainer()->get('piggy_bank.service');

            $isPayedFilter = [
                'LOGIC' => 'OR',
            ];
            foreach ($this->orderService::STATUS_FINAL as $status)
            {
                $isPayedFilter[] = ['ORDER.STATUS_ID' => $status];
            }

            $marksArray = BasketTable::query()
                ->setSelect([
                    'QUANTITY',
                ])
                ->setFilter([
                    [
                        'LOGIC' => 'OR',
                        ['PRODUCT_ID' => $piggyBankService->getVirtualMarkId()],
                        ['PRODUCT_ID' => $piggyBankService->getPhysicalMarkId()],
                        ['PRODUCT_ID' => $piggyBankService->getOldVirtualMarkId()],
                    ],
                    $isPayedFilter,
                    'ORDER.USER_ID' => $userId,
                    //->where('DATE', '<',
                    //                    DateTime::createFromTimestamp($time - static::SMS_LIFE_TIME))
                    //TODO filter by date (по дате создания заказа) of promo offer dates range
                    //TODO somehow filter out manzana duplicates
                ])
                ->registerRuntimeField(
                    new ReferenceField(
                        'ORDER',
                        OrderTable::class,
                        Query\Join::on('this.ORDER_ID', 'ref.ID'),
                        ['join_type' => 'INNER']
                    )
                )
                ->exec()
                ->fetchAll();

            $marksQuantity = array_reduce($marksArray, function($carry, $item) {
                $carry += $item['QUANTITY'];
                return $carry;
            }, 0);

        } catch (\Exception $e) {
		    $logger = LoggerFactory::create('piggyBank');
		    $logger->error($e->getMessage());

            $marksQuantity = 0;
        }

        return (int)$marksQuantity;
    }

    /**
     * @param BasketItem $basketItem
     * @throws ArgumentException
     * @throws ArgumentNullException
     */
    public function updateRegionDiscountForBasketItem(BasketItem $basketItem, string $regionCode = ''): void
    {
        $safe = false;

        if(!$regionCode){
            /** @var LocationService $locationService */
            $locationService = App::getInstance()->getContainer()->get('location.service');
            $regionCode = $locationService->getCurrentRegionCode();
        }

        foreach ($basketItem->getPropertyCollection() as $propertyItem) {
            if (in_array($propertyItem->getField('CODE'), [Offer::SIMPLE_SHARE_SALE_CODE, Offer::SIMPLE_SHARE_DISCOUNT_CODE])) {
                $propertyItem->delete();
                $safe = true;
            }
        }

        /** @var BasketItem $basketItem */
        if($offer = OfferQuery::getById((int)$basketItem->getProductId())){
            $regionDiscount = $offer->getRegionDiscount($regionCode);
            if($regionDiscount){
                $value = $regionDiscount['price_action'] ? $regionDiscount['price_action'] : $regionDiscount['cond_value'];
                $this->setBasketItemPropertyValue($basketItem, $regionDiscount['cond_for_action'], (string)$value);
                $safe = true;
            }
        }

        if($safe){
            $basketItem->save();
        }
    }

    /**
     * Возвращает объект корзины из массива вида ['productId', 'quantity']
     *
     * @param array $items
     * @return \Bitrix\Sale\BasketBase
     * @throws ApplicationCreateException
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectException
     * @throws Exception
     */
    public function createBasketFromItems(array $items)
    {
        $basket = Basket::create(SITE_ID);
        $tItems = [];
        foreach($items as $item){
            $tItems[$item['productId']] = [
                'OFFER_ID' => $item['productId'],
                'QUANTITY' => $item['quantity']
            ];
        }

        $offerIds = array_column($tItems, 'OFFER_ID');
        if(empty($offerIds)){
            throw new Exception("Empty offerIds");
        }

        $offers = (new OfferQuery())
            ->withFilter(["ID" => $offerIds])
            ->exec();

        /** @var Offer $offer */
        foreach($offers as $offer){
            $tItems[$offer->getId()]['PRICE'] = $offer->getSubscribePrice();
            $tItems[$offer->getId()]['BASE_PRICE'] = $offer->getPrice();
            $tItems[$offer->getId()]['NAME'] = $offer->getName();
            $tItems[$offer->getId()]['WEIGHT'] = $offer->getCatalogProduct()->getWeight();
            $tItems[$offer->getId()]['DETAIL_PAGE_URL'] = $offer->getDetailPageUrl();
            $tItems[$offer->getId()]['PRODUCT_XML_ID'] = $offer->getXmlId();
            if($tItems[$offer->getId()]['QUANTITY'] > $offer->getQuantity()){
                $tItems[$offer->getId()]['QUANTITY'] = $offer->getQuantity();
            }
        }

        foreach($tItems as $item){
            $basketItem = BasketItem::create($basket, 'sale', $item['OFFER_ID']);
            $basketItem->setFields([
                'PRICE'                  => $item['PRICE'],
                'BASE_PRICE'             => $item['BASE_PRICE'],
                'CUSTOM_PRICE'           => BitrixUtils::BX_BOOL_TRUE,
                'QUANTITY'               => $item['QUANTITY'],
                'CURRENCY'               => CurrencyManager::getBaseCurrency(),
                'NAME'                   => $item['NAME'],
                'WEIGHT'                 => $item['WEIGHT'],
                'DETAIL_PAGE_URL'        => $item['DETAIL_PAGE_URL'],
                'PRODUCT_PROVIDER_CLASS' => CatalogProvider::class,
                'CATALOG_XML_ID'         => IblockUtils::getIblockId(IblockType::CATALOG, IblockCode::OFFERS),
                'PRODUCT_XML_ID'         => $item['PRODUCT_XML_ID'],
                'CAN_BUY'                => "Y",
            ]);

            /** @noinspection PhpInternalEntityUsedInspection */
            $basket->addItem($basketItem);
        }

        return $basket;
    }
}
