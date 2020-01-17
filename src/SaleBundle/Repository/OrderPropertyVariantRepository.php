<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SaleBundle\Repository;

use Bitrix\Main\ArgumentException;
use Bitrix\Sale\Internals\OrderPropsVariantTable;
use FourPaws\SaleBundle\Collection\OrderPropertyVariantCollection;
use FourPaws\SaleBundle\Entity\OrderProperty;
use FourPaws\SaleBundle\Entity\OrderPropertyVariant;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\DeserializationContext;
use WebArch\BitrixCache\BitrixCache;

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
     * @param OrderProperty $property
     *
     * @throws ArgumentException
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
     * @param int $cacheTime
     * @throws ArgumentException
     * @return OrderPropertyVariantCollection
     */
    public function findBy(array $filter = [], int $cacheTime = 86400): OrderPropertyVariantCollection
    {
        $getVariants = function() use ($filter) {
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
        };

        return (new BitrixCache())
            ->withId(__METHOD__ . '_' . md5(serialize($filter)))
            ->withTime($cacheTime)
            ->withTag('OrderPropertyVariantRepository_FindBy')
            ->resultOf($getVariants)['result'];
    }
}
