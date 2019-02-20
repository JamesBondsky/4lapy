<?php

namespace FourPaws\CatalogBundle\Dto\ExpertSender;

use Doctrine\Common\Annotations\Annotation\Required;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Promo
 *
 * @package FourPaws\CatalogBundle\Dto\ExpertSender
 *
 * @Serializer\XmlRoot("promo")
 */
class Promo
{
    /**
     * @Serializer\XmlAttribute()
     * @Serializer\Type("int")
     * @Required()
     *
     * @var int
     */
    protected $id;

    /**
     * @Serializer\XmlAttribute()
     * @Serializer\Type("string")
     * @Required()
     *
     * @var string
     */
    protected $type = '';

    /**
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("string")
     * @Serializer\SerializedName("start-date")
     *
     * @var string
     */
    protected $startDate;

    /**
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("string")
     * @Serializer\SerializedName("end-date")
     *
     * @var string
     */
    protected $endDate;

    /**
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $description;

    /**
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("string")
     *
     * @var string
     */
    protected $url;

    /**
     * @Serializer\XmlElement(cdata=false)
     * @Serializer\Type("FourPaws\CatalogBundle\Dto\ExpertSender\Purchase")
     *
     * @var Purchase
     */
    protected $purchase;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Promo
     */
    public function setId(int $id): Promo
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Promo
     */
    public function setType(string $type): Promo
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getStartDate(): string
    {
        return $this->startDate;
    }

    /**
     * @param string $startDate
     * @return Promo
     */
    public function setStartDate(string $startDate): Promo
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getEndDate(): string
    {
        return $this->endDate;
    }

    /**
     * @param string $endDate
     * @return Promo
     */
    public function setEndDate(string $endDate): Promo
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Promo
     */
    public function setDescription(string $description): Promo
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return Promo
     */
    public function setUrl(string $url): Promo
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return Purchase
     */
    public function getPurchase(): Purchase
    {
        return $this->purchase;
    }

    /**
     * @param Purchase $purchase
     * @return Promo
     */
    public function setPurchase(Purchase $purchase): Promo
    {
        $this->purchase = $purchase;

        return $this;
    }
}
