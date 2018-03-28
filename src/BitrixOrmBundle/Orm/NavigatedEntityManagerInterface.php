<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\BitrixOrmBundle\Orm;

use Doctrine\Common\Collections\Collection;

interface NavigatedEntityManagerInterface extends EntityManagerInterface
{
    /**
     * @inheritdoc
     * @param null|Navigation $navigation
     */
    public function findBy(
        array $criteria = [],
        array $orderBy = [],
        ?Navigation $navigation = null
    ): Collection;
}
