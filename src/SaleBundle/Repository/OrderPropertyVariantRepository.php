<?php

namespace FourPaws\SaleBundle\Repository;

use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Sale\Internals\OrderPropsTable;
use Bitrix\Sale\Internals\OrderPropsVariantTable;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\SaleBundle\Entity\OrderPropertyVariant;
use FourPaws\SaleBundle\Exception\NotFoundException;
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
     * @param string $propertyCode
     * @param array $additionalFilter
     *
     * @return ArrayCollection
     */
    public function getAvailableVariants(string $propertyCode, array $additionalFilter = []): ArrayCollection
    {
        $filter = [
            'PROPERTY.CODE' => $propertyCode,
        ];

        if (!empty($additionalFilter)) {
            $filter = array_merge($filter, $additionalFilter);
        }

        $result = new ArrayCollection();

        $variants = OrderPropsVariantTable::getList(
            [
                'filter'  => $filter,
                'runtime' => [
                    new ReferenceField(
                        'PROPERTY',
                        OrderPropsTable::class,
                        ['=this.ORDER_PROPS_ID' => 'ref.ID'],
                        ['join_type' => 'LEFT']
                    ),
                ],
                'select'  => [
                    'ID',
                    'NAME',
                    'ORDER_PROPS_ID',
                    'VALUE',
                    'SORT',
                    'PROPERTY.CODE',
                ],
            ]
        );

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
