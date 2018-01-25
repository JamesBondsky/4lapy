<?php

namespace FourPaws\MobileApiBundle\Dto\Response;

use FourPaws\MobileApiBundle\Dto\Object\OrderHistory;
use JMS\Serializer\Annotation as Serializer;

class OrderStatusHistoryResponse
{
    /**
     * ОбъектЗаказИстория[]
     * @Serializer\SerializedName("status_history")
     * @Serializer\Type("FourPaws\MobileApiBundle\Dto\Object\OrderHistory")
     * @var OrderHistory[]
     */
    protected $statusHistory = [];

    /**
     * @return OrderHistory[]
     */
    public function getStatusHistory(): array
    {
        return $this->statusHistory;
    }

    /**
     * @param OrderHistory[] $statusHistory
     *
     * @return OrderStatusHistoryResponse
     */
    public function setStatusHistory(array $statusHistory): OrderStatusHistoryResponse
    {
        $this->statusHistory = $statusHistory;
        return $this;
    }
}
