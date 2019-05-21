<?php

namespace FourPaws\MobileApiBundle\Dto\Response;

use FourPaws\MobileApiBundle\Dto\Object\Order;
use JMS\Serializer\Annotation as Serializer;

class OrderListResponse
{
    /**
     * @Serializer\Type("array<FourPaws\MobileApiBundle\Dto\Object\Order>")
     * @var Order[]
     */
    protected $orderList = [];

    /**
     * @var int
     */
    protected $totalItems = 0;

    /**
     * @var int
     */
    protected $totalPages = 0;

    /**
     * @return Order[]
     */
    public function getOrderList(): array
    {
        return $this->orderList;
    }

    /**
     * @param Order[] $orderList
     *
     * @return OrderListResponse
     */
    public function setOrderList(array $orderList): OrderListResponse
    {
        $this->orderList = $orderList;
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
     * @return OrderListResponse
     */
    public function setTotalItems(int $totalItems): OrderListResponse
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
     * @return OrderListResponse
     */
    public function setTotalPages(int $totalPages): OrderListResponse
    {
        $this->totalPages = $totalPages;
        return $this;
    }

}
