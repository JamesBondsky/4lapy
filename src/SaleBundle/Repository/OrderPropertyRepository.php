<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SaleBundle\Repository;

use Bitrix\Sale\Internals\OrderPropsTable;
use FourPaws\SaleBundle\Collection\OrderPropertyCollection;
use FourPaws\SaleBundle\Entity\OrderProperty;
use FourPaws\SaleBundle\Exception\NotFoundException;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;

class OrderPropertyRepository
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
     * @param int $id
     *
     * @throws NotFoundException
     * @return mixed
     */
    public function findById(int $id)
    {
        $property = $this->findBy(['ID' => $id])->first();
        if (!$property) {
            throw new NotFoundException(sprintf('Property with id %s not found', $id));
        }

        return $property;
    }

    /**
     * @param string $code
     *
     * @throws NotFoundException
     * @return mixed
     */
    public function findByCode(string $code)
    {
        $property = $this->findBy(['CODE' => $code])->first();
        if (!$property) {
            throw new NotFoundException(sprintf('Property with code %s not found', $code));
        }

        return $property;
    }

    /**
     * @param array $filter
     *
     * @return OrderPropertyCollection
     */
    public function findBy(array $filter = []): OrderPropertyCollection
    {
        $defaultFilter = ['ACTIVE' => 'Y'];
        $filter = array_merge($defaultFilter, $filter);

        $properties = OrderPropsTable::query()
                                     ->setSelect(['*'])
                                     ->setFilter($filter)
                                     ->exec();

        return new OrderPropertyCollection(
            $this->arrayTransformer->fromArray(
                $properties->fetchAll(),
                sprintf('array<%s>', OrderProperty::class),
                DeserializationContext::create()->setGroups(['read'])
            )
        );
    }

    /**
     * @param OrderProperty $property
     * @param array $groups
     *
     * @return array
     */
    public function toArray(OrderProperty $property, array $groups = ['read']): array
    {
        return $this->arrayTransformer->toArray($property, SerializationContext::create()->setGroups($groups));
    }
}
