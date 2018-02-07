<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SaleBundle\Repository;

use Bitrix\Sale\Internals\OrderPropsVariantTable;
use FourPaws\SaleBundle\Collection\OrderPropertyVariantCollection;
use FourPaws\SaleBundle\Entity\OrderProperty;
use FourPaws\SaleBundle\Entity\OrderPropertyVariant;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\DeserializationContext;

class OrderPropertyVariantRepository
{
    /**
     * @var ArrayTransformerInterface
     */
    protected $arrayTransformer;

    public function __construct(ArrayTransformerInterface $arrayTransformer)
    {
        $this->arrayTransformer = $arrayTransformer;
    }

    /**
     * @param OrderProperty $propertyCode
     *
     * @return OrderPropertyVariantCollection
     */
    public function findByProperty(OrderProperty $property): OrderPropertyVariantCollection
    {
        $variants = $this->findBy(['ORDER_PROPS_ID' => $property->getId()]);
        /** @var OrderPropertyVariant $variant */
        foreach ($variants->getIterator() as $variant) {
            $variant->setProperty($property);
        }

        return $variants;
    }

    /**
     * @param array $filter
     *
     * @return OrderPropertyVariantCollection
     */
    public function findBy(array $filter = []): OrderPropertyVariantCollection
    {
        $variants = OrderPropsVariantTable::getList(
            [
                'select' => ['*'],
                'filter' => $filter,
            ]
        );

        $result = new OrderPropertyVariantCollection();
        while ($variant = $variants->fetch()) {
            /** @var OrderPropertyVariant $entity */
            $entity = $this->arrayTransformer->fromArray(
                $variant,
                OrderPropertyVariant::class,
                DeserializationContext::create()->setGroups(['read'])
            );

            $result[$entity->getValue()] = $entity;
        }

        return $result;
    }
}
