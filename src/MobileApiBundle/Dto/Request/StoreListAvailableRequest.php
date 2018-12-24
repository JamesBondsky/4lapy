<?php

namespace FourPaws\MobileApiBundle\Dto\Request;

use FourPaws\MobileApiBundle\Dto\Object\ProductQuantity;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class StoreListRequest
 * @package FourPaws\MobileApiBundle\Dto\Request
 */
class StoreListAvailableRequest implements SimpleUnserializeRequest, GetRequest
{
    /**
     * @Assert\NotBlank()
     * @Serializer\SerializedName("goods")
     * @Serializer\Type("array<FourPaws\MobileApiBundle\Dto\Object\ProductQuantity>")
     * @var ProductQuantity[]
     */
    protected $goods = [];

    /**
     * @Assert\NotBlank()
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("city_id")
     */
    protected $cityId = '';

    /**
     * @return ProductQuantity[]
     */
    public function getGoods(): array
    {
        return $this->goods;
    }

    /**
     * @param ProductQuantity[] $goods
     *
     * @return StoreListAvailableRequest
     */
    public function setGoods(array $goods): StoreListAvailableRequest
    {
        $this->goods = $goods;
        return $this;
    }

    /**
     * @return string
     */
    public function getCityId(): string
    {
        return $this->cityId ?? '';
    }

    /**
     * @param string $cityId
     *
     * @return StoreListAvailableRequest
     */
    public function setCityId(string $cityId): StoreListAvailableRequest
    {
        $this->cityId = $cityId;
        return $this;
    }
}
