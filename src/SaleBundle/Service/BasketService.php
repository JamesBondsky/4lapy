<?php

namespace FourPaws\SaleBundle\Service;

class BasketService
{
    public function __construct()
    {
        /**
         * Внешние зависимости желательно определять в конструкторе
         */
    }
    
    /**
     * @param int $offerId
     * @param int $quantity
     *
     * @return bool
     * @throws \RuntimeException
     */
    public function addOfferToBasket(int $offerId, int $quantity = 1) : bool
    {
        /**
         * @todo добавление в корзину
         */
        
        throw new \RuntimeException('А тут ошибки');
    }
    
    /**
     * @param int $basketId
     *
     * @return bool
     * @throws \RuntimeException
     */
    public function deleteOfferFromBasket(int $basketId) : bool
    {
        /**
         * @todo удаление из корзины
         */
        throw new \RuntimeException('А тут ошибки');
    }
    
}
