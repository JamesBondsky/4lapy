<?php

namespace FourPaws\SapBundle\Dto\In;

use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Materials
 * @package FourPaws\SapBundle\Dto\In
 * @Serializer\XmlRoot("ns0:mt_Materials")
 * @Serializer\XmlNamespace(uri="urn:4lapy.ru:ERP_2_BITRIX:DataExchange", prefix="ns0")
 */
class Materials
{
    /**
     * Код файла выгрузки IDoc
     * @Serializer\SerializedName("DN")
     * @Serializer\XmlAttribute()
     * @Serializer\Type("integer")
     * @var int
     */
    protected $documentNumber;

    /**
     * @Serializer\XmlList(inline=true, entry="Mat")
     * @Serializer\Type("ArrayCollection<FourPaws\SapBundle\Dto\In\Material>")
     * @var Collection|Material[]
     */
    protected $materials;

    /**
     * @return int
     */
    public function getDocumentNumber(): int
    {
        return $this->documentNumber;
    }

    /**
     * @param int $documentNumber
     * @return Materials
     */
    public function setDocumentNumber(int $documentNumber): Materials
    {
        $this->documentNumber = $documentNumber;
        return $this;
    }
}
