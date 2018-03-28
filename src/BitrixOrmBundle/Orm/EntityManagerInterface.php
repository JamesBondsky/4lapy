<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\BitrixOrmBundle\Orm;

use Doctrine\Common\Collections\Collection;

/**
 * Class EntityManager
 * @package FourPaws\BitrixOrmBundle\Repository
 * @todo    log
 */
interface EntityManagerInterface
{
    /**
     * @param $id
     * @throws \FourPaws\AppBundle\Exception\RuntimeException
     * @return null|object
     * @todo lock mode
     */
    public function find($id);

    /**
     * @param array $criteria
     * @param array $orderBy
     * @return Collection
     */
    public function findBy(
        array $criteria = [],
        array $orderBy = []
    ): Collection;

    /**
     * @param array $criteria
     * @param array $orderBy
     * @return array[]|Collection
     */
    public function findByRaw(
        array $criteria = [],
        array $orderBy = []
    ): Collection;

    /**
     * @param array $criteria
     * @return int
     */
    public function count(array $criteria = []): int;

    /**
     *
     * @param object $entity
     * @throws \FourPaws\BitrixOrmBundle\Exception\ValidationException
     * @throws \FourPaws\BitrixOrmBundle\Exception\DataManagerException
     * @return bool
     */
    public function create($entity): bool;

    /**
     *
     * @param object $entity
     * @throws \FourPaws\BitrixOrmBundle\Exception\ValidationException
     * @throws \FourPaws\BitrixOrmBundle\Exception\DataManagerException
     * @return bool
     */
    public function update($entity): bool;

    /**
     * @param int $id
     * @throws \FourPaws\BitrixOrmBundle\Exception\DataManagerException
     * @return bool
     */
    public function delete($id): bool;

    /**
     * @return string
     */
    public function getEntityClass(): string;

    /**
     * @param array $select
     */
    public function setSelect(array $select = []): void;

    /**
     * @param array $filter
     */
    public function setFilter(array $filter = []): void;
}
