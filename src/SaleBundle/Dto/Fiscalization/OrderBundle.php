<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Dto\Fiscalization;

use JMS\Serializer\Annotation as Serializer;

class OrderBundle
{
    /**
     * @var CartItems
     *
     * @Serializer\SerializedName("cartItems")
     * @Serializer\Type("FourPaws\SaleBundle\Dto\Fiscalization\CartItems")
     */
    protected $cartItems;

    /**
     * @var \DateTime
     *
     * @Serializer\SerializedName("orderCreationDate")
     * @Serializer\Type("DateTime<'U'>")
     */
    protected $dateCreate;

    /**
     * @var CustomerDetails
     *
     * @Serializer\SerializedName("customerDetails")
     * @Serializer\Type("FourPaws\SaleBundle\Dto\Fiscalization\CustomerDetails")
     */
    protected $customerDetails;

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

    /**
     * @return \DateTime
     */
    public function getDateCreate(): \DateTime
    {
        return $this->dateCreate;
    }

    /**
     * @param \DateTime $dateCreate
     * @return OrderBundle
     */
    public function setDateCreate(\DateTime $dateCreate): OrderBundle
    {
        $this->dateCreate = $dateCreate;
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
}