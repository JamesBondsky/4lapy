<?php

namespace FourPaws\StoreBundle\Repository;

use FourPaws\StoreBundle\Entity\Base as BaseEntity;
use FourPaws\StoreBundle\Collection\BaseCollection;

interface RepositoryInterface
{
    /**
     * @param \FourPaws\StoreBundle\Entity\Base $entity
     *
     * @return bool
     */
    public function create(BaseEntity $entity): bool;

    /**
     * @param int $id
     *
     * @return BaseEntity|null
     */
    public function find(int $id);

    /**
     * @param array $criteria
     * @param array $orderBy
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return BaseCollection
     */
    public function findBy(
        array $criteria = [],
        array $orderBy = [],
        int $limit = null,
        int $offset = null
    );

    /**
     * @param $entity
     *
     * @return bool
     */
    public function update(BaseEntity $entity): bool;

    /**
     * @param int $id
     *
     * @return bool
     */
    public function delete(int $id): bool;
}
