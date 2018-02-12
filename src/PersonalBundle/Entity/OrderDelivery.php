<?php

namespace FourPaws\PersonalBundle\Entity;


use Bitrix\Main\Type\DateTime;
use FourPaws\AppBundle\Entity\BaseEntity;
use FourPaws\Helpers\DateHelper;
use JMS\Serializer\Annotation as Serializer;

class OrderDelivery extends BaseEntity
{
    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\SerializedName("DELIVERY_NAME")
     * @Serializer\Groups(groups={"read","update", "create"})
     */
    protected $deliveryName = '';

    /**
     * @var float
     * @Serializer\Type("float")
     * @Serializer\SerializedName("PRICE_DELIVERY")
     * @Serializer\Groups(groups={"read","update", "create"})
     */
    protected $priceDelivery = 0;

    /**
     * @var bool
     * @Serializer\Type("bitrix_bool")
     * @Serializer\SerializedName("DEDUCTED")
     * @Serializer\Groups(groups={"read","update", "create"})
     */
    protected $deducted = false;

    /**
     * @var DateTime
     * @Serializer\Type("bitrix_date_time")
     * @Serializer\SerializedName("DATE_DEDUCTED")
     * @Serializer\Groups(groups={"read","update", "create"})
     */
    protected $dateDeducted;

    /**
     * @return string
     */
    public function getDeliveryName(): string
    {
        return $this->deliveryName ?? '';
    }

    /**
     * @param string $deliveryName
     */
    public function setDeliveryName(string $deliveryName)
    {
        $this->deliveryName = $deliveryName;
    }

    /**
     * @return float
     */
    public function getPriceDelivery(): float
    {
        return $this->priceDelivery ?? 0;
    }

    public function getFormatedPriceDelivery()
    {
        return number_format(round($this->getPriceDelivery(), 2), 2, '.', ' ');
    }

    /**
     * @param float $priceDelivery
     */
    public function setPriceDelivery(float $priceDelivery)
    {
        $this->priceDelivery = $priceDelivery;
    }

    /**
     * @return bool
     */
    public function isDeducted(): bool
    {
        return $this->deducted ?? false;
    }

    /**
     * @param bool $deducted
     */
    public function setDeducted(bool $deducted)
    {
        $this->deducted = $deducted;
    }

    /**
     * @return DateTime
     */
    public function getDateDeducted(): DateTime
    {
        return $this->dateDeducted;
    }

    /**
     * @param DateTime $dateDeducted
     */
    public function setDateDeducted(DateTime $dateDeducted)
    {
        $this->dateDeducted = $dateDeducted;
    }

    /**
     * @return string
     */
    public function getFormatedDateDeducted(): string
    {
        return DateHelper::replaceRuMonth($this->getDateDeducted()->format('d #m# Y'), DateHelper::GENITIVE);
    }
}