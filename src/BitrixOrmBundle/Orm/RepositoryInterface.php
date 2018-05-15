<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\BitrixOrmBundle\Orm;

use Doctrine\Common\Collections\Collection;

/**
 * Interface RepositoryInterface
 *
 * @package FourPaws\BitrixOrmBundle\Orm
 */
interface RepositoryInterface
{
    /**
     * @param mixed $id
     *
     * @return null|object
     */
    public function find($id);

    /**
     * @param array    $criteria
     * @param array    $orderBy
     *
     * @return Collection|object[]
     */
    public function findBy(
        array $criteria = [],
        array $orderBy = []
    ): Collection;

    /**
     * @param object $object
     * @return bool
     */
    public function create($object): bool;

    /**
     * @param object $object
     * @return bool
     */
    public function update($object): bool;

    /**
     * @param $id
     * @return bool
     */
    public function delete($id): bool;

    /**
     * @param array $criteria
     * @return int
     */
    public function count(array $criteria = []): int;

    /**
     * @return string
     */
    public function getEntityClass(): string ;
}
