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
}
