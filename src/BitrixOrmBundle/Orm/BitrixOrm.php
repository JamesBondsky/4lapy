<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\BitrixOrmBundle\Orm;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FourPaws\BitrixOrmBundle\Exception\NotFoundRepository;

class BitrixOrm
{
    /**
     * @var Collection|D7RepositoryInterface[]
     */
    protected $d7Repositories;

    public function __construct()
    {
        $this->d7Repositories = new ArrayCollection();
    }

    /**
     * @param D7RepositoryInterface $d7Repository
     * @return bool
     */
    public function addD7Repository(D7RepositoryInterface $d7Repository): bool
    {
        return $this->d7Repositories->add($d7Repository);
    }

    /**
     * @param D7RepositoryInterface $d7Repository
     * @return bool
     */
    public function removeD7Repository(D7RepositoryInterface $d7Repository): bool
    {
        return $this->d7Repositories->removeElement($d7Repository);
    }

    /**
     * @param string $entityClass
     * @throws \FourPaws\BitrixOrmBundle\Exception\NotFoundRepository
     * @return D7RepositoryInterface
     */
    public function getD7Repository(string $entityClass): D7RepositoryInterface
    {
        $repository = $this
            ->d7Repositories
            ->filter(function (D7RepositoryInterface $d7Repository) use ($entityClass) {
                return $d7Repository->getEntityClass() === $entityClass;
            })
            ->first();
        if (null === $repository) {
            throw new NotFoundRepository(sprintf('Repository for entity %s not found', $entityClass));
        }
        return $repository;
    }
}
