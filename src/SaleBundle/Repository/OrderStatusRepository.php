<?php

/**
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\SaleBundle\Repository;

use Bitrix\Sale\Internals\StatusLangTable;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\SaleBundle\Entity\OrderStatus;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;

class OrderStatusRepository
{
    /**
     * @var ArrayTransformerInterface
     */
    protected $arrayTransformer;

    /**
     * @var ArrayCollection
     */
    protected static $statusesCollection;

    public function __construct(ArrayTransformerInterface $arrayTransformer)
    {
        $this->arrayTransformer = $arrayTransformer;
    }

    /**
     * @param string $id
     * @return OrderStatus
     */
    public function findById(string $id): OrderStatus
    {
        return $this
            ->getAll()
            ->filter(function($status) use($id) {
                /** @var $status OrderStatus */
                return $status->getId() === $id;
            })
            ->current();
    }

    /**
     * @return ArrayCollection
     */
    protected function getAll(): ArrayCollection
    {
        if (!empty(self::$statusesCollection)) {
            return self::$statusesCollection;
        }

        $filter = ['LID' => 'ru'];

        $statuses = StatusLangTable::query()
            ->setSelect(['*'])
            ->setFilter($filter)
            ->exec();

        self::$statusesCollection = new ArrayCollection(
            $this->arrayTransformer->fromArray(
                $statuses->fetchAll(),
                sprintf('array<%s>', OrderStatus::class),
                DeserializationContext::create()->setGroups(['read'])
            )
        );

        return self::$statusesCollection;
    }

    /**
     * @param OrderStatus $status
     * @param array $groups
     *
     * @return array
     */
    public function toArray(OrderStatus $status, array $groups = ['read']): array
    {
        return $this->arrayTransformer->toArray($status, SerializationContext::create()->setGroups($groups));
    }
}
