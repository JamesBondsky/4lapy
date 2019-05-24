<?php

namespace FourPaws\PersonalBundle\Entity;


use Bitrix\Main\Type\DateTime;
use FourPaws\SaleBundle\Entity\OrderStatus;

class OrderStatusChange
{
    /**
     * @var OrderStatus
     */
    protected $orderStatus;

    /**
     * @var DateTime
     */
    protected $dateCreate;

    /**
     * @return DateTime
     */
    public function getDateCreate(): DateTime
    {
        return $this->dateCreate;
    }

    /**
     * @param DateTime $dateCreate
     * @return OrderStatusChange
     */
    public function setDateCreate(DateTime $dateCreate): OrderStatusChange
    {
        $this->dateCreate = $dateCreate;
        return $this;
    }

    /**
     * @return OrderStatus
     */
    public function getOrderStatus(): OrderStatus
    {
        return $this->orderStatus;
    }

    /**
     * @param OrderStatus $orderStatus
     * @return OrderStatusChange
     */
    public function setOrderStatus(OrderStatus $orderStatus): OrderStatusChange
    {
        $this->orderStatus = $orderStatus;
        return $this;
    }
}