<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Dto\SberbankOrderInfo\OrderBundle;

use JMS\Serializer\Annotation as Serializer;

class Item
{
    /**
     * @var string
     *
     * @Serializer\SerializedName("positionId")
     * @Serializer\Type("string")
     */
    protected $positionId = '';

    /**
     * @var string
     *
     * @Serializer\SerializedName("name")
     * @Serializer\Type("string")
     */
    protected $name = '';

    /**
     * @var ItemQuantity
     *
     * @Serializer\SerializedName("quantity")
     * @Serializer\Type("FourPaws\SaleBundle\Dto\SberbankOrderInfo\OrderBundle\ItemQuantity")
     */
    protected $quantity;

    /**
     * @var int
     *
     * @Serializer\SerializedName("itemAmount")
     * @Serializer\Type("int")
     */
    protected $itemAmount = 0;

    /**
     * @var int
     *
     * @Serializer\SerializedName("itemCurrency")
     * @Serializer\Type("int")
     */
    protected $itemCurrency = 0;

    /**
     * @var string
     *
     * @Serializer\SerializedName("itemCode")
     * @Serializer\Type("string")
     */
    protected $itemCode = '';
}
