<?php

namespace FourPaws\SapBundle\Dto\In\Stock;

use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Stock
 * @package FourPaws\SapBundle\Dto\In
 * @Serializer\XmlRoot("ns0:mt_Stock")
 * @Serializer\XmlNamespace(uri="urn:4lapy.ru:ERP_2_BITRIX:DataExchange", prefix="ns0")
 */
class Stock
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
     * Код ИМ
     *
     * @Serializer\Type("sap_string")
     * @Serializer\SerializedName("Plant_IM")
     * @Serializer\XmlAttribute()
     *
     * @var string
     */
    protected $plantIm = '';

    /**
     * Код РЦ
     *
     * @Serializer\Type("sap_string")
     * @Serializer\SerializedName("Plant_DC")
     * @Serializer\XmlAttribute()
     *
     * @var string
     */
    protected $plantDc = '';

    /**
     * Остатки
     *
     * @Serializer\XmlList(inline=true, entry="DC")
     * @Serializer\Type("ArrayCollection<FourPaws\SapBundle\Dto\In\Stock\Dc>")
     *
     * @var Collection|Dc[]
     */
    protected $dcs;

    /**
     * @return bool
     */
    public function getUploadToIm(): bool
    {
        return $this->uploadToIm;
    }

    /**
     * @param bool $uploadToIm
     *
     * @return Stock
     */
    public function setUploadToIm(bool $uploadToIm): Stock
    {
        $this->uploadToIm = $uploadToIm;
        return $this;
    }

    /**
     * @return string
     */
    public function getPlantIm(): string
    {
        return $this->plantIm;
    }

    /**
     * @param string $plantIm
     *
     * @return Stock
     */
    public function setPlantIm(string $plantIm): Stock
    {
        $this->plantIm = $plantIm;
        return $this;
    }

    /**
     * @return string
     */
    public function getPlantDc(): string
    {
        return $this->plantDc;
    }

    /**
     * @param string $plantDc
     *
     * @return Stock
     */
    public function setPlantDc(string $plantDc): Stock
    {
        $this->plantDc = $plantDc;
        return $this;
    }

    /**
     * @return Collection|Dc[]
     */
    public function getDcs(): Collection
    {
        return $this->dcs;
    }

    /**
     * @param Collection $dcs
     *
     * @return Stock
     */
    public function setDcs(Collection $dcs): Stock
    {
        $this->dcs = $dcs;
        return $this;
    }
}
