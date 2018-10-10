<?php

namespace FourPaws\SaleBundle\Dto\Reports\RROrderReport;

use JMS\Serializer\Annotation as Serializer;

class Order
{
    /**
     * @var string
     *
     * @Serializer\SerializedName("Номер заказа")
     * @Serializer\Type("string")
     */
    protected $orderNumber;

    /**
     * @var \DateTimeImmutable
     *
     * @Serializer\SerializedName("Дата оформления")
     * @Serializer\Type("DateTimeImmutable<'Y-m-d'>")
     */
    protected $date;

    /**
     * @var string
     *
     * @Serializer\SerializedName("Артикул товара")
     * @Serializer\Type("string")
     */
    protected $productXmlId;

    /**
     * @var int
     *
     * @Serializer\SerializedName("ID пользователя")
     * @Serializer\Type("int")
     */
    protected $userId;

    /**
     * @return string
     */
    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

    /**
     * @param string $orderNumber
     *
     * @return Order
     */
    public function setOrderNumber(string $orderNumber): Order
    {
        $this->orderNumber = $orderNumber;

        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * @param \DateTimeImmutable $date
     *
     * @return Order
     */
    public function setDate(\DateTimeImmutable $date): Order
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return string
     */
    public function getProductXmlId(): string
    {
        return $this->productXmlId;
    }

    /**
     * @param string $productXmlId
     *
     * @return Order
     */
    public function setProductXmlId(string $productXmlId): Order
    {
        $this->productXmlId = $productXmlId;

        return $this;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     *
     * @return Order
     */
    public function setUserId(int $userId): Order
    {
        $this->userId = $userId;

        return $this;
    }
}
