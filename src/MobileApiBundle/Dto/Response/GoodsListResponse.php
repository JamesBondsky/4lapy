<?php

namespace FourPaws\MobileApiBundle\Dto\Response;

use FourPaws\MobileApiBundle\Dto\Object\Catalog\ShortProduct;

class GoodsListResponse
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
     * @return GoodsListResponse
     */
    public function setGoods(array $goods): GoodsListResponse
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
     * @return GoodsListResponse
     */
    public function setTotalItem(int $totalItem): GoodsListResponse
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
     * @return GoodsListResponse
     */
    public function setTotalPages(int $totalPages): GoodsListResponse
    {
        $this->totalPages = $totalPages;
        return $this;
    }
}
