<?php
/**
 * Created by PhpStorm.
 * Date: 08.02.2018
 * Time: 19:09
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */
declare(strict_types=1);

namespace FourPaws\SaleBundle\Discount\Utils;

use Bitrix\Sale\Order;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\SaleBundle\Discount\Gift;
use FourPaws\SaleBundle\Service\BasketService;

/**
 * Class Adder
 * @package FourPaws\SaleBundle\Discount\Utils
 */
class Adder
{
    /**
     * @var Order
     */
    private $order;

    private $basketService;

    /**
     * Adder constructor.
     *
     * @param Order $order
     *
     * @param BasketService $basketService
     */
    public function __construct(Order $order, BasketService $basketService)
    {
        $this->order = $order;
        $this->basketService = $basketService;
    }

    /**
     *
     *
     * @throws \FourPaws\SaleBundle\Exception\NotFoundException
     * @throws \RuntimeException
     * @throws \FourPaws\SaleBundle\Exception\InvalidArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\NotSupportedException
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \FourPaws\SaleBundle\Exception\BitrixProxyException
     */
    public function processOrder()
    {
        if (!$discount = $this->order->getDiscount()) {
            return;
        }
        // TODO Если подарок был а потом кончился необходимо здесь добавлять подарки которые есть
        $groups = Gift::getPossibleGiftGroups($this->order);

        foreach ($groups as $group) {
            if (\count($group) === 1) {
                $group = current($group);
            } else {
                throw new \RuntimeException('TODO');
            }

            if (
                ($group = $this->basketService->getGiftGroupOfferCollection($group['discountId']))
                && $group['list'] && $group['list'] instanceof OfferCollection
            ) {
                $existGiftsQuantity = $this->getExistGiftsQuantity($group);
                if ($group['count'] > $existGiftsQuantity) {
                    $group['count'] -= $existGiftsQuantity;
                    // Находим оффер с минимальной ценой
                    $minPrice = PHP_INT_MAX;
                    /** @var OfferCollection $OfferCollection */
                    $OfferCollection = $group['list'];
                    /** @var Offer $offer */
                    foreach ($OfferCollection as $offer) {
                        if ($minPrice > $offer->getPrice()) {
                            $minPrice = $offer->getPrice();
                            $offerId = $offer->getId();
                        }
                    }
                    if (!empty($offerId)) {
                        $this->addGift($offerId, $group['count'], $group['discountId']);
                    }
                }
            }
        }
    }

    /**
     *
     *
     * @param int $offerId
     * @param int $quantity
     * @param int $discountId
     * @param bool $selected
     *
     * @throws \FourPaws\SaleBundle\Exception\InvalidArgumentException
     * @throws \FourPaws\SaleBundle\Exception\BitrixProxyException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectNotFoundException
     */
    protected function addGift(int $offerId, int $quantity, int $discountId, bool $selected = false)
    {
        if (!$offerId || !$quantity || !$discountId || $offerId < 0 || $quantity < 0 || $discountId < 0) {
            return;
        }
        $fields = [
            'PRICE' => 0,
            'CUSTOM_PRICE' => 'Y',
            'NOTES' => 'Подарок',
            'PROPS' => [
                [
                    'NAME' => 'Подарок',
                    'CODE' => 'IS_GIFT',
                    'VALUE' => $discountId,
                    'SORT' => 100,
                ],
                [
                    'NAME' => 'Выбран пользователем',
                    'CODE' => 'IS_GIFT_SELECTED',
                    'VALUE' => $selected ? 'Y' : 'N',
                    'SORT' => 100,
                ],
            ]
        ];
        $this->basketService->addOfferToBasket($offerId, $quantity, $fields);
    }

    /**
     *
     *
     * @param int $discountId
     *
     * @return array
     */
    protected function getExistGifts(int $discountId): array
    {
        $result = Manager::getExistGifts($this->order);
        if (!empty($result) && !empty($discountId)) {
            $result = array_filter($result, function ($elem) use ($discountId) {
                return $elem['discountId'] === $discountId;
            });
        }
        return $result;
    }

    /**
     *
     *
     * @param array $group
     *
     * @return int
     */
    protected function getExistGiftsQuantity(array $group): int
    {
        $quantity = 0;
        $existGifts = $this->getExistGifts($group['discountId']);
        /** @var OfferCollection $offerCollection */
        $offerCollection = $group['list'];
        if (!empty($existGifts)) {
            foreach ($existGifts as $elem) {
                // Считаем только возможные подарки, остальные будут удалены
                if (\in_array($elem['offerId'], $offerCollection->getKeys(), true)) {
                    $quantity += $elem['quantity'];
                }
            }
        }
        return $quantity;
    }


}