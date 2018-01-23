<?php

namespace FourPaws\SaleBundle\Repository\OrderPropertyEnum;

use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Sale\Internals\OrderPropsTable;
use Bitrix\Sale\Internals\OrderPropsVariantTable;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\SaleBundle\Entity\OrderPropertyEnum;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\DeserializationContext;

abstract class BaseRepository
{
    /**
     * @var ArrayCollection
     */
    protected $availableValues;

    abstract protected function getPropertyCode(): string;

    abstract protected function getAvailableValueCodes(): array;

    /**
     * @var ArrayTransformerInterface
     */
    protected $arrayTransformer;

    public function __construct(ArrayTransformerInterface $arrayTransformer)
    {
        $this->arrayTransformer = $arrayTransformer;
    }

    public function getAvailableVariants(): ArrayCollection
    {
        if (!$this->availableValues instanceof ArrayCollection) {
            $filter = [
                'PROPERTY.CODE' => $this->getPropertyCode(),
            ];
            if (!empty($this->getAvailableValueCodes())) {
                $filter['VALUE'] = $this->getAvailableValueCodes();
            }

            $this->availableValues = new ArrayCollection();

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
                /** @var OrderPropertyEnum $entity */
                $entity = $this->arrayTransformer->fromArray(
                    $variant,
                    OrderPropertyEnum::class,
                    DeserializationContext::create()->setGroups(['read'])
                );

                $this->availableValues[$entity->getValue()] = $entity;
            }
        }

        return $this->availableValues;
    }
}
