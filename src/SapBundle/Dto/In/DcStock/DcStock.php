<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Dto\In\DcStock;

use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Stock
 * @package FourPaws\SapBundle\Dto\In
 * @Serializer\XmlRoot("ns0:mt_Stock")
 * @Serializer\XmlNamespace(uri="urn:4lapy.ru:ERP_2_BITRIX:DataExchange", prefix="ns0")
 */
class DcStock
{
    /**
     * Метка времени
     *
     * @Serializer\XmlElement()
     * @Serializer\Type("int")
     * @Serializer\SerializedName("TIMESTAMP")
     *
     * @var string
     */
    protected $timestamp = '';

    /**
     * Остатки
     *
     * @Serializer\XmlList(inline=true, entry="STOCKITEMS")
     * @Serializer\Type("ArrayCollection<FourPaws\SapBundle\Dto\In\DcStock\StockItem>")
     *
     * @var Collection|StockItem[]
     */
    protected $items;

    /**
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    /**
     * @param int $timestamp
     *
     * @return DcStock
     */
    public function setTimestamp(int $timestamp): DcStock
    {
        $this->timestamp = $timestamp;
        return $this;
    }

    /**
     * @return Collection|StockItem[]
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    /**
     * @param Collection $items
     *
     * @return DcStock
     */
    public function setItems(Collection $items): DcStock
    {
        $this->items = $items;
        return $this;
    }
}
