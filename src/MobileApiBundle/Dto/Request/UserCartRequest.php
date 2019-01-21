<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Object\OrderParameter;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class UserCartRequest implements SimpleUnserializeRequest, GetRequest
{
    /**
     * Промокод
     * @Serializer\SerializedName("promocode")
     * @Serializer\Type("string")
     * @var string
     */
    protected $promoCode = '';

    /**
     * @return string
     */
    public function getPromoCode(): string
    {
        return $this->promoCode;
    }

    /**
     * @param string $promoCode
     *
     * @return UserCartRequest
     */
    public function setPromoCode(string $promoCode): UserCartRequest
    {
        $this->promoCode = $promoCode;
        return $this;
    }
}
