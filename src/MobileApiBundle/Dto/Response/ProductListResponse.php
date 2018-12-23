<?php

namespace FourPaws\MobileApiBundle\Dto\Response;

use FourPaws\MobileApiBundle\Dto\Object\Catalog\FullProduct;
use JMS\Serializer\Annotation as Serializer;

class ProductListResponse
{
    /**
     * @Serializer\Type("array<FourPaws\MobileApiBundle\Dto\Object\Catalog\FullProduct>")
     * @Serializer\SerializedName("goods")
     * @var FullProduct[]
     */
    protected $productList = [];

    /**
     * @Serializer\Type("int")
     * @Serializer\SerializedName("total_items")
     * @var int
     */
    protected $totalItems = 0;

    /**
     * @Serializer\Type("int")
     * @Serializer\SerializedName("total_pages")
     * @var int
     */
    protected $totalPages = 0;

    /**
     * @return FullProduct[]
     */
    public function getProductList(): array
    {
        return $this->productList;
    }

    /**
     * @param FullProduct[] $productList
     *
     * @return ProductListResponse
     */
    public function setProductList(array $productList): ProductListResponse
    {
        $this->productList = $productList;
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalItems(): int
    {
        return $this->totalItems;
    }

    /**
     * @param int $totalItems
     *
     * @return ProductListResponse
     */
    public function setTotalItems(int $totalItems): ProductListResponse
    {
        $this->totalItems = $totalItems;
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
     * @return ProductListResponse
     */
    public function setTotalPages(int $totalPages): ProductListResponse
    {
        $this->totalPages = $totalPages;
        return $this;
    }
}
