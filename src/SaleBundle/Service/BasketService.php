<?php
declare(strict_types=1);

namespace FourPaws\SaleBundle\Service;

use Bitrix\Catalog\Product\CatalogProvider;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Compatible\DiscountCompatibility;
use Bitrix\Sale\Order;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\SaleBundle\Discount\Gift;
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

    /**
     * BasketService constructor.
     *
     * @param CurrentUserProviderInterface $currentUserProvider
     */
    public function __construct(CurrentUserProviderInterface $currentUserProvider)
    {
        $this->currentUserProvider = $currentUserProvider;
    }

    /**
     * @param int $offerId
     * @param int $quantity
     *
     * @throws \FourPaws\SaleBundle\Exception\BitrixProxyException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \FourPaws\SaleBundle\Exception\InvalidArgumentException
     *
     * @return bool
     */
    public function addOfferToBasket(int $offerId, int $quantity = null): bool
    {
        if ($quantity < 1) {
            throw new InvalidArgumentException('Wrong $quantity');
        }
        if ($offerId < 1 || null === $quantity) {
            throw new InvalidArgumentException('Wrong $offerId');
        }

        $fields = [
            'PRODUCT_ID' => $offerId,
            'QUANTITY' => $quantity,
            'MODULE' => 'catalog',
            'PRODUCT_PROVIDER_CLASS' => CatalogProvider::class,
//            'PROPS' => [[
//                'NAME' => 'Тест',
//                'CODE' => 'TEST',
//                'VALUE' => 1,
//                'SORT' => 100,
//            ]]
        ];

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
     * @throws \FourPaws\SaleBundle\Exception\InvalidArgumentException
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
     * @param int $basketId
     * @param int|null $quantity
     *
     * @throws \Exception
     * @throws \FourPaws\SaleBundle\Exception\BitrixProxyException
     * @throws \FourPaws\SaleBundle\Exception\NotFoundException
     * @throws \FourPaws\SaleBundle\Exception\InvalidArgumentException
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
     * @throws \FourPaws\SaleBundle\Exception\InvalidArgumentException
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
            if(\count($giftGroups[$discountId]) === 1) {
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
            'USER_ID' => $userId
        ];
    }

    /**
     * Возвращает OfferCollection содержащих товары корзины и возможные подарки
     *
     * @return OfferCollection
     */
    public function getOfferCollection(): OfferCollection
    {
        return $this->offerCollection ?? $this->loadOfferCollection();
    }

    /**
     *
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
