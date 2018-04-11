<?php

namespace FourPaws\PersonalBundle\Entity;

use Bitrix\Main\Result;
use FourPaws\SaleBundle\Helper\OrderCopy;

class OrderSubscribeCopyResult extends Result
{
    /** @var OrderSubscribeCopyParams $orderSubscribeCopyParams */
    private $orderSubscribeCopyParams;
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
     * @param OrderSubscribeCopyParams $params
     * @return OrderSubscribeCopyResult
     */
    public function setOrderSubscribeCopyParams(OrderSubscribeCopyParams $params): self
    {
        $this->orderSubscribeCopyParams = $params;

        return $this;
    }

    /**
     * @return OrderSubscribeCopyParams|null
     */
    public function getOrderSubscribeCopyParams()
    {
        return $this->orderSubscribeCopyParams ?? null;
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
