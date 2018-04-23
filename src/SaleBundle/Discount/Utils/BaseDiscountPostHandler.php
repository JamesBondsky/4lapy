<?php
/**
 * Created by PhpStorm.
 * Date: 15.03.2018
 * Time: 13:17
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Discount\Utils;

use Bitrix\Sale\Order;
use FourPaws\SaleBundle\Service\BasketService;


/**
 * Class BaseDiscountPostHandler
 * @package FourPaws\SaleBundle\Discount\Utils
 */
abstract class BaseDiscountPostHandler
{
    /**
     * @var Order
     */
    protected $order;
    /**
     * @var BasketService
     */
    protected $basketService;


    /**
     * BaseDiscountPostHandler constructor.
     *
     * @param Order $order
     * @param BasketService $basketService
     */
    public function __construct(Order $order, BasketService $basketService)
    {
        $this->order = $order;
        $this->basketService = $basketService;
    }

    abstract public function processOrder(): void;

    /**
     * @return bool
     */
    protected function canBasketSave(): bool
    {
        $result = true;

        /**
         * Здесь есть вопросы с сохранением в базу.
         * При работе из публички нужно сохранять, чтобы выбранный юзером подарок не сбрасывался.
         * А, например, при копировании заказа сохранение сбросит текущую корзину авторизованного пользователя.
         * Пока решил отдавать флаг необходимости сохранения, если в составе корзины нет позиций без ID
         * (т.е. еще не сброшенных в базу)
         */
        $basket = $this->order->getBasket();
        if ($basket) {
            foreach ($basket->getBasketItems() as $item) {
                /** @var $item \Bitrix\Sale\BasketItem */
                if (!$item->getId()) {
                    $result = false;
                    break;
                }
            }
        }

        return $result;
    }
}
