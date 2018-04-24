<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\BitrixOrmBundle\Orm;

use Doctrine\Common\Collections\Collection;

/**
 * Interface D7RepositoryInterface
 *
 * @package FourPaws\BitrixOrmBundle\Orm
 */
interface D7RepositoryInterface extends RepositoryInterface
{
    /**
     * @param array    $criteria
     * @param array    $orderBy
     * @param null|int $limit
     * @param null|int $offset
     *
     * @return Collection|object[]
     */
    public function findBy(
        array $criteria = [],
        array $orderBy = [],
        ?int $limit = null,
        ?int $offset = null
    ): Collection;
}
