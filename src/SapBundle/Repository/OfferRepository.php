<?php

namespace FourPaws\SapBundle\Repository;

use Bitrix\Highloadblock\DataManager;
use FourPaws\BitrixOrm\Query\HlbReferenceQuery;
use FourPaws\Catalog\Model\Offer;
use FourPaws\SapBundle\Dto\In\Offers\Material;
use FourPaws\SapBundle\Dto\In\Offers\Property;
use FourPaws\SapBundle\Dto\In\Offers\PropertyValue;
use FourPaws\SapBundle\Enum\OfferProperty;
use FourPaws\SapBundle\ReferenceDirectory\SapReferenceStorage;

class OfferRepository
{
    /**
     * @var SapReferenceStorage
     */
    private $sapReferenceStorage;

    public function __construct(SapReferenceStorage $sapReferenceStorage)
    {
        $this->sapReferenceStorage = $sapReferenceStorage;
    }

    public function createByMaterial(Material $material)
    {
    }

    public function fillHlbReference(Offer $offer, Material $material)
    {
        $properties = $material->getProperties();

        $colour = $properties->getProperty(OfferProperty::COLOUR);
        $volume = $properties->getProperty(OfferProperty::VOLUME);
        $clothingSize = $properties->getProperty(OfferProperty::CLOTHING_SIZE);
        $kindOfPacking = $properties->getProperty(OfferProperty::KIND_OF_PACKING);
        $seasonOfYear = $properties->getProperty(OfferProperty::SEASON_YEAR);
    }

    protected function getSapHlReference(Property $property)
    {
        /**
         * @var PropertyValue $value
         */
        $value = $property->getValues()->first();
        return $this->sapReferenceStorage->findByXmlId($property->getCode(), $value->getCode());
    }

    protected function getOrCreate(DataManager $dataManager, string $name, string $xmlId)
    {
        $hlbElement = (new HlbReferenceQuery($dataManager::query()))
            ->withFilterParameter('=UF_XML_ID', $xmlId)
            ->exec()
            ->current();
    }
}
