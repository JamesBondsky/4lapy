<?php
/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace FourPaws\SaleBundle\Dto\Fiscalization;

use JMS\Serializer\Annotation as Serializer;

class ItemTax
{
    /**
     * @var int
     *
     * @Serializer\SerializedName("taxType")
     * @Serializer\Type("int")
     */
    protected $type;

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     * @return ItemTax
     */
    public function setType(int $type): ItemTax
    {
        $this->type = $type;
        return $this;
    }
}