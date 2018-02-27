<?php
declare(strict_types=1);

namespace FourPaws\SaleBundle\Service;

use Adv\Bitrixtools\Tools\BitrixUtils;
use Bitrix\Catalog\Product\CatalogProvider;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Compatible\DiscountCompatibility;
use Bitrix\Sale\Order;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\External\Manzana\Exception\ExecuteException;
use FourPaws\External\ManzanaPosService;
use FourPaws\External\ManzanaService;
use FourPaws\SaleBundle\Discount\Gift;
use FourPaws\SaleBundle\Discount\Utils\Adder;
use FourPaws\SaleBundle\Discount\Utils\Cleaner;
use FourPaws\SaleBundle\Exception\BitrixProxyException;
use FourPaws\SaleBundle\Exception\InvalidArgumentException;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;

/**
 * Class BasketService
 * @package FourPaws\SaleBundle\Service
 */
class BasketService
{
    /** @var Basket */
    private $basket = null;

    /** @var CurrentUserProviderInterface */
    private $currentUserProvider;

    /** @var OfferCollection */
    private $offerCollection = null;
    /** @var ManzanaPosService */
    private $manzanaPosService;
    /** @var ManzanaService */
    private $manzanaService;

    /**
     * BasketService constructor.
     *
     * @param CurrentUserProviderInterface $currentUserProvider
     * @param ManzanaPosService            $manzanaPosService
     * @param ManzanaService               $manzanaService
     */
    public function __construct(
        CurrentUserProviderInterface $currentUserProvider,
        ManzanaPosService $manzanaPosService,
        ManzanaService $manzanaService
    ) {
        $this->currentUserProvider = $currentUserProvider;
        $this->manzanaPosService = $manzanaPosService;
        $this->manzanaService = $manzanaService;
    }


    /**
     *
     *
     * @param int      $offerId
     * @param int|null $quantity
     * @param array    $rewriteFields
     *
     * @throws InvalidArgumentException
     * @throws BitrixProxyException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @return bool
     */
    public function addOfferToBasket(int $offerId, int $quantity = null, array $rewriteFields = []): bool
    {
        if ($quantity < 0) {
            throw new InvalidArgumentException('Wrong $quantity');
        }
        if ($offerId < 1 || null === $quantity) {
            throw new InvalidArgumentException('Wrong $offerId');
        }
        if (!$quantity) {
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

        // вызов новго провайдера
//        \Bitrix\Sale\Internals\Catalog\Provider::getProductData(
//            $this->getBasket(), $this->getContext()
//        );

        $result = \Bitrix\Catalog\Product\Basket::addProductToBasketWithPermissions(
            $this->getBasket(),
            $fields,
            $this->getContext()
        );

        if (!$result->isSuccess()) {
            throw new BitrixProxyException($result);
        }
        $this->getBasket()->save();

        return true;
    }


    /**
     * @param int $basketId
     *
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \FourPaws\SaleBundle\Exception\BitrixProxyException
     * @throws \FourPaws\SaleBundle\Exception\NotFoundException
     * @throws InvalidArgumentException
     * @throws \Exception
     *
     * @return bool
     */
    public function deleteOfferFromBasket(int $basketId): bool
    {
        if ($basketId < 1) {
            throw new InvalidArgumentException('Wrong $basketId');
        }
        $basketItem = $this->getBasket()->getItemById($basketId);
        if (null === $basketItem) {
            throw new NotFoundException('Не найден элемент корзины');
        }
        $result = $basketItem->delete();
        if (!$result->isSuccess()) {
            throw new BitrixProxyException($result);
        }
        $this->getBasket()->save();
        return true;
    }

    /**
     *
     *
     * @param int      $basketId
     * @param int|null $quantity
     *
     * @throws \Exception
     * @throws \FourPaws\SaleBundle\Exception\BitrixProxyException
     * @throws \FourPaws\SaleBundle\Exception\NotFoundException
     * @throws InvalidArgumentException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     *
     * @return bool
     */
    public function updateBasketQuantity(int $basketId, int $quantity = null): bool
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
        $this->getBasket()->save();
        return true;
    }


