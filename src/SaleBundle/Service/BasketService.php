<?php
declare(strict_types=1);

namespace FourPaws\SaleBundle\Service;

use Adv\Bitrixtools\Tools\BitrixUtils;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Catalog\Product\CatalogProvider;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Compatible\DiscountCompatibility;
use Bitrix\Sale\Order;
use Exception;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\External\Manzana\Exception\ExecuteException;
use FourPaws\External\ManzanaPosService;
use FourPaws\SaleBundle\Discount\Gift;
use FourPaws\SaleBundle\Discount\Utils;
use FourPaws\SaleBundle\Discount\Utils\AdderInterface;
use FourPaws\SaleBundle\Discount\Utils\CleanerInterface;
use FourPaws\SaleBundle\Exception\BitrixProxyException;
use FourPaws\SaleBundle\Exception\InvalidArgumentException;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class BasketService
 * @package FourPaws\SaleBundle\Service
 */
class BasketService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /** @var Basket */
    private $basket;
    /** @var CurrentUserProviderInterface */
    private $currentUserProvider;
    /** @var OfferCollection */
    private $offerCollection;
    /** @var ManzanaPosService */
    private $manzanaPosService;
    /** @todo КОСТЫЛЬ! УБРАТЬ В КУПОНЫ */
    private $promocodeDiscount = 0.0;
    private $firstDiscount = 0.0;

    /** Оплата бонусами до 90% заказа */
    public const MAX_BONUS_PAYMENT = 0.9;

    /**
     * BasketService constructor.
     *
     * @param CurrentUserProviderInterface $currentUserProvider
     * @param ManzanaPosService $manzanaPosService
     */
    public function __construct(
        CurrentUserProviderInterface $currentUserProvider,
        ManzanaPosService $manzanaPosService
    )
    {
        $this->currentUserProvider = $currentUserProvider;
        $this->manzanaPosService = $manzanaPosService;
    }


    /**
     * @param int $offerId
     * @param int|null $quantity
     * @param array $rewriteFields
     * @param bool $save
     * @param Basket|null $basket
     * @param bool $withPermissions
     * @return BasketItem
     * @throws BitrixProxyException
     * @throws ObjectNotFoundException
     * @throws \Bitrix\Main\LoaderException
     */
    public function addOfferToBasket(
        int $offerId,
        int $quantity = null,
        array $rewriteFields = [],
        bool $save = true,
        ?Basket $basket = null,
        bool $withPermissions = true
    ): BasketItem
    {
        if ($quantity < 0) {
            throw new InvalidArgumentException('Wrong $quantity');
        }
        if ($offerId < 1) {
            throw new InvalidArgumentException('Wrong $offerId');
        }
        if (!$quantity) {
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

        // Если корзина является аргументом, то берем контекст из нее, иначе от текущего юзера
        $context = $basket ? $basket->getContext() : $this->getContext();

        $basket = $basket instanceof Basket ? $basket : $this->getBasket();
        if ($withPermissions) {
            $result = \Bitrix\Catalog\Product\Basket::addProductToBasketWithPermissions(
                $basket,
                $fields,
                $context
            );
        } else {
            $result = \Bitrix\Catalog\Product\Basket::addProductToBasket(
                $basket,
                $fields,
                $context
            );
        }

        if (!$result->isSuccess()) {
            throw new BitrixProxyException($result);
        }
        if ($save) {
            $basket->save();
        }

        return $result->getData()['BASKET_ITEM'];
    }


    /**
     * @param int $basketId
     * @param Basket|null $basket
     * @param bool $save
     * @return bool
     * @throws BitrixProxyException
     * @throws ObjectNotFoundException
     */
    public function deleteOfferFromBasket(int $basketId, ?Basket $basket = null, bool $save = true): bool
    {
        if ($basketId < 1) {
            throw new InvalidArgumentException('Wrong $basketId');
        }
        if (!$basket) {
            $basket = $this->getBasket();
        }

        $basketItem = $basket->getItemById($basketId);
        if (null === $basketItem) {
            throw new NotFoundException('Не найден элемент корзины');
        }

        $result = $basketItem->delete();
        if (!$result->isSuccess()) {
            throw new BitrixProxyException($result);
        }

        if ($save) {
            $basket->save();
        }

        return true;
    }

    /**
     * @param int $basketId
     * @param int|null $quantity
     * @param Basket|null $basket
     * @param bool $save
     * @return bool
     * @throws ArgumentOutOfRangeException
     * @throws BitrixProxyException
     * @throws Exception
     */
    public function updateBasketQuantity(int $basketId, ?int $quantity = null, ?Basket $basket = null, bool $save = true): bool
    {
        if ($quantity < 1) {
            throw new InvalidArgumentException('Wrong $quantity');
        }

        if ($basketId < 1) {
            throw new InvalidArgumentException('Wrong $basketId');
        }

        if (!$basket) {
            $basket = $this->getBasket();
        }

        $basketItem = $basket->getItemById($basketId);
        if (null === $basketItem) {
            throw new NotFoundException('BasketItem');
        }

        $result = $basketItem->setField('QUANTITY', $quantity);
        if (!$result->isSuccess()) {
            throw new BitrixProxyException($result);
        }

        if ($save) {
            $basket->save();
        }

        return true;
    }


    /**
     * @param int|null $discountId
     * @param Order|null $order
     * @throws NotFoundException
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     *
     * @return array
     */
    public function getGiftGroupOfferCollection(?int $discountId = null, Order $order = null): array
    {
        if (!$discountId || $discountId < 0) {
            throw new InvalidArgumentException('Отсутствует идентификатор скидки');
        }

        if (!$order) {
            $basket = $this->getBasket();
            if (null === $order = $basket->getOrder()) {
                $order = Order::create(SITE_ID);
                $order->setBasket($basket);
            }
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
        $giftGroup['list'] = (new OfferQuery())->withFilterParameter('ID', $giftIds)->exec();

        return $giftGroup;
    }

    /**
     *
     *
     * @param bool|null $reload
     *
     * @param int $fUserId
     *
     * @return Basket
     */
    public function getBasket(bool $reload = null, int $fUserId = 0): Basket
    {
        if (null === $this->basket || $reload) {
            /** @var Basket $basket */
            /** @noinspection PhpInternalEntityUsedInspection */
            DiscountCompatibility::stopUsageCompatible();

            if ($fUserId === 0) {
                $fUserId = $this->currentUserProvider->getCurrentFUserId();
            }

            $this->basket = Basket::loadItemsForFUser($fUserId, SITE_ID);
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
     * @throws InvalidArgumentException
     *
     * @return OfferCollection
     */
    public function getOfferCollection(): OfferCollection
    {
        return $this->offerCollection ?? $this->loadOfferCollection();
    }

    /**
     * @throws InvalidArgumentException
     *
     * @return OfferCollection
     */
    private function loadOfferCollection(): OfferCollection
    {
        /**
         * @var Basket $basket
         * @var BasketItem $basketItem
         * @var OfferCollection $offerCollection
         *
         * @todo перенести в метод выше и при повторном запросе проверять айдишники,
         * если нет в коллекции, то делать запрос
         */
        $ids = [];
        $basket = $this->getBasket();
        foreach ($basket->getBasketItems() as $basketItem) {
            $ids[] = $basketItem->getProductId();
        }

        if (null !== $order = $basket->getOrder()) {
            /** @noinspection AdditionOperationOnArraysInspection */
            $ids += Gift::getPossibleGifts($order);
        }

        $ids = \array_flip(\array_flip(\array_filter($ids)));
        if (empty($ids)) {
            $ids = false;
        }

        $offerCollection = (new OfferQuery())->withFilterParameter('ID', $ids)->exec();

        return $this->offerCollection = $offerCollection;
    }

    /**
     *
     * @param BasketItem $basketItem
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws InvalidArgumentException
     * @throws ArgumentOutOfRangeException
     * @throws Exception
     * @throws ApplicationCreateException
     * @throws ArgumentException
     *
     * @return BasketItem
     */
    public function refreshItemAvailability(BasketItem $basketItem): BasketItem
    {
        $offerCollection = $this->getOfferCollection();
        /** @var Offer $offer */
        foreach ($offerCollection as $offer) {
            if ($offer->getId() !== (int)$basketItem->getProductId()) {
                continue;
            }

            $delay = $offer->getStocks()->isEmpty();
            if ($basketItem->isDelay() !== $delay) {
                $basketItem->setField(
                    'DELAY',
                    $delay ? BitrixUtils::BX_BOOL_TRUE : BitrixUtils::BX_BOOL_FALSE
                );
            }

            break;
        }

        return $basketItem;
    }

    /**
     * @todo Избавиться от этих двух методов, перенеся их непосредственно в обработчики акций, однако необходимо отделить общую часть от проектной
     */
    /**
     * @param string $type
     * @param bool $renew
     * @param Order|null $order
     * @return AdderInterface
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     */
    public function getAdder(string $type, bool $renew = false, Order $order = null): AdderInterface
    {
        static $storage;
/** @todo Добавил обнуление, т.к. на одном хите может обрабатываться много заказов */
$storage = null;

        if (null === $storage || $renew) {
            $storage = [
                'gift' => null,
                'detach' => null
            ];
        }
        if (!$order) {
            $order = $this->getBasket()->getOrder();
            if (!$order) {
                $order = Order::create(SITE_ID);
                $order->setBasket($this->getBasket());
            }
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
     * @param bool $renew
     * @param Order|null $order
     * @return CleanerInterface
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     */
    public function getCleaner(string $type, bool $renew = false, Order $order = null): CleanerInterface
    {
        static $storage;
/** @todo Добавил обнуление, т.к. на одном хите может обрабатываться много заказов */
$storage = null;

        if (null === $storage || $renew) {
            $storage = [
                'gift' => null,
                'detach' => null
            ];
        }
        if (!$order) {
            $order = $this->getBasket()->getOrder();
            if (!$order) {
                $order = Order::create(SITE_ID);
                $order->setBasket($this->getBasket());
            }
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
     * @return float
     */
    public function getBasketBonus(): float
    {
        /**
         * @todo Remove multiple return statements usage
         * @see https://github.com/kalessil/phpinspectionsea/blob/master/docs/architecture.md#multiple-return-statements-usage
         */
        try {
            try {
                $cardNumber = $this->currentUserProvider->getCurrentUser()->getDiscountCardNumber();
            } catch (NotAuthorizedException $e) {
                /** запрашиваем без карты */
            } catch (InvalidIdentifierException | ConstraintDefinitionException $e) {
                $logger = LoggerFactory::create('params');
                $logger->error($e->getMessage());
                /** запрашиваем без карты */
            }

            $cheque = $this->manzanaPosService->processChequeWithoutBonus(
                $this->manzanaPosService->buildRequestFromBasket(
                    $this->getBasket(),
                    $cardNumber ?? ''
                )
            );

            return $cheque->getChargedBonus();
        } catch (ExecuteException $e) {
            return 0.0;
        }
    }

    /**
     * @todo КОСТЫЛЬ
     *
     * @return void
     */
    public function setDiscountBeforeManzana(): void
    {
        $this->firstDiscount = $this->basket->getBasePrice() - $this->basket->getPrice();
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
        $this->promocodeDiscount = $promocodeDiscount - $this->firstDiscount;
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
                if ($user->getDiscountCardNumber()) {
                    $chequeRequest = $this->manzanaPosService->buildRequestFromBasket(
                        $basket,
                        $user->getDiscountCardNumber()
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
}
