<?php

namespace FourPaws\SapBundle\Dto\In\Prices;

use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Prices
 * @package FourPaws\SapBundle\Dto\In
 * @Serializer\XmlRoot("ns0:mt_Prices")
 * @Serializer\XmlNamespace(uri="urn:4lapy.ru:ERP_2_BITRIX:DataExchange", prefix="ns0")
 */
class Prices
{
    /**
     * Выгружать в ИМ
     *
     * @Serializer\Type("sap_bool")
     * @Serializer\SerializedName("For_IM")
     * @Serializer\XmlAttribute()
     *
     * @var bool
     */
    protected $uploadToIm = false;

    /**
     * Код региона
     *
     * @Serializer\Type("sap_bool")
     * @Serializer\SerializedName("Plant")
     * @Serializer\XmlAttribute()
     *
     * @var string
     */
    protected $regionCode = '';

    /**
     * Цены
     *
     * @Serializer\XmlList(inline=true, entry="Item")
     * @Serializer\Type("ArrayCollection<FourPaws\SapBundle\Dto\In\Prices\Item>")
     *
     * @var Collection|Item[]
     */
    protected $items;
}