    /**
     *
     *
     * @param int|null $discountId
     *
     * @throws \FourPaws\SaleBundle\Exception\NotFoundException
     * @throws InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectNotFoundException
     *
     * @return array
     */
    public function getGiftGroupOfferCollection(int $discountId = null): array
    {
        if (!$discountId || $discountId < 0) {
            throw new InvalidArgumentException('Отсутствует идентификатор скидки');
        }
        $basket = $this->getBasket();
        if (null === $order = $basket->getOrder()) {
            $order = Order::create(SITE_ID);
            $order->setBasket($basket);
        }
        if ($giftGroups = Gift::getPossibleGiftGroups($order, $discountId)) {
            if (\count($giftGroups[$discountId]) === 1) {
                $giftGroup = current($giftGroups[$discountId]);
            } else {
                throw new \RuntimeException('todo');
            }
        } else {
            throw new NotFoundException('Товары по акции не найдены');
        }
        $giftIds = $giftGroup['list'];
        if (!\is_array($giftIds) || !($giftIds = array_flip(array_flip(array_filter($giftIds))))) {
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
     * @return Basket
     */
    public function getBasket(bool $reload = null): Basket
    {
        if (null === $this->basket || $reload) {
            /** @var Basket $basket */
            /** @noinspection PhpInternalEntityUsedInspection */
            DiscountCompatibility::stopUsageCompatible();
            $this->basket = Basket::loadItemsForFUser($this->currentUserProvider->getCurrentFUserId(), SITE_ID);
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
     * @param BasketItem $basketItem
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
     *
     *
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @return Adder
     */
    public function getAdder(): Adder
    {
        if (null === $order = $this->getBasket()->getOrder()) {
            $order = Order::create(SITE_ID);
            $order->setBasket($this->getBasket());
        }
        return new Adder($order, $this);
    }

    /**
     *
     *
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @return Cleaner
     */
    public function getCleaner(): Cleaner
    {
        if (null === $order = $this->getBasket()->getOrder()) {
            $order = Order::create(SITE_ID);
            $order->setBasket($this->getBasket());
        }
        return new Cleaner($order, $this);
    }

    /**
     * @param Offer $offer
     * @param int   $quantity
     *
     * @return float
     */
    public function getItemBonus(Offer $offer, int $quantity = 1): float
    {
        try {
            $cardNumber = $this->currentUserProvider->getActiveCard();
            $cheque = $this->manzanaPosService->processChequeWithoutBonus(
                $this->manzanaPosService->buildRequestFromItem(
                    $offer,
                    $cardNumber,
                    $quantity
                )
            );

            return $cheque->getChargedBonus();
        } catch (ExecuteException $e) {
            return (float)0;
        }
    }

    /**
     * @return float
     */
    public function getBasketBonus(): float
    {
        try {
            $cardNumber = $this->currentUserProvider->getActiveCard();
            $cheque = $this->manzanaPosService->processChequeWithoutBonus(
                $this->manzanaPosService->buildRequestFromBasket(
                    $this->getBasket(),
                    $cardNumber
                )
            );

            return $cheque->getChargedBonus();
        } catch (ExecuteException $e) {
            return (float)0;
        }
    }

    /**
     *
     *
     * @throws InvalidArgumentException
     *
     * @return OfferCollection
     */
    private function loadOfferCollection(): OfferCollection
    {
        //todo перенести в метод выше и при повторном запросе проверять айдишники, если нет в коллекции, то делать запрос
        $ids = [];
        /** @var Basket $basket */
        $basket = $this->getBasket();
        /** @var BasketItem $basketItem */
        foreach ($basket->getBasketItems() as $basketItem) {
            $ids[] = $basketItem->getProductId();
        }
        if (null !== $order = $basket->getOrder()) {
            /** @noinspection AdditionOperationOnArraysInspection */
            $ids += Gift::getPossibleGifts($order);
        }
        $ids = array_flip(array_flip(array_filter($ids)));

        if (empty($ids)) {
            $ids = false;
        }
        /** @var OfferCollection $offerCollection */
        $offerCollection = (new OfferQuery())->withFilterParameter('ID', $ids)->exec();

        return $this->offerCollection = $offerCollection;
    }
}
