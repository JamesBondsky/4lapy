<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\BitrixOrmBundle\Orm;

use Bitrix\Main\Entity\Query;
use Doctrine\Common\Collections\Collection;

/**
 * Class EntityManager
 * @package FourPaws\BitrixOrmBundle\Repository
 * @todo    log
 */
interface D7EntityManagerInterface extends EntityManagerInterface
{
    /**
     * @return Query
     */
    public function getQuery(): Query;

    /**
     * @param Query $query
     * @return Collection|object[]
     */
    public function findByQuery(Query $query): Collection;

    /**
     * @param Query $query
     * @return array[]|Collection
     */
    public function findByQueryRaw(Query $query): Collection;

    /**
     * @inheritdoc
     * @param null|int $limit
     * @param null|int $offset
     */
    public function findBy(
        array $criteria = [],
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): Collection;

    /**
     * @inheritdoc
     * @param null|int $limit
     * @param null|int $offset
     * @return array[]|Collection
     */
    public function findByRaw(
        array $criteria = [],
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): Collection;
}
