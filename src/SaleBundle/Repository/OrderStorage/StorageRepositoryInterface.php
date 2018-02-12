<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SaleBundle\Repository\OrderStorage;

use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Service\OrderService;
use Symfony\Component\Validator\ConstraintViolationListInterface;

interface StorageRepositoryInterface
{
    /**
     * @param int $fuserId
     *
     * @return OrderStorage
     */
    public function findByFuser(int $fuserId): OrderStorage;

    /**
     * @param OrderStorage $storage
     *
     * @return bool
     */
    public function save(OrderStorage $storage, string $step = OrderService::AUTH_STEP): bool;

    /**
     * @param OrderStorage $storage
     *
     * @return bool
     */
    public function clear(OrderStorage $storage): bool;

    /**
     * @param OrderStorage $storage
     * @param string $step
     *
     * @return ConstraintViolationListInterface
     */
    public function validate(OrderStorage $storage, string $step): ConstraintViolationListInterface;

    /**
     * @param OrderStorage $storage
     * @param array $groups
     *
     * @return array
     */
    public function toArray(OrderStorage $storage, array $groups = ['read']): array;
}
