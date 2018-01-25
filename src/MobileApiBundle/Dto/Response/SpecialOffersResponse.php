<?php

namespace FourPaws\MobileApiBundle\Dto\Response;

use FourPaws\MobileApiBundle\Dto\Object\Catalog\ShortProduct;
use JMS\Serializer\Annotation as Serializer;

class SpecialOffersResponse
{
    /**
     * @Serializer\SerializedName("goods")
     * @Serializer\Type("array<FourPaws\MobileApiBundle\Dto\Object\Catalog\ShortProduct>")
     * @var ShortProduct[]
     */
    protected $goods = [];

    /**
     * @Serializer\SerializedName("total_items")
     * @Serializer\Type("integer")
     * @var int
     */
    protected $totalItem;

    /**
     * @Serializer\Type("integer")
     * @Serializer\SerializedName("total_pages")
     * @var int
     */
    protected $totalPages;

    /**
     * @return ShortProduct[]
     */
    public function getGoods(): array
    {
        return $this->goods;
    }

    /**
     * @param ShortProduct[] $goods
     *
     * @return SpecialOffersResponse
     */
    public function setGoods(array $goods): SpecialOffersResponse
    {
        $this->goods = $goods;
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalItem(): int
    {
        return $this->totalItem;
    }

    /**
     * @param int $totalItem
     *
     * @return SpecialOffersResponse
     */
    public function setTotalItem(int $totalItem): SpecialOffersResponse
    {
        $this->totalItem = $totalItem;
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    /**
     * @param int $totalPages
     *
     * @return SpecialOffersResponse
     */
    public function setTotalPages(int $totalPages): SpecialOffersResponse
    {
        $this->totalPages = $totalPages;
        return $this;
    }
}
