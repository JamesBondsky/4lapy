<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\BitrixOrmBundle\Orm;

/**
 * Interface EntityRepositoryInterface
 *
 * @package FourPaws\BitrixOrmBundle\Orm
 */
interface EntityRepositoryInterface extends RepositoryInterface
{
    /**
     * @return EntityManagerInterface
     */
    public function getEntityManager(): EntityManagerInterface;
}
