<?php

namespace FourPaws\SapBundle\Repository;

use FourPaws\Catalog\Query\ProductQuery;
use FourPaws\SapBundle\Dto\In\Offers\Material;
use FourPaws\SapBundle\Dto\In\Offers\PropertyValue;
use FourPaws\SapBundle\Enum\OfferProperty;

class ProductRepository
{
    public function findByMaterial(Material $material)
    {
        $query = new ProductQuery();
        $query->withFilter($query->getBaseFilter());

        /**
         * Если есть объединение по упаковке - ищем продукт по объединению
         * @var PropertyValue $value
         */
        if (
            ($packingProperty = $material->getProperties()->getProperty(OfferProperty::PACKING_COMBINATION)) &&
            ($value = $packingProperty->getValues()->first()) &&
            $value->getCode()
        ) {
            $query->withFilterParameter('PROPERTY_PACKING_COMBINATION', $value->getCode());
            return $query->exec()->current();
        }

        $query->withFilterParameter('=XML_ID', $material->getOfferXmlId());
        return $query->exec()->current();
    }

    public function createByMaterial()
    {
    }
}
