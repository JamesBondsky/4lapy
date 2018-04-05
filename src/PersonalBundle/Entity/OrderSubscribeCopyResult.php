<?php

namespace FourPaws\PersonalBundle\Entity;

use Bitrix\Main\Result;
use FourPaws\SaleBundle\Helper\OrderCopy;

class OrderSubscribeCopyResult extends Result
{
    /** @var OrderCopy $orderCopyHelper */
    private $orderCopyHelper;
    /** @var \Bitrix\Sale\Result $orderSaveResult */
    private $orderSaveResult;

    /**
     * @param string $offsetKey
     * @param $value
     * @return OrderSubscribeCopyResult
     */
    public function offsetSetData(string $offsetKey, $value): self
    {
        $data = $this->getData();
        $data[$offsetKey] = $value;
        $this->setData($data);

        return $this;
    }

    /**
     * @param OrderCopy $orderCopyHelper
     * @return OrderSubscribeCopyResult
     */
    public function setOrderCopyHelper(OrderCopy $orderCopyHelper): self
    {
        $this->orderCopyHelper = $orderCopyHelper;

        return $this;
    }

    /**
     * @return OrderCopy|null
     */
    public function getOrderCopyHelper()
    {
        return $this->orderCopyHelper ?? null;
    }

    /**
     * @param \Bitrix\Sale\Result $orderSaveResult
     * @return OrderSubscribeCopyResult
     */
    public function setOrderSaveResult(\Bitrix\Sale\Result $orderSaveResult): self
    {
        $this->orderSaveResult = $orderSaveResult;

        return $this;
    }

    /**
     * @return \Bitrix\Sale\Result|null
     */
    public function getOrderSaveResult()
    {
        return $this->orderSaveResult ?? null;
    }

    /**
     * @return int
     */
    public function getNewOrderId(): int
    {
        $saveResult = $this->getOrderSaveResult();

        return $saveResult ? $saveResult->getId() : 0;
    }
}
