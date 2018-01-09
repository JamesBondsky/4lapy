<?php

namespace FourPaws\SapBundle\Repository;

use Bitrix\Highloadblock\DataManager;
use FourPaws\BitrixOrm\Query\HlbReferenceQuery;
use FourPaws\Catalog\Model\Offer;
use FourPaws\SapBundle\Dto\In\Offers\Material;
use FourPaws\SapBundle\Enum\OfferProperty;

class OfferRepository
{
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

    protected function getOrCreate(DataManager $dataManager, string $name, string $xmlId)
    {
        $hlbElement = (new HlbReferenceQuery($dataManager::query()))
            ->withFilterParameter('=UF_XML_ID', $xmlId)
            ->exec()
            ->current();
    }
}
