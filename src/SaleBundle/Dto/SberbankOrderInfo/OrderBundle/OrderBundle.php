<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Dto\SberbankOrderInfo\OrderBundle;

use JMS\Serializer\Annotation as Serializer;

class OrderBundle
{
    /**
     * @var int
     *
     * @Serializer\SerializedName("orderCreationDate")
     * @Serializer\Type("int")
     */
    protected $orderCreationDate = 0;

    /**
     * @var CustomerDetails
     *
     * @Serializer\SerializedName("customerDetails")
     * @Serializer\Type("FourPaws\SaleBundle\Dto\SberbankOrderInfo\OrderBundle\CustomerDetails")
     */
    protected $customerDetails;

    /**
     * @var CartItems
     *
     * @Serializer\SerializedName("cartItems")
     * @Serializer\Type("FourPaws\SaleBundle\Dto\SberbankOrderInfo\OrderBundle\CartItems")
     */
    protected $cartItems;

    /**
     * @return int
     */
    public function getOrderCreationDate(): int
    {
        return $this->orderCreationDate;
    }

    /**
     * @param int $orderCreationDate
     * @return OrderBundle
     */
    public function setOrderCreationDate(int $orderCreationDate): OrderBundle
    {
        $this->orderCreationDate = $orderCreationDate;

        return $this;
    }

    /**
     * @return CustomerDetails
     */
    public function getCustomerDetails(): CustomerDetails
    {
        return $this->customerDetails;
    }

    /**
     * @param CustomerDetails $customerDetails
     * @return OrderBundle
     */
    public function setCustomerDetails(CustomerDetails $customerDetails): OrderBundle
    {
        $this->customerDetails = $customerDetails;

        return $this;
    }

    /**
     * @return CartItems
     */
    public function getCartItems(): CartItems
    {
        return $this->cartItems;
    }

    /**
     * @param CartItems $cartItems
     * @return OrderBundle
     */
    public function setCartItems(CartItems $cartItems): OrderBundle
    {
        $this->cartItems = $cartItems;

        return $this;
    }
}
